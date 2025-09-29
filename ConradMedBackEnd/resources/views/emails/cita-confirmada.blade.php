<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita Confirmada - ConradMed</title>
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
        .success-icon {
            font-size: 48px;
            color: #10b981;
            margin-bottom: 20px;
        }
        .cita-details {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #4b5563;
        }
        .detail-value {
            color: #1f2937;
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
            <div class="success-icon">✅</div>
            <h1>Cita Confirmada</h1>
        </div>
        
        <div class="content">
            <p>Hola {{ $user->name }},</p>
            
            <p>Tu cita médica ha sido confirmada exitosamente. Aquí tienes los detalles:</p>
            
            <div class="cita-details">
                <div class="detail-row">
                    <span class="detail-label">Doctor:</span>
                    <span class="detail-value">Dr. {{ $doctor->nombre }} {{ $doctor->apellido }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Especialidad:</span>
                    <span class="detail-value">{{ $doctor->especialidad }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Hora:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Lugar:</span>
                    <span class="detail-value">{{ $cita->lugar->nombre ?? 'Por definir' }}</span>
                </div>
            </div>
            
            <p><strong>Importante:</strong></p>
            <ul>
                <li>Llega 15 minutos antes de tu cita</li>
                <li>Trae tu identificación</li>
                <li>Si necesitas cancelar, hazlo con al menos 24 horas de anticipación</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/citas" class="button">Ver Mis Citas</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>© {{ date('Y') }} ConradMed. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

