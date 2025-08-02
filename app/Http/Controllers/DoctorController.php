<?php

namespace App\Http\Controllers;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class DoctorController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'    => 'required|string|min:3|max:45',
            'telefono'  => 'required|string|min:10|max:10|unique:doctores',
            'email'     => 'required|string|email|unique:doctores',
            'password'  => 'required|string|min:8|confirmed',
            'puntos'    => 'nullable|string',
            'cedula'    => 'required|string|min:10|max:10|unique:doctores'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $doctor = Doctor::create([
                'nombre'    => $request->nombre,
                'telefono'  => $request->telefono,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
                'cedula'    => $request->cedula,
                'puntos'    => $request->puntos ?? '0'
            ]);

            $token = Auth::guard('doctor-api')->login($doctor);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar el doctor',
                'details' => $e->getMessage()
            ], 500);
        }

        $doctor->makeHidden(['password']);

        return response()->json([
            'message' => 'Doctor creado correctamente',
            'doctor' => $doctor,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only(['telefono', 'password']);

        try {
            if (!$token = Auth::guard('doctor-api')->attempt($credentials)) {
                return response()->json(['error' => 'Teléfono o contraseña incorrectos'], 401);
            }

            $doctor = Auth::guard('doctor-api')->user();

            return response()->json(['doctor' => $doctor, 'token' => $token], 200);

        } catch (JWTException $e) {
            return response()->json(['error' => 'No se puede iniciar sesión, intente más tarde', 'details' => $e->getMessage()], 500);
        }
    }


    public function getDoctor()
    {
        try {

        $doctor = Auth::guard('doctor-api')->user();

            if (!$doctor) {
                return response()->json(['success' => false, 'error' => 'Usuario no autenticado'], 401);
            }

            return response()->json(['doctor' => $doctor], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        }
    }
}
