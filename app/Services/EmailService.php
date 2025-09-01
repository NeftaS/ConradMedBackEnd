<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $phpMailService;
    protected $resendService;
    protected $useResend;

    public function __construct(PhpMailService $phpMailService, ResendPhpService $resendService = null)
    {
        $this->phpMailService = $phpMailService;
        $this->resendService = $resendService;
        $this->useResend = config('mail.default') === 'resend' && $this->resendService && $this->resendService->isConfigured();
    }

    /**
     * Enviar correo de bienvenida
     */
    public function sendWelcomeEmail($user)
    {
        try {
            $subject = 'Bienvenido a ConradMed';
            $htmlContent = $this->getWelcomeEmailTemplate($user);
            $textContent = $this->getWelcomeEmailTextTemplate($user);

            if ($this->useResend) {
                $result = $this->resendService->sendEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            } else {
                $result = $this->phpMailService->sendHtmlEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            }

            if ($result['success'] ?? $result) {
                Log::info('Correo de bienvenida enviado exitosamente a: ' . $user->email);
                return true;
            } else {
                Log::error('Error al enviar correo de bienvenida a: ' . $user->email);
                return false;
            }
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
            $subject = 'Confirmación de Cita - ConradMed';
            $htmlContent = $this->getCitaConfirmationTemplate($cita, $user, $doctor);
            $textContent = $this->getCitaConfirmationTextTemplate($cita, $user, $doctor);

            if ($this->useResend) {
                $result = $this->resendService->sendEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            } else {
                $result = $this->phpMailService->sendHtmlEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            }

            if ($result['success'] ?? $result) {
                Log::info('Confirmación de cita enviada exitosamente a: ' . $user->email);
                return true;
            } else {
                Log::error('Error al enviar confirmación de cita a: ' . $user->email);
                return false;
            }
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
            $subject = 'Confirmación de Orden - ConradMed';
            $htmlContent = $this->getOrdenConfirmationTemplate($orden, $user);
            $textContent = $this->getOrdenConfirmationTextTemplate($orden, $user);

            if ($this->useResend) {
                $result = $this->resendService->sendEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            } else {
                $result = $this->phpMailService->sendHtmlEmail(
                    $user->email, 
                    $subject, 
                    $htmlContent, 
                    $textContent
                );
            }

            if ($result['success'] ?? $result) {
                Log::info('Confirmación de orden enviada exitosamente a: ' . $user->email);
                return true;
            } else {
                Log::error('Error al enviar confirmación de orden a: ' . $user->email);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar confirmación de orden: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo personalizado
     */
    public function sendCustomEmail($to, $subject, $htmlContent, $textContent = null)
    {
        try {
            if ($this->useResend) {
                $result = $this->resendService->sendEmail($to, $subject, $htmlContent, $textContent);
            } else {
                $result = $this->phpMailService->sendHtmlEmail($to, $subject, $htmlContent, $textContent);
            }
            
            if ($result['success'] ?? $result) {
                Log::info('Correo personalizado enviado exitosamente a: ' . $to);
                return true;
            } else {
                Log::error('Error al enviar correo personalizado a: ' . $to);
                return false;
            }
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
        if ($this->useResend) {
            return $this->resendService->isConfigured();
        } else {
            return $this->phpMailService->isConfigured();
        }
    }

    /**
     * Obtener información de configuración
     */
    public function getConfigurationInfo()
    {
        if ($this->useResend) {
            return [
                'service' => 'Resend (PHP puro)',
                'configured' => $this->resendService->isConfigured(),
                'config_info' => $this->resendService->getConfigurationInfo()
            ];
        } else {
            return [
                'service' => 'PHP Mail',
                'configured' => $this->phpMailService->isConfigured(),
                'config_info' => $this->phpMailService->getConfigurationInfo()
            ];
        }
    }

    /**
     * Obtener el servicio activo
     */
    public function getActiveService()
    {
        return $this->useResend ? 'Resend' : 'PHP Mail';
    }

    // Plantillas de email

    private function getWelcomeEmailTemplate($user)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bienvenido a ConradMed</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h1 style='color: #2c3e50;'>¡Bienvenido a ConradMed!</h1>
                <p>Hola {$user->name},</p>
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
        </html>";
    }

    private function getWelcomeEmailTextTemplate($user)
    {
        return "¡Bienvenido a ConradMed!

Hola {$user->name},

Nos complace darte la bienvenida a ConradMed. Tu cuenta ha sido creada exitosamente.

Con ConradMed podrás:
- Agendar citas médicas
- Realizar pedidos de análisis
- Acceder a tu historial médico
- Recibir notificaciones importantes

Si tienes alguna pregunta, no dudes en contactarnos.

Saludos,
El equipo de ConradMed";
    }

    private function getCitaConfirmationTemplate($cita, $user, $doctor)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmación de Cita</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h1 style='color: #2c3e50;'>Confirmación de Cita</h1>
                <p>Hola {$user->name},</p>
                <p>Tu cita ha sido confirmada exitosamente.</p>
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalles de la cita:</h3>
                    <p><strong>Doctor:</strong> {$doctor->name}</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y', strtotime($cita->fecha)) . "</p>
                    <p><strong>Hora:</strong> " . date('H:i', strtotime($cita->hora)) . "</p>
                    <p><strong>Lugar:</strong> {$cita->lugar->nombre}</p>
                </div>
                <p>Por favor, llega 10 minutos antes de tu cita.</p>
                <p>Saludos,<br>El equipo de ConradMed</p>
            </div>
        </body>
        </html>";
    }

    private function getCitaConfirmationTextTemplate($cita, $user, $doctor)
    {
        return "Confirmación de Cita

Hola {$user->name},

Tu cita ha sido confirmada exitosamente.

Detalles de la cita:
- Doctor: {$doctor->name}
- Fecha: " . date('d/m/Y', strtotime($cita->fecha)) . "
- Hora: " . date('H:i', strtotime($cita->hora)) . "
- Lugar: {$cita->lugar->nombre}

Por favor, llega 10 minutos antes de tu cita.

Saludos,
El equipo de ConradMed";
    }

    private function getOrdenConfirmationTemplate($orden, $user)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmación de Orden</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h1 style='color: #2c3e50;'>Confirmación de Orden</h1>
                <p>Hola {$user->name},</p>
                <p>Tu orden ha sido procesada exitosamente.</p>
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalles de la orden:</h3>
                    <p><strong>Número de orden:</strong> #{$orden->id}</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y', strtotime($orden->created_at)) . "</p>
                    <p><strong>Total:</strong> $" . number_format($orden->total, 2) . "</p>
                </div>
                <p>Te notificaremos cuando tu orden esté lista.</p>
                <p>Saludos,<br>El equipo de ConradMed</p>
            </div>
        </body>
        </html>";
    }

    private function getOrdenConfirmationTextTemplate($orden, $user)
    {
        return "Confirmación de Orden

Hola {$user->name},

Tu orden ha sido procesada exitosamente.

Detalles de la orden:
- Número de orden: #{$orden->id}
- Fecha: " . date('d/m/Y', strtotime($orden->created_at)) . "
- Total: $" . number_format($orden->total, 2) . "

Te notificaremos cuando tu orden esté lista.

Saludos,
El equipo de ConradMed";
    }
}
