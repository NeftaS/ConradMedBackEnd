<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lugar extends Model
{
    use HasFactory;
    protected $table = 'lugares';

    protected $fillable = ['lugar_nombre'];

}
