<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Services\CalificacionAsistenciaService;
use App\Services\ParcialService;
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
        // Momento del perÃ­odo presente: define quÃ© ve el profesor (durante la
        // matrÃ­cula solo su horario; en cursado, sus estudiantes).
        $momento = $this->servicio->cuatrimestrePresente();
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
            $vigente = $this->servicio->cuatrimestrePresente();
            $cuatrimestre = $cuatrimestres->contains('id_cuatrimestre', $vigente?->id_cuatrimestre)
                ? $vigente
                : $cuatrimestres->first();
        }
        if (!$cuatrimestre) {
            return back()->with('error', 'Este curso no tiene un cuatrimestre activo.');
        }
        // Durante la matrÃ­cula el profesor no ve estudiantes: todavÃ­a no se
        // sabe quiÃ©n se inscribiÃ³. Cuando el perÃ­odo se cierra, la pÃ¡gina de
        // notas queda en solo lectura.
        if ($cuatrimestre->estado === Cuatrimestre::ESTADO_MATRICULA) {
            return redirect()->route('profesor.cursos')
                ->with('error', 'La matrÃ­cula aÃºn estÃ¡ abierta: no sabes quiÃ©n se matriculÃ³. Tus estudiantes aparecerÃ¡n cuando el perÃ­odo estÃ© en cursado.');
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
            // Mientras el perÃ­odo sigue abierto el porcentaje sale de las clases
            // dictadas; una vez terminado, del total programado.
            $porcentaje = $this->servicio->porcentajeInasistencia($est->id_usuario, $cursoId, $idCuatr, $cuatrimestreTerminado);
            // Lo que manda es el promedio de las parciales; la nota final solo
            // aparece si la fila todavia no tiene parciales (datos anteriores).
            $notaEfectiva = $calificacion?->notaEfectiva();

            return [
                'id'                 => $est->id_usuario,
                'nombre'             => $est->usuario->nombres . ' ' . $est->usuario->apellidos,
                'nota'               => $notaEfectiva,
                'parciales'          => $calificacion?->parciales() ?? [null, null, null, null],
                // El acumulado sobre 100 (la suma de las cuatro parciales) y el
                // promedio sobre 10 salen de las parciales, no se escriben.
                'acumulado'          => $calificacion?->acumulado(),
                'observaciones'      => $calificacion?->observaciones,
                'clasesRegistradas'  => $clasesRegistradas,
                'totalClases'        => $totalClases,
                'faltas'             => $faltas,
                'porcentajeFaltas'   => $porcentaje,
                'alerta'             => $this->servicio->nivelAlerta($porcentaje),
                'estado'             => $this->servicio->estadoEstudiante($notaEfectiva, $porcentaje, $cuatrimestreTerminado),
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

        // Solo se califica mientras el perÃ­odo estÃ¡ EN CURSO: durante la
        // matrÃ­cula las notas todavÃ­a no existen y en un perÃ­odo cerrado ya
        // no se pueden modificar.
        abort_unless(
            $curso->cuatrimestres()->find($idCuatr)?->estado === Cuatrimestre::ESTADO_EN_CURSO,
            422,
            'Este perÃ­odo no estÃ¡ en cursado: las notas no se pueden guardar.'
        );

        $request->validate([
            'id_curso'              => 'required|exists:curso,id_curso',
            'id_cuatrimestre'       => 'required|exists:cuatrimestre,id_cuatrimestre',
            'notas'                 => 'required|array',
            'notas.*.id_estudiante' => 'required|exists:estudiante,id_usuario',
            // Cada parcial se captura en PUNTOS sobre 25 (el 25 % de la materia),
            // con decimales permitidos (18.5) y vacias porque el profesor carga
            // de a una. El tope es el del parcial: el acumulado nunca puede
            // pasarse de 100 porque 4 x 25 = 100.
            'notas.*.parcial1'      => 'nullable|numeric|min:0|max:25',
            'notas.*.parcial2'      => 'nullable|numeric|min:0|max:25',
            'notas.*.parcial3'      => 'nullable|numeric|min:0|max:25',
            'notas.*.parcial4'      => 'nullable|numeric|min:0|max:25',
            'notas.*.observaciones' => 'nullable|string|max:500',
        ]);

        // Espaciador entre correos: se encolan en vez de dispararse de golpe.
        $segundosRetraso = 0;
        $notificados = 0;

        foreach ($request->notas as $item) {
            $idEstudiante = (int) $item['id_estudiante'];

            $parciales = [
                $item['parcial1'] ?? null,
                $item['parcial2'] ?? null,
                $item['parcial3'] ?? null,
                $item['parcial4'] ?? null,
            ];

            // Que parciales cambiaron de verdad: si el profesor reenvia el
            // formulario sin tocar nada, al estudiante no se le vuelve a
            // escribir. Solo se avisa de lo que se corrige o se agrega.
            $anterior = Calificacion::query()
                ->where('id_estudiante', $idEstudiante)
                ->where('id_curso', $idCurso)
                ->where('id_cuatrimestre', $idCuatr)
                ->first();

            $cambiadas = $this->parcialesQueCambian($anterior?->parciales() ?? [null, null, null, null], $parciales);

            $calificacion = $this->servicio->guardarParciales(
                $idEstudiante,
                $idCurso,
                $idCuatr,
                $parciales,
                $item['observaciones'] ?? null
            );

            // Sin cambio real no hay aviso, y si el profesor borro todas las
            // parciales tampoco: no se le puede avisar de una evaluacion que ya
            // no existe.
            if ($cambiadas === [] || $calificacion === null) {
                continue;
            }

            $estudiante = Estudiante::with('usuario')->find($idEstudiante);

            if ($estudiante?->usuario?->email) {
                Mail::to($estudiante->usuario->email)->later(
                    now()->addSeconds($segundosRetraso),
                    new NotaPublicada(
                        $estudiante->usuario->nombres,
                        $curso->nombre,
                        $calificacion?->notaEfectiva() !== null
                            ? round((float) $calificacion->notaEfectiva(), 2)
                            : null,
                        $cambiadas
                    )
                );

                $segundosRetraso += 15;
                $notificados++;
            }
        }

        $mensaje = $notificados > 0
            ? "Notas guardadas. Se avisÃ³ a {$notificados} ".($notificados === 1 ? 'estudiante' : 'estudiantes').' por email.'
            : 'Notas guardadas.';

        return redirect()->route('profesor.notas', ['curso' => $idCurso, 'cuatrimestre' => $idCuatr])
            ->with('success', $mensaje);
    }

    /**
     * Etiquetas de las parciales cuyo valor cambio ("Parcial 1", ...).
     *
     * Compara con la misma normalizacion con la que se guardan (decimal de un
     * lugar, vacio = null): el navegador manda las cuatro casillas siempre, las
     * vacias como "", y sin normalizar cada reenvio pareceria un cambio. Asi,
     * reenviar 7.5 tal cual no genera aviso, y borrar una parcial si.
     *
     * @param  array<int, float|string|null>  $anterior
     * @param  array<int, float|string|null>  $nuevas
     * @return array<int, string>
     */
    private function parcialesQueCambian(array $anterior, array $nuevas): array
    {
        $normalizar = fn ($valor) => ($valor === null || $valor === '')
            ? null
            : round((float) $valor, 1);

        $cambiadas = [];

        foreach (ParcialService::PARCIALES as $i => $columna) {
            $antes = $normalizar($anterior[$i] ?? null);
            $ahora = $normalizar($nuevas[$i] ?? null);

            if ($antes !== $ahora) {
                $cambiadas[] = ParcialService::ETIQUETAS[$i];
            }
        }

        return $cambiadas;
    }

}
