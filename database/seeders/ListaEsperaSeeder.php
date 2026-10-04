<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListaEsperaSeeder extends Seeder
{
    public function run(): void
    {
        $filas = [];

        foreach (Curso::orderBy('id_curso')->get() as $curso) {
            $matriculados = Inscripcion::where('id_curso', $curso->id_curso)->count();

            if ($matriculados < $curso->limite_estudiantes) {
                continue;
            }

            $inscritos = Inscripcion::where('id_curso', $curso->id_curso)->pluck('id_estudiante')->all();
            $carrerasCurso = $curso->carreras->pluck('nombre')->all();

            $porCarrera = collect(InscripcionSeeder::$carreraDeEstudiante)
                ->filter(fn ($carrera) => in_array($carrera, $carrerasCurso, true))
                ->keys()
                ->reject(fn ($sid) => in_array($sid, $inscritos, true))
                ->sort()
                ->values();

            $resto = collect(InscripcionSeeder::$enrolados)
                ->keys()
                ->reject(fn ($sid) => in_array($sid, $inscritos, true))
                ->reject(fn ($sid) => $porCarrera->contains($sid))
                ->sort()
                ->values();

            $candidatos = $porCarrera->concat($resto);
            $cantidad = 2 + ($curso->id_curso % 4);
            $elegidos = $candidatos->take($cantidad);

            foreach ($elegidos->values() as $i => $sid) {
                $filas[] = [
                    'id_curso' => $curso->id_curso,
                    'id_estudiante' => $sid,
                    'created_at' => now()->subMinutes(($curso->id_curso * 25) + ($i * 12))->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];
            }
        }

        DB::table('lista_espera')->insert($filas);
    }
}