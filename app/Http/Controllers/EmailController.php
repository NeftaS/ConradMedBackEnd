<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Resend\Resend;


class EmailController extends Controller
{

    public function sendWelcomeEmail()
    {
        try {
            $resend = Resend::client(config('services.resend.key'));

            $resend->emails->send([
                'from' => 'Notificaciones ConRadMed <notificacionesconradmed@conradmed.com.mx>',
                'to' => ['correorespaldouno@gmail.com'],
                'subject' => 'Prueba envio de correos',
                'html' => '<strong>Prueba Completada</strong>',
            ]);

            return response()->json(['message' => 'Correo enviado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar el correo: '.$e->getMessage()], 500);
        }
    }

}

