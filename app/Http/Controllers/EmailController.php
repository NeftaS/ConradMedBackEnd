<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResendPhpService;

class EmailController extends Controller
{
    protected $resendService;

    public function __construct(ResendPhpService $resendService)
    {
        $this->resendService = $resendService;
    }

    public function sendWelcomeEmail()
    {
        try {
            // Verificar configuración
            if (!$this->resendService->isConfigured()) {
                return response()->json([
                    'error' => 'Servicio de email no configurado correctamente'
                ], 500);
            }

            $htmlContent = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Bienvenido a ConradMed</title>
            </head>
            <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                    <h1 style="color: #2c3e50;">¡Bienvenido a ConradMed!</h1>
                    <p>Hola,</p>
                    <p>Nos complace darte la bienvenida a ConradMed. Tu cuenta ha sido creada exitosamente.</p>
                    <p>Con ConradMed podrás:</p>
                    <ul>
                        <li>Agendar citas médicas</li>
                        <li>Realizar pedidos de análisis</li>
                        <li>Acceder a tu historial médico</li>
                        <li>Recibir notificaciones importantes</li>
                    </ul>
                    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                    <p>Saludos,<br>El equipo de ConradMed</p>
                </div>
            </body>
            </html>';

            $result = $this->resendService->sendEmail(
                'correorespaldouno@gmail.com',
                'Prueba envio de correos - ConradMed',
                $htmlContent,
                'Prueba Completada - Bienvenido a ConradMed',
                'Notificaciones ConRadMed <notificacionesconradmed@conradmed.com.mx>'
            );

            if ($result['success']) {
                return response()->json([
                    'message' => 'Correo enviado correctamente.',
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'error' => 'Error al enviar el correo: ' . $result['error']
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al enviar el correo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email personalizado
     */
    public function sendCustomEmail(Request $request)
    {
        $validated = $request->validate([
            'to'           => ['required','email'],
            'subject'      => ['required','string','max:200'],
            'html_content' => ['required','string'],
            'text_content' => ['nullable','string'],
            'from'         => ['nullable','regex:/^[^<>]+<[^<>@\s]+@[^<>@\s]+\.[^<>@\s]+>$/'],
        ]);

        if (!$this->resendService->isConfigured()) {
            return response()->json(['error' => 'Servicio de email no configurado'], 503);
        }

        $result = $this->resendService->sendEmail(
            $validated['to'],
            $validated['subject'],
            $validated['html_content'],
            $validated['text_content'] ?? null,
            $validated['from'] ?? null
        );

        if ($result['success'] ?? false) {
            return response()->json(['message' => 'Email enviado', 'id' => data_get($result,'data.id')], 200);
        }
        return response()->json(['error' => $result['error'] ?? 'Fallo desconocido'], 502);
    }


    /**
     * Verificar estado del servicio
     */
    public function checkServiceStatus()
    {
        try {
            $configInfo = $this->resendService->getConfigurationInfo();
            $apiStatus = $this->resendService->checkApiStatus();

            return response()->json([
                'configured' => $this->resendService->isConfigured(),
                'configuration' => $configInfo,
                'api_status' => $apiStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al verificar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener dominios verificados
     */
    public function getDomains()
    {
        try {
            $domains = $this->resendService->getDomains();
            return response()->json($domains);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener dominios: ' . $e->getMessage()
            ], 500);
        }
    }
}

