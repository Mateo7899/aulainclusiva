<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JuegoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('ruleta'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'mostrarRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/ruleta', [JuegoController::class, 'ruleta'])->name('ruleta');
    Route::post('/ruleta/girar', [JuegoController::class, 'girar'])->name('ruleta.girar');

    Route::get('/retos', [JuegoController::class, 'index'])->name('retos.index');
    Route::get('/retos/{reto}', [JuegoController::class, 'show'])->name('retos.show');
    Route::post('/retos/{reto}/practica', [JuegoController::class, 'practica'])->name('retos.practica');
    Route::post('/retos/{reto}/aplicar', [JuegoController::class, 'aplicar'])->name('retos.aplicar');

    Route::get('/progreso', [JuegoController::class, 'progreso'])->name('progreso');
    Route::get('/ranking', [JuegoController::class, 'ranking'])->name('ranking');
    Route::post('/ranking/visibilidad', [JuegoController::class, 'visibilidad'])->name('ranking.visibilidad');

    Route::get('/admin/indicadores', [AdminController::class, 'indicadores'])->name('admin.indicadores');
});