<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function __construct(private CalificacionAsistenciaService $servicio) {}
    // muestra la hoja de asistencia; fecha por defecto: hoy
    public function registrar($cursoId, Request $request)
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
        $fecha   = $request->input('fecha', now()->toDateString());
        $idCuatr = $cuatrimestre->id_cuatrimestre;
        $cuatrimestreTerminado = $cuatrimestre->fecha_fin->lt(now());
        $horarios = $curso->horarios;
        // asistencias ya registradas para la fecha (pre-marca los checkboxes)
        $asistenciasHoy = Asistencia::where('id_curso', $cursoId)
            ->where('id_cuatrimestre', $idCuatr)
            ->where('fecha', $fecha)
            ->pluck('presente', 'id_estudiante');
        $inscripciones = Inscripcion::where('id_curso', $cursoId)
            ->with('estudiante.usuario')
            ->get();
        $clasesRegistradas = $this->servicio->clasesDictadas($cursoId, $idCuatr);
        $totalClases       = $this->servicio->totalClasesProgramadas($cursoId, $idCuatr) ?? $clasesRegistradas;
        $estudiantes = $inscripciones->map(function ($ins) use ($cursoId, $idCuatr, $clasesRegistradas, $totalClases, $asistenciasHoy) {
            $est        = $ins->estudiante;
            $faltas     = $this->servicio->faltasEstudiante($est->id_usuario, $cursoId, $idCuatr);
            $porcentaje = $this->servicio->porcentajeInasistencia($est->id_usuario, $cursoId, $idCuatr);
            return [
                'id'                 => $est->id_usuario,
                'nombre'             => $est->usuario->nombres . ' ' . $est->usuario->apellidos,
                'clasesRegistradas'  => $clasesRegistradas,
                'totalClases'        => $totalClases,
                'faltas'             => $faltas,
                'porcentajeFaltas'   => $porcentaje,
                'alerta'             => $this->servicio->nivelAlerta($porcentaje),
                // si ya existe registro para la fecha lo usa; si no, asume presente
                'presente'           => $asistenciasHoy->has($est->id_usuario)
                    ? (bool) $asistenciasHoy[$est->id_usuario]
                    : true,
            ];
        });
        return view('profesor.asistencia', compact('curso', 'cuatrimestre', 'cuatrimestres', 'fecha', 'estudiantes', 'cuatrimestreTerminado', 'horarios', 'clasesRegistradas', 'totalClases'))
            ->with('puedeEditar', $cuatrimestre->estado === Cuatrimestre::ESTADO_EN_CURSO);
    }
    // guarda o actualiza la asistencia de todos los inscritos para la fecha indicada
    public function guardar(Request $request)
    {
        $idCurso = (int) $request->id_curso;
        $idCuatr = (int) $request->id_cuatrimestre;
        $profesor = auth()->user()->profesor;
        $curso = $profesor?->cursos()->whereKey($idCurso)->first();
        abort_unless($curso !== null, 403);
        $cuatrimestre = $curso->cuatrimestres()->find($idCuatr);
        abort_unless($cuatrimestre !== null, 403);

        // Solo se registra asistencia mientras el período está EN CURSO.
        abort_unless(
            $cuatrimestre->estado === Cuatrimestre::ESTADO_EN_CURSO,
            422,
            'Este período no está en cursado: la asistencia no se puede guardar.'
        );

        $request->validate([
            'id_curso'        => 'required|exists:curso,id_curso',
            'id_cuatrimestre' => 'required|exists:cuatrimestre,id_cuatrimestre',
            'fecha'           => 'required|date',
            'presentes'       => 'nullable|array',
        ]);

        $fecha = $request->date('fecha');
        abort_unless(
            $fecha->between($cuatrimestre->fecha_inicio, $cuatrimestre->fecha_fin),
            422,
            'La fecha está fuera del período de la materia.'
        );
        abort_unless(
            $this->servicio->esDiaDeClase($curso, $fecha),
            422,
            'La fecha no corresponde a un día de clase del curso.'
        );

        $inscripciones = Inscripcion::where('id_curso', $idCurso)->get();
        $presentesIds  = $request->input('presentes', []);
        foreach ($inscripciones as $ins) {
            $this->servicio->registrarAsistencia(
                $ins->id_estudiante,
                $idCurso,
                $idCuatr,
                $request->fecha,
                in_array($ins->id_estudiante, $presentesIds)
            );
        }
        return redirect()
            ->route('profesor.asistencia', ['curso' => $idCurso, 'cuatrimestre' => $idCuatr, 'fecha' => $request->fecha])
            ->with('success', 'Asistencia guardada para el ' . $request->fecha);
    }
}
