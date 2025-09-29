<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar si el token está presente
            if (!$request->bearerToken()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de acceso requerido',
                    'error' => 'No se proporcionó token de autenticación'
                ], 401);
            }

            // Verificar y validar el token
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                    'error' => 'El token no corresponde a un usuario válido'
                ], 401);
            }

            // Agregar el usuario autenticado a la request
            $request->merge(['auth_user' => $user]);

            return $next($request);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expirado',
                'error' => 'El token de acceso ha expirado'
            ], 401);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
                'error' => 'El token de acceso no es válido'
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el token',
                'error' => 'Error al procesar el token de acceso'
            ], 401);
        }
    }
}
