<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function __construct(private CalificacionAsistenciaService $servicio) {}
    // lista de cursos del profesor autenticado
    public function cursos()
    {
        $profesor = auth()->user()->profesor;
        $cursos = $profesor->cursos()
            ->with(['cuatrimestres' => fn($q) => $q->orderByDesc('id_cuatrimestre')])
            ->get();
        return view('profesor.mis_cursos', compact('cursos'));
    }
    // tabla de notas del curso con porcentaje de faltas por estudiante
    public function notas($cursoId, Request $request)
    {
        $profesor     = auth()->user()->profesor;
        $curso        = $profesor->cursos()->where('id_curso', $cursoId)->firstOrFail();
        $cuatrimestres = $curso->cuatrimestres()->orderByDesc('fecha_inicio')->get();
        if ($request->filled('cuatrimestre')) {
            $cuatrimestre = $curso->cuatrimestres()->find($request->integer('cuatrimestre')) ?? abort(404);
        } else {
            $vigente = $this->servicio->cuatrimestreVigente();
            $cuatrimestre = $cuatrimestres->contains('id_cuatrimestre', $vigente?->id_cuatrimestre)
                ? $vigente
                : $cuatrimestres->first();
        }
        if (!$cuatrimestre) {
            return back()->with('error', 'Este curso no tiene un cuatrimestre activo.');
        }
        $idCuatr     = $cuatrimestre->id_cuatrimestre;
        $clasesRegistradas = $this->servicio->clasesDictadas($cursoId, $idCuatr);
        $totalClases       = $this->servicio->totalClasesProgramadas($cursoId, $idCuatr) ?? $clasesRegistradas;
        $cuatrimestreTerminado = $cuatrimestre->fecha_fin->lt(now());
        $inscripciones = Inscripcion::where('id_curso', $cursoId)
            ->with('estudiante.usuario', 'estudiante.calificaciones')
            ->get();
        $estudiantes = $inscripciones->map(function ($ins) use ($cursoId, $idCuatr, $clasesRegistradas, $totalClases, $cuatrimestreTerminado) {
            $est  = $ins->estudiante;
            $calificacion = $est->calificaciones
                ->where('id_curso', $cursoId)
                ->where('id_cuatrimestre', $idCuatr)
                ->first();
            $faltas     = $this->servicio->faltasEstudiante($est->id_usuario, $cursoId, $idCuatr);
            $porcentaje = $this->servicio->porcentajeInasistencia($est->id_usuario, $cursoId, $idCuatr);
            return [
                'id'                 => $est->id_usuario,
                'nombre'             => $est->usuario->nombres . ' ' . $est->usuario->apellidos,
                'nota'               => $calificacion?->nota,
                'observaciones'      => $calificacion?->observaciones,
                'clasesRegistradas'  => $clasesRegistradas,
                'totalClases'        => $totalClases,
                'faltas'             => $faltas,
                'porcentajeFaltas'   => $porcentaje,
                'alerta'             => $this->servicio->nivelAlerta($porcentaje),
                'estado'             => $this->servicio->estadoEstudiante($calificacion?->nota, $porcentaje, $cuatrimestreTerminado),
            ];
        });
        return view('profesor.notas', compact('curso', 'cuatrimestre', 'cuatrimestres', 'estudiantes', 'cuatrimestreTerminado', 'clasesRegistradas', 'totalClases'));
    }
    // guarda o actualiza las notas enviadas en el formulario
    public function guardarNota(Request $request)
    {
        $idCurso = (int) $request->id_curso;
        $idCuatr = (int) $request->id_cuatrimestre;
        $profesor = auth()->user()->profesor;
        $curso = $profesor?->cursos()->whereKey($idCurso)->first();
        abort_unless($curso !== null, 403);
        abort_unless($curso->cuatrimestres()->whereKey($idCuatr)->exists(), 403);

        $request->validate([
            'id_curso'              => 'required|exists:curso,id_curso',
            'id_cuatrimestre'       => 'required|exists:cuatrimestre,id_cuatrimestre',
            'notas'                 => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id_usuario',
            'notas.*.nota'          => 'nullable|integer|min:1|max:10',
            'notas.*.observaciones' => 'nullable|string|max:500',
        ]);
        foreach ($request->notas as $item) {
            $idEstudiante = (int) $item['id_estudiante'];
            if (is_null($item['nota'] ?? null)) {
                $this->servicio->quitarNota($idEstudiante, $idCurso, $idCuatr);
                continue;
            }
            $this->servicio->guardarNota(
                $idEstudiante,
                $idCurso,
                $idCuatr,
                (int) $item['nota'],
                $item['observaciones'] ?? null
            );
        }
        return redirect()->route('profesor.notas', ['curso' => $idCurso, 'cuatrimestre' => $idCuatr])
            ->with('success', 'Notas guardadas correctamente.');
    }
}
