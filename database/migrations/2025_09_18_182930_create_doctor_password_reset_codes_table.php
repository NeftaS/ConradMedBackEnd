<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la tabla existe pero mal formada, la borramos para crearla bien.
        if (Schema::hasTable('doctor_password_reset_codes')) {
            Schema::drop('doctor_password_reset_codes');
        }

        Schema::create('doctor_password_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->string('telefono');     // teléfono del doctor (string)
            $table->string('code', 6);      // código de 6 dígitos
            $table->timestamp('expires_at'); // fecha/hora de expiración
            $table->timestamps();

            $table->index('telefono');      // lookup rápido por teléfono
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_password_reset_codes');
    }
};
