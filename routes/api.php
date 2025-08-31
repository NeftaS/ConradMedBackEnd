<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\DoctorController;
use App\Http\Middleware\IsUserAuth;
use App\Http\Middleware\IsDoctor;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\EmailController;

Route::post('login-doctor', [DoctorController::class, 'login']);
Route::post('register-doctor', [DoctorController::class, 'register']);


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
        Route::get('mostrar/{id}', 'mostrarAnalisisPorId');
        Route::put('actualizar/{id}', 'actualizarAnalisis');
        Route::get('eliminar/{id}', 'eliminarAnalisis');
    });
});

Route::middleware([IsDoctor::class])->group(function () {
    Route::prefix('doctor')->controller(DoctorController::class)->group(function () {
        Route::get('me', 'getDoctor');
        Route::put('actualizar', 'updateDoctor');
    });

    Route::prefix('ordenes')->controller(OrdenController::class)->group(function(){
        Route::get('/', 'index');
        Route::get('/estadisticas', 'estadisticas');
        Route::get('/{id}', 'show');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        
        // Endpoints JWT
        Route::post('/refresh-token', 'refreshToken');
        Route::post('/logout', 'logout');
    });

    Route::prefix('email')->controller(EmailController::class)->group(function(){
        Route::get('/check', 'checkEmailService');
        Route::post('/welcome', 'sendWelcomeEmail');
        Route::post('/cita-confirmation', 'sendCitaConfirmation');
        Route::post('/orden-confirmation', 'sendOrdenConfirmation');
        Route::post('/custom', 'sendCustomEmail');
    });
});

Route::prefix('productos')->controller(ProductosController::class)->group(function(){
    Route::get('/', 'mostrarProducto');
    Route::get('/buscar', 'buscarProductos');
    Route::get('/{id}', 'mostrarProductoPorId');
    Route::post('/', 'agregarProducto');
    Route::put('/{id}', 'actualizarProducto');
    Route::delete('/{id}', 'eliminarProducto');
});