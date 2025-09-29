<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoAnalisis extends Model
{
    use HasFactory;
    protected $table = "tipo_analisis";

    protected $fillable = [
        "tipoanalisis_nombre",
        "categoria_id"
    ];

    public function categoria(){
        return $this->belongsTo(CategoriaAnalisis::class);
    }
}
