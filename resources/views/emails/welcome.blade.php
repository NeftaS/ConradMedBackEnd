<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a ConradMed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏥 ConradMed</div>
            <h1>¡Bienvenido a ConradMed!</h1>
        </div>
        
        <div class="content">
            <p class="welcome-text">Hola {{ $user->name }},</p>
            
            <p>Nos complace darte la bienvenida a ConradMed, tu plataforma de confianza para servicios médicos.</p>
            
            <p>Con tu cuenta podrás:</p>
            <ul>
                <li>📅 Agendar citas médicas</li>
                <li>🔬 Solicitar análisis clínicos</li>
                <li>💊 Comprar productos médicos</li>
                <li>📋 Ver tu historial médico</li>
                <li>👨‍⚕️ Consultar con médicos especialistas</li>
            </ul>
            
            <p>Tu cuenta ha sido creada exitosamente y ya puedes comenzar a usar todos nuestros servicios.</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="button">Acceder a ConradMed</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>© {{ date('Y') }} ConradMed. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
