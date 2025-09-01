<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PhpMailService
{
    /**
     * Enviar email usando PHP puro
     */
    public function sendEmail($to, $subject, $message, $headers = [])
    {
        try {
            // Configurar headers por defecto
            $defaultHeaders = [
                'From' => config('mail.from.address'),
                'Reply-To' => config('mail.from.address'),
                'Content-Type' => 'text/html; charset=UTF-8',
                'MIME-Version' => '1.0',
            ];

            // Combinar headers por defecto con los personalizados
            $finalHeaders = array_merge($defaultHeaders, $headers);
            
            // Convertir array de headers a string
            $headerString = '';
            foreach ($finalHeaders as $key => $value) {
                $headerString .= "$key: $value\r\n";
            }

            // Enviar email usando la función mail() de PHP
            $result = mail($to, $subject, $message, $headerString);
            
            if ($result) {
                Log::info("Email enviado exitosamente a: $to");
                return true;
            } else {
                Log::error("Error al enviar email a: $to");
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error en PhpMailService: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email con plantilla HTML
     */
    public function sendHtmlEmail($to, $subject, $htmlContent, $textContent = null)
    {
        $boundary = md5(uniqid(time()));
        
        $headers = [
            'MIME-Version' => '1.0',
            'Content-Type' => "multipart/alternative; boundary=\"$boundary\"",
        ];

        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $textContent ?: strip_tags($htmlContent) . "\r\n\r\n";
        
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlContent . "\r\n\r\n";
        
        $message .= "--$boundary--";

        return $this->sendEmail($to, $subject, $message, $headers);
    }

    /**
     * Enviar email con archivos adjuntos
     */
    public function sendEmailWithAttachment($to, $subject, $message, $attachments = [])
    {
        $boundary = md5(uniqid(time()));
        
        $headers = [
            'MIME-Version' => '1.0',
            'Content-Type' => "multipart/mixed; boundary=\"$boundary\"",
        ];

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $message . "\r\n\r\n";

        // Agregar archivos adjuntos
        foreach ($attachments as $attachment) {
            if (file_exists($attachment['path'])) {
                $fileContent = file_get_contents($attachment['path']);
                $fileContent = chunk_split(base64_encode($fileContent));
                
                $body .= "--$boundary\r\n";
                $body .= "Content-Type: {$attachment['type']}; name=\"{$attachment['name']}\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"{$attachment['name']}\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= $fileContent . "\r\n\r\n";
            }
        }

        $body .= "--$boundary--";

        return $this->sendEmail($to, $subject, $body, $headers);
    }

    /**
     * Verificar si el servicio está configurado correctamente
     */
    public function isConfigured()
    {
        // Verificar si la función mail() está disponible
        if (!function_exists('mail')) {
            return false;
        }

        // Verificar configuración básica
        return !empty(config('mail.from.address')) && 
               !empty(config('mail.from.name'));
    }

    /**
     * Obtener información de configuración
     */
    public function getConfigurationInfo()
    {
        return [
            'mail_function_exists' => function_exists('mail'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'sendmail_path' => ini_get('sendmail_path'),
            'smtp_host' => config('mail.mailers.smtp.host'),
            'smtp_port' => config('mail.mailers.smtp.port'),
        ];
    }
}
