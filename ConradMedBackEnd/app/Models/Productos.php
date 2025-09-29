<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos';
    
    // El ID no es auto-incrementable, se define manualmente
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id',
        'clave',
        'producto',
        'precio',
        'precio_iva',
        'clave_producto_servicio',
        'detalles',
        'indicaciones',
        'tiempoEntrega',
        'duracion',
        'descripcion'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_iva' => 'decimal:2',
    ];

    /**
     * Scope para buscar productos por nombre
     */
    public function scopePorNombre($query, $nombre)
    {
        return $query->where('producto', 'like', '%' . $nombre . '%');
    }

    /**
     * Scope para filtrar por rango de precios
     */
    public function scopePorRangoPrecio($query, $min, $max)
    {
        return $query->whereBetween('precio', [$min, $max]);
    }

    /**
     * Scope para productos con IVA
     */
    public function scopeConIVA($query)
    {
        return $query->whereNotNull('precio_iva');
    }
}
