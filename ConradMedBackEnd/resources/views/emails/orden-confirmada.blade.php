<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden Confirmada - ConradMed</title>
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
        .orden-details {
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
        .productos-list {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .producto-item {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .producto-item:last-child {
            border-bottom: none;
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
            <h1>Orden Confirmada</h1>
        </div>
        
        <div class="content">
            <p>Hola {{ $user->name }},</p>
            
            <p>Tu orden ha sido confirmada exitosamente. Aquí tienes los detalles:</p>
            
            <div class="orden-details">
                <div class="detail-row">
                    <span class="detail-label">Número de Orden:</span>
                    <span class="detail-value">#{{ $orden->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($orden->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value">{{ ucfirst($orden->estado) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value">${{ number_format($orden->total, 2) }}</span>
                </div>
            </div>
            
            <h3>Productos en tu orden:</h3>
            <div class="productos-list">
                @foreach($orden->productos as $producto)
                <div class="producto-item">
                    <strong>{{ $producto->nombre }}</strong><br>
                    <small>Cantidad: {{ $producto->pivot->cantidad }} | Precio: ${{ number_format($producto->precio, 2) }}</small>
                </div>
                @endforeach
            </div>
            
            <p><strong>Próximos pasos:</strong></p>
            <ul>
                <li>Recibirás una notificación cuando tu orden esté lista</li>
                <li>Puedes hacer seguimiento del estado en tu perfil</li>
                <li>Si tienes alguna pregunta, contacta a nuestro equipo de soporte</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/ordenes" class="button">Ver Mis Órdenes</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>© {{ date('Y') }} ConradMed. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

