<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Dompdf\Dompdf;

class PdfMailController extends Controller
{
    /**
     * POST /api/mail/pdf-from-text
     *
     * Recibe (JSON):
     * - to: correo destino
     * - subject: asunto
     * - title: título del PDF (sin .pdf)
     * - text_base64: contenido en base64 (texto plano, PDF o DOCX; acepta data:URI)
     * - body_html: cuerpo del correo en HTML
     *
     * Responde JSON con { ok, message|error, detail? }
     */
    public function sendPdfFromText(Request $request)
    {
        try {
            $data = $request->validate([
                'to'          => 'required|email',
                'subject'     => 'required|string',
                'title'       => 'required|string',
                'text_base64' => 'required|string',
                'body_html'   => 'required|string',
            ]);

            $to         = $data['to'];
            $subject    = $data['subject'];
            $title      = trim($data['title']);
            $bodyHtml   = $data['body_html'];
            $textBase64 = $data['text_base64'];

            // 1) Decodificar Base64 (acepta data:URI)
            if (str_starts_with($textBase64, 'data:')) {
                $textBase64 = preg_replace('#^data:.*?;base64,#', '', $textBase64);
            }
            $decoded = base64_decode($textBase64, true);
            if ($decoded === false) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Base64 inválido'
                ], 422);
            }

            // 2) Detectar tipo (PDF/DOCX/Texto)
            $head4  = substr($decoded, 0, 4);
            $isPdf  = ($head4 === '%PDF');
            $isDocx = (substr($decoded, 0, 2) === 'PK'); // DOCX (ZIP)

            if ($isPdf) {
                // PDF directo
                $pdfContent = $decoded;

            } elseif ($isDocx) {
                // DOCX → PDF con LibreOffice
                $pdfContent = $this->convertDocxToPdf($decoded);

            } else {
                // Texto plano → generar PDF con Dompdf
                $pdfContent = $this->renderTextToPdf($title, $decoded);
            }

            // 3) Enviar correo con Mail::send usando el mailer resend
            Mail::send([], [], function ($m) use ($to, $subject, $bodyHtml, $pdfContent, $title) {
                $m->to($to)
                  ->subject($subject)
                  ->html($bodyHtml)
                  ->attachData($pdfContent, $title . '.pdf', [
                      'mime' => 'application/pdf',
                  ]);
            });

            return response()->json([
                'ok'      => true,
                'message' => "Correo enviado correctamente a $to",
            ]);

        } catch (\Throwable $e) {
            Log::error('Error al enviar PDF por correo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok'     => false,
                'error'  => 'Excepción al enviar correo.',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convierte un DOCX (bytes) a PDF (bytes) usando LibreOffice.
     * Requiere libreoffice instalado en la imagen/servidor.
     */
    private function convertDocxToPdf(string $docxBytes): string
    {
        $tempDocx = storage_path('app/' . uniqid('doc_', true) . '.docx');
        file_put_contents($tempDocx, $docxBytes);

        $outputDir = storage_path('app');
        $cmd = sprintf(
            'libreoffice --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($outputDir),
            escapeshellarg($tempDocx)
        );
        $out = shell_exec($cmd);

        $pdfFile = substr($tempDocx, 0, -5) . '.pdf';
        if (!file_exists($pdfFile)) {
            @unlink($tempDocx);
            Log::error('LibreOffice no generó PDF', ['cmd' => $cmd, 'out' => $out]);
            throw new \Exception('Error al convertir DOCX a PDF (LibreOffice no disponible o falló la conversión).');
        }

        $pdfContent = file_get_contents($pdfFile);

        @unlink($tempDocx);
        @unlink($pdfFile);

        return $pdfContent;
    }

    /**
     * Renderiza texto plano a PDF (bytes) usando Dompdf.
     */
    private function renderTextToPdf(string $title, string $plainBytes): string
    {
        $text = $plainBytes;
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = @mb_convert_encoding($text, 'UTF-8', 'auto') ?: $text;
        }

        $html = '<!doctype html>
            <html><head><meta charset="utf-8"><style>
                body{font-family:DejaVu Sans, sans-serif; font-size:12pt; line-height:1.5; color:#111}
                h1{font-size:18pt; margin:0 0 12px}
                pre{white-space:pre-wrap; word-wrap:break-word}
                .wrap{padding:24px}
            </style></head><body>
            <div class="wrap">
                <h1>' . e($title) . '</h1>
                <pre>' . e($text) . '</pre>
            </div>
            </body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
