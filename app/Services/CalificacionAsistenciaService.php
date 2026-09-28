<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
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
    // upsert de nota por estudiante, curso y cuatrimestre
    public function guardarNota(int $idEstudiante, int $idCurso, int $idCuatrimestre, int $nota, ?string $observaciones): Calificacion
    {
        return Calificacion::updateOrCreate(
            ['id_estudiante' => $idEstudiante, 'id_curso' => $idCurso, 'id_cuatrimestre' => $idCuatrimestre],
            ['nota' => $nota, 'observaciones' => $observaciones]
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
        $denominador = $this->totalClasesProgramadas($idCurso, $idCuatrimestre)
            ?? $this->clasesDictadas($idCurso, $idCuatrimestre);
        if ($denominador <= 0) return 0.0;
        return round(($this->faltasEstudiante($idEstudiante, $idCurso, $idCuatrimestre) / $denominador) * 100, 1);
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
    public function estadoEstudiante(?int $nota, float $porcentajeInasistencia, bool $cuatrimestreTerminado = true): string
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
    // promedio de notas del curso en el cuatrimestre; null si no hay notas
    public function promedioPorCurso(int $idCurso, int $idCuatrimestre): ?float
    {
        $promedio = Calificacion::where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->avg('nota');
        return $promedio !== null ? round((float) $promedio, 2) : null;
    }
}
