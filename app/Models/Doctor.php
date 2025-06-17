<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    //
    protected $table = 'doctores';

    protected $fillable = [
        'doctor_nombre',
        'rol_id'
    ];

    
}
