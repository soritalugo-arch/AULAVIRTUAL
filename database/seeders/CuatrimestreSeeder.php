<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use Illuminate\Database\Seeder;

class CuatrimestreSeeder extends Seeder
{
    /**
     * Tres cuatrimestres pasados completos + el presente, tal como pidió
     * el director del plantel.
     *
     * No hay cuatrimestre futuro: el sistema no lo maneja. Cuando el presente
     * se finaliza, el sistema crea automáticamente un cuatrimestre nuevo en
     * "pre_matricula", para que desde el panel de la rectora se arme la oferta.
     */
    public const PASADO_1 = ['2025-09-08', '2025-12-19'];
    public const PASADO_2 = ['2026-01-12', '2026-04-30'];
    public const PASADO_3 = ['2026-05-04', '2026-07-31'];
    public const PRESENTE = ['2026-09-07', '2026-12-18'];

    public function run(): void
    {
        $periodos = [
            ['fechas' => self::PASADO_1, 'estado' => Cuatrimestre::ESTADO_FINALIZADO],
            ['fechas' => self::PASADO_2, 'estado' => Cuatrimestre::ESTADO_FINALIZADO],
            ['fechas' => self::PASADO_3, 'estado' => Cuatrimestre::ESTADO_FINALIZADO],
            ['fechas' => self::PRESENTE, 'estado' => Cuatrimestre::ESTADO_MATRICULA],
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