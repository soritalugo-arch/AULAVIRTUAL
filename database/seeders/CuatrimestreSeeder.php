<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use Illuminate\Database\Seeder;

class CuatrimestreSeeder extends Seeder
{
    public const PASADO = ['2026-05-04', '2026-07-31'];
    public const ACTUAL = ['2026-09-07', '2026-12-18'];
    public const PROXIMO = ['2027-01-11', '2027-04-30'];

    public function run(): void
    {
        foreach ([self::PASADO, self::ACTUAL, self::PROXIMO] as $periodo) {
            Cuatrimestre::create([
                'fecha_inicio' => $periodo[0],
                'fecha_fin' => $periodo[1],
            ]);
        }
    }
}