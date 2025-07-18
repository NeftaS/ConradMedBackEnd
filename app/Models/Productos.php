<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id',
        'clave',
        'producto',
        'precio',
        'precio_iva',
        'clave_producto_servicio'
    ];
}
