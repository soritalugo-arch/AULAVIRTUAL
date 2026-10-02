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
        $cursoPorId = $cursos->keyBy('id_curso');
        $materiasDeEtapa = $this->materiasDeEtapa($cursos);

        $idPorEmail = Usuario::whereIn('email', [
            DatabaseSeeder::EMAIL_EGRESADA,
            DatabaseSeeder::EMAIL_INASISTENTE,
            DatabaseSeeder::EMAIL_ALERTA,
            DatabaseSeeder::EMAIL_REPITIENTE,
            DatabaseSeeder::EMAIL_CONFLICTO,
        ])->pluck('id_usuario', 'email');

        self::$paresQ1 = [];

        // Nota del período pasado por curso, para un estudiante.
        $notasDe = function (int $sid, array $cids, callable $nota) use ($cursoPorId, $pasado): void {
            foreach ($cids as $cid) {
                self::$paresQ1[$sid][$cid] = $nota($cursoPorId[$cid]);
            }
        };

        // Egresada de Diseño Gráfico: aprobó TODA la carrera en el pasado.
        $this->registrarNota(
            $idPorEmail[DatabaseSeeder::EMAIL_EGRESADA],
            $this->cursosDeEgresada($cursoPorNombre),
            $pasado,
            $this->notaEgresada(...)
        );

        // Informática: cada estudiante tiene aprobadas solo las etapas
        // ANTERIORES a la suya, para que "en qué cuatrimestre voy" sea real.
        $e1 = $materiasDeEtapa['Informática'][1] ?? [];
        $e2 = $materiasDeEtapa['Informática'][2] ?? [];

        // Cuatrimestre 2 del plan: aprobaron el 1ro.
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_CONFLICTO], $e1, fn () => 8);

        // Carlos aprobó el 1ro pero reprobó Fundamentos (2do) por inasistencia:
        // en el vigente lo está repitiendo.
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_INASISTENTE], $e1, fn () => 8);
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_INASISTENTE], [$cursoPorNombre['Fundamentos de Programación']->id_curso], fn () => 8);

        // Andreina aprobó 1ro y 2do: va por el 3ro.
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_ALERTA], array_merge($e1, $e2), fn () => 8);

        // Diego aprobó 1ro y 2do pero reprobó Base de Datos I (3ro): la repite.
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_REPITIENTE], array_merge($e1, $e2), fn () => 8);
        $notasDe($idPorEmail[DatabaseSeeder::EMAIL_REPITIENTE], [$cursoPorNombre['Base de Datos I']->id_curso], fn () => self::NOTA_REPITIENTE_REPROBADA);

        // Estudiantes genéricos: aprueban las etapas anteriores a la suya
        // (todas con notas aprobadas, 7 a 9) para que la matrícula por etapa
        // se vea coherente en toda la base.
        $excluidos = $idPorEmail->values()->all();
        $generales = Estudiante::where('deuda', false)
            ->whereNotIn('id_usuario', $excluidos)
            ->orderBy('id_usuario')
            ->pluck('id_usuario');

        foreach ($generales as $sid) {
            $etapa = InscripcionSeeder::$etapaDeEstudiante[$sid] ?? 1;
            $carrera = InscripcionSeeder::$carreraDeEstudiante[$sid] ?? null;

            if ($etapa < 2 || ! $carrera) {
                continue;
            }

            $cids = [];
            for ($e = 1; $e < $etapa; $e++) {
                $cids = array_merge($cids, $materiasDeEtapa[$carrera][$e] ?? []);
            }

            foreach ($cids as $cid) {
                self::$paresQ1[$sid][$cid] = 7 + (($sid + $cid) % 3);
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

    /**
     * Materias por carrera y por etapa del plan, como ids:
     * [carrera => [etapa => [id_curso, ...]]].
     */
    private function materiasDeEtapa($cursos): array
    {
        $mapa = [];

        foreach (CarreraSeeder::CARRERAS as $carrera) {
            $mapa[$carrera['nombre']] = [];
        }

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            foreach ($item['carreras'] as $nombre => $etapa) {
                $mapa[$nombre][$etapa][] = $cursos[$i]->id_curso;
            }
        }

        return $mapa;
    }

    private function cursosDeEgresada($cursoPorNombre): array
    {
        $cursos = [];

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            if (array_key_exists(DatabaseSeeder::CARRERA_EGRESADA, $item['carreras'])) {
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