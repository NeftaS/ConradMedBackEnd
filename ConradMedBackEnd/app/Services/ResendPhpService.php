<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ResendPhpService
{
    private $apiKey;
    private $baseUrl = 'https://api.resend.com';

    public function __construct()
    {
        $this->apiKey = config('services.resend.key');
    }

    /**
     * Enviar email usando la API de Resend con PHP puro
     */
    public function sendEmail($to, $subject, $htmlContent, $textContent = null, $from = null, $replyTo = null)
    {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('API key de Resend no configurada');
            }

            $data = [
                'from' => $from ?: config('mail.from.address'),
                'to' => is_array($to) ? $to : [$to],
                'subject' => $subject,
                'html' => $htmlContent,
            ];

            if ($textContent) {
                $data['text'] = $textContent;
            }

            if ($replyTo) {
                $data['reply_to'] = $replyTo;
            }

            $response = $this->makeApiRequest('/emails', 'POST', $data);

            if ($response['success']) {
                Log::info("Email enviado exitosamente a: " . implode(', ', is_array($to) ? $to : [$to]));
                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Email enviado correctamente'
                ];
            } else {
                Log::error("Error al enviar email: " . $response['error']);
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error en ResendPhpService: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enviar email con plantilla HTML
     */
    public function sendHtmlEmail($to, $subject, $htmlContent, $textContent = null, $from = null)
    {
        return $this->sendEmail($to, $subject, $htmlContent, $textContent, $from);
    }

    /**
     * Enviar email con archivos adjuntos
     */
    public function sendEmailWithAttachment($to, $subject, $htmlContent, $attachments = [], $from = null)
    {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('API key de Resend no configurada');
            }

            $data = [
                'from' => $from ?: config('mail.from.address'),
                'to' => is_array($to) ? $to : [$to],
                'subject' => $subject,
                'html' => $htmlContent,
            ];

            // Procesar archivos adjuntos
            if (!empty($attachments)) {
                $data['attachments'] = [];
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment['path'])) {
                        $data['attachments'][] = [
                            'filename' => $attachment['name'],
                            'content' => base64_encode(file_get_contents($attachment['path']))
                        ];
                    }
                }
            }

            $response = $this->makeApiRequest('/emails', 'POST', $data);

            if ($response['success']) {
                Log::info("Email con adjuntos enviado exitosamente a: " . implode(', ', is_array($to) ? $to : [$to]));
                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Email enviado correctamente'
                ];
            } else {
                Log::error("Error al enviar email con adjuntos: " . $response['error']);
                return [
                    'success' => false,
                    'error' => $response['error']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error en ResendPhpService (adjuntos): ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si el servicio está configurado correctamente
     */
    public function isConfigured()
    {
        return !empty($this->apiKey);
    }

    /**
     * Obtener información de configuración
     */
    public function getConfigurationInfo()
    {
        return [
            'api_key_configured' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey),
            'base_url' => $this->baseUrl,
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    /**
     * Realizar petición a la API de Resend
     */
    private function makeApiRequest($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $error
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $responseData,
                'http_code' => $httpCode
            ];
        } else {
            $errorMessage = 'HTTP Error ' . $httpCode;
            if (isset($responseData['message'])) {
                $errorMessage .= ': ' . $responseData['message'];
            }
            return [
                'success' => false,
                'error' => $errorMessage,
                'http_code' => $httpCode,
                'response' => $responseData
            ];
        }
    }

    /**
     * Verificar el estado de la API de Resend
     */
    public function checkApiStatus()
    {
        return $this->makeApiRequest('/domains');
    }

    /**
     * Obtener dominios verificados
     */
    public function getDomains()
    {
        $response = $this->makeApiRequest('/domains');
        return $response;
    }
}
