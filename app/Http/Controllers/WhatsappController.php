<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Dompdf\Dompdf;
use Illuminate\Support\Str;
use App\Jobs\DeletePublicFile;

class WhatsappController extends Controller
{
    /** =========================
     *  Config helpers
     *  ========================= */
    private function http(): Client
    {
        return new Client([
            'base_uri' => 'https://api-ws.wasapi.io/api/v1/',
            'timeout'  => 25,
        ]);
    }

    private function wasapiHeaders(): array
    {
        return [
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
            'authorization' => 'Bearer ' . config('services.wasapi.token'),
        ];
    }

    /** Falla temprano si falta config crítica */
    private function assertWasapiConfig(): void
    {
        $required = [
            'services.wasapi.token'       => config('services.wasapi.token'),
            'services.wasapi.from_id'     => config('services.wasapi.from_id'),
            'services.wasapi.template_id' => config('services.wasapi.template_id'),
        ];

        $missing = array_keys(array_filter($required, fn($v) => empty($v)));
        if (!empty($missing)) {
            throw new \RuntimeException('Config faltante o vacía: ' . implode(', ', $missing));
        }

        if (!filter_var($required['services.wasapi.from_id'], FILTER_VALIDATE_INT)
            || (int) $required['services.wasapi.from_id'] <= 0) {
            throw new \RuntimeException('services.wasapi.from_id inválido (debe ser entero > 0).');
        }
    }

