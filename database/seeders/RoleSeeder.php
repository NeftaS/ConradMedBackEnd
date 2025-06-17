<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['rol_usuario' => 'administrador']);
        Role::create(['rol_usuario' => 'cliente']);
        Role::create(['rol_usuario' => 'doctor']);
    }
}
