<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
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
        Route::post('agendar', 'agregarCita');
        Route::get('mostrar', 'mostrarCita');
        // Route::put('actualizar', 'actualizarCita');
        Route::get('eliminar/{id}', 'eliminarCita');
    });

});