    /** =========================
     *  1) Enviar texto plano
     *  ========================= */
    public function enviarTexto(Request $request, $order_id)
    {
        $request->validate([
            'wa_id'   => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            $this->assertWasapiConfig();

            $resp = $this->http()->post('whatsapp-messages', [
                'headers' => $this->wasapiHeaders(),
                'body'    => json_encode([
                    'wa_id'   => trim($request->wa_id),
                    'from_id' => (int) config('services.wasapi.from_id'),
                    'message' => $request->message,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => (string) $order_id,
                'response' => json_decode($resp->getBody(), true),
            ]);
        } catch (\Throwable $e) {
            Log::error('enviarTexto error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Internal error',
            ], 500);
        }
    }

    /** =========================
     *  2) Consultar conversación por wa_id (robusto)
     *  ========================= */
    private function getConversationByWaidRaw(string $wa_id): array
    {
        $resp = $this->http()->get("whatsapp-messages/{$wa_id}", [
            'headers'     => $this->wasapiHeaders(),
            'http_errors' => false,
        ]);

        $code = $resp->getStatusCode();
        $json = json_decode($resp->getBody(), true);

        if ($code >= 400) {
            return [
                'http_code' => $code,
                'raw'       => $json,
                'error'     => $json['message'] ?? 'Wasapi error',
            ];
        }

        $data = [];
        if (isset($json['data']) && is_array($json['data'])) {
            $data = $json['data'];
        }

        // Orden ascendente por created_at si existe
        if (!empty($data)) {
            usort($data, function ($a, $b) {
                $ta = strtotime($a['created_at'] ?? '0');
                $tb = strtotime($b['created_at'] ?? '0');
                return $ta <=> $tb;
            });
        }

        return [
            'http_code' => $code,
            'raw'       => ['data' => $data],
        ];
    }

    /** =========================
     *  3) Si status === failed → mandar plantilla con documento
     *     y borrar archivo con Job diferido
     *  ========================= */
    public function checarStatusYEnviarSiFailed(Request $request)
    {
        $request->validate([
            'wa_id'          => 'required|string',
            'client_name'    => 'required|string',
            'filename_hint'  => 'required|string',
            'content_base64' => 'required|string',
            'body_vars'      => 'nullable|array',
        ]);

        $wa_id         = trim((string) $request->wa_id);
        $clientName    = (string) $request->client_name;
        $filenameHint  = (string) $request->filename_hint;
        $contentBase64 = (string) $request->content_base64;
        $bodyVars      = $request->input('body_vars', []);

        try {
            $this->assertWasapiConfig();

            // 1) Consultar historial en Wasapi
            $result   = $this->getConversationByWaidRaw($wa_id);
            $httpCode = $result['http_code'] ?? 0;
            $raw      = $result['raw'] ?? [];

            // Si Wasapi respondió error, devolvemos 502 con detalle
            if ($httpCode >= 400) {
                return response()->json([
                    'success'   => false,
                    'wa_id'     => $wa_id,
                    'error'     => 'Wasapi HTTP ' . $httpCode,
                    'raw'       => $result,
                ], 502);
            }

            // 2) Determinar último status
            $status = 'no_messages';
            $items  = $raw['data'] ?? [];
            if (is_array($items) && !empty($items)) {
                $last = end($items);
                $status = strtolower($last['status']
                    ?? $last['message_status']
                    ?? $last['delivery_status']
                    ?? 'unknown');
            }

            // 3) Reglas
            if ($status === 'no_messages') {
                return response()->json([
                    'success'   => true,
                    'wa_id'     => $wa_id,
                    'status'    => $status,
                    'action'    => 'ignored_no_messages',
                    'http_code' => $httpCode,
                ]);
            }

            if ($status !== 'failed') {
                return response()->json([
                    'success'   => true,
                    'wa_id'     => $wa_id,
                    'status'    => $status,
                    'action'    => 'none',
                    'http_code' => $httpCode,
                ]);
            }

            // === status === failed → preparar documento y enviar plantilla ===

            // 4) Validar tamaño máximo (opcional por .env)
            $maxMb = (int) env('WHATSAPP_MAX_DOC_MB', 8);
            // Estimación base64 → bytes ~ len * 3/4
            $approxBytes = (int) (strlen(preg_replace('/\s+/', '', $contentBase64)) * 0.75);
            if ($maxMb > 0 && $approxBytes > $maxMb * 1024 * 1024) {
                throw new \RuntimeException("Archivo excede {$maxMb}MB.");
            }

            // 5) Guardar base64 temporal
            $safeBase = Str::slug(pathinfo($filenameHint, PATHINFO_FILENAME)) ?: 'orden-medica';
            $ext      = strtolower(pathinfo($filenameHint, PATHINFO_EXTENSION) ?: 'pdf');

            $tmpDir = storage_path('app/tmp-whatsapp');
            if (!is_dir($tmpDir)) @mkdir($tmpDir, 0775, true);

            $sourcePath = $tmpDir . '/' . $safeBase . '-' . uniqid('', true) . '.' . $ext;
            $this->writeBase64ToFile($contentBase64, $sourcePath);

            // 6) Convertir a PDF si hace falta (según bandera)
            $pdfPath = $sourcePath;
            if ($ext !== 'pdf') {
                $enableConvert = filter_var(env('WHATSAPP_ENABLE_CONVERT', false), FILTER_VALIDATE_BOOL);
                if (!$enableConvert) {
                    @unlink($sourcePath);
                    throw new \RuntimeException('Solo se aceptan PDFs (conversión deshabilitada).');
                }
                $pdfPath = $this->convertToPdfWithLibreOffice($sourcePath, $tmpDir);
                @unlink($sourcePath);
            }

            // 7) Subir a storage público EXACTO: storage/app/public/ordenes
            $disk      = 'public';
            $dir       = 'ordenes';
            $finalName = $safeBase . '-' . date('YmdHis') . '.pdf';

            Storage::disk($disk)->makeDirectory($dir);
            $stream = fopen($pdfPath, 'r');
            Storage::disk($disk)->put("$dir/$finalName", $stream);
            if (is_resource($stream)) fclose($stream);
            @unlink($pdfPath);

            // URL pública (requiere APP_URL correcto + storage:link)
            $publicUrl   = asset('storage/' . $dir . '/' . $finalName);
            $fileNameOut = pathinfo($filenameHint, PATHINFO_FILENAME) . '.pdf';

            // 8) body_vars por defecto si no vienen
            if (empty($bodyVars)) {
                $bodyVars = [
                    ['text' => '{{1}}', 'val' => $clientName],
                ];
            }

            // 9) Validaciones locales antes del envío
            $templateId = (string) config('services.wasapi.template_id');
            $fromId     = (int) config('services.wasapi.from_id');
            if (!$templateId || $fromId <= 0) {
                throw new \RuntimeException('Config inválida: template_id o from_id');
            }

            // 10) Enviar plantilla (send-template)
            $tplResp = $this->http()->post('whatsapp-messages/send-template', [
                'headers' => $this->wasapiHeaders(),
                'body'    => json_encode([
                    "contact_type"        => "phone",
                    "chatbot_status"      => "disable",
                    "conversation_status" => "unchanged",
                    "recipients"          => $wa_id,
                    "file"                => "document",
                    "url_file"            => $publicUrl,
                    "file_name"           => $fileNameOut,
                    "body_vars"           => $bodyVars,
                    "from_id"             => $fromId,
                    "template_id"         => $templateId,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $tplData = json_decode($tplResp->getBody(), true);

            // 11) Borrado diferido con Job (sin bloquear la respuesta)
            $relative = $dir . '/' . $finalName; // relativo dentro de disk('public')
            $ttlMin   = (int) env('WHATSAPP_FILE_TTL_MIN', 10);
            DeletePublicFile::dispatch($relative)->delay(now()->addMinutes($ttlMin));

            return response()->json([
                'success'         => true,
                'wa_id'           => $wa_id,
                'status'          => $status,
                'action'          => 'send-template',
                'file_url'        => $publicUrl,
                'template_result' => $tplData,
                'http_code'       => $httpCode,
            ]);
        } catch (\Throwable $e) {
            Log::error('checarStatusYEnviarSiFailed error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /** =========================
     *  Helpers
     *  ========================= */
    private function writeBase64ToFile(string $base64, string $path): void
    {
        $clean = trim($base64);
        $clean = trim($clean, "<> \t\n\r\0\x0B");
        if (str_contains($clean, ',')) {
            $clean = explode(',', $clean, 2)[1]; // soporta data:*;base64,
        }
        $clean = preg_replace('/\s+/', '', $clean);

        $data = base64_decode($clean, true);
        if ($data === false) {
            throw new \InvalidArgumentException('content_base64 inválido.');
        }
        if (file_put_contents($path, $data) === false) {
            throw new \RuntimeException('No se pudo escribir archivo temporal.');
        }
    }

    private function convertToPdfWithLibreOffice(string $sourcePath, string $outDir): string
    {
        $cmd = sprintf(
            'soffice --headless --norestore --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($outDir),
            escapeshellarg($sourcePath)
        );
        exec($cmd, $out, $exit);
        if ($exit !== 0) {
            throw new \RuntimeException('LibreOffice falló: ' . implode("\n", $out));
        }
        $expected = $outDir . '/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
        if (file_exists($expected)) return $expected;

        $candidates = glob($outDir . '/*.pdf') ?: [];
        if (empty($candidates)) {
            throw new \RuntimeException('No se generó PDF.');
        }
        usort($candidates, fn($a, $b) => filemtime($b) <=> filemtime($a));
        return $candidates[0];
    }

    /** =========================
     *  4) Único endpoint público pedido: consultarStatusFinal
     *     - Lee el último status
     *     - Si es failed, genera PDF desde texto base64 y envía plantilla
     *  ========================= */
    public function consultarStatusFinal(Request $request)
    {
        $request->validate([
            'wa_id'         => 'required|string',
            'client_name'   => 'required|string',
            'filename_hint' => 'required|string',   // ej: "orden_123.pdf"
            'text_base64'   => 'required|string',   // texto en base64 (no PDF)
        ]);

        try {
            // Valida config crítica (token, from_id, template_id)
            $this->assertWasapiConfig();

            $waId       = trim((string) $request->wa_id);
            $clientName = (string) $request->client_name;
            $fileHint   = (string) $request->filename_hint;
            $textB64    = (string) $request->text_base64;

            // === 1) Consultar conversación y obtener ÚLTIMO status ===
            $conv   = $this->getConversationByWaidRaw($waId);
            $code   = (int) ($conv['http_code'] ?? 0);
            $items  = (array) (($conv['raw']['data'] ?? []) ?: []);

            if ($code >= 400) {
                return response()->json([
                    'success'   => false,
                    'wa_id'     => $waId,
                    'status'    => 'unknown',
                    'http_code' => $code,
                    'raw'       => $conv,
                ], 502);
            }

            if (!empty($items)) {
                usort($items, fn($a,$b) => strtotime($a['created_at'] ?? '0') <=> strtotime($b['created_at'] ?? '0'));
            }

            $status = 'no_messages';
            if (!empty($items)) {
                $last   = end($items);
                $status = strtolower($last['status']
                         ?? $last['message_status']
                         ?? $last['delivery_status']
                         ?? 'unknown');
            }

            // === 2) Si NO es failed → no hacer nada ===
            if ($status !== 'failed') {
                return response()->json([
                    'success' => true,
                    'wa_id'   => $waId,
                    'status'  => $status,    // sent | delivered | read | no_messages | unknown
                    'action'  => 'none',
                ], 200);
            }

            // === 3) FAILED → generar PDF desde texto base64 ===
            $clean   = preg_replace('/\s+/', '', preg_replace('/^.*?,/', '', trim($textB64))); // soporta data:*;base64,
            $decoded = base64_decode($clean, true);
            if ($decoded === false) {
                return response()->json([
                    'success' => false,
                    'wa_id'   => $waId,
                    'status'  => 'failed',
                    'error'   => 'text_base64 inválido',
                ], 422);
            }

            $safeText = htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Documento</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12pt; color:#222; margin: 24px; }
  .pre  { white-space: pre-wrap; line-height: 1.45; }
</style>
</head>
<body>
  <div class="pre">{$safeText}</div>
</body>
</html>
HTML;

            $dompdf = new Dompdf(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4');
            $dompdf->render();
            $pdfBytes = $dompdf->output();

            // === 4) Guardar en storage/app/public/ordenes ===
            $safeBase  = Str::slug(pathinfo($fileHint, PATHINFO_FILENAME)) ?: 'documento';
            $dir       = 'ordenes';
            $finalName = $safeBase . '-' . date('YmdHis') . '.pdf';

            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put("$dir/$finalName", $pdfBytes);

            // URL pública usando Storage::url (requiere `php artisan storage:link`)
            $publicBase = rtrim(config('app.url') ?: env('APP_URL'), '/'); // ej: https://xxx.ngrok-free.app
            $publicUrl  = $publicBase . Storage::url("$dir/$finalName");   // => /storage/ordenes/...

            // === 5) Borrado diferido (TTL por .env, default 1440 min = 24h) ===
            $ttlMin = (int) env('WHATSAPP_FILE_TTL_MIN', 1440);
            DeletePublicFile::dispatch("$dir/$finalName")->delay(now()->addMinutes($ttlMin));

            // === 6) body_vars ({{1}} = nombre) ===
            $bodyVars = [['text' => '{{1}}', 'val' => $clientName]];

            // === 7) Enviar plantilla con adjunto ===
            $templateId = (string) config('services.wasapi.template_id'); // ed63d109-...
            $fromId     = (int) config('services.wasapi.from_id');        // 15200

            if (!$templateId || $fromId <= 0) {
                return response()->json([
                    'success' => false,
                    'wa_id'   => $waId,
                    'status'  => $status,
                    'error'   => 'Config inválida: template_id o from_id',
                ], 500);
            }

            $tplResp = $this->http()->post('whatsapp-messages/send-template', [
                'headers' => $this->wasapiHeaders(),
                'body'    => json_encode([
                    "contact_type"        => "phone",
                    "chatbot_status"      => "disable",
                    "conversation_status" => "unchanged",
                    "recipients"          => $waId,
                    "file"                => "document",
                    "url_file"            => $publicUrl,
                    "file_name"           => pathinfo($fileHint, PATHINFO_FILENAME) . '.pdf',
                    "body_vars"           => $bodyVars,
                    "from_id"             => $fromId,
                    "template_id"         => $templateId,
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $tplData = json_decode($tplResp->getBody(), true);

            return response()->json([
                'success'         => true,
                'wa_id'           => $waId,
                'status'          => $status,           // failed
                'action'          => 'send-template',
                'file_url'        => $publicUrl,
                'template_result' => $tplData,
                'delete_in'       => $ttlMin . 'm',
            ], 200);

        } catch (\Throwable $e) {
            Log::error('consultarStatusFinal error', [
                'wa_id' => $request->wa_id ?? null,
                'msg'   => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'wa_id'   => $request->wa_id ?? null,
                'status'  => 'unknown',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
