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
        Schema::create('tipo_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('tipoanalisis_nombre');
            $table->foreignId('categoria_id')
                ->constrained('categoria_analisis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_analisis');
    }
};
