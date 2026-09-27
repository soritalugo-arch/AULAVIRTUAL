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
        $inscripciones = Inscripcion::where('id_estudiante', $estudiante->id_usuario)
            ->with('curso.cuatrimestres', 'curso.calificaciones')
            ->get();
        $resumen = $inscripciones->map(function ($ins) use ($estudiante) {
            $curso        = $ins->curso;
            $cuatrimestre = $curso->cuatrimestres()->orderByDesc('id_cuatrimestre')->first();
            if (!$cuatrimestre) return null;
            $idCuatr      = $cuatrimestre->id_cuatrimestre;
            $calificacion = $curso->calificaciones
                ->where('id_estudiante', $estudiante->id_usuario)
                ->where('id_cuatrimestre', $idCuatr)
                ->first();
            $porcentaje = $this->servicio->porcentajeInasistencia($estudiante->id_usuario, $curso->id_curso, $idCuatr);
            return [
                'curso'            => $curso->nombre,
                'cuatrimestre'     => $idCuatr,
                'nota'             => $calificacion?->nota,
                'observaciones'    => $calificacion?->observaciones,
                'totalClases'      => $this->servicio->clasesDictadas($curso->id_curso, $idCuatr),
                'porcentajeFaltas' => $porcentaje,
                'estado'           => $this->servicio->estadoEstudiante($calificacion?->nota, $porcentaje),
                'alerta'           => $this->servicio->nivelAlerta($porcentaje),
                'promedioCurso'    => $this->servicio->promedioPorCurso($curso->id_curso, $idCuatr),
            ];
        })->filter()->values();
        return view('estudiante.notas', compact('resumen'));
    }
}
