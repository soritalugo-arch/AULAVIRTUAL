<?php

namespace App\Services;

/**
 * Las cuatro evaluaciones parciales y el promedio ponderado automatico.
 *
 * El profesor captura cuatro parciales de 25 % cada una y el promedio sale
 * solo: no hay que volver a una nota final. Al ser cuatro de igual peso, el
 * promedio es la media de las cuatro, y los espacios que aun no se han
 * capturado cuentan como cero (opcion A): asi el numero que ve el profesor
 * sube a medida que escribe y el veredicto lee siempre ese mismo promedio.
 */
class ParcialService
{
    /** Las cuatro parciales de la evaluacion, en orden. */
    public const PARCIALES = ['parcial1', 'parcial2', 'parcial3', 'parcial4'];

    /** Peso de cada parcial: cuatro de 25 % suman el 100 %. */
    public const PESO = 25;

    /** Escala de una parcial y del promedio: la misma del aula, de 1 a 10. */
    public const ESCALA = 10;

    /** Etiquetas de las cuatro parciales, para la tabla del profesor. */
    public const ETIQUETAS = ['Parcial 1', 'Parcial 2', 'Parcial 3', 'Parcial 4'];

    /**
     * Promedio ponderado de las cuatro parciales (opcion A: vacio = 0).
     *
     * Si las cuatro estan vacias devuelve null: todavia no hay nada que promediar
     * y el veredicto sigue leyendo la nota final antigua.
     *
     * @param  array<int, float|null>  $parciales  [p1, p2, p3, p4]
     */
    public static function promedioPonderado(array $parciales): ?float
    {
        $valores = array_map(
            fn ($p) => $p === null || $p === '' ? null : (float) $p,
            $parciales
        );

        // Sin ninguna parcial cargada no hay promedio que mostrar.
        if (count(array_filter($valores, fn ($v) => $v !== null)) === 0) {
            return null;
        }

        // Cuatro parciales de 25 % es la media aritmetica de las cuatro, con las
        // que aun no se capturan contando como cero.
        $suma = array_sum(array_map(fn ($v) => $v ?? 0.0, $valores));

        return round($suma / count(self::PARCIALES), 2);
    }

    /**
     * Los mismos cuatro valores pero ya redondeados para persistir.
     *
     * @param  array<int, float|null>  $parciales
     * @return array{parcial1: float|null, parcial2: float|null, parcial3: float|null, parcial4: float|null, promedio: float|null}
     */
    public static function calcularDesde(array $parciales): array
    {
        $valores = array_map(
            fn ($p) => ($p === null || $p === '') ? null : round((float) $p, 1),
            $parciales
        );

        // Rellena a cuatro posiciones para que siempre existan parcial1..4, aunque
        // al metodo le lleguen menos (el controlador manda las cuatro, con null
        // en las que aun no se capturan).
        $valores = array_pad($valores, count(self::PARCIALES), null);

        return [
            'parcial1' => $valores[0],
            'parcial2' => $valores[1],
            'parcial3' => $valores[2],
            'parcial4' => $valores[3],
            'promedio' => self::promedioPonderado($valores),
        ];
    }
}