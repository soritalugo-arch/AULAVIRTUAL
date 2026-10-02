<?php

namespace App\Services;

/**
 * Las cuatro evaluaciones parciales, el acumulado sobre 100 y el promedio sobre 10.
 *
 * Cada parcial vale 25 puntos (el 25 % de la materia), asi que el profesor
 * captura P1..P4 en puntos de 0 a 25. De ahi salen los otros dos numeros sin que
 * nadie los escriba: el acumulado es la suma de las cuatro (0 a 100) y el
 * promedio es ese acumulado dividido entre 10 (0 a 10). Un acumulado de 70 es un
 * promedio de 7, y se aprueba con 6 de promedio (60 de 100).
 *
 * Los espacios que aun no se han capturado cuentan como cero (opcion A): asi el
 * numero que ve el profesor sube a medida que escribe, y una parcial nunca puede
 * pasarse de su tope de 25 puntos.
 */
class ParcialService
{
    /** Las cuatro parciales de la evaluacion, en orden. */
    public const PARCIALES = ['parcial1', 'parcial2', 'parcial3', 'parcial4'];

    /** Puntos que vale cada parcial: cuatro de 25 suman los 100 de la materia. */
    public const PESO = 25;

    /** Tope de una parcial: la captura no puede salirse de 0 a 25. */
    public const MAXIMO_PARCIAL = 25;

    /** Tope del acumulado. */
    public const MAXIMO_ACUMULADO = 100;

    /** Escala del promedio: de 0 a 10. */
    public const ESCALA_PROMEDIO = 10;

    /** Promedio minimo para aprobar. */
    public const MINIMO_APROBACION = 6;

    /** Lo mismo expresado en puntos acumulados, para hablar el mismo idioma. */
    public const MINIMO_APROBACION_ACUMULADO = 60;

    /** Etiquetas de las cuatro parciales, para la tabla del profesor. */
    public const ETIQUETAS = ['Parcial 1', 'Parcial 2', 'Parcial 3', 'Parcial 4'];

    /**
     * Deja las cuatro parciales listas para usar: vacio a null, un decimal, y
     * siempre cuatro posiciones aunque al metodo le lleguen menos.
     *
     * @param  array<int, float|string|null>  $parciales
     * @return array<int, float|null>
     */
    public static function normalizar(array $parciales): array
    {
        $valores = array_map(
            fn ($p) => ($p === null || $p === '') ? null : round((float) $p, 1),
            $parciales
        );

        return array_pad($valores, count(self::PARCIALES), null);
    }

    /**
     * Acumulado sobre 100: la suma de las cuatro parciales, sin capturarlas aun.
     *
     * Si las cuatro estan vacias devuelve null: todavia no hay nada que sumar.
     *
     * @param  array<int, float|string|null>  $parciales
     */
    public static function acumulado(array $parciales): ?float
    {
        $valores = self::normalizar($parciales);

        if (self::todasVacias($valores)) {
            return null;
        }

        return round(array_sum(array_map(fn ($v) => $v ?? 0.0, $valores)), 2);
    }

    /**
     * Promedio sobre 10: el acumulado dividido entre diez.
     *
     * @param  array<int, float|string|null>  $parciales
     */
    public static function promedio(array $parciales): ?float
    {
        $acumulado = self::acumulado($parciales);

        return $acumulado === null
            ? null
            : round($acumulado / self::ESCALA_PROMEDIO, 2);
    }

    /**
     * La nota aprueba o no, leyendo el promedio sobre 10.
     */
    public static function aprueba(float|int|string|null $nota): bool
    {
        return $nota !== null && (float) $nota >= self::MINIMO_APROBACION;
    }

    /**
     * Las cuatro parciales ya redondeadas y el promedio, listos para persistir.
     *
     * @param  array<int, float|string|null>  $parciales
     * @return array{parcial1: float|null, parcial2: float|null, parcial3: float|null, parcial4: float|null, promedio: float|null}
     */
    public static function calcularDesde(array $parciales): array
    {
        $valores = self::normalizar($parciales);

        return [
            'parcial1' => $valores[0],
            'parcial2' => $valores[1],
            'parcial3' => $valores[2],
            'parcial4' => $valores[3],
            'promedio' => self::promedio($valores),
        ];
    }

    /**
     * @param  array<int, float|null>  $valores
     */
    private static function todasVacias(array $valores): bool
    {
        return count(array_filter($valores, fn ($v) => $v !== null)) === 0;
    }
}
