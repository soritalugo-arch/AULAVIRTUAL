<?php

use App\Http\Controllers\Estudiante\HistorialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de reportes del estudiante
|--------------------------------------------------------------------------
|
| Modulo aparte de routes/web.php, como pide el enunciado.
|
| Solo entra el rol estudiante y ninguna ruta recibe un id de estudiante: el
| historial y el certificado se resuelven con el usuario autenticado, de modo
| que no existe forma de pedir el registro de otra persona cambiando la URL.
|
*/

Route::middleware(['auth', 'role:estudiante'])->group(function () {
    Route::get('/estudiante/historial', [HistorialController::class, 'index'])
        ->name('estudiante.historial');

    Route::get('/estudiante/historial/certificado', [HistorialController::class, 'certificado'])
        ->name('estudiante.certificado');
});
