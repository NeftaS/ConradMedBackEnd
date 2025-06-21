<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analisis', function (Blueprint $table) {
            $table->id();
            $table->timestamp('analisis_fecha');
            $table->string('analisis_ruta');
            $table->foreignId('cliente_id')
                ->constrained('usuarios');
            $table->foreignId('categoria_id')
                ->constrained('categoria_analisis');
            $table->foreignId('tipoanalisis_id')
                ->constrained('tipo_analisis');
            $table->foreignId('doctor_id')
                ->constrained('doctores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};
