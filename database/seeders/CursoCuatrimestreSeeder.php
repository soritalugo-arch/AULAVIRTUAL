<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use App\Models\Curso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoCuatrimestreSeeder extends Seeder
{
    // clases programadas por curso y cuatrimestre (mismo total que el AsistenciaSeeder)
    private const TOTAL_CLASES = 12;

    public function run(): void
    {
        $cuatrimestres = Cuatrimestre::orderBy('fecha_inicio')->get();
        $cursos = Curso::orderBy('id_curso')->get();

        foreach ($cuatrimestres as $cuatrimestre) {
            foreach ($cursos as $curso) {
                DB::table('curso_cuatrimestre')->insertOrIgnore([
                    'curso_id' => $curso->id_curso,
                    'cuatrimestre_id' => $cuatrimestre->id_cuatrimestre,
                    'total_clases' => self::TOTAL_CLASES,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // respaldo para filas creadas antes de la migración de total_clases
        DB::table('curso_cuatrimestre')
            ->whereNull('total_clases')
            ->update([
                'total_clases' => self::TOTAL_CLASES,
                'updated_at' => now(),
            ]);
    }
}