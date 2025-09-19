<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPasswordResetCode extends Model
{
    protected $fillable = [
        'telefono',
        'code',
        'expires_at',
    ];

    protected $dates = ['expires_at'];
}
