<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['rol_usuario'];

    public function user()
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    public function doctor()
    {
        return $this->hasMany(Doctor::class, 'rol_id');
    }
}
