<?php

use App\Models\Calificacion;
use App\Services\ParcialService;

/*
|--------------------------------------------------------------------------
| Las cuatro parciales de 25 puntos, el acumulado y el promedio
|--------------------------------------------------------------------------
|
| El profesor captura cada parcial en puntos, de 0 a 25, que es el 25 % de la
| materia. El acumulado (sobre 100) es la suma de las cuatro y el promedio
| (sobre 10) es el acumulado dividido entre diez. Ninguno de los dos se
| escribe: salen solos. Aca se fija esa regla porque es la que usan el profesor,
| el estudiante, el historial, el certificado y los reportes.
|
| Una parcial sin capturar cuenta como cero (opcion A): el numero que ve el
| profesor sube a medida que escribe.
|
*/

it('sin ninguna parcial no hay acumulado ni promedio que mostrar', function () {
    expect(ParcialService::acumulado([null, null, null, null]))->toBeNull()
        ->and(ParcialService::promedio([null, null, null, null]))->toBeNull()
        ->and(ParcialService::acumulado(['', '', '', '']))->toBeNull()
        ->and(ParcialService::promedio(['', '', '', '']))->toBeNull();
});

it('el acumulado es la suma de las cuatro parciales sobre cien', function () {
    // 20 de 25 es el 80 % de esa parcial: 20 + 5 + 0 + 0 = 25 de 100.
    expect(ParcialService::acumulado([20, 5, null, null]))->toBe(25.0)
        ->and(ParcialService::acumulado([25, 25, 25, 25]))->toBe(100.0);
});

it('el promedio es el acumulado dividido entre diez', function () {
    // 80 de 100 es un promedio de 8.
    expect(ParcialService::promedio([20, 20, 20, 20]))->toBe(8.0)
        ->and(ParcialService::promedio([25, 25, 25, 25]))->toBe(10.0)
        ->and(ParcialService::promedio([22.5, 17.5, 20, 20]))->toBe(8.0);
});

it('una sola parcial ya pesa un cuarto del promedio (las vacias valen cero)', function () {
    expect(ParcialService::promedio([20, null, null, null]))->toBe(2.0)
        ->and(ParcialService::promedio([20, 20, null, null]))->toBe(4.0)
        // (25 + 25 + 0 + 0) / 10 = 5
        ->and(ParcialService::promedio([25, 25, null, null]))->toBe(5.0);
});

it('redondea el promedio a dos decimales', function () {
    // (17.5 + 17.5 + 20 + 0) / 10 = 5.5
    expect(ParcialService::promedio([17.5, 17.5, 20, null]))->toBe(5.5)
        // (17.5 + 20 + 20 + 0) / 10 = 5.75
        ->and(ParcialService::promedio([17.5, 20, 20, null]))->toBe(5.75)
        // (22.5 + 20 + 20 + 22.5) / 10 = 8.5
        ->and(ParcialService::promedio([22.5, 20, 20, 22.5]))->toBe(8.5);
});

it('una parcial nunca puede pasarse de su tope de 25 puntos', function () {
    expect(ParcialService::MAXIMO_PARCIAL)->toBe(25)
        ->and(ParcialService::MAXIMO_ACUMULADO)->toBe(100)
        ->and(ParcialService::PESO)->toBe(25)
        // El promedio de un acumulado de 70 sobre 100 es un 7.
        ->and(ParcialService::promedio([25, 25, 20, 0]))->toBe(7.0);
});

it('aprueba con 6 de promedio, que son 60 puntos acumulados', function () {
    expect(ParcialService::MINIMO_APROBACION)->toBe(6)
        ->and(ParcialService::MINIMO_APROBACION_ACUMULADO)->toBe(60)
        ->and(ParcialService::aprueba(6))->toBeTrue()
        ->and(ParcialService::aprueba(5.99))->toBeFalse()
        ->and(ParcialService::aprueba(10))->toBeTrue()
        ->and(ParcialService::aprueba(null))->toBeFalse();
});

it('calcularDesde devuelve las cuatro columnas y el promedio listos para guardar', function () {
    $valores = ParcialService::calcularDesde([22.5, 20, null, '']);

    expect($valores)->toBe([
        'parcial1' => 22.5,
        'parcial2' => 20.0,
        'parcial3' => null,
        'parcial4' => null,
        // (22.5 + 20) / 10 = 4.25
        'promedio' => 4.25,
    ]);
});

it('calcularDesde rellena las parciales que faltan en vez de dejar huecos', function () {
    // Solo le llegan dos: las otras dos tienen que quedar en null, no ausentes.
    $valores = ParcialService::calcularDesde([17.5, 22.5]);

    expect($valores)->toBe([
        'parcial1' => 17.5,
        'parcial2' => 22.5,
        'parcial3' => null,
        'parcial4' => null,
        'promedio' => 4.0,
    ])->and(array_keys($valores))->toBe(['parcial1', 'parcial2', 'parcial3', 'parcial4', 'promedio']);
});

it('el veredicto lee el promedio cuando la fila tiene parciales y la nota cuando no', function () {
    // Fila del regimen nuevo: aunque traiga una nota vieja, manda el promedio.
    $conParciales = new Calificacion([
        'nota' => 8,
        'parcial1' => 20,
        'parcial2' => 20,
        'parcial3' => 20,
        'parcial4' => 20,
        'promedio' => 8.0,
        'tiene_parciales' => true,
    ]);

    expect((float) $conParciales->notaEfectiva())->toBe(8.0)
        // El acumulado de la fila sale de las cuatro parciales.
        ->and($conParciales->acumulado())->toBe(80.0);

    // Fila anterior a las parciales: se sigue leyendo su nota final y no hay
    // acumulado de donde sacarlo.
    $vieja = new Calificacion(['nota' => 8, 'tiene_parciales' => false]);

    expect((float) $vieja->notaEfectiva())->toBe(8.0)
        ->and($vieja->parciales())->toBe([null, null, null, null])
        ->and($vieja->acumulado())->toBeNull();
});