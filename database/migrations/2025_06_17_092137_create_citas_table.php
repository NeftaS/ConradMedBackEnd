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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->timestamp('cita_fecha');
            $table->string('cita_estatus');

            $table->foreignId('lugar_id')
                ->constrained('lugares')
                ->onDelete('cascade');
            $table->foreignId('doctor_id')
                ->constrained('doctores')
                ->onDelete('cascade');
            $table->foreignId('cliente_id')
                ->constrained('usuarios')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
