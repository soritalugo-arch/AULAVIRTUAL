<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Estudiante\InscripcionController;
use App\Http\Controllers\Estudiante\MisNotasController;
use App\Http\Controllers\Profesor\CalificacionController;
use App\Http\Controllers\Profesor\AsistenciaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->user()
    ? redirect()->route(AuthController::nombreRutaHome(auth()->user()))
    : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:login');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Panel de la Rectora: una página por función, unidas por el menú lateral.
    Route::get('/admin',               [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/inscripciones', [DashboardController::class, 'inscripciones'])->name('admin.inscripciones');
    Route::get('/admin/rendimiento',   [DashboardController::class, 'rendimiento'])->name('admin.rendimiento');
    Route::get('/admin/asistencia',    [DashboardController::class, 'asistencia'])->name('admin.asistencia');
    Route::get('/admin/deudas',        [DashboardController::class, 'deudas'])->name('admin.deudas');
});

// ─── Rutas del Profesor ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:profesor'])->group(function () {
    Route::get('/profesor', fn () => view('profesor.dashboard'))->name('profesor.dashboard');

    // Módulo: Calificaciones y Asistencias
    Route::get('/profesor/mis-cursos',          [CalificacionController::class, 'cursos'])       ->name('profesor.cursos');
    Route::get('/profesor/notas/{curso}',        [CalificacionController::class, 'notas'])        ->name('profesor.notas');
    Route::post('/profesor/notas/guardar',       [CalificacionController::class, 'guardarNota'])  ->name('profesor.notas.guardar');
    Route::get('/profesor/asistencia/{curso}',   [AsistenciaController::class,   'registrar'])    ->name('profesor.asistencia');
    Route::post('/profesor/asistencia/guardar',  [AsistenciaController::class,   'guardar'])      ->name('profesor.asistencia.guardar');
});

// ─── Rutas del Estudiante ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/estudiante', fn () => view('estudiante.dashboard'))->name('estudiante.dashboard');

    // Módulo: Matriculación y desmatriculación
    Route::get('/estudiante/matriculacion',  [InscripcionController::class, 'index'])            ->name('estudiante.matriculacion');
    Route::post('/estudiante/inscribir',     [InscripcionController::class, 'inscribir'])         ->name('estudiante.inscribir');
    Route::post('/estudiante/desinscribir',  [InscripcionController::class, 'desinscribir'])      ->name('estudiante.desinscribir');
    Route::post('/estudiante/quitar-lista',  [InscripcionController::class, 'quitarListaEspera'])->name('estudiante.quitarListaEspera');

    // Modulo: Mis Notas y Asistencia
    Route::get('/estudiante/notas',          [MisNotasController::class, 'index'])               ->name('estudiante.notas');
});

