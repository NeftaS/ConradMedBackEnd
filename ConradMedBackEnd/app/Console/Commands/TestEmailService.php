<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Services\PhpMailService;

class TestEmailService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email} {--subject=Test Email} {--message=Este es un email de prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el servicio de email con PHP puro';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $subject = $this->option('subject');
        $message = $this->option('message');

        $this->info('Probando servicio de email...');
        $this->line("Enviando email a: $email");
        $this->line("Asunto: $subject");
        $this->line("Mensaje: $message");

        // Crear instancia del servicio
        $phpMailService = new PhpMailService();
        $emailService = new EmailService($phpMailService);

        // Verificar configuración
        if (!$emailService->isConfigured()) {
            $this->error('El servicio de email no está configurado correctamente.');
            $this->line('Información de configuración:');
            $configInfo = $emailService->getConfigurationInfo();
            foreach ($configInfo as $key => $value) {
                $this->line("  $key: " . (is_bool($value) ? ($value ? 'Sí' : 'No') : $value));
            }
            return 1;
        }

        // Crear contenido HTML simple
        $htmlContent = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h1 style='color: #2c3e50;'>$subject</h1>
                <p>$message</p>
                <p>Este es un email de prueba enviado desde ConradMed usando PHP puro.</p>
                <p>Fecha y hora: " . now()->format('d/m/Y H:i:s') . "</p>
                <p>Saludos,<br>El equipo de ConradMed</p>
            </div>
        </body>
        </html>";

        // Enviar email
        try {
            $result = $emailService->sendCustomEmail($email, $subject, $htmlContent);
            
            if ($result) {
                $this->info('✅ Email enviado exitosamente!');
                $this->line('Revisa tu bandeja de entrada (y carpeta de spam).');
            } else {
                $this->error('❌ Error al enviar el email.');
                $this->line('Revisa los logs para más detalles.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Excepción al enviar email: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}