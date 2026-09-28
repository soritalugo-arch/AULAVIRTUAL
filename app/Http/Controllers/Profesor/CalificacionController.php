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
    public function notas($cursoId)
    {
        $profesor     = auth()->user()->profesor;
        $curso        = $profesor->cursos()->where('id_curso', $cursoId)->firstOrFail();
        $cuatrimestre = $curso->cuatrimestres()->orderByDesc('id_cuatrimestre')->first();
        if (!$cuatrimestre) {
            return back()->with('error', 'Este curso no tiene un cuatrimestre activo.');
        }
        $idCuatr     = $cuatrimestre->id_cuatrimestre;
        $totalClases = $this->servicio->clasesDictadas($cursoId, $idCuatr);
        $inscripciones = Inscripcion::where('id_curso', $cursoId)
            ->with('estudiante.usuario', 'estudiante.calificaciones')
            ->get();
        $estudiantes = $inscripciones->map(function ($ins) use ($cursoId, $idCuatr, $totalClases) {
            $est  = $ins->estudiante;
            $calificacion = $est->calificaciones
                ->where('id_curso', $cursoId)
                ->where('id_cuatrimestre', $idCuatr)
                ->first();
            $porcentaje = $this->servicio->porcentajeInasistencia($est->id_usuario, $cursoId, $idCuatr);
            return [
                'id'               => $est->id_usuario,
                'nombre'           => $est->usuario->nombres . ' ' . $est->usuario->apellidos,
                'nota'             => $calificacion?->nota,
                'observaciones'    => $calificacion?->observaciones,
                'totalClases'      => $totalClases,
                'porcentajeFaltas' => $porcentaje,
                'alerta'           => $this->servicio->nivelAlerta($porcentaje),
                'estado'           => $this->servicio->estadoEstudiante($calificacion?->nota, $porcentaje),
            ];
        });
        return view('profesor.notas', compact('curso', 'cuatrimestre', 'estudiantes'));
    }
    // guarda o actualiza las notas enviadas en el formulario
    public function guardarNota(Request $request)
    {
        $request->validate([
            'id_curso'              => 'required|exists:curso,id_curso',
            'id_cuatrimestre'       => 'required|exists:cuatrimestre,id_cuatrimestre',
            'notas'                 => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id_usuario',
            'notas.*.nota'          => 'nullable|integer|min:1|max:10',
            'notas.*.observaciones' => 'nullable|string|max:500',
        ]);
        foreach ($request->notas as $item) {
            if (is_null($item['nota'] ?? null)) continue; // omitir filas sin nota
            $this->servicio->guardarNota(
                (int) $item['id_estudiante'],
                (int) $request->id_curso,
                (int) $request->id_cuatrimestre,
                (int) $item['nota'],
                $item['observaciones'] ?? null
            );
        }
        return redirect()->route('profesor.notas', $request->id_curso)
            ->with('success', 'Notas guardadas correctamente.');
    }
}
