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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id('Orden_Id');
            $table->string('Orden_NombreMedico');
            $table->string('Orden_Cedula');
            $table->string('Orden_NombrePaciente');
            $table->string('Orden_Celular');
            $table->string('Orden_Correo');
            $table->datetime('Orden_Fecha');
            $table->string('Orden_Diagnostico');
            $table->string('Orden_Tipo');
            $table->text('Orden_Descripcion');
            $table->text('Orden_Observaciones')->nullable();
            $table->string('Orden_NivelUrgencia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
