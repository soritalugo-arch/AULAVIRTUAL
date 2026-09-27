<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
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
        $cuatrimestre = $curso->cuatrimestres()->orderByDesc('id_cuatrimestre')->first();
        if (!$cuatrimestre) {
            return back()->with('error', 'Este curso no tiene un cuatrimestre activo.');
        }
        $fecha   = $request->input('fecha', now()->toDateString());
        $idCuatr = $cuatrimestre->id_cuatrimestre;
        // asistencias ya registradas para la fecha (pre-marca los checkboxes)
        $asistenciasHoy = Asistencia::where('id_curso', $cursoId)
            ->where('id_cuatrimestre', $idCuatr)
            ->where('fecha', $fecha)
            ->pluck('presente', 'id_estudiante');
        $inscripciones = Inscripcion::where('id_curso', $cursoId)
            ->with('estudiante.usuario')
            ->get();
        $totalClases = $this->servicio->clasesDictadas($cursoId, $idCuatr);
        $estudiantes = $inscripciones->map(function ($ins) use ($cursoId, $idCuatr, $totalClases, $asistenciasHoy) {
            $est        = $ins->estudiante;
            $porcentaje = $this->servicio->porcentajeInasistencia($est->id_usuario, $cursoId, $idCuatr);
            return [
                'id'               => $est->id_usuario,
                'nombre'           => $est->usuario->nombres . ' ' . $est->usuario->apellidos,
                'totalClases'      => $totalClases,
                'porcentajeFaltas' => $porcentaje,
                'alerta'           => $this->servicio->nivelAlerta($porcentaje),
                // si ya existe registro para la fecha lo usa; si no, asume presente
                'presente'         => $asistenciasHoy->has($est->id_usuario)
                    ? (bool) $asistenciasHoy[$est->id_usuario]
                    : true,
            ];
        });
        return view('profesor.asistencia', compact('curso', 'cuatrimestre', 'fecha', 'estudiantes'));
    }
    // guarda o actualiza la asistencia de todos los inscritos para la fecha indicada
    public function guardar(Request $request)
    {
        $request->validate([
            'id_curso'        => 'required|exists:curso,id_curso',
            'id_cuatrimestre' => 'required|exists:cuatrimestre,id_cuatrimestre',
            'fecha'           => 'required|date',
            'presentes'       => 'nullable|array',
        ]);
        $inscripciones = Inscripcion::where('id_curso', $request->id_curso)->get();
        $presentesIds  = $request->input('presentes', []);
        foreach ($inscripciones as $ins) {
            $this->servicio->registrarAsistencia(
                $ins->id_estudiante,
                (int) $request->id_curso,
                (int) $request->id_cuatrimestre,
                $request->fecha,
                in_array($ins->id_estudiante, $presentesIds)
            );
        }
        return redirect()
            ->route('profesor.asistencia', ['curso' => $request->id_curso, 'fecha' => $request->fecha])
            ->with('success', 'Asistencia guardada para el ' . $request->fecha);
    }
}
