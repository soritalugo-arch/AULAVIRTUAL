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
     *     resumen: array,
     *     repetidos: Collection<int, int>
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

        // Cursos que aparecen en mas de un cuatrimestre: el historial debe
        // decir que se repitieron, no ocultarlo como un caso raro.
        $repetidos = $periodos->flatMap(fn ($p) => $p['cursos']->all())
            ->concat($enCurso)
            ->groupBy('id_curso')
            ->filter(fn ($g) => $g->count() > 1)
            ->keys();

        return [
            'periodos' => $periodos,
            'enCurso' => $enCurso,
            'enCursoCuatrimestre' => $cuatrimestreEnCurso,
            'resumen' => $this->resumen($periodos),
            'repetidos' => $repetidos,
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
                'calificacion.id_curso',
                'calificacion.nota',
                'calificacion.promedio',
                'calificacion.tiene_parciales',
                'calificacion.parcial1',
                'calificacion.parcial2',
                'calificacion.parcial3',
                'calificacion.parcial4',
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
                'inscripcion.id_curso',
                'curso.nombre as curso',
            ])
            ->selectRaw('null as nota')
            ->selectRaw('null as promedio')
            ->selectRaw('null as tiene_parciales')
            ->selectRaw('null as parcial1')
            ->selectRaw('null as parcial2')
            ->selectRaw('null as parcial3')
            ->selectRaw('null as parcial4')
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
            $terminado,
        );

        // Con parciales manda el promedio de las cuatro: es la nota que ve el
        // alumno y la que el sistema promedia, no un entero viejo.
        $tieneParciales = (bool) ($fila->tiene_parciales ?? false);
        $promedio = $tieneParciales && $fila->promedio !== null
            ? (float) $fila->promedio
            : ($nota !== null ? (float) $nota : null);

        return [
            'cuatrimestre' => $cuatrimestre,
            'id_cuatrimestre' => (int) $fila->id_cuatrimestre,
            'id_curso' => (int) $fila->id_curso,
            'curso' => $fila->curso,
            'nota' => $promedio,
            'tiene_parciales' => $tieneParciales,
            'parciales' => [
                $fila->parcial1 ?? null,
                $fila->parcial2 ?? null,
                $fila->parcial3 ?? null,
                $fila->parcial4 ?? null,
            ],
            'observaciones' => $fila->observaciones,
            'inasistencia' => $inasistencia,
            'alerta' => $this->reglas->nivelAlerta($inasistencia),
            'estado' => $this->reglas->estadoEstudiante($promedio, $inasistencia, $terminado),
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

    /**
     * ¿Completó la carrera?
     *
     * Regla institucional para el certificado: egresa quien aprobó TODOS los
     * cursos del pensum de su carrera. Solo cuentan los que la misma regla del
     * módulo del profesor considera aprobados (nota >= 6 y sin exceso de
     * faltas), así el certificado habla el mismo idioma que el aula.
     *
     * Quien aún no tiene carrera o cuyo pensum no tiene cursos, no egresa.
     */
    public function esEgresado(Estudiante $estudiante, ?array $historial = null): bool
    {
        $carrera = $estudiante->carrera;

        if (! $carrera) {
            return false;
        }

        $cursosDeLaCarrera = $carrera->cursos()->pluck('curso.id_curso');

        if ($cursosDeLaCarrera->isEmpty()) {
            return false;
        }

        $historial ??= $this->historial($estudiante);

        $aprobados = collect($historial['periodos'])
            ->flatMap(fn ($p) => $p['cursos']->all())
            ->reject(fn ($f) => $f['estado'] !== 'Aprobado')
            ->pluck('id_curso');

        return $cursosDeLaCarrera->diff($aprobados)->isEmpty();
    }

    /**
     * Materias aprobadas de toda la carrera, con el mismo veredicto del aula.
     *
     * Al estar basado en historial(), solo cuenta como aprobado lo que la regla
     * del profesor (nota >= 6 y sin exceso de faltas) decide aprobar, de modo
     * que nadie avanza de cuatrimestre con un curso que la pantalla del aula
     * todavía considera reprobado.
     *
     * @return Collection<int, int>
     */
    public function cursosAprobados(Estudiante $estudiante): Collection
    {
        $historial = $this->historial($estudiante);

        return collect($historial['periodos'])
            ->flatMap(fn ($p) => $p['cursos']->all())
            ->filter(fn ($f) => $f['estado'] === 'Aprobado')
            ->pluck('id_curso');
    }

    /**
     * Ventana de cuatrimestres del plan que el estudiante puede ver e inscribir.
     *
     * Regla de avance por arrastres (materias raspadas):
     *
     *  - Sin materias raspadas: la ventana es un solo cuatrimestre, el primero
     *    del plan que todavía no está aprobado por completo. Para pasar al
     *    siguiente hay que aprobar TODAS sus materias.
     *  - Con materias raspadas (un intento reprobado y todavía sin aprobar): la
     *    ventana se abre a dos cuatrimestres: el de la materia raspada más
     *    atrasada y el siguiente. Así el estudiante repite lo que le faltó y
     *    adelanta materias del cuatrimestre siguiente, pero no ve más allá
     *    hasta aprobar todo lo pendiente: si sigue raspando se queda congelado
     *    repitiendo el mismo bucle sin avanzar.
     *
     * Devuelve null cuando el estudiante no tiene carrera, cuando el pensum no
     * define etapas (matrícula sin restricción por cuatrimestre) o cuando
     * completó todo el plan (egresada/egresado).
     *
     * @return array{
     *     desde: int, hasta: int, etapas: array<int, int>,
     *     formato: string, totalEtapas: int,
     *     aprobadas: int, reprobadas: int, pendientes: int
     * }|null
     */
    public function ventanaEtapas(Estudiante $estudiante): ?array
    {
        $carrera = $estudiante?->carrera;

        if (! $carrera) {
            return null;
        }

        $cursos = $carrera->cursos()->withPivot('etapa')->get();

        if ($cursos->isEmpty()) {
            return null;
        }

        $porEtapa = $cursos
            ->filter(fn ($c) => $c->pivot->etapa !== null)
            ->sortBy([['pivot.etapa', 'asc'], ['nombre', 'asc']])
            ->groupBy('pivot.etapa');

        if ($porEtapa->isEmpty()) {
            return null; // pensum sin etapas: matrícula sin restricción por cuatrimestre
        }

        $totalEtapas = (int) $porEtapa->keys()->max();

        // Un solo recorrido del historial para saber qué está aprobado y qué se
        // raspó en algún momento (evita consultar el historial por materia).
        $filas = collect($this->historial($estudiante)['periodos'])
            ->flatMap(fn ($p) => $p['cursos']->all());

        $aprobados = $filas
            ->filter(fn ($f) => $f['estado'] === 'Aprobado')
            ->pluck('id_curso')
            ->unique();

        $raspadas = $filas
            ->filter(fn ($f) => str_starts_with((string) $f['estado'], 'Reprobado'))
            ->pluck('id_curso')
            ->unique();

        $pendientesPorEtapa = collect();
        $raspadasPorEtapa = collect();
        $aprobadas = 0;

        for ($etapa = 1; $etapa <= $totalEtapas; $etapa++) {
            $materias = $porEtapa->get($etapa, collect());

            $pendientes = $materias->reject(fn ($c) => $aprobados->contains($c->id_curso));
            $pendientesPorEtapa[$etapa] = $pendientes->values();

            $raspadasPorEtapa[$etapa] = $pendientes
                ->filter(fn ($c) => $raspadas->contains($c->id_curso))
                ->values();

            $aprobadas += $materias->count() - $pendientes->count();
        }

        $primeraPendiente = $pendientesPorEtapa
            ->search(fn ($materias) => $materias->isNotEmpty());

        if ($primeraPendiente === false) {
            return null; // completó el plan completo (egresada/egresado)
        }

        // Con algo raspado la ventana se abre un cuatrimestre más (1-2, 2-3...):
        // se repite lo pendiente y se adelanta el siguiente, sin pasar de ahí.
        $primeraRaspada = $raspadasPorEtapa
            ->search(fn ($materias) => $materias->isNotEmpty());

        if ($primeraRaspada !== false) {
            $desde = (int) $primeraRaspada;
            $hasta = min($desde + 1, $totalEtapas);
        } else {
            $desde = (int) $primeraPendiente;
            $hasta = $desde;
        }

        $reprobadas = $raspadasPorEtapa
            ->map(fn ($materias) => $materias->count())
            ->sum();

        $pendientes = collect(range($desde, $hasta))
            ->sum(fn ($etapa) => $pendientesPorEtapa[$etapa]->count());

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'etapas' => range($desde, $hasta),
            'formato' => $desde === $hasta ? (string) $desde : $desde.'-'.$hasta,
            'totalEtapas' => $totalEtapas,
            'aprobadas' => $aprobadas,
            'reprobadas' => $reprobadas,
            'pendientes' => $pendientes,
        ];
    }

    /**
     * Cuatrimestre base del estudiante: el primero del plan sin aprobar del todo.
     *
     * Con una materia raspada devuelve la etapa de esa materia; sin raspadas,
     * la primera etapa con pendientes. Es el "desde" de la ventana de etapas.
     *
     * Devuelve null si el estudiante no tiene carrera, si su carrera no define
     * etapas en el pensum, o si completó todo el plan (egresada/egresado).
     */
    public function etapaActual(Estudiante $estudiante): ?int
    {
        return $this->ventanaEtapas($estudiante)['desde'] ?? null;
    }

    /**
     * Materias de un estudiante en un cuatrimestre concreto, tengan nota o no.
     *
     * Es la pieza del historial acotada a un solo periodo, para la ficha que ve
     * la rectora desde el reporte de rendimiento: junta las notas registradas
     * con las matriculas que todavia no tienen calificacion y le aplica a cada
     * fila el mismo veredicto del aula (nota >= 6 y sin exceso de faltas).
     *
     * @return Collection<int, array>
     */
    public function materiasDeUnPeriodo(int $idEstudiante, int $idCuatrimestre): Collection
    {
        $cuatrimestre = Cuatrimestre::find($idCuatrimestre);

        if (! $cuatrimestre) {
            return collect();
        }

        $terminado = $cuatrimestre->fecha_fin->lt(today());

        return $this->notasRegistradas($idEstudiante)
            ->where('id_cuatrimestre', $idCuatrimestre)
            ->concat($this->matriculasSinNota($idEstudiante, $cuatrimestre))
            ->map(fn ($fila) => $this->filaHistorial($fila, $cuatrimestre))
            ->sortBy('curso')
            ->values();
    }
}
