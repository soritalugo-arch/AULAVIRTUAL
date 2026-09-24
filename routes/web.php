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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

Route::middleware(['auth', 'role:profesor'])->group(function () {
    Route::get('/profesor', fn () => view('profesor.dashboard'))->name('profesor.dashboard');
});

Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/estudiante', fn () => view('estudiante.dashboard'))->name('estudiante.dashboard');
});