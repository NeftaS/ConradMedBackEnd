<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CitaController extends Controller
{
    public function mostrarCita()
    {
        $user_id = Auth::id();
        $citas = Cita::with(['lugar:id,lugar_nombre', 'doctor:id,doctor_nombre', 'user:id,nombre'])
            ->where('cliente_id', $user_id)
            ->get();

        return response()->json(['citas' => $citas], 200);
    }

    public function mostrarCitaPorId($id)
    {
        $userId = Auth::id();

        $cita = Cita::with([
            'lugar:id,lugar_nombre',
            'doctor:id,doctor_nombre',
            'user:id,nombre'
        ])
        ->where('id', $id)
        ->where('cliente_id', $userId)
        ->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        return response()->json($cita, 200);
    }

    public function agregarCita(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cita_fecha'    => 'required|date_format:Y-m-d H:i:s',
            'cita_estatus'  => 'required|in:Activo,Cancelado,Completado',
            'lugar_id'      => 'required|exists:lugares,id',
            'doctor_id'     => 'required|exists:doctores,id',
            'cliente_id'    => 'required|exists:usuarios,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $cita = Cita::create([
            'cita_fecha' => $request->cita_fecha,
            'cita_estatus' => $request->cita_estatus,
            'lugar_id' => $request->lugar_id,
            'doctor_id' => $request->doctor_id,
            'cliente_id' => $request->cliente_id,
        ]);

        return response()->json(['message' => 'Cita creada correctamente', 'cita' => $cita], 201);
    }



    public function cancelarCita($id)
    {
        $userId = Auth::id();


        $cita = Cita::where('id', $id)->where('cliente_id', $userId)->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $cita->update([
            'cita_estatus' => "Cancelado"
        ]);

        return response()->json(['message' => 'Cita cancelada correctamente'], 200);
    }

    public function actualizarCita(Request $request, $id)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'cita_fecha'    => 'required|date_format:Y-m-d H:i:s',
            'cita_estatus'  => 'required|in:Activo,Cancelado,Completado',
            'lugar_id'      => 'required|exists:lugares,id',
            'doctor_id'     => 'required|exists:doctores,id',
            'cliente_id'    => 'required|exists:usuarios,id',
        ]);

        // $validator = Validator::make($request->all(), [
        //     'nombre_paciente' => 'sometimes|required|string|min:3|max:45',
        //     'telefono_paciente' => 'sometimes|required|digits:10',
        //     'tipo_estudio' => 'sometimes|required|string',
        //     'edad_paciente' => 'sometimes|required|integer|min:1|max:120',
        // ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $cita = Cita::where('id', $id)->where('cliente_id', $userId)->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $cita->update($request->only([
            'cita_fecha',
            'cita_estatus',
            'lugar_id',
            'doctor_id',
            'cliente_id'
        ]));

        return response()->json(['message' => 'Cita actualizada correctamente', 'cita' => $cita], 200);
    }
}
