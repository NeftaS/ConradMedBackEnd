<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Enviar correo de bienvenida
     */
    public function sendWelcomeEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = \App\Models\User::find($request->user_id);
            
            if (!$this->emailService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de correos no está configurado correctamente'
                ], 500);
            }

            $result = $this->emailService->sendWelcomeEmail($user);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Correo de bienvenida enviado exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo de bienvenida'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar confirmación de cita
     */
    public function sendCitaConfirmation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cita_id' => 'required|exists:citas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cita = \App\Models\Cita::with(['user', 'doctor'])->find($request->cita_id);
            
            if (!$this->emailService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de correos no está configurado correctamente'
                ], 500);
            }

            $result = $this->emailService->sendCitaConfirmation($cita, $cita->user, $cita->doctor);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Confirmación de cita enviada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar la confirmación de cita'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar confirmación de orden
     */
    public function sendOrdenConfirmation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orden_id' => 'required|exists:ordenes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $orden = \App\Models\Orden::with(['user', 'productos'])->find($request->orden_id);
            
            if (!$this->emailService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de correos no está configurado correctamente'
                ], 500);
            }

            $result = $this->emailService->sendOrdenConfirmation($orden, $orden->user);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Confirmación de orden enviada exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar la confirmación de orden'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar correo personalizado
     */
    public function sendCustomEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'view' => 'required|string',
            'data' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if (!$this->emailService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio de correos no está configurado correctamente'
                ], 500);
            }

            $result = $this->emailService->sendCustomEmail(
                $request->to,
                $request->subject,
                $request->view,
                $request->data ?? []
            );

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Correo personalizado enviado exitosamente'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo personalizado'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado del servicio de correos
     */
    public function checkEmailService(): JsonResponse
    {
        try {
            $isConfigured = $this->emailService->isConfigured();
            
            return response()->json([
                'success' => true,
                'configured' => $isConfigured,
                'message' => $isConfigured ? 'Servicio de correos configurado correctamente' : 'Servicio de correos no configurado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el servicio de correos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

