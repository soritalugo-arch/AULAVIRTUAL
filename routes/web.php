<?php

use App\Http\Controllers\Admin\AsignacionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Estudiante\InscripcionController;
use App\Http\Controllers\Estudiante\MisNotasController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\Profesor\CalificacionController;
use App\Http\Controllers\Profesor\AsistenciaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Auth::check()
    ? redirect()->route(AuthController::nombreRutaHome(Auth::user()))
    : redirect()->route('login'));

// "Mis datos": al alcance de cualquier rol conectado.
Route::get('/mi-perfil', [PerfilController::class, 'show'])->name('perfil')->middleware('auth');

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
    Route::get('/admin/rendimiento/estudiante/{estudiante}', [DashboardController::class, 'fichaEstudiante'])->name('admin.rendimiento.estudiante');
    Route::get('/admin/asistencia',    [DashboardController::class, 'asistencia'])->name('admin.asistencia');
    Route::get('/admin/deudas',        [DashboardController::class, 'deudas'])->name('admin.deudas');
    
    // ─── NUEVAS RUTAS DE ASIGNACIONES (AHORA PROTEGIDAS POR EL MIDDLEWARE) ───
    Route::get('/admin/asignaciones', [AsignacionController::class, 'index'])->name('admin.asignaciones.index');
    Route::post('/admin/asignaciones', [AsignacionController::class, 'store'])->name('admin.asignaciones.store');
    
    // Rutas API para las peticiones dinámicas (Fetch)
    Route::get('/admin/api/curso/{id}/horarios', [AsignacionController::class, 'getHorariosPorCurso']);
    Route::get('/admin/api/profesor/{id}/materias', [AsignacionController::class, 'getMateriasPorProfesor']);
    // NUEVA RUTA PARA ELIMINAR
    Route::post('/admin/api/asignaciones/eliminar', [AsignacionController::class, 'eliminarAsignacion']);
    Route::get('/admin/api/profesor/{id}/horario_semanal', [AsignacionController::class, 'getHorarioSemanalProfesor']);
   
    Route::get('/admin/plan-estudios', [DashboardController::class, 'planEstudios'])->name('admin.plan');
    Route::get('/admin/periodo',       [DashboardController::class, 'periodo'])->name('admin.periodo');
    Route::post('/admin/periodo/estado', [DashboardController::class, 'guardarEstadoPeriodo'])->name('admin.periodo.estado');
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

