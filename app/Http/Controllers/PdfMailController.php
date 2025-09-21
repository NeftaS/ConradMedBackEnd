<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;

class PdfMailController extends Controller
{
    /**
     * POST /api/mail/pdf-from-text
     *
     * Recibe:
     * - to: correo destino
     * - subject: asunto
     * - title: título del PDF
     * - text_base64: contenido en base64 (texto o archivo)
     * - body_html: cuerpo del correo en HTML
     */
    public function sendPdfFromText(Request $request)
    {
        try {
            $request->validate([
                'to' => 'required|email',
                'subject' => 'required|string',
                'title' => 'required|string',
                'text_base64' => 'required|string',
                'body_html' => 'required|string',
            ]);

            $to = $request->input('to');
            $subject = $request->input('subject');
            $title = $request->input('title');
            $bodyHtml = $request->input('body_html');
            $textBase64 = $request->input('text_base64');

            // Decodificar contenido Base64
            $decoded = base64_decode($textBase64);

            // Detectar tipo de contenido
            $isPdf = str_starts_with($decoded, "%PDF");
            $isDocx = substr($decoded, 0, 2) === "PK"; // DOCX = ZIP

            if ($isPdf) {
                // PDF recibido → usarlo directo
                $pdfContent = $decoded;

            } elseif ($isDocx) {
                // Guardar DOCX temporal
                $tempDocx = storage_path("app/".uniqid().".docx");
                file_put_contents($tempDocx, $decoded);

                // Convertir DOCX a PDF con LibreOffice
                $outputDir = storage_path("app");
                exec("libreoffice --headless --convert-to pdf --outdir $outputDir $tempDocx");

                // Obtener PDF generado
                $pdfFile = str_replace(".docx", ".pdf", $tempDocx);
                if (!file_exists($pdfFile)) {
                    throw new \Exception("Error al convertir DOCX a PDF");
                }

                $pdfContent = file_get_contents($pdfFile);

                // Limpiar archivos temporales
                unlink($tempDocx);
                unlink($pdfFile);

            } else {
                // Texto plano → generar PDF con Dompdf puro
                $plainText = $decoded;

                $dompdf = new Dompdf();
                $dompdf->loadHtml('<h1>'.$title.'</h1><p>'.nl2br(e($plainText)).'</p>');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $pdfContent = $dompdf->output();
            }

            // Enviar correo con PDF adjunto
            Mail::send([], [], function ($m) use ($to, $subject, $bodyHtml, $pdfContent, $title) {
                $m->to($to)
                  ->subject($subject)
                  ->html($bodyHtml)
                  ->attachData($pdfContent, $title.'.pdf', [
                      'mime' => 'application/pdf',
                  ]);
            });

            return response()->json([
                "ok" => true,
                "message" => "Correo enviado correctamente a $to"
            ]);

        } catch (\Throwable $e) {
            Log::error("Error al enviar PDF por correo", ["error" => $e->getMessage()]);
            return response()->json([
                "ok" => false,
                "error" => "Excepción al enviar correo.",
                "detail" => $e->getMessage()
            ], 500);
        }
    }
}
