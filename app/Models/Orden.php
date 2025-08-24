<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes';
    protected $primaryKey = 'Orden_Id';

    protected $fillable = [
        'Orden_NombreMedico',
        'Orden_Cedula',
        'Orden_NombrePaciente',
        'Orden_Celular',
        'Orden_Correo',
        'Orden_Fecha',
        'Orden_Diagnostico',
        'Orden_Tipo',
        'Orden_Descripcion',
        'Orden_Observaciones',
        'Orden_NivelUrgencia'
    ];

    protected $casts = [
        'Orden_Fecha' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con el doctor (opcional, si quieres vincular con la tabla de doctores)
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'Orden_Cedula', 'cedula');
    }

    // Scopes para filtros comunes
    public function scopePorMedico($query, $nombreMedico)
    {
        return $query->where('Orden_NombreMedico', 'like', "%{$nombreMedico}%");
    }

    public function scopePorPaciente($query, $nombrePaciente)
    {
        return $query->where('Orden_NombrePaciente', 'like', "%{$nombrePaciente}%");
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('Orden_Tipo', $tipo);
    }

    public function scopePorUrgencia($query, $nivelUrgencia)
    {
        return $query->where('Orden_NivelUrgencia', $nivelUrgencia);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('Orden_Fecha', $fecha);
    }

    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('Orden_Fecha', [$fechaInicio, $fechaFin]);
    }
}
