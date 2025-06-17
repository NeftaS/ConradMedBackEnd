<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CitaController extends Controller
{

    public function mostrarCitaPorId($id)
    {
        $userId = Auth::id();

        $cita = Cita::where('id', $id)->where('user_id', $userId)->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        return response()->json($cita, 200);
    }

    public function agregarCita(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_paciente' =>'required|string|min:3|max:45',
            'telefono_paciente' => 'required|digits:10',
            'tipo_estudio' =>'required|string',
            'edad_paciente' => 'required|integer|min:1|max:120'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $cita = Cita::create([
            'nombre_paciente' => $request->nombre_paciente,
            'telefono_paciente' => $request->telefono_paciente,
            'tipo_estudio' => $request->tipo_estudio,
            'edad_paciente' => $request->edad_paciente,
            'user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'Cita creada correctamente', 'cita' => $cita], 201);
    }

    public function mostrarCita()
    {
        $user_id = Auth::id();
        $citas = Cita::where('user_id', $user_id)->get();

        return response()->json(['citas' => $citas], 200);
    }

    public function eliminarCita($id)
    {
        $userId = Auth::id();

        $cita = Cita::where('id', $id)->where('user_id', $userId)->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $cita->delete();

        return response()->json(['message' => 'Cita eliminada correctamente'], 200);
    }


    public function actualizarCita(Request $request, $id)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'nombre_paciente' => 'sometimes|required|string|min:3|max:45',
            'telefono_paciente' => 'sometimes|required|digits:10',
            'tipo_estudio' => 'sometimes|required|string',
            'edad_paciente' => 'sometimes|required|integer|min:1|max:120',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $cita = Cita::where('id', $id)->where('user_id', $userId)->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $cita->update($request->only([
            'nombre_paciente',
            'telefono_paciente',
            'tipo_estudio',
            'edad_paciente',
        ]));

        return response()->json(['message' => 'Cita actualizada correctamente', 'cita' => $cita], 200);
    }
}
