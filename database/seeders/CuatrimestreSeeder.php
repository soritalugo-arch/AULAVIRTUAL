<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use Illuminate\Database\Seeder;

class CuatrimestreSeeder extends Seeder
{
    public const PASADO = ['2026-05-04', '2026-07-31'];
    public const ACTUAL = ['2026-09-07', '2026-12-18'];
    public const PROXIMO = ['2027-01-11', '2027-04-30'];

    /**
     * Cada período arranca en un momento distinto:
     * - el pasado ya cerró (histórico, solo lectura);
     * - el vigente está con la MATRÍCULA ABIERTA para que la rectora pueda
     *   mostrar inscripción y luego pasar a "en cursado" con un clic;
     * - el próximo también espera su inscripción.
     */
    public function run(): void
    {
        $periodos = [
            ['fechas' => self::PASADO, 'estado' => Cuatrimestre::ESTADO_CERRADO],
            ['fechas' => self::ACTUAL, 'estado' => Cuatrimestre::ESTADO_MATRICULA],
            ['fechas' => self::PROXIMO, 'estado' => Cuatrimestre::ESTADO_MATRICULA],
        ];

        foreach ($periodos as $periodo) {
            Cuatrimestre::create([
                'fecha_inicio' => $periodo['fechas'][0],
                'fecha_fin' => $periodo['fechas'][1],
                'estado' => $periodo['estado'],
            ]);
        }
    }
}