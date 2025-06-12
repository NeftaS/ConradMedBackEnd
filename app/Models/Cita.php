<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_paciente',
        'telefono_paciente',
        'tipo_estudio',
        'edad_paciente',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
