# Configuración de Resend para ConradMed

## Configuración Requerida

Para usar únicamente Resend como servicio de correos, necesitas configurar las siguientes variables en tu archivo `.env`:

```env
# Configuración principal de correos
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="ConradMed"

# Configuración de Resend
RESEND_KEY=re_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Configuración de la aplicación
APP_URL=http://localhost:8000
APP_NAME="ConradMed"
```

## Pasos para Configurar Resend

### 1. Crear Cuenta en Resend
- Ve a [https://resend.com](https://resend.com)
- Crea una cuenta gratuita
- Verifica tu email

### 2. Obtener API Key
- Ve a [https://resend.com/api-keys](https://resend.com/api-keys)
- Crea una nueva API key
- Copia la key que comienza con `re_`

### 3. Configurar Dominio (Opcional pero Recomendado)
- Ve a [https://resend.com/domains](https://resend.com/domains)
- Agrega tu dominio
- Configura los registros DNS necesarios
- Una vez verificado, puedes usar emails desde tu dominio

### 4. Configurar Variables de Entorno
```env
# Reemplaza con tu API key real
RESEND_KEY=re_tu_api_key_aqui

# Reemplaza con tu dominio verificado o usa el dominio por defecto de Resend
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="ConradMed"
```

## Límites de Resend

### Plan Gratuito
- 3,000 emails por mes
- 100 emails por día
- Solo desde dominios verificados

### Planes de Pago
- Más emails por mes
- Más emails por día
- Soporte prioritario
- Dominios personalizados

## Verificación de Configuración

Puedes verificar que Resend esté configurado correctamente usando el endpoint de prueba:

```bash
curl -X GET http://localhost:8000/api/enviar-prueba
```

## Migración desde PHP Mail

Si anteriormente usabas PHP Mail, la migración es automática. Solo necesitas:

1. Configurar las variables de Resend en tu `.env`
2. Reiniciar tu aplicación
3. Todos los correos ahora se enviarán a través de Resend

## Ventajas de Resend

- **Confiabilidad**: 99.9% de tiempo de actividad
- **Deliverability**: Mejor tasa de entrega que PHP Mail
- **Analytics**: Estadísticas detalladas de envío
- **API**: API moderna y fácil de usar
- **Escalabilidad**: Maneja grandes volúmenes de correos
- **Seguridad**: Autenticación robusta y encriptación

## Troubleshooting

### Error: "API key de Resend no configurada"
- Verifica que `RESEND_KEY` esté configurado en tu `.env`
- Asegúrate de que la key comience con `re_`

### Error: "Dominio no verificado"
- Verifica tu dominio en Resend
- O usa el dominio por defecto de Resend temporalmente

### Error: "Límite de emails excedido"
- Verifica tu plan de Resend
- Considera actualizar a un plan de pago

## Soporte

- Documentación de Resend: [https://resend.com/docs](https://resend.com/docs)
- Soporte de Resend: [https://resend.com/support](https://resend.com/support)

