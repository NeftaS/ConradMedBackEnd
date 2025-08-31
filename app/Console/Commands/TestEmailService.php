<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailService;
use App\Models\User;

class TestEmailService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email : Email para enviar la prueba}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de correos enviando un email de prueba';

    /**
     * Execute the console command.
     */
    public function handle(EmailService $emailService)
    {
        $email = $this->argument('email');
        
        $this->info('🧪 Probando sistema de correos...');
        
        // Verificar configuración
        if (!$emailService->isConfigured()) {
            $this->error('❌ El servicio de correos no está configurado correctamente');
            $this->line('Verifica que tengas configurado:');
            $this->line('- RESEND_KEY en tu archivo .env');
            $this->line('- MAIL_MAILER=resend en tu archivo .env');
            return 1;
        }
        
        $this->info('✅ Servicio de correos configurado correctamente');
        
        // Crear usuario de prueba
        $testUser = new User();
        $testUser->name = 'Usuario de Prueba';
        $testUser->email = $email;
        
        $this->info('📧 Enviando correo de bienvenida de prueba...');
        
        try {
            $result = $emailService->sendWelcomeEmail($testUser);
            
            if ($result) {
                $this->info('✅ Correo enviado exitosamente a: ' . $email);
                $this->line('Revisa tu bandeja de entrada (y carpeta de spam)');
            } else {
                $this->error('❌ Error al enviar el correo');
            }
        } catch (\Exception $e) {
            $this->error('❌ ['.get_class($e).'] code='.$e->getCode().' msg='.$e->getMessage());
            $this->line(substr($e->getTraceAsString(), 0, 800)); // un resumen
            return 1;
        }
        
        return 0;
    }
}