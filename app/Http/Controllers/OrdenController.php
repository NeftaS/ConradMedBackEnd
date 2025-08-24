<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class OrdenController extends Controller
{
    /**
     * Obtener el usuario autenticado desde el token JWT
     */
    private function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return null;
            }
            return $user;
        } catch (JWTException $e) {
            return null;
        }
    }

    /**
     * Verificar si el usuario está autenticado
     */
    private function checkAuthentication()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
                'error' => 'Token de autenticación inválido o expirado'
            ], 401);
        }
        return $user;
    }

    public function index(Request $request)
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $query = Orden::query();

            // Aplicar filtros si se proporcionan
            if ($request->filled('medico')) {
                $query->porMedico($request->medico);
            }

            if ($request->filled('paciente')) {
                $query->porPaciente($request->paciente);
            }

            if ($request->filled('tipo')) {
                $query->porTipo($request->tipo);
            }

            if ($request->filled('urgencia')) {
                $query->porUrgencia($request->urgencia);
            }

            if ($request->filled('fecha')) {
                $query->porFecha($request->fecha);
            }

            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->porRangoFechas($request->fecha_inicio, $request->fecha_fin);
            }

            // Ordenar por fecha de creación (más recientes primero)
            $query->orderBy('created_at', 'desc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $ordenes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $ordenes,
                'message' => 'Órdenes obtenidas correctamente',
                'user_info' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener órdenes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las órdenes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una orden específica
     */
    public function show($id)
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $orden = Orden::find($id);

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $orden,
                'message' => 'Orden obtenida correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener orden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva orden
     */
    public function store(Request $request)
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $validator = Validator::make($request->all(), [
                'Orden_NombreMedico' => 'required|string|max:255',
                'Orden_Cedula' => 'required|string|max:255',
                'Orden_NombrePaciente' => 'required|string|max:255',
                'Orden_Celular' => 'required|string|max:20',
                'Orden_Correo' => 'required|email|max:255',
                'Orden_Fecha' => 'required|date',
                'Orden_Diagnostico' => 'required|string|max:255',
                'Orden_Tipo' => 'required|string|max:100',
                'Orden_Descripcion' => 'required|string',
                'Orden_Observaciones' => 'nullable|string',
                'Orden_NivelUrgencia' => 'required|string|in:BAJA,MEDIA,ALTA,CRITICA'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $orden = Orden::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $orden,
                'message' => 'Orden creada correctamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear orden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una orden existente
     */
    public function update(Request $request, $id)
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $orden = Orden::find($id);

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'Orden_NombreMedico' => 'sometimes|required|string|max:255',
                'Orden_Cedula' => 'sometimes|required|string|max:255',
                'Orden_NombrePaciente' => 'sometimes|required|string|max:255',
                'Orden_Celular' => 'sometimes|required|string|max:20',
                'Orden_Correo' => 'sometimes|required|email|max:255',
                'Orden_Fecha' => 'sometimes|required|date',
                'Orden_Diagnostico' => 'sometimes|required|string|max:255',
                'Orden_Tipo' => 'sometimes|required|string|max:100',
                'Orden_Descripcion' => 'sometimes|required|string',
                'Orden_Observaciones' => 'nullable|string',
                'Orden_NivelUrgencia' => 'sometimes|required|string|in:BAJA,MEDIA,ALTA,CRITICA'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $orden->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $orden,
                'message' => 'Orden actualizada correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al actualizar orden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una orden
     */
    public function destroy($id)
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $orden = Orden::find($id);

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada'
                ], 404);
            }

            $orden->delete();

            return response()->json([
                'success' => true,
                'message' => 'Orden eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar orden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de órdenes
     */
    public function estadisticas()
    {
        // Verificar autenticación JWT
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $user = $authResult;

        try {
            $totalOrdenes = Orden::count();
            $ordenesHoy = Orden::porFecha(now()->toDateString())->count();
            $ordenesUrgentes = Orden::porUrgencia('ALTA')->count() + Orden::porUrgencia('CRITICA')->count();
            
            $ordenesPorTipo = Orden::selectRaw('ordenes.Orden_Tipo, COUNT(*) as total')
                ->groupBy('ordenes.Orden_Tipo')
                ->get();

            $ordenesPorUrgencia = Orden::selectRaw('Orden_NivelUrgencia, COUNT(*) as total')
                ->groupBy('Orden_NivelUrgencia')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_ordenes' => $totalOrdenes,
                    'ordenes_hoy' => $ordenesHoy,
                    'ordenes_urgentes' => $ordenesUrgentes,
                    'por_tipo' => $ordenesPorTipo,
                    'por_urgencia' => $ordenesPorUrgencia
                ],
                'message' => 'Estadísticas obtenidas correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refrescar el token JWT
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Token refrescado correctamente',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60 // Convertir minutos a segundos
                ]
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo refrescar el token',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Cerrar sesión (invalidar token)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo cerrar la sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
