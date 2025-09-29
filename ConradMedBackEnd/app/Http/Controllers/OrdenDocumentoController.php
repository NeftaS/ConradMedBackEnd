<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdenDocumentoController extends Controller
{
    /**
     * POST /api/ordenes/{order_id}/documento
     *
     * Recibe un archivo en base64 y lo guarda como PDF accesible públicamente.
     */
    public function guardarDocumento(Request $request, $order_id)
    {
        $request->validate([
            'mime_type'      => 'required|string',
            'filename_hint'  => 'required|string',
            'content_base64' => 'required|string',
        ]);

        try {
            $mimeType = $request->input('mime_type');
            $filename = $request->input('filename_hint');
            $base64   = $request->input('content_base64');

            // Decodificar base64
            $binaryData = base64_decode($base64);

            if ($binaryData === false) {
                return response()->json([
                    'success' => false,
                    'error'   => 'El contenido base64 no es válido.',
                ], 400);
            }

            // Ruta donde se guardará el PDF
            $path = "ordenes/{$order_id}/" . $filename;

            // Guardar en storage/app/public/ordenes/{order_id}/
            Storage::disk('public')->put($path, $binaryData);

            // Generar URL pública
            $pdfUrl = Storage::url($path);

            return response()->json([
                'success'  => true,
                'order_id' => $order_id,
                'pdf_url'  => url($pdfUrl),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
