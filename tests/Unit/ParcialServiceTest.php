<?php

use App\Models\Calificacion;
use App\Services\ParcialService;

/*
|--------------------------------------------------------------------------
| Las cuatro parciales de 25 % y su promedio
|--------------------------------------------------------------------------
|
| El promedio no lo escribe nadie: sale de las parciales. Aca se fija la regla
| (opcion A: una parcial sin capturar cuenta como cero) porque es la que usan
| el profesor, el estudiante, el historial, el certificado y los reportes.
|
*/

it('sin ninguna parcial no hay promedio que mostrar', function () {
    expect(ParcialService::promedioPonderado([null, null, null, null]))->toBeNull()
        ->and(ParcialService::promedioPonderado(['', '', '', '']))->toBeNull();
});

it('una sola parcial ya pesa un cuarto del promedio (las vacias valen cero)', function () {
    // (8 + 0 + 0 + 0) / 4 = 2
    expect(ParcialService::promedioPonderado([8, null, null, null]))->toBe(2.0)
        ->and(ParcialService::promedioPonderado([8, 8, null, null]))->toBe(4.0)
        // (10 + 10 + 0 + 0) / 4 = 5
        ->and(ParcialService::promedioPonderado([10, 10, null, null]))->toBe(5.0);
});

it('las cuatro parciales completas promedian sobre diez', function () {
    expect(ParcialService::promedioPonderado([8, 7, 9, 8]))->toBe(8.0)
        ->and(ParcialService::promedioPonderado([9, 8, 7, 6]))->toBe(7.5)
        ->and(ParcialService::promedioPonderado([10, 10, 10, 10]))->toBe(10.0);
});

it('redondea el promedio a dos decimales', function () {
    // (7 + 7 + 8 + 0) / 4 = 5.5
    expect(ParcialService::promedioPonderado([7, 7, 8, null]))->toBe(5.5)
        // (7 + 8 + 8 + 0) / 4 = 5.75
        ->and(ParcialService::promedioPonderado([7, 8, 8, null]))->toBe(5.75)
        // (9 + 8 + 8 + 9) / 4 = 8.5
        ->and(ParcialService::promedioPonderado([9, 8, 8, 9]))->toBe(8.5);
});

it('calcularDesde devuelve las cuatro columnas y el promedio listos para guardar', function () {
    $valores = ParcialService::calcularDesde([9, 8.5, null, '']);

    expect($valores)->toBe([
        'parcial1' => 9.0,
        'parcial2' => 8.5,
        'parcial3' => null,
        'parcial4' => null,
        'promedio' => 4.38,
    ]);
});

it('calcularDesde rellena las parciales que faltan en vez de dejar huecos', function () {
    // Solo le llegan dos: las otras dos tienen que quedar en null, no ausentes.
    $valores = ParcialService::calcularDesde([7, 9]);

    expect($valores)->toBe([
        'parcial1' => 7.0,
        'parcial2' => 9.0,
        'parcial3' => null,
        'parcial4' => null,
        'promedio' => 4.0,
    ])->and(array_keys($valores))->toBe(['parcial1', 'parcial2', 'parcial3', 'parcial4', 'promedio']);
});

it('el veredicto lee el promedio cuando la fila tiene parciales y la nota cuando no', function () {
    // Fila del regimen nuevo: aunque traiga una nota vieja, manda el promedio.
    $conParciales = new Calificacion([
        'nota' => 8,
        'parcial1' => 5,
        'parcial2' => 5,
        'parcial3' => 5,
        'parcial4' => 5,
        'promedio' => 5.0,
        'tiene_parciales' => true,
    ]);

    expect((float) $conParciales->notaEfectiva())->toBe(5.0);

    // Fila anterior a las parciales: se sigue leyendo su nota final.
    $vieja = new Calificacion(['nota' => 8, 'tiene_parciales' => false]);

    expect((float) $vieja->notaEfectiva())->toBe(8.0)
        ->and($vieja->parciales())->toBe([null, null, null, null]);
});
