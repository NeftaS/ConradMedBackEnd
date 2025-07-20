<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' =>'required|string|min:3|max:45',
            'telefono' =>'required|string|min:10|max:10',
            'email' =>'required|string|email',
            'password' =>'required|string|min:8|confirmed',
            'rol_id' =>'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $existingUser = User::where(function($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('telefono', $request->telefono);
        })->where('rol_id', $request->rol_id)->first();

        if ($existingUser) {
            return response()->json([
                'error' => 'Ya existe un usuario con ese correo o teléfono.'
            ], 409);
        }

        try {
            $user = User::create([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'rol_id' => $request->rol_id
            ]);

            $token = JWTAuth::fromUser($user);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar el usuario',
                'details' => $e->getMessage()
            ], 500);
        }

        $user->makeHidden(['password']);

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string',
            'password' =>'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only(['telefono', 'password']);
        $credentials['rol_id'] = 2;

        try{
            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'Telefono o contraseña incorrectos'], 401);
            }

            $user = Auth::user();

            return response()->json(['user' => $user,'token' => $token], 200);

        }catch(JWTException $e){
            return response()->json(['error' => 'No se puede iniciar sesión, intente más tarde', $e], 500);
        }
    }

    public function getUser(){
        $user = Auth::user();
        return response()->json($user, 200);
    }

    public function logout(){
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Se ha cerrado sesión'], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo cerrar sesión, token inválido'], 500);
        }
    }

    public function loginDoctor(Request $request){
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string',
            'password' =>'required|string|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = $request->only(['telefono', 'password']);
        $credentials['rol_id'] = 3;

        try{
            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'Telefono o contraseña incorrectos'], 401);
            }

            $user = Auth::user();

            return response()->json(['user' => $user,'token' => $token], 200);

        }catch(JWTException $e){
            return response()->json(['error' => 'No se puede iniciar sesión, intente más tarde', $e], 500);
        }
    }
}
