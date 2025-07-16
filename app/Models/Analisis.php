<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analisis extends Model
{

    use HasFactory;

    protected $table = "analisis";

    protected $fillable = [
        "analisis_fecha",
        "analisis_ruta",
        "cliente_id",
        "categoria_id",
        "tipoanalisis_id",
        "doctor_id"
    ];

    public function categoria(){
        return $this->belongsTo(CategoriaAnalisis::class);
    }

    public function tipoanalisis(){
        return $this->belongsTo(TipoAnalisis::class);
    }

    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }
    public function user(){
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
