<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'cita_fecha',
        'cita_estatus',
        'lugar_id',
        'doctor_id',
        'cliente_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function lugar()
    {
        return $this->belongsTo(Lugar::class);
    }
}
