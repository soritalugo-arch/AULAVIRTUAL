<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Cuatrimestre;
use App\Models\Inscripcion;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotaPublicada;

class CalificacionController extends Controller
{
    public function __construct(private CalificacionAsistenciaService $servicio) {}
    // lista de cursos del profesor autenticado
    public function cursos()
    {   
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $profesor = $usuario->profesor;
        $cursos = $profesor->cursos()
            ->with(['horarios', 'cuatrimestres' => fn($q) => $q->orderByDesc('id_cuatrimestre')])
            ->get();
        // Momento del período vigente: define qué ve el profesor (durante la
        // matrícula solo su horario; en cursado, sus estudiantes).
        $momento = $this->servicio->cuatrimestreVigente();
        return view('profesor.mis_cursos', compact('cursos', 'momento'));
    }
    // tabla de notas del curso con porcentaje de faltas por estudiante
    public function notas(int $cursoId, Request $request)
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $profesor = $usuario->profesor;
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
        // Durante la matrícula el profesor no ve estudiantes: todavía no se
        // sabe quién se inscribió. Cuando el período se cierra, la página de
        // notas queda en solo lectura.
        if ($cuatrimestre->estado === Cuatrimestre::ESTADO_MATRICULA) {
            return redirect()->route('profesor.cursos')
                ->with('error', 'La matrícula aún está abierta: no sabes quién se matriculó. Tus estudiantes aparecerán cuando el período esté en cursado.');
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
        return view('profesor.notas', compact('curso', 'cuatrimestre', 'cuatrimestres', 'estudiantes', 'cuatrimestreTerminado', 'clasesRegistradas', 'totalClases'))
            ->with('puedeEditar', $cuatrimestre->estado === Cuatrimestre::ESTADO_EN_CURSO);
    }
    // guarda o actualiza las notas enviadas en el formulario
    public function guardarNota(Request $request)
    {
        $idCurso = (int) $request->id_curso;
        $idCuatr = (int) $request->id_cuatrimestre;
    
        /** @var \App\Models\Usuario $usuario */
        $usuario = Auth::user();
        $profesor = $usuario->profesor;
        $curso = $profesor?->cursos()->whereKey($idCurso)->first();
    
        abort_unless($curso !== null, 403);
        abort_unless($curso->cuatrimestres()->whereKey($idCuatr)->exists(), 403);

        // Solo se califica mientras el período está EN CURSO: durante la
        // matrícula las notas todavía no existen y en un período cerrado ya
        // no se pueden modificar.
        abort_unless(
            $curso->cuatrimestres()->find($idCuatr)?->estado === Cuatrimestre::ESTADO_EN_CURSO,
            422,
            'Este período no está en cursado: las notas no se pueden guardar.'
        );

        $request->validate([
            'id_curso'              => 'required|exists:curso,id_curso',
            'id_cuatrimestre'       => 'required|exists:cuatrimestre,id_cuatrimestre',
            'notas'                 => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id_usuario',
            'notas.*.nota'          => 'nullable|integer|min:1|max:10',
            'notas.*.observaciones' => 'nullable|string|max:500',
        ]);

        //  contador de segundos
        $segundosRetraso = 0;

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

            $estudiante = \App\Models\Estudiante::with('usuario')->find($idEstudiante);

            if ($estudiante?->usuario?->email) {
                // En lugar de send(), usamos later() con el tiempo calculado
                Mail::to($estudiante->usuario->email)->later(
                    now()->addSeconds($segundosRetraso), 
                    new NotaPublicada(
                        $estudiante->usuario->nombres, 
                        $curso->nombre, 
                        (int) $item['nota']
                    )
                );
        
                // Sumamos 3 segundos para el próximo correo en la iteración
                $segundosRetraso += 15; 
            }
        }
    

        return redirect()->route('profesor.notas', ['curso' => $idCurso, 'cuatrimestre' => $idCuatr])
            ->with('success', 'Notas guardadas y correos enviados correctamente.');
    }

}
