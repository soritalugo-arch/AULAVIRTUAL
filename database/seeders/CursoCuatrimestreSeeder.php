<?php

namespace Database\Seeders;

use App\Models\Cuatrimestre;
use App\Models\Curso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoCuatrimestreSeeder extends Seeder
{
    public function run(): void
    {
        $cuatrimestres = Cuatrimestre::orderBy('fecha_inicio')->get();
        $cursos = Curso::orderBy('id_curso')->get();

        foreach ($cuatrimestres as $cuatrimestre) {
            foreach ($cursos as $curso) {
                DB::table('curso_cuatrimestre')->insertOrIgnore([
                    'curso_id' => $curso->id_curso,
                    'cuatrimestre_id' => $cuatrimestre->id_cuatrimestre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}