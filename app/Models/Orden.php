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

    // Relación con el doctor
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

    public function scopePorDoctor($query, $cedula)
    {
        return $query->where('Orden_Cedula', $cedula);
    }

    public function scopeUrgentes($query)
    {
        return $query->whereIn('Orden_NivelUrgencia', ['ALTA', 'CRITICA']);
    }

    public function scopeDeHoy($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeUltimosDias($query, $dias = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    // Accessors para formatear datos
    public function getFechaFormateadaAttribute()
    {
        return $this->Orden_Fecha ? $this->Orden_Fecha->format('d/m/Y H:i') : null;
    }

    public function getNivelUrgenciaColorAttribute()
    {
        $colores = [
            'BAJA' => 'green',
            'MEDIA' => 'yellow',
            'ALTA' => 'orange',
            'CRITICA' => 'red'
        ];

        return $colores[$this->Orden_NivelUrgencia] ?? 'gray';
    }

    public function getEsUrgenteAttribute()
    {
        return in_array($this->Orden_NivelUrgencia, ['ALTA', 'CRITICA']);
    }

    // Métodos para estadísticas
    public static function estadisticasPorDoctor($cedula)
    {
        return [
            'total' => self::where('Orden_Cedula', $cedula)->count(),
            'hoy' => self::where('Orden_Cedula', $cedula)->deHoy()->count(),
            'urgentes' => self::where('Orden_Cedula', $cedula)->urgentes()->count(),
            'ultimos_7_dias' => self::where('Orden_Cedula', $cedula)->ultimosDias(7)->count(),
        ];
    }
}
