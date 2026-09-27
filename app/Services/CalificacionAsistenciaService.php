<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Calificacion;

class CalificacionAsistenciaService
{
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
    // faltas / clases dictadas * 100; retorna 0 si no hay clases
    public function porcentajeInasistencia(int $idEstudiante, int $idCurso, int $idCuatrimestre): float
    {
        $totalClases = $this->clasesDictadas($idCurso, $idCuatrimestre);
        if ($totalClases === 0) return 0.0;
        $faltas = Asistencia::where('id_estudiante', $idEstudiante)
            ->where('id_curso', $idCurso)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->where('presente', false)
            ->count();
        return round(($faltas / $totalClases) * 100, 1);
    }
    // reglas: >30% faltas = reprobado; sin nota = en curso; nota>=6 = aprobado
    public function estadoEstudiante(?int $nota, float $porcentajeInasistencia): string
    {
        if ($porcentajeInasistencia > 30) return 'Reprobado';
        if (is_null($nota))               return 'En curso';
        return $nota >= 6 ? 'Aprobado' : 'Reprobado';
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
