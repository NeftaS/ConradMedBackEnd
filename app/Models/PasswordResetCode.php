<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    /**
     * Generar un código de 6 dígitos
     */
    public static function generateCode()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Crear un nuevo código de restablecimiento
     */
    public static function createCode($email)
    {
        // Invalidar códigos anteriores para este email
        self::where('email', $email)->update(['used' => true]);

        // Crear nuevo código
        return self::create([
            'email' => $email,
            'code' => self::generateCode(),
            'expires_at' => Carbon::now()->addMinutes(15) // Expira en 15 minutos
        ]);
    }

    /**
     * Verificar si el código es válido
     */
    public static function verifyCode($email, $code)
    {
        $resetCode = self::where('email', $email)
            ->where('code', $code)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($resetCode) {
            // Marcar como usado
            $resetCode->update(['used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Limpiar códigos expirados
     */
    public static function cleanExpiredCodes()
    {
        return self::where('expires_at', '<', Carbon::now())->delete();
    }
}
