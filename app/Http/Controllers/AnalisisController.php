<?php

namespace App\Http\Controllers;

use App\Models\Analisis;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnalisisController extends Controller
{
    public function agregarAnalisis(Request $request){
        $validator = Validator::make($request->all(),[
            "analisis_fecha" => 'required|date_format:Y-m-d H:i:s',
            "analisis_ruta" => 'required|string',
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
            "categoria_id" => $request->categoria_id,
            "tipoanalisis_id" => $request->tipoanalisis_id,
            "doctor_id" => $request->doctor_id,
        ]);


        return response()->json(['message' => 'Analsis creada correctamente', 'analisis' => $analisis], 201);
    }

    public function mostrarAnalisis(){
        // $user_id = Auth::id();

        $analisis = Analisis::with(['categoria:id,categoria_nombre', 'tipoanalisis:id,tipoanalisis_nombre', 'doctor:id,doctor_nombre'])
            ->get();

        return response()->json(['analisis' => $analisis], 200);
    }

    public function
}
