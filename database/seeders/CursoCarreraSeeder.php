<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Curso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoCarreraSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = Curso::orderBy('id_curso')->get();
        $carreras = Carrera::pluck('id_carrera', 'nombre');

        foreach ($cursos as $curso) {
            $carrerasDelCurso = CursoSeeder::CATALOGO[$curso->id_curso - 1]['carreras'];

            foreach ($carrerasDelCurso as $nombreCarrera => $etapa) {
                $carreraId = $carreras[$nombreCarrera] ?? null;
                if ($carreraId === null) {
                    continue;
                }

                // updateOrInsert: sirve tanto en base nueva como al correr el
                // seeder de nuevo, porque actualiza la etapa (y no duplica).
                DB::table('curso_carrera')->updateOrInsert(
                    ['curso_id' => $curso->id_curso, 'carrera_id' => $carreraId],
                    ['etapa' => $etapa, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}