<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Services\CalificacionAsistenciaService;

class MisNotasController extends Controller
{
    public function __construct(private CalificacionAsistenciaService $servicio) {}
    // resumen de notas y asistencia del estudiante autenticado por curso
    public function index()
    {
        $estudiante    = auth()->user()->estudiante;
        $vigente       = $this->servicio->cuatrimestreVigente();
        $inscripciones = Inscripcion::where('id_estudiante', $estudiante->id_usuario)
            ->with('curso.cuatrimestres', 'curso.calificaciones')
            ->get();
        $resumen = $inscripciones->map(function ($ins) use ($estudiante, $vigente) {
            $curso        = $ins->curso;
            $cuatrimestres = $curso->cuatrimestres()->orderByDesc('fecha_inicio')->get();
            $cuatrimestre = $cuatrimestres->contains('id_cuatrimestre', $vigente?->id_cuatrimestre)
                ? $vigente
                : $cuatrimestres->first();
            if (!$cuatrimestre) return null;
            $idCuatr      = $cuatrimestre->id_cuatrimestre;
            $cuatrimestreTerminado = $cuatrimestre->fecha_fin->lt(now());
            $calificacion = $curso->calificaciones
                ->where('id_estudiante', $estudiante->id_usuario)
                ->where('id_cuatrimestre', $idCuatr)
                ->first();
            $porcentaje = $this->servicio->porcentajeInasistencia($estudiante->id_usuario, $curso->id_curso, $idCuatr);
            $clasesRegistradas = $this->servicio->clasesDictadas($curso->id_curso, $idCuatr);
            return [
                'curso'            => $curso->nombre,
                'cuatrimestre'     => $idCuatr,
                'cuatrimestreTerminado' => $cuatrimestreTerminado,
                'nota'             => $calificacion?->nota,
                'observaciones'    => $calificacion?->observaciones,
                'clasesRegistradas' => $clasesRegistradas,
                'totalClases'      => $this->servicio->totalClasesProgramadas($curso->id_curso, $idCuatr) ?? $clasesRegistradas,
                'porcentajeFaltas' => $porcentaje,
                'estado'           => $this->servicio->estadoEstudiante($calificacion?->nota, $porcentaje, $cuatrimestreTerminado),
                'alerta'           => $this->servicio->nivelAlerta($porcentaje),
                'promedioCurso'    => $this->servicio->promedioPorCurso($curso->id_curso, $idCuatr),
            ];
        })->filter()->values();
        $cuatrimestreTerminado = $vigente ? $vigente->fecha_fin->lt(now()) : true;
        return view('estudiante.notas', compact('resumen', 'cuatrimestreTerminado'));
    }
}
