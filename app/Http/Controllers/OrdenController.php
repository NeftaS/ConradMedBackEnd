<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrdenController extends Controller
{
    /**
     * Obtener el doctor autenticado
     */
    private function getAuthenticatedDoctor()
    {
        return Auth::guard('doctor-api')->user();
    }

    /**
     * Verificar si el doctor está autenticado
     */
    private function checkAuthentication()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
                'error' => 'Token de autenticación inválido o expirado'
            ], 401);
        }
        return $doctor;
    }

    /**
     * Obtener todas las órdenes del doctor autenticado
     */
    public function index(Request $request)
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $query = Orden::query();

            // Filtrar por el doctor autenticado
            $query->where('Orden_Cedula', $doctor->cedula);

            // Aplicar filtros adicionales si se proporcionan
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
                'doctor_info' => [
                    'id' => $doctor->id,
                    'nombre' => $doctor->nombre,
                    'email' => $doctor->email,
                    'cedula' => $doctor->cedula
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
     * Mostrar una orden específica del doctor autenticado
     */
    public function show($id)
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $orden = Orden::where('Orden_Id', $id)
                         ->where('Orden_Cedula', $doctor->cedula)
                         ->first();

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada o no tienes permisos para verla'
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
     * Crear una nueva orden para el doctor autenticado
     */
    public function store(Request $request)
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $validator = Validator::make($request->all(), [
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

            // Crear la orden con los datos del doctor autenticado
            $ordenData = $request->all();
            $ordenData['Orden_NombreMedico'] = $doctor->nombre;
            $ordenData['Orden_Cedula'] = $doctor->cedula;

            $orden = Orden::create($ordenData);

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
     * Actualizar una orden existente del doctor autenticado
     */
    public function update(Request $request, $id)
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $orden = Orden::where('Orden_Id', $id)
                         ->where('Orden_Cedula', $doctor->cedula)
                         ->first();

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada o no tienes permisos para modificarla'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
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
     * Eliminar una orden del doctor autenticado
     */
    public function destroy($id)
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $orden = Orden::where('Orden_Id', $id)
                         ->where('Orden_Cedula', $doctor->cedula)
                         ->first();

            if (!$orden) {
                return response()->json([
                    'success' => false,
                    'message' => 'Orden no encontrada o no tienes permisos para eliminarla'
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
     * Obtener estadísticas de órdenes del doctor autenticado
     */
    public function estadisticas()
    {
        // Verificar autenticación del doctor
        $authResult = $this->checkAuthentication();
        if ($authResult instanceof \Illuminate\Http\JsonResponse) {
            return $authResult;
        }
        $doctor = $authResult;

        try {
            $totalOrdenes = Orden::where('Orden_Cedula', $doctor->cedula)->count();
            $ordenesHoy = Orden::where('Orden_Cedula', $doctor->cedula)
                              ->porFecha(now()->toDateString())
                              ->count();
            $ordenesUrgentes = Orden::where('Orden_Cedula', $doctor->cedula)
                                   ->whereIn('Orden_NivelUrgencia', ['ALTA', 'CRITICA'])
                                   ->count();
            
            $ordenesPorTipo = Orden::where('Orden_Cedula', $doctor->cedula)
                ->selectRaw('Orden_Tipo, COUNT(*) as total')
                ->groupBy('Orden_Tipo')
                ->get();

            $ordenesPorUrgencia = Orden::where('Orden_Cedula', $doctor->cedula)
                ->selectRaw('Orden_NivelUrgencia, COUNT(*) as total')
                ->groupBy('Orden_NivelUrgencia')
                ->get();

            // Órdenes de los últimos 7 días
            $ordenesUltimos7Dias = Orden::where('Orden_Cedula', $doctor->cedula)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_ordenes' => $totalOrdenes,
                    'ordenes_hoy' => $ordenesHoy,
                    'ordenes_urgentes' => $ordenesUrgentes,
                    'ordenes_ultimos_7_dias' => $ordenesUltimos7Dias,
                    'por_tipo' => $ordenesPorTipo,
                    'por_urgencia' => $ordenesPorUrgencia
                ],
                'message' => 'Estadísticas obtenidas correctamente',
                'doctor_info' => [
                    'nombre' => $doctor->nombre,
                    'cedula' => $doctor->cedula
                ]
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

}
