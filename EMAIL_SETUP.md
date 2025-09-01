# Configuración de Email con PHP Puro - ConradMed

Este documento explica cómo configurar el sistema de email usando PHP puro en lugar de servicios externos como Resend.

## Cambios Realizados

### 1. Eliminación de Resend
- ✅ Removidas las dependencias de Resend del `composer.json`
- ✅ Eliminada la configuración de Resend de `config/services.php`
- ✅ Actualizada la configuración de mail en `config/mail.php`

### 2. Nuevo Servicio de Email
- ✅ Creado `PhpMailService` - Servicio de email usando PHP puro
- ✅ Actualizado `EmailService` - Ahora usa el nuevo servicio
- ✅ Plantillas de email integradas en el servicio

## Configuración

### Opción 1: SMTP (Recomendado)

1. **Configurar variables de entorno** en tu archivo `.env`:

```env
# Configuración principal
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="ConradMed"

# Configuración SMTP
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicación
MAIL_SCHEME=tls
```

2. **Para Gmail**, necesitas crear una "Contraseña de aplicación":
   - Ve a tu cuenta de Google
   - Seguridad > Verificación en dos pasos > Contraseñas de aplicación
   - Genera una nueva contraseña para "Correo"
   - Usa esa contraseña en `MAIL_PASSWORD`

### Opción 2: Sendmail (Servidor Linux)

```env
MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="ConradMed"
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -bs -i
```

### Opción 3: Log (Desarrollo)

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="ConradMed"
```

Los emails se guardarán en `storage/logs/laravel.log`.

## Uso

### Envío de Emails

```php
use App\Services\EmailService;
use App\Services\PhpMailService;

// Crear instancias
$phpMailService = new PhpMailService();
$emailService = new EmailService($phpMailService);

// Enviar email de bienvenida
$emailService->sendWelcomeEmail($user);

// Enviar confirmación de cita
$emailService->sendCitaConfirmation($cita, $user, $doctor);

// Enviar confirmación de orden
$emailService->sendOrdenConfirmation($orden, $user);

// Enviar email personalizado
$htmlContent = "<h1>Mi Email</h1><p>Contenido HTML</p>";
$emailService->sendCustomEmail('usuario@email.com', 'Asunto', $htmlContent);
```

### Verificación de Configuración

```php
// Verificar si está configurado
if ($emailService->isConfigured()) {
    echo "Servicio configurado correctamente";
} else {
    echo "Error en la configuración";
}

// Obtener información de configuración
$configInfo = $emailService->getConfigurationInfo();
print_r($configInfo);
```

## Comando de Prueba

Usa el comando Artisan para probar el servicio:

```bash
# Email simple
php artisan email:test usuario@email.com

# Email personalizado
php artisan email:test usuario@email.com --subject="Mi Asunto" --message="Mi mensaje"
```

## Características del Nuevo Sistema

### ✅ Ventajas
- **Sin dependencias externas**: No requiere servicios de terceros
- **Configuración flexible**: Soporta SMTP, sendmail, o log
- **Plantillas integradas**: HTML y texto plano incluidos
- **Soporte para adjuntos**: Puede enviar archivos adjuntos
- **Logging detallado**: Registra éxitos y errores

### ⚠️ Consideraciones
- **Configuración del servidor**: Requiere que el servidor tenga configurado el envío de emails
- **Limitaciones del servidor**: Depende de la configuración de PHP y el servidor
- **Spam**: Los emails pueden ir a spam si el servidor no está bien configurado

## Solución de Problemas

### Error: "mail() function not available"
- Verifica que PHP tenga habilitada la función `mail()`
- En algunos hosts compartidos, esta función puede estar deshabilitada

### Error: "SMTP connection failed"
- Verifica las credenciales SMTP
- Asegúrate de que el puerto no esté bloqueado
- Para Gmail, usa una contraseña de aplicación

### Emails no llegan
- Revisa la carpeta de spam
- Verifica los logs en `storage/logs/laravel.log`
- Usa el comando de prueba para diagnosticar

### Para desarrollo local
- Usa `MAIL_MAILER=log` para ver los emails en los logs
- O configura un servidor SMTP local como MailHog

## Migración desde Resend

Si estabas usando Resend anteriormente:

1. **Actualiza las dependencias**:
   ```bash
   composer update
   ```

2. **Actualiza tu archivo `.env`** con la nueva configuración

3. **Prueba el sistema**:
   ```bash
   php artisan email:test tu-email@ejemplo.com
   ```

4. **Verifica que todo funcione** antes de desplegar

## Archivos Modificados

- `composer.json` - Eliminadas dependencias de Resend
- `config/mail.php` - Configuración actualizada
- `config/services.php` - Eliminada configuración de Resend
- `app/Services/EmailService.php` - Actualizado para usar PHP puro
- `app/Services/PhpMailService.php` - Nuevo servicio (creado)
- `app/Console/Commands/TestEmailService.php` - Comando actualizado
- `env.email.php.example` - Nuevo archivo de ejemplo (creado)
