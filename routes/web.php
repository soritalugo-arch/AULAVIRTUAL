<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:login');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
