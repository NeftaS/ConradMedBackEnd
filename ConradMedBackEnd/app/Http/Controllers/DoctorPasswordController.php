<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorPasswordResetCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DoctorPasswordController extends Controller
{
    private const CODE_LENGTH = 6;       // dígitos del código
    private const EXPIRES_MINUTES = 10;  // minutos de validez

    /**
     * POST /api/password/request-code
     * Recibe: { telefono }
     * Responde siempre de forma uniforme para no permitir enumeración de usuarios.
     */
    public function requestCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telefono' => ['required', 'string'],
        ]);

        $doctor = Doctor::where('telefono', $data['telefono'])->first();

        if ($doctor) {
            // Generar nuevo código y limpiar anteriores
            $code = $this->generateCode(self::CODE_LENGTH);

            try {
                DoctorPasswordResetCode::where('telefono', $doctor->telefono)->delete();
            } catch (\Throwable $e) {
                Log::warning('Limpieza de códigos previos falló: '.$e->getMessage());
            }

            DoctorPasswordResetCode::create([
                'telefono'   => $doctor->telefono,
                'code'       => $code,
                'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            ]);

            // Enviar correo — nunca bloquear la respuesta si el SMTP falla
            try {
                $this->sendCodeMail($doctor->email, $code, self::EXPIRES_MINUTES);
            } catch (\Throwable $e) {
                Log::error('SMTP request-code: '.$e->getMessage());
                // No relanzar: el endpoint debe responder igual
            }
        }

        return response()->json([
            'message' => 'Si el teléfono existe, hemos enviado un código al correo registrado.',
        ]);
    }

    /**
     * POST /api/password/verify-code
     * Recibe: { telefono, code }
     * Valida existencia y expiración del código.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $digits = self::CODE_LENGTH;

        $data = $request->validate([
            'telefono' => ['required', 'string'],
            'code'     => ['required', "digits:{$digits}"],
        ]);

        $reset = DoctorPasswordResetCode::where('telefono', $data['telefono'])
            ->where('code', $data['code'])
            ->orderByDesc('id')
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Código inválido'], 422);
        }

        if (now()->greaterThan(Carbon::parse($reset->expires_at))) {
            return response()->json(['error' => 'Código expirado'], 422);
        }

        return response()->json(['message' => 'Código válido']);
    }

    /**
     * POST /api/password/reset
     * Recibe: { telefono, code, password, password_confirmation }
     * Cambia la contraseña si el código es válido y no está expirado.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $digits = self::CODE_LENGTH;

        $data = $request->validate([
            'telefono'             => ['required', 'string'],
            'code'                 => ['required', "digits:{$digits}"],
            'password'             => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $reset = DoctorPasswordResetCode::where('telefono', $data['telefono'])
            ->where('code', $data['code'])
            ->orderByDesc('id')
            ->first();

        if (!$reset) {
            return response()->json(['error' => 'Código inválido'], 422);
        }

        if (now()->greaterThan(Carbon::parse($reset->expires_at))) {
            return response()->json(['error' => 'Código expirado'], 422);
        }

        $doctor = Doctor::where('telefono', $data['telefono'])->first();
        if (!$doctor) {
            return response()->json(['error' => 'Doctor no encontrado'], 404);
        }

        $doctor->password = Hash::make($data['password']);
        $doctor->save();

        // Invalida todos los códigos de ese teléfono
        try {
            DoctorPasswordResetCode::where('telefono', $data['telefono'])->delete();
        } catch (\Throwable $e) {
            Log::warning('Limpieza de códigos tras reset falló: '.$e->getMessage());
        }

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    /* ===================== helpers ===================== */

    private function generateCode(int $length = 6): string
    {
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;
        return (string) random_int($min, $max); // seguro criptográficamente
    }

    private function sendCodeMail(string $email, string $code, int $expiresMinutes): void
    {
        $body = "Tu código de recuperación es: {$code}\nVence en {$expiresMinutes} minutos.";
        Mail::raw($body, function ($m) use ($email) {
            $m->to($email)->subject('Código de recuperación de contraseña');
        });
    }
}
