<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Productos;

class ProductosController extends Controller
{
    /**
     * Mostrar todos los productos con filtros y paginación
     */
    public function mostrarProducto(Request $request)
    {
        try {
            $query = Productos::query();

            // Aplicar filtros si se proporcionan
            if ($request->filled('nombre')) {
                $query->porNombre($request->nombre);
            }

            if ($request->filled('precio_min') && $request->filled('precio_max')) {
                $query->porRangoPrecio($request->precio_min, $request->precio_max);
            }

            if ($request->filled('con_iva') && $request->con_iva == 'true') {
                $query->conIVA();
            }

            // Ordenar por ID ascendente por defecto
            $query->orderBy('id', 'asc');

            // Paginación
            $perPage = $request->get('per_page', 15);
            $productos = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos obtenidos correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un producto específico por ID
     */
    public function mostrarProductoPorId($id)
    {
        try {
            $producto = Productos::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto obtenido correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo producto
     */
    public function agregarProducto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|unique:productos,id',
            'clave' => 'required|string|max:255',
            'producto' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'precio_iva' => 'required|numeric|min:0',
            'clave_producto_servicio' => 'required|string|max:255',
            'detalles' => 'nullable|string',
            'indicaciones' => 'nullable|string',
            'tiempoEntrega' => 'nullable|string|max:255',
            'duracion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $producto = Productos::create($validator->validated());

            Log::info('Producto creado exitosamente', ['producto_id' => $producto->id]);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto creado correctamente'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un producto existente
     */
    public function actualizarProducto(Request $request, $id)
    {
        try {
            $producto = Productos::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'clave' => 'sometimes|required|string|max:255',
                'producto' => 'sometimes|required|string|max:255',
                'precio' => 'sometimes|required|numeric|min:0',
                'precio_iva' => 'sometimes|required|numeric|min:0',
                'clave_producto_servicio' => 'sometimes|required|string|max:255',
                'detalles' => 'nullable|string',
                'indicaciones' => 'nullable|string',
                'tiempoEntrega' => 'nullable|string|max:255',
                'duracion' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $producto->update($validator->validated());

            Log::info('Producto actualizado exitosamente', ['producto_id' => $producto->id]);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto actualizado correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al actualizar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un producto
     */
    public function eliminarProducto($id)
    {
        try {
            $producto = Productos::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $producto->delete();

            Log::info('Producto eliminado exitosamente', ['producto_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al eliminar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar productos por nombre
     */
    public function buscarProductos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $productos = Productos::porNombre($request->nombre)
                ->orderBy('producto', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Búsqueda completada correctamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al buscar productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
