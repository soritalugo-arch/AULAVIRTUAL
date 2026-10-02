<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Estudiante;
use App\Services\HistorialService;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Panel de la Rectora: zona con menu lateral y una pagina por funcion.
 *
 * El panel siempre muestra un solo cuatrimestre, nunca la suma de todos: los
 * graficos comparan ocupacion, rendimiento e inasistencia por curso, y mezclar
 * periodos en la misma barra produce cifras que no significan nada. Por eso no
 * existe la opcion "Todos".
 *
 * Cada pagina pide solo los datos que usa (un reporte por metodo, no panel()
 * completo): abrir "Deudas" no dispara los cinco reportes del panel.
 */
class DashboardController extends Controller
{
    /** Inicio / Resumen: numeros del dia y alertas rapidas. */
    public function index(Request $request, ReporteService $reportes)
    {
        $ctx = $this->contexto($request, $reportes);

        $cursosEnRiesgo = collect(
            $reportes->asistenciaPorCurso($ctx['idCuatrimestre'])
        )->filter(fn ($f) => $f['alerta'] === 'peligro')->count();

        return view('admin.inicio', $ctx + [
            'kpis' => $reportes->kpis($ctx['idCuatrimestre']),
            'deudoresCount' => Estudiante::where('deuda', true)->count(),
            'cursosEnRiesgo' => $cursosEnRiesgo,
        ]);
    }

    /** Inscripciones: cupo por curso y estudiantes por carrera. */
    public function inscripciones(Request $request, ReporteService $reportes)
    {
        $ctx = $this->contexto($request, $reportes);

        return view('admin.inscripciones', $ctx + [
            'inscripcionPorCurso' => $reportes->inscripcionPorCurso($ctx['idCuatrimestre']),
            'inscritosPorCarrera' => $reportes->inscritosPorCarrera($ctx['idCuatrimestre']),
        ]);
    }

    /** Rendimiento: por curso en grafico y por estudiante en tabla. */
    public function rendimiento(Request $request, ReporteService $reportes)
    {
        $ctx = $this->contexto($request, $reportes);

        return view('admin.rendimiento', $ctx + [
            'rendimientoPorCurso' => $reportes->rendimientoPorCurso($ctx['idCuatrimestre']),
            'rendimientoPorEstudiante' => $reportes->rendimientoPorEstudiante($ctx['idCuatrimestre']),
        ]);
    }

    /** Asistencia: porcentaje de inasistencia por curso, con semaforo. */
    public function asistencia(Request $request, ReporteService $reportes)
    {
        $ctx = $this->contexto($request, $reportes);

        return view('admin.asistencia', $ctx + [
            'asistenciaPorCurso' => $reportes->asistenciaPorCurso($ctx['idCuatrimestre']),
        ]);
    }

    /** Deudas: estudiantes con la matricula bloqueada. */
    public function deudas(Request $request, ReporteService $reportes)
    {
        $ctx = $this->contexto($request, $reportes);

        return view('admin.deudas', $ctx + [
            'deudores' => Estudiante::with(['usuario:id_usuario,nombres,apellidos', 'carrera:id_carrera,nombre'])
                ->where('deuda', true)
                ->orderBy('id_usuario')
                ->get(),
        ]);
    }

    /**
     * Ficha de un estudiante desde la tabla de rendimiento: datos personales,
     * cifras del periodo y de toda la carrera, y sus materias del periodo.
     *
     * La ficha entra con el mismo periodo en el que estaba la rectora, para que
     * los numeros que vea coincidan con los de la fila en la que hizo clic.
     */
    public function fichaEstudiante(Request $request, ReporteService $reportes, HistorialService $historial, Estudiante $estudiante)
    {
        $ctx = $this->contexto($request, $reportes);
        $id = $estudiante->id_usuario;

        $notasDelPeriodo = Calificacion::where('id_estudiante', $id)
            ->where('id_cuatrimestre', $ctx['idCuatrimestre'])
            ->get();
        $notasDeLaCarrera = Calificacion::where('id_estudiante', $id)->get();

        return view('admin.ficha_estudiante', $ctx + [
            'estudiante' => $estudiante->load([
                'usuario:id_usuario,nombres,apellidos,email',
                'carrera:id_carrera,nombre',
            ]),
            'materias' => $historial->materiasDeUnPeriodo($id, $ctx['idCuatrimestre']),
            'egresado' => $historial->esEgresado($estudiante),
            'periodo' => [
                'promedio' => $notasDelPeriodo->isEmpty() ? null : round($notasDelPeriodo->avg('nota'), 2),
                'aprobadas' => $notasDelPeriodo->where('nota', '>=', 6)->count(),
                'reprobadas' => $notasDelPeriodo->where('nota', '<', 6)->count(),
            ],
            'carrera' => [
                'promedio' => $notasDeLaCarrera->isEmpty() ? null : round($notasDeLaCarrera->avg('nota'), 2),
                'aprobadas' => $notasDeLaCarrera->where('nota', '>=', 6)->count(),
                'reprobadas' => $notasDeLaCarrera->where('nota', '<', 6)->count(),
            ],
        ]);
    }

    /**
     * Datos comunes a todas las paginas: cuatrimestres para el filtro, el
     * periodo elegido y el total de clases programadas de ese periodo.
     */
    private function contexto(Request $request, ReporteService $reportes): array
    {
        $cuatrimestres = $reportes->cuatrimestres();

        $idCuatrimestre = $request->filled('cuatrimestre')
            ? ($cuatrimestres->firstWhere('id_cuatrimestre', $request->integer('cuatrimestre'))?->id_cuatrimestre
                ?? abort(404))
            : $this->cuatrimestrePorDefecto($cuatrimestres);

        $totalClases = (int) DB::table('curso_cuatrimestre')
            ->where('cuatrimestre_id', $idCuatrimestre)
            ->sum('total_clases');

        return compact('cuatrimestres', 'idCuatrimestre', 'totalClases');
    }

    /**
     * Cuatrimestre que se abre sin parametro en la URL.
     *
     * La coleccion llega del mas reciente al mas antiguo. Se busca el que
     * contiene la fecha de hoy y, si no hay ninguno, el mas reciente que ya
     * empezo: asi el panel nunca arranca en un periodo futuro vacio, que es lo
     * que veria la rectora si se le abriera el cuatrimestre siguiente.
     */
    private function cuatrimestrePorDefecto($cuatrimestres): int
    {
        $vigente = $cuatrimestres->first(
            fn ($c) => $c->fecha_inicio->lte(today()) && $c->fecha_fin->gte(today())
        );

        $empezado = $cuatrimestres->first(fn ($c) => $c->fecha_inicio->lte(today()));

        return ($vigente ?? $empezado ?? $cuatrimestres->first())->id_cuatrimestre;
    }
}