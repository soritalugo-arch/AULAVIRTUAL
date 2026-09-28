<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Historial academico del estudiante: todos los cuatrimestres, no solo el vigente.
 *
 * A diferencia de "Mis Notas", que mira un unico periodo, aqui se acumula todo lo
 * que el estudiante ha cursado. Los veredictos no se recalculan: salen de las
 * mismas reglas que usa el modulo del profesor (CalificacionAsistenciaService),
 * de modo que un curso aprobado en el historial esta aprobado por la misma razon
 * que lo muestra la pantalla del alumno.
 *
 * El historial se arma en un numero fijo de consultas, no una por fila:
 *   1. notas ya registradas, con faltas y clases dictadas agregadas en la misma
 *      consulta mediante subconsultas correlacionadas;
 *   2. los cuatrimestres que aparecen en ellas, de una sola vez;
 *   3. matriculas del cuatrimestre en curso que aun no tienen nota.
 */
class HistorialService
{
    public function __construct(private CalificacionAsistenciaService $reglas) {}

    /**
     * @return array{
     *     periodos: Collection<int, array>,
     *     enCurso: Collection<int, array>,
     *     enCursoCuatrimestre: Cuatrimestre|null,
     *     resumen: array
     * }
     */
    public function historial(Estudiante $estudiante): array
    {
        $cuatrimestreEnCurso = $this->reglas->cuatrimestreEnCurso();

        $notas = $this->notasRegistradas($estudiante->id_usuario);

        // Un Cuatrimestre::find() por periodo seria una consulta por cuatrimestre:
        // se traen todos de una vez y despues se indexan por id. Ademas las fechas
        // llegan ya como Carbon por los casts del modelo, en vez de como texto de
        // un join.
        $cuatrimestres = Cuatrimestre::whereIn(
            'id_cuatrimestre',
            $notas->pluck('id_cuatrimestre')->unique()
        )->get()->keyBy('id_cuatrimestre');

        $periodos = $notas
            ->map(fn ($fila) => $this->filaHistorial($fila, $cuatrimestres[$fila->id_cuatrimestre]))
            ->groupBy('id_cuatrimestre')
            // Del mas reciente al mas antiguo, para leer la carrera hacia atras.
            ->sortByDesc(fn (Collection $filas) => $filas->first()['cuatrimestre']->fecha_inicio)
            ->map(fn (Collection $filas) => $this->periodo($filas->first()['cuatrimestre'], $filas))
            ->values();

        $enCurso = $cuatrimestreEnCurso
            ? $this->matriculasSinNota($estudiante->id_usuario, $cuatrimestreEnCurso)
                ->map(fn ($fila) => $this->filaHistorial($fila, $cuatrimestreEnCurso))
                ->values()
            : collect();

        return [
            'periodos' => $periodos,
            'enCurso' => $enCurso,
            'enCursoCuatrimestre' => $cuatrimestreEnCurso,
            'resumen' => $this->resumen($periodos),
        ];
    }

    /**
     * Cursos con nota registrada, de cualquier cuatrimestre.
     *
     * Faltas y clases dictadas llegan como subconsultas correlacionadas sobre la
     * misma fila; pedir la asistencia por curso despues seria un N+1 disfrazado
     * (tres consultas por cada nota del historial).
     */
    private function notasRegistradas(int $idEstudiante): Collection
    {
        return Calificacion::query()
            ->join('curso', 'curso.id_curso', '=', 'calificacion.id_curso')
            ->leftJoin('curso_cuatrimestre', $this->puentePeriodo('calificacion'))
            ->where('calificacion.id_estudiante', $idEstudiante)
            ->orderBy('curso.nombre')
            ->select([
                'calificacion.id_cuatrimestre',
                'calificacion.nota',
                'calificacion.observaciones',
                'curso.nombre as curso',
            ])
            ->selectRaw('curso_cuatrimestre.total_clases as total_clases')
            ->selectSub($this->conteoAsistencia('calificacion', true), 'faltas')
            ->selectSub($this->conteoAsistencia('calificacion'), 'clases_dictadas')
            ->get();
    }

