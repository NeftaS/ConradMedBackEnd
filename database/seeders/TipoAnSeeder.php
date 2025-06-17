<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoAnalisis;

class TipoAnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoAnalisis::create(['tipoanalisis_nombre' => 'Rayos x', 'categoria_id' => 1]);
    }
}
