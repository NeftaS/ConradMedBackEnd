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
        Schema::create('productos', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('clave')->nullable();
            $table->string('producto')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->decimal('precio_iva', 10, 2)->nullable();
            $table->string('clave_producto_servicio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
