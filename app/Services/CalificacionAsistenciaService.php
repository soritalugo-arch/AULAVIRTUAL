<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Services\ParcialService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalificacionAsistenciaService
{
    private const DIAS_SEMANA = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    // cuatrimestre cuyo rango de fechas incluye la fecha actual
    public function cuatrimestreVigente(): ?Cuatrimestre
    {
        return Cuatrimestre::where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->orderByDesc('fecha_inicio')
            ->first();
    }
    // cuatrimestre cuyo rango de fechas incluye la fecha actual
    public function cuatrimestreEnCurso(): ?Cuatrimestre
    {
        return $this->cuatrimestreVigente()
            ?? Cuatrimestre::where('fecha_inicio', '<=', now())
                ->orderByDesc('fecha_inicio')
                ->first();
    }
    // upsert de nota por estudiante, curso y cuatrimestre
    public function guardarNota(int $idEstudiante, int $idCurso, int $idCuatrimestre, int $nota, ?string $observaciones): Calificacion
    {
        return Calificacion::updateOrCreate(
            ['id_estudiante' => $idEstudiante, 'id_curso' => $idCurso, 'id_cuatrimestre' => $idCuatrimestre],
            ['nota' => $nota, 'observaciones' => $observaciones]
        );
    }

    /**
     * Upsert de las cuatro parciales con su promedio ya calculado.
     *
     * El promedio no lo escribe el profesor: sale de ParcialService al vuelo, y
     * la columna "nota" queda null porque en este flujo la definitiva es el
     * promedio. Si el profesor borra las cuatro parciales, la fila se elimina:
     * una calificacion sin ningun numero no es una calificacion.
     *
     * @param  array<int, float|null>  $parciales  [p1, p2, p3, p4]
     */
    public function guardarParciales(int $idEstudiante, int $idCurso, int $idCuatrimestre, array $parciales, ?string $observaciones = null): ?Calificacion
    {
        $valores = ParcialService::calcularDesde($parciales);

        // Sin ninguna parcial cargada no queda nada que promediar: se borra la
        // fila, igual que hacia la version de nota final vacia.
        if ($valores['promedio'] === null) {
            $this->quitarNota($idEstudiante, $idCurso, $idCuatrimestre);

            return null;
        }

        return Calificacion::updateOrCreate(
            ['id_estudiante' => $idEstudiante, 'id_curso' => $idCurso, 'id_cuatrimestre' => $idCuatrimestre],
            $valores + [
                'tiene_parciales' => true,
                // La definitiva vive en el promedio; "nota" se deja sincronizada
                // para que las consultas que leen esa columna sigan cuadrando.
                'nota' => (int) round($valores['promedio']),
                'observaciones' => $observaciones,
            ]
        );
    }
    // upsert de asistencia por estudiante, curso, cuatrimestre y fecha
    public function registrarAsistencia(int $idEstudiante, int $idCurso, int $idCuatrimestre, string $fecha, bool $presente): Asistencia
    {
        return Asistencia::updateOrCreate(
            ['id_estudiante' => $idEstudiante, 'id_curso' => $idCurso, 'id_cuatrimestre' => $idCuatrimestre, 'fecha' => $fecha],
            ['presente' => $presente]
        );
    }
    // fechas distintas registradas en asistencia para el curso y cuatrimestre
    public function clasesDictadas(int $idCurso, int $idCuatrimestre): int
    {
        return Asistencia::where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->distinct('fecha')
            ->count('fecha');
    }
    // total de clases programadas para el curso y cuatrimestre (columna de la pivot);
    // null si no está definido
    public function totalClasesProgramadas(int $idCurso, int $idCuatrimestre): ?int
    {
        $total = DB::table('curso_cuatrimestre')
            ->where('curso_id', $idCurso)
            ->where('cuatrimestre_id', $idCuatrimestre)
            ->value('total_clases');

        return $total === null ? null : (int) $total;
    }
    // faltas / clases programadas * 100 cuando el total programado existe;
    // si no, faltas / clases dictadas * 100; retorna 0 si no hay clases
    public function porcentajeInasistencia(int $idEstudiante, int $idCurso, int $idCuatrimestre): float
    {
        return $this->inasistenciaDesdeConteos(
            $this->faltasEstudiante($idEstudiante, $idCurso, $idCuatrimestre),
            $this->totalClasesProgramadas($idCurso, $idCuatrimestre) ?? 0,
            $this->clasesDictadas($idCurso, $idCuatrimestre),
        );
    }

    /**
     * Porcentaje de inasistencia a partir de conteos ya resueltos.
     *
     * Es la misma regla que porcentajeInasistencia(), pero sin las consultas por
     * curso. El historial del estudiante trae faltas y clases de todos sus
     * cursos en una sola consulta y las pasa por aqui, para que el reporte diga
     * exactamente lo mismo que el modulo del profesor. Si la regla del
     * denominador cambia, cambia en un solo lugar.
     *
     * @param  int  $faltas  cantidad de clases a las que faltó
     * @param  int  $programadas  total_clases de la pivot, 0 si no está definido
     * @param  int  $dictadas  fechas distintas con asistencia registrada
     */
    public function inasistenciaDesdeConteos(int $faltas, int $programadas, int $dictadas): float
    {
        $denominador = $programadas > 0 ? $programadas : $dictadas;

        if ($denominador <= 0) {
            return 0.0;
        }

        return round(($faltas / $denominador) * 100, 1);
    }
    // cantidad de inasistencias del estudiante en el curso y cuatrimestre
    public function faltasEstudiante(int $idEstudiante, int $idCurso, int $idCuatrimestre): int
    {
        return Asistencia::where('id_estudiante', $idEstudiante)
            ->where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->where('presente', false)
            ->count();
    }
    // "sin nota" = sin fila: borra la calificacion del estudiante en curso/cuatrimestre
    public function quitarNota(int $idEstudiante, int $idCurso, int $idCuatrimestre): void
    {
        Calificacion::where('id_estudiante', $idEstudiante)
            ->where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->delete();
    }
    // reglas: >30% faltas = reprobado; sin nota = en curso; nota>=6 = aprobado.
    // Mientras el cuatrimestre esta activo el veredicto por faltas es "presunto".
    //
    // $nota admite decimales porque con cuatro parciales lo que llega es el
    // promedio (pueden ser 7.25), no un entero.
    public function estadoEstudiante(int|float|null $nota, float $porcentajeInasistencia, bool $cuatrimestreTerminado = true): string
    {
        if ($porcentajeInasistencia > 30) {
            return $cuatrimestreTerminado ? 'Reprobado' : 'Reprobado (presunto)';
        }
        if (is_null($nota))               return 'En curso';
        return $nota >= 6 ? 'Aprobado' : 'Reprobado';
    }
    // el curso se dicta los dias indicados en su horario; sin horario se acepta cualquier fecha
    public function esDiaDeClase(Curso $curso, Carbon $fecha): bool
    {
        $horarios = $curso->horarios;

        if ($horarios->isEmpty()) {
            return true;
        }

        return $horarios->pluck('dia_semana')->contains(self::DIAS_SEMANA[$fecha->dayOfWeek]);
    }
    // ok < 25%, advertencia 25-30%, peligro > 30%
    public function nivelAlerta(float $porcentaje): string
    {
        if ($porcentaje > 30)  return 'peligro';
        if ($porcentaje >= 25) return 'advertencia';
        return 'ok';
    }
    // promedio de notas del curso en el cuatrimestre; null si no hay notas.
    // Es el promedio de las cuatro parciales (COALESCE) y no el entero de la
    // columna nota, para que el alumno vea el mismo número que su profesor.
    public function promedioPorCurso(int $idCurso, int $idCuatrimestre): ?float
    {
        $promedio = Calificacion::where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->avg(DB::raw('COALESCE(promedio, nota)'));
        return $promedio !== null ? round((float) $promedio, 2) : null;
    }
}
