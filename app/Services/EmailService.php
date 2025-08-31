<?php

namespace App\Services;

use App\Mail\WelcomeEmail;
use App\Mail\CitaConfirmada;
use App\Mail\OrdenConfirmada;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar correo de bienvenida
     */
    public function sendWelcomeEmail($user)
    {
        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
            Log::info('Correo de bienvenida enviado exitosamente a: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de bienvenida: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar confirmación de cita
     */
    public function sendCitaConfirmation($cita, $user, $doctor)
    {
        try {
            Mail::to($user->email)->send(new CitaConfirmada($cita, $user, $doctor));
            Log::info('Confirmación de cita enviada exitosamente a: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar confirmación de cita: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar confirmación de orden
     */
    public function sendOrdenConfirmation($orden, $user)
    {
        try {
            Mail::to($user->email)->send(new OrdenConfirmada($orden, $user));
            Log::info('Confirmación de orden enviada exitosamente a: ' . $user->email);
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar confirmación de orden: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo personalizado
     */
    public function sendCustomEmail($to, $subject, $view, $data = [])
    {
        try {
            Mail::send($view, $data, function($message) use ($to, $subject) {
                $message->to($to)
                        ->subject($subject);
            });
            Log::info('Correo personalizado enviado exitosamente a: ' . $to);
            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo personalizado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el servicio de correos está configurado correctamente
     */
    public function isConfigured()
    {
        return !empty(config('services.resend.key')) && 
               config('mail.default') === 'resend';
    }
}
