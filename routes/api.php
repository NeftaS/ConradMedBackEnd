<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\AnalisisController;
use App\Http\Middleware\IsUserAuth;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware([IsUserAuth::class])->group(function () {
    Route::prefix('user')->controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('me', 'getUser');
    });

    Route::prefix('cita')->controller(CitaController::class)->group(function () {
        Route::get('mostrar/{id}', 'mostrarCitaPorId');
        Route::get('mostrar', 'mostrarCita');
        Route::post('agendar', 'agregarCita');
        Route::put('actualizar/{id}', 'actualizarCita');
        Route::get('cancelar/{id}', 'cancelarCita');
    });

    Route::prefix('analisis')->controller(AnalisisController::class)->group(function () {
        Route::post('agendar', 'agregarAnalisis');
        Route::get('mostrar', 'mostrarAnalisis');
        Route::get('mostrar/{id}', 'mostrarAnalisis');
        Route::put('actualizar/{id}', 'actualizarAnalisis');
        Route::get('eliminar/{id}', 'eliminarAnalisis');
    });
});
