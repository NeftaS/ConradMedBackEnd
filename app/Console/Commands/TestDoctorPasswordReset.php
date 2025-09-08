<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Doctor;
use App\Models\DoctorPasswordResetCode;
use App\Services\EmailService;
use App\Services\ResendPhpService;

class TestDoctorPasswordReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:doctor-password-reset {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de restablecimiento de contraseña para doctores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Por favor proporciona un email válido');
            return 1;
        }

        $this->info('Probando sistema de restablecimiento de contraseña para doctores...');
        
        try {
            // Verificar si el doctor existe
            $doctor = Doctor::where('email', $email)->first();
            if (!$doctor) {
                $this->error('No se encontró un doctor con este email');
                $this->line('Asegúrate de que el doctor esté registrado en la tabla doctores');
                return 1;
            }
            
            $this->info("✓ Doctor encontrado: Dr. {$doctor->nombre} (Cédula: {$doctor->cedula})");
            
            // Crear instancia del servicio Resend
            $resendService = new ResendPhpService();
            
            // Verificar configuración
            if (!$resendService->isConfigured()) {
                $this->error('Resend no está configurado correctamente');
                $this->line('Verifica que RESEND_KEY esté configurado en tu archivo .env');
                return 1;
            }
            
            $this->info('✓ Resend está configurado correctamente');
            
            // Crear instancia del EmailService
            $emailService = new EmailService($resendService);
            
            // Limpiar códigos expirados
            DoctorPasswordResetCode::cleanExpiredCodes();
            $this->info('✓ Códigos expirados limpiados');
            
            // Crear código de prueba
            $resetCode = DoctorPasswordResetCode::createCode($email);
            $this->info("✓ Código generado: {$resetCode->code}");
            
            // Enviar correo de prueba
            $this->info("Enviando correo de restablecimiento a: {$email}");
            $result = $emailService->sendDoctorPasswordResetCode($doctor, $resetCode->code);
            
            if ($result) {
                $this->info('✓ Correo enviado exitosamente');
                $this->line('Revisa tu bandeja de entrada (y spam) para ver el correo');
                
                // Probar verificación de código
                $this->info('Probando verificación de código...');
                $isValid = DoctorPasswordResetCode::verifyCode($email, $resetCode->code);
                
                if ($isValid) {
                    $this->info('✓ Código verificado correctamente');
                } else {
                    $this->error('✗ Error al verificar el código');
                }
                
            } else {
                $this->error('✗ Error al enviar el correo');
                $this->line('Revisa los logs para más detalles');
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
