<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Panel de la Rectora: resumen visual de la institucion.
 *
 * El panel siempre muestra un solo cuatrimestre, nunca la suma de todos: los
 * graficos por curso comparan occupancy, rendimiento e inasistencia, y mezclar
 * periodos en la misma barra produce cifras que no significan nada (un curso
 * de 2026 junto a uno de 2027 no comparte escala). Por eso no existe la opcion
 * "Todos".
 */
class DashboardController extends Controller
{
    public function index(Request $request, ReporteService $reportes)
    {
        $cuatrimestres = $reportes->cuatrimestres();

        $idCuatrimestre = $request->filled('cuatrimestre')
            ? $cuatrimestres
                ->firstWhere('id_cuatrimestre', $request->integer('cuatrimestre'))?->id_cuatrimestre
                ?? abort(404)
            : $this->cuatrimestrePorDefecto($cuatrimestres);

        // Suma de clases programadas de los cursos del periodo.
        // Las filas sin total_clases no aportan (suman 0).
        $totalClases = (int) DB::table('curso_cuatrimestre')
            ->where('cuatrimestre_id', $idCuatrimestre)
            ->sum('total_clases');

        $panel = $reportes->panel($idCuatrimestre);

        return view('admin.dashboard', [
            'cuatrimestres' => $cuatrimestres,
            'idCuatrimestre' => $idCuatrimestre,
            'totalClases' => $totalClases,
            'kpis' => $panel['kpis'],
            'inscritosPorCarrera' => $panel['inscritosPorCarrera'],
            'inscripcionPorCurso' => $panel['inscripcionPorCurso'],
            'rendimientoPorCurso' => $panel['rendimientoPorCurso'],
            'asistenciaPorCurso' => $panel['asistenciaPorCurso'],
            // Secciones nuevas: el detalle por estudiante y el bloque de deuda,
            // ambas derivadas de datos que ya existian.
            'rendimientoPorEstudiante' => $reportes->rendimientoPorEstudiante($idCuatrimestre),
            'deudores' => Estudiante::with(['usuario:id_usuario,nombres,apellidos', 'carrera:id_carrera,nombre'])
                ->where('deuda', true)
                ->orderBy('id_usuario')
                ->get(),
        ]);
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