    /**
     * Cursos matriculados en el cuatrimestre en curso que todavia no tienen nota.
     *
     * Se excluyen los que ya tienen fila en calificacion: si la hay, ese curso ya
     * aparece en el bloque de notas y repetirlo seria mostrarlo dos veces.
     */
    private function matriculasSinNota(int $idEstudiante, Cuatrimestre $cuatrimestre): Collection
    {
        return Inscripcion::query()
            ->join('curso', 'curso.id_curso', '=', 'inscripcion.id_curso')
            ->leftJoin('curso_cuatrimestre', $this->puentePeriodo('inscripcion'))
            ->where('inscripcion.id_estudiante', $idEstudiante)
            ->where('inscripcion.id_cuatrimestre', $cuatrimestre->id_cuatrimestre)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('calificacion')
                    ->whereColumn('calificacion.id_estudiante', 'inscripcion.id_estudiante')
                    ->whereColumn('calificacion.id_curso', 'inscripcion.id_curso')
                    ->whereColumn('calificacion.id_cuatrimestre', 'inscripcion.id_cuatrimestre');
            })
            ->orderBy('curso.nombre')
            ->select([
                'inscripcion.id_cuatrimestre',
                'curso.nombre as curso',
            ])
            ->selectRaw('null as nota')
            ->selectRaw('null as observaciones')
            ->selectRaw('curso_cuatrimestre.total_clases as total_clases')
            ->selectSub($this->conteoAsistencia('inscripcion', true), 'faltas')
            ->selectSub($this->conteoAsistencia('inscripcion'), 'clases_dictadas')
            ->get();
    }

    /**
     * Traduce una fila cruda a la forma que pintan la vista y el certificado.
     *
     * El veredicto y el nivel de alerta no se deciden aqui: los decide el
     * servicio de calificaciones, el mismo que ve el profesor.
     */
    private function filaHistorial(object $fila, Cuatrimestre $cuatrimestre): array
    {
        $terminado = $cuatrimestre->fecha_fin->lt(today());
        $nota = is_null($fila->nota) ? null : (int) $fila->nota;

        $inasistencia = $this->reglas->inasistenciaDesdeConteos(
            (int) $fila->faltas,
            (int) $fila->total_clases,
            (int) $fila->clases_dictadas,
        );

        return [
            'cuatrimestre' => $cuatrimestre,
            'id_cuatrimestre' => (int) $fila->id_cuatrimestre,
            'curso' => $fila->curso,
            'nota' => $nota,
            'observaciones' => $fila->observaciones,
            'inasistencia' => $inasistencia,
            'alerta' => $this->reglas->nivelAlerta($inasistencia),
            'estado' => $this->reglas->estadoEstudiante($nota, $inasistencia, $terminado),
        ];
    }

    /**
     * Un cuatrimestre del historial con su promedio y sus conteos.
     *
     * @param  Collection<int, array>  $filas
     */
    private function periodo(Cuatrimestre $cuatrimestre, Collection $filas): array
    {
        $conNota = $filas->filter(fn ($f) => ! is_null($f['nota']));

        // Se cuenta por veredicto y no por nota: un 9 con 40 % de faltas sale
        // reprobado, asi que la fila y el conteo del periodo tienen que decir
        // lo mismo.
        $aprobados = $conNota->filter(fn ($f) => $f['estado'] === 'Aprobado')->count();

        // Promedio simple de lo que ya tiene nota. Un curso reprobado pesa igual
        // que uno aprobado: el indice resume el rendimiento del periodo, no
        // premia haber aprobado mas.
        return [
            'cuatrimestre' => $cuatrimestre,
            'codigo' => 'Q'.str_pad($cuatrimestre->id_cuatrimestre, 2, '0', STR_PAD_LEFT),
            'cursos' => $filas->values(),
            'promedio' => $conNota->isEmpty() ? null : round($conNota->avg('nota'), 2),
            'aprobados' => $aprobados,
            'reprobados' => $conNota->count() - $aprobados,
            'total' => $conNota->count(),
        ];
    }

    /**
     * Cifras de toda la carrera, para el resumen y el certificado.
     *
     * @param  Collection<int, array>  $periodos
     */
    private function resumen(Collection $periodos): array
    {
        $cerrados = $periodos->flatMap(fn ($p) => $p['cursos']->all())
            ->reject(fn ($f) => is_null($f['nota']));

        $aprobados = $cerrados->filter(fn ($f) => $f['estado'] === 'Aprobado')->count();

        return [
            'promedio' => $cerrados->isEmpty() ? null : round($cerrados->avg('nota'), 2),
            'aprobados' => $aprobados,
            'reprobados' => $cerrados->count() - $aprobados,
            'cursos' => $cerrados->count(),
            'cuatrimestres' => $periodos->count(),
            'inasistencia' => $cerrados->isEmpty() ? null : round($cerrados->avg('inasistencia'), 1),
        ];
    }

    /**
     * La pivot curso_cuatrimestre tiene una fila por curso y periodo, asi que las
     * dos columnas van en la closure del join y no en un where: unrestringido,
     * el LEFT JOIN traeria filas de otros periodos y el total_clases leido seria
     * el de un cuatrimestre que no es.
     */
    private function puentePeriodo(string $tabla): \Closure
    {
        return function ($join) use ($tabla) {
            $join->on('curso_cuatrimestre.curso_id', '=', $tabla.'.id_curso')
                ->on('curso_cuatrimestre.cuatrimestre_id', '=', $tabla.'.id_cuatrimestre');
        };
    }

    /**
     * Subconsulta correlacionada que cuenta asistencias de un curso en un
     * periodo. Contando solo las faltas devuelve cuantas; sin ese filtro cuenta
     * fechas distintas, es decir, clases dictadas.
     *
     * @param  bool  $soloFaltas
     */
    private function conteoAsistencia(string $tabla, bool $soloFaltas = false): Builder
    {
        $sub = DB::table('asistencia')
            ->selectRaw($soloFaltas ? 'count(*)' : 'count(distinct asistencia.fecha)')
            ->whereColumn('asistencia.id_estudiante', $tabla.'.id_estudiante')
            ->whereColumn('asistencia.id_curso', $tabla.'.id_curso')
            ->whereColumn('asistencia.id_cuatrimestre', $tabla.'.id_cuatrimestre');

        return $soloFaltas ? $sub->where('asistencia.presente', false) : $sub;
    }
}
