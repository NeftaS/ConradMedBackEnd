<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnalisisController extends Controller
{
    public function agregarAnalisis(Request $request){
        $validator = Validator::make($request->all(),[
            "analisis_fecha" => 'required|date_format:Y-m-d H:i:s',
            "analisis_ruta" => 'required|string',
            "cliente_id"=>'required|exists:usuarios,id',
            "categoria_id" => 'required|exists:categoria_analisis,id',
            "tipoanalisis_id" => 'required|exists:tipo_analisis,id',
            "doctor_id" => 'required|exists:doctores,id'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        $analisis = Analisis::create([
            "analisis_fecha" => $request->analisis_fecha,
            "analisis_ruta" => $request->analisis_ruta,
            "cliente_id"=> $request->cliente_id,
            "categoria_id" => $request->categoria_id,
            "tipoanalisis_id" => $request->tipoanalisis_id,
            "doctor_id" => $request->doctor_id,
        ]);

        return response()->json(['message' => 'Analsis creada correctamente', 'analisis' => $analisis], 201);
    }

    public function mostrarAnalisis(){
        $user_id = Auth::id();

        $analisis = Analisis::with(['user:id,nombre,telefono,email','categoria:id,categoria_nombre', 'tipoanalisis:id,tipoanalisis_nombre', 'doctor:id,doctor_nombre'])
        ->where('cliente_id', $user_id)
        ->get();

        return response()->json(['analisis' => $analisis], 200);
    }

    public function mostrarAnalisisPorId($id)
    {
        $user_id = Auth::id();

        $analisis = Analisis::with([
                'user:id,nombre,telefono,email',
                'categoria:id,categoria_nombre',
                'tipoanalisis:id,tipoanalisis_nombre',
                'doctor:id,doctor_nombre'
            ])
            ->where('id', $id)
            ->where('cliente_id', $user_id)
            ->first();

        if (!$analisis) {
            return response()->json(['error' => 'Análisis no encontrado'], 404);
        }

        return response()->json(['analisis' => $analisis], 200);
    }

    public function actualizarAnalisis(Request $request, $id){

        $validator = Validator::make($request->all(),[
            "analisis_fecha" => 'required|date_format:Y-m-d H:i:s',
            "analisis_ruta" => 'required|string',
            "cliente_id"=> 'required|exists:usuarios,id',
            "categoria_id" => 'required|exists:categoria_analisis,id',
            "tipoanalisis_id" => 'required|exists:tipo_analisis,id',
            "doctor_id" => 'required|exists:doctores,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $analisis = Analisis::where('id', $id)->first();

        if(!$analisis){
            return response()->json(['error' => 'Cita no encontrada' ], 404);
        }

        $analisis->update($request->only([
            "analisis_fecha",
            "analisis_ruta",
            "cliente_id",
            "categoria_id",
            "tipoanalisis_id",
            "doctor_id",
        ]));

        return response()->json(['message' => 'Analisis actualizado correctamente', 'analisis' => $analisis], 200);
    }

    public function eliminarAnalisis($id)
    {
        $userId = Auth::id(); // ID del usuario autenticado

        $analisis = Analisis::where('id', $id)
            ->where('cliente_id', $userId)
            ->first();

        if (!$analisis) {
            return response()->json(['error' => 'Análisis no encontrado o no autorizado'], 404);
        }

        $analisis->delete();

        return response()->json(['message' => 'Análisis eliminado correctamente'], 200);
    }
}
