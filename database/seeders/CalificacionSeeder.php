<?php

namespace Database\Seeders;

use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CalificacionSeeder extends Seeder
{
    public static array $paresQ1 = [];

    private const NOTA_EGRESADA = 8;
    private const NOTA_REPITIENTE_REPROBADA = 4;

    public function run(): void
    {
        $pasado = Cuatrimestre::orderBy('fecha_inicio')->first()->id_cuatrimestre;
        $cursos = Curso::orderBy('id_curso')->get();
        $cursoPorNombre = $cursos->keyBy('nombre');
        $cohortesPorCarrera = $this->cohortesPorCarrera($cursos);

        $idPorEmail = Usuario::whereIn('email', [
            DatabaseSeeder::EMAIL_EGRESADA,
            DatabaseSeeder::EMAIL_INASISTENTE,
            DatabaseSeeder::EMAIL_ALERTA,
            DatabaseSeeder::EMAIL_REPITIENTE,
        ])->pluck('id_usuario', 'email');

        self::$paresQ1 = [];

        $this->registrarNota($idPorEmail[DatabaseSeeder::EMAIL_EGRESADA], $this->cursosDeEgresada($cursoPorNombre), $pasado, $this->notaEgresada(...));

        $this->registrarNota($idPorEmail[DatabaseSeeder::EMAIL_INASISTENTE], [$cursoPorNombre['Fundamentos de Programación']], $pasado, fn () => 8);
        $this->registrarNota($idPorEmail[DatabaseSeeder::EMAIL_ALERTA], [$cursoPorNombre['Fundamentos de Programación']], $pasado, fn () => 8);
        $this->registrarNota($idPorEmail[DatabaseSeeder::EMAIL_REPITIENTE], [
            $cursoPorNombre['Base de Datos I'],
            $cursoPorNombre['Redes de Computadoras'],
        ], $pasado, fn ($curso) => $curso->nombre === 'Base de Datos I' ? self::NOTA_REPITIENTE_REPROBADA : 8);

        $excluidos = $idPorEmail->values()->all();
        $generales = Estudiante::where('deuda', false)
            ->whereNotIn('id_usuario', $excluidos)
            ->orderBy('id_usuario')
            ->pluck('id_usuario');

        $todos = $cursos->pluck('id_curso')->all();

        foreach ($generales as $sid) {
            $enQ2 = InscripcionSeeder::$enrolados[$sid] ?? [];
            $carrera = InscripcionSeeder::$carreraDeEstudiante[$sid] ?? null;
            $cohorte = $carrera ? ($cohortesPorCarrera[$carrera] ?? []) : [];
            $candidatos = array_values(array_diff($cohorte, $enQ2));

            if (count($candidatos) < 1) {
                $candidatos = array_values(array_slice(array_diff($todos, $enQ2), 0, 1));
            }

            $candidatos = array_slice($candidatos, 0, 2);

            foreach ($candidatos as $cid) {
                $this->notaGenerica($sid, $cid);
            }
        }

        $filas = [];
        foreach (self::$paresQ1 as $sid => $cursosDelEstudiante) {
            foreach ($cursosDelEstudiante as $cid => $nota) {
                $filas[] = [
                    'id_estudiante' => $sid,
                    'id_curso' => $cid,
                    'id_cuatrimestre' => $pasado,
                    'nota' => $nota,
                    'observaciones' => $this->observacionPara($nota),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($filas, 500) as $lote) {
            Calificacion::insert($lote);
        }
    }

    private function cohortesPorCarrera($cursos): array
    {
        $mapa = [];

        foreach (CarreraSeeder::CARRERAS as $carrera) {
            $mapa[$carrera['nombre']] = [];
        }

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            foreach ($item['carreras'] as $nombre) {
                $mapa[$nombre][] = $cursos[$i]->id_curso;
            }
        }

        return $mapa;
    }

    private function cursosDeEgresada($cursoPorNombre): array
    {
        $cursos = [];

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            if (in_array(DatabaseSeeder::CARRERA_EGRESADA, $item['carreras'], true)) {
                $cursos[] = $cursoPorNombre[$item['nombre']];
            }
        }

        return $cursos;
    }

    private function registrarNota(int $sid, array $cursos, int $pasado, callable $nota): void
    {
        foreach ($cursos as $curso) {
            self::$paresQ1[$sid][$curso->id_curso] = $nota($curso);
        }
    }

    private function notaEgresada($curso): int
    {
        return self::NOTA_EGRESADA + (($curso->id_curso) % 3);
    }

    private function notaGenerica(int $sid, int $cid): void
    {
        $r = ($sid * 7 + $cid * 3) % 10;

        $nota = match (true) {
            $r < 2 => 9,
            $r < 5 => 8,
            $r < 7 => 10,
            $r < 9 => 7,
            default => 5,
        };

        self::$paresQ1[$sid][$cid] = $nota;
    }

    private function observacionPara(int $nota): string
    {
        return match (true) {
            $nota >= 9 => 'Excelente desempeño en la materia.',
            $nota >= 8 => 'Muy buen desempeño en la materia.',
            $nota === 7 => 'Buen desempeño; puede mejorar.',
            default => 'Debe reforzar los contenidos de la materia.',
        };
    }
}