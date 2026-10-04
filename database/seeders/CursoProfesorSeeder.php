<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoProfesorSeeder extends Seeder
{
    public function run(): void
    {
        $demo = Usuario::where('email', DatabaseSeeder::EMAIL_PROFESOR)->value('id_usuario');
        $genericos = Usuario::whereHas('roles', fn ($q) => $q->where('nombre', 'profesor'))
            ->where('id_usuario', '!=', $demo)
            ->orderBy('id_usuario')
            ->pluck('id_usuario')
            ->all();

        $cursos = Curso::orderBy('id_curso')->get();

        $profesorDeCurso = [];
        $slotsPorProfesor = array_fill_keys($genericos, []);

        foreach ($cursos as $curso) {
            if (in_array($curso->nombre, DatabaseSeeder::CURSOS_CONFLICTO, true)) {
                $profesorDeCurso[$curso->id_curso] = $demo;
                continue;
            }

            $slot = HorarioSeeder::slotDe($curso);

            $candidatos = array_values(array_filter(
                $genericos,
                fn ($p) => ! in_array($slot, $slotsPorProfesor[$p], true)
            ));

            usort($candidatos, function ($a, $b) use ($slotsPorProfesor) {
                $cargaA = count($slotsPorProfesor[$a]);
                $cargaB = count($slotsPorProfesor[$b]);

                return $cargaA === $cargaB ? $a <=> $b : $cargaA <=> $cargaB;
            });

            $elegido = $candidatos[0];
            $profesorDeCurso[$curso->id_curso] = $elegido;
            $slotsPorProfesor[$elegido][] = $slot;
        }

        $rows = [];
        foreach ($profesorDeCurso as $cursoId => $profesorId) {
            $rows[] = [
                'curso_id' => $cursoId,
                'profesor_id' => $profesorId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('curso_profesor')->insert($rows);
    }
}