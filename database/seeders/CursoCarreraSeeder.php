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

        foreach ($cursos as $curso) {
            $carreras = CursoSeeder::CATALOGO[$curso->id_curso - 1]['carreras'];

            foreach ($carreras as $nombre) {
                $carrera = Carrera::where('nombre', $nombre)->first();
                DB::table('curso_carrera')->insertOrIgnore([
                    'curso_id' => $curso->id_curso,
                    'carrera_id' => $carrera->id_carrera,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}