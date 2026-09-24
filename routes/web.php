<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:login');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/admin', fn () => response('<h1>Panel admin</h1>'))->name('admin.dashboard');
    Route::get('/profesor', fn () => response('<h1>Panel profesor</h1>'))->name('profesor.dashboard');
    Route::get('/estudiante', fn () => response('<h1>Panel estudiante</h1>'))->name('estudiante.dashboard');
});