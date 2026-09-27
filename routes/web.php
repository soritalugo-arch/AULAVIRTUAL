<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Estudiante\InscripcionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
    ->name('login.submit')
    ->middleware('throttle:login');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

Route::middleware(['auth', 'role:profesor'])->group(function () {
    Route::get('/profesor', fn () => view('profesor.dashboard'))->name('profesor.dashboard');
});

Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/estudiante', fn () => view('estudiante.dashboard'))->name('estudiante.dashboard');

    // Rutas del módulo de matriculación y desmatriculación
    Route::get('/estudiante/matriculacion', [InscripcionController::class, 'index'])->name('estudiante.matriculacion');
    Route::post('/estudiante/inscribir', [InscripcionController::class, 'inscribir'])->name('estudiante.inscribir');
    Route::post('/estudiante/desinscribir', [InscripcionController::class, 'desinscribir'])->name('estudiante.desinscribir');
});
