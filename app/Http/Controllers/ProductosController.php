<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Productos;

use function Laravel\Prompts\error;

class ProductosController extends Controller
{
    public function mostrarProducto(){
        try {
            $productos = Productos::orderBy('id', 'asc')->get();
        } catch (\Exception $e) {
            return response()->json(['Error' => $e], 200);
        }

        return response()->json(['productos' => $productos], 200);
    }

    public function agregarProducto(Request $request){
        // Validar los datos recibidos
        $validated = Validator::make($request->all(),[
            'id' => 'required|integer',
            'clave' => 'required|string|max:255',
            'producto' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'precioIVA' => 'required|numeric',
            'claveProdServ' => 'required|string|max:255',
            'detalles' => 'nullable|string',
            'indicaciones' => 'nullable|string',
            'tiempoEntrega' => 'nullable|string|max:255',
            'duracion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        // Si la validación falla, retornar un error 422
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 422);
        }

        try {
            $producto = Productos::create([
                'id' => $validated->validated()['id'],
                'clave' => $validated->validated()['clave'],
                'producto' => $validated->validated()['producto'],
                'precio' => $validated->validated()['precio'],
                'precio_iva' => $validated->validated()['precioIVA'],
                'claveProdServ' => $validated->validated()['claveProdServ'],
                'detalles' => $validated->validated()['detalles'] ?? '',
                'indicaciones' => $validated->validated()['indicaciones'] ?? '',
                'tiempoEntrega' => $validated->validated()['tiempoEntrega'] ?? '',
                'duracion' => $validated->validated()['duracion'] ?? '',
                'descripcion' => $validated->validated()['descripcion'] ?? '',
            ]);
        } catch (\Exception $e) {
            // Capturar cualquier error y devolverlo
            return response()->json([
                'error' => 'Error al registrar el producto',
                'details' => $e->getMessage()
            ], 500);
        }

        // Retornar mensaje de éxito con el producto creado
        return response()->json(['message' => 'Producto creado correctamente', 'producto' => $producto], 201);
    }

    public function mostrarProductoPorId(){

    }
}
