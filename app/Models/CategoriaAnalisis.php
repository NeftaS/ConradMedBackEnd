<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaAnalisis extends Model
{
    use HasFactory;

    protected $table = "categoria_analisis";

    protected $fillable = [
        "categoria_nombre"
    ];

}
