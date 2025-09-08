<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\PasswordResetCode;
use App\Services\EmailService;

class PasswordResetController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Solicitar código de restablecimiento de contraseña
     */
    public function requestResetCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;

            // Verificar si el usuario existe
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una cuenta con este correo electrónico'
                ], 404);
            }

            // Limpiar códigos expirados
            PasswordResetCode::cleanExpiredCodes();

            // Crear nuevo código
            $resetCode = PasswordResetCode::createCode($email);

            // Enviar correo con el código
            $emailSent = $this->emailService->sendPasswordResetCode($user, $resetCode->code);

            if (!$emailSent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo electrónico'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Código de restablecimiento enviado a tu correo electrónico',
                'expires_in_minutes' => 15
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al solicitar código de restablecimiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar código y restablecer contraseña
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'code' => 'required|string|size:6',
                'password' => 'required|string|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $code = $request->code;
            $password = $request->password;

            // Verificar si el usuario existe
            $user = User::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una cuenta con este correo electrónico'
                ], 404);
            }

            // Verificar el código
            if (!PasswordResetCode::verifyCode($email, $code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código inválido o expirado'
                ], 400);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($password)
            ]);

            // Limpiar códigos expirados
            PasswordResetCode::cleanExpiredCodes();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al restablecer contraseña: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un código es válido (sin usarlo)
     */
    public function verifyCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->email;
            $code = $request->code;

            // Verificar si el código existe y es válido
            $resetCode = PasswordResetCode::where('email', $email)
                ->where('code', $code)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$resetCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código inválido o expirado'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Código válido',
                'expires_at' => $resetCode->expires_at
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al verificar código: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
