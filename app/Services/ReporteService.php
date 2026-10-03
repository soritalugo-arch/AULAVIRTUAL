<?php

namespace App\Services;

use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Consultas de lectura para el panel de la rectora.
 *
 * Todas aceptan un cuatrimestre opcional: null agrupa todos los periodos.
 * Se apoya en agregaciones en SQL porque el volumen de asistencia (decenas de
 * miles de filas) hace inviable traer los registros a memoria.
 */
class ReporteService
{
    /**
     * Barras que muestra cada gráfico antes de que la rectora pida ver mas.
     * Los datos completos viajan siempre al navegador; esto solo acota el
     * alto inicial para que la pagina no quede interminable.
     */
    public const BLOQUE_CURSOS = 12;

    /** Porcentaje de inasistencia a partir del cual el alumno pierde el curso. */
    private const UMBRAL_PERDIDA = 30;

    /**
     * Alerta de un curso sin ninguna asistencia registrada. No es una alerta
     * buena ni mala: es la ausencia del dato, y la vista la pinta en gris.
     */
    public const SIN_DATOS = 'sin_datos';

    /**
     * La nota que cuenta, en SQL: el promedio de las cuatro parciales cuando la
     * fila ya lo tiene y la nota final antigua en las que todavia no. Es el
     * mismo criterio que Calificacion::notaEfectiva(), escrito para que las
     * agregaciones del panel promedien exactamente lo que ve el alumno.
     */
    public const NOTA_SQL = 'COALESCE(promedio, nota)';

    /**
     * Datos completos del panel para un cuatrimestre (null = todos).
     */
    public function panel(?int $idCuatrimestre): array
    {
        $asistencia = $this->asistenciaPorCurso($idCuatrimestre);
        $porCarrera = $this->inscritosPorCarrera($idCuatrimestre);

        return [
            'kpis' => $this->kpis($idCuatrimestre, $porCarrera, $asistencia),
            'inscritosPorCarrera' => $porCarrera,
            'inscripcionPorCurso' => $this->inscripcionPorCurso($idCuatrimestre),
            'rendimientoPorCurso' => $this->rendimientoPorCurso($idCuatrimestre),
            'asistenciaPorCurso' => $asistencia,
        ];
    }

    /**
     * Cifras de cabecera: matrícula, oferta, aprobación y asistencia.
     *
     * Los porcentajes se devuelven en null cuando no hay registros en el
     * periodo, para que la vista pueda distinguir "0%" de "sin datos".
     */
    public function kpis(?int $id, ?array $porCarrera = null, ?array $asistencia = null): array
    {
        $totalCalificaciones = $this->base('calificacion', $id)->count();
        $aprobadas = $this->base('calificacion', $id)->whereRaw(self::NOTA_SQL.' >= 6')->count();

        $totalAsistencias = $this->base('asistencia', $id)->count();
        $faltas = $this->base('asistencia', $id)->where('presente', false)->count();

        $cursosIds = $this->cursosEnOferta($id);
        $cupoTotal = $cursosIds === []
            ? 0
            : Curso::whereIn('id_curso', $cursosIds)->sum('limite_estudiantes');
        $inscritos = $this->base('inscripcion', $id)->count();

        $promedio = $this->base('calificacion', $id)->avg(DB::raw(self::NOTA_SQL));
        $asistencia = $asistencia ?? $this->asistenciaPorCurso($id);

        return [
            // Con periodo refleja el movimiento real de ese cuatrimestre: un
            // periodo futuro muestra 0, no los 600 de la institucion.
            'estudiantes' => $this->estudiantesConMovimiento($id),
            'estudiantesTotales' => Estudiante::count(),
            'cursosOferta' => count($cursosIds),
            'inscritos' => $inscritos,
            'cupoTotal' => (int) $cupoTotal,
            'cuposLibres' => max((int) $cupoTotal - $inscritos, 0),
            'tasaAprobacion' => $totalCalificaciones > 0
                ? round($aprobadas / $totalCalificaciones * 100, 1)
                : null,
            'aprobadas' => $aprobadas,
            'reprobadas' => $totalCalificaciones - $aprobadas,
            'totalCalificaciones' => $totalCalificaciones,
            'promedio' => $promedio === null ? null : round($promedio, 2),
            'pctAsistencia' => $totalAsistencias > 0
                ? round(($totalAsistencias - $faltas) / $totalAsistencias * 100, 1)
                : null,
            'inasistencia' => $totalAsistencias > 0
                ? round($faltas / $totalAsistencias * 100, 1)
                : null,
            'totalAsistencias' => $totalAsistencias,
            'cursosSinDatos' => count(array_filter(
                $asistencia,
                fn ($f) => $f['alerta'] === self::SIN_DATOS
            )),
        ];
    }

    /**
     * Estudiantes con movimiento en el periodo: matricula o nota.
     *
     * Sin periodo es la poblacion total de la institucion.
     */
    private function estudiantesConMovimiento(?int $id): int
    {
        if ($id === null) {
            return Estudiante::count();
        }

        return Estudiante::where(fn ($q) => $q
            ->whereExists($this->existeEnPeriodo('inscripcion', $id))
            ->orWhereExists($this->existeEnPeriodo('calificacion', $id)))
            ->count();
    }

    /**
     * Cuatrimestre al que corresponde una fecha.
     */
    private function periodoDeFecha($fecha): ?int
    {
        $contenido = Cuatrimestre::where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->value('id_cuatrimestre');

        if ($contenido !== null) {
            return (int) $contenido;
        }

        $iniciado = Cuatrimestre::where('fecha_inicio', '<=', $fecha)
            ->orderByDesc('fecha_inicio')
            ->value('id_cuatrimestre');

        return $iniciado === null ? null : (int) $iniciado;
    }

    /**
     * Estudiantes por carrera.
     *
     * Se agrupa por la carrera del propio estudiante (estudiante.id_carrera) y
     * no por la del curso: cinco cursos comunes (Matematica Basica,
     * Expresion Oral, Emprendimiento, Ofimatica e Ingles Tecnico) pertenecen a
     * varias carreras, asi que contar por curso_carrera daria el mismo alumno en
     * todas ellas y la suma no cuadraria con el total de la institucion.
     *
     * Sin periodo: el total de la carrera.
     * Con periodo: cuantos estudiantes de la carrera tienen movimiento en el
     * cuatrimestre, ya sea matricula (inscripcion) o nota (calificacion). En un
     * periodo cerrado solo hay notas; en uno en curso, solo matricula.
     */
    public function inscritosPorCarrera(?int $id): array
    {
        $base = DB::table('carrera');

        if ($id === null) {
            $base->leftJoin('estudiante', 'estudiante.id_carrera', '=', 'carrera.id_carrera');
        } else {
            // El filtro va dentro del JOIN, no en un WHERE: puesto en el WHERE
            // el LEFT JOIN se degrada a inner join y las carreras sin
            // estudiantes desaparecerian del grafico en vez de marcar 0.
            $base->leftJoin('estudiante', function ($join) use ($id) {
                $join->on('estudiante.id_carrera', '=', 'carrera.id_carrera')
                    ->where(fn ($q) => $q
                        ->whereExists($this->existeEnPeriodo('inscripcion', $id))
                        ->orWhereExists($this->existeEnPeriodo('calificacion', $id)));
            });
        }

        return $base->groupBy('carrera.id_carrera', 'carrera.nombre')
            ->orderByDesc('total')
            ->selectRaw('carrera.nombre, COUNT(estudiante.id_usuario) as total')
            ->get()
            ->map(fn ($f) => ['nombre' => $f->nombre, 'total' => (int) $f->total])
            ->all();
    }

    /**
     * Subconsulta EXISTS: el estudiante tiene registro en la tabla dentro del
     * periodo indicado.
     */
    private function existeEnPeriodo(string $tabla, int $id): Builder
    {
        return DB::table($tabla)
            ->selectRaw('1')
            ->whereColumn("{$tabla}.id_estudiante", 'estudiante.id_usuario')
            ->where("{$tabla}.id_cuatrimestre", $id);
    }

    /**
     * Inscripcion por curso: ocupado frente a cupo.
     *
     * El conteo de inscritos entra como withCount, que se resuelve en una
     * subconsulta dentro del SELECT: una sola consulta para los 45 cursos. La
     * forma naive de recorrer los cursos y contar $curso->inscripciones->count()
     * en un bucle emitiria una consulta por curso, y con el filtro de periodo
     * habria que repetirla.
     */
    public function inscripcionPorCurso(?int $id): array
    {
        $cursos = Curso::query()
            ->when($id, fn ($q) => $q->whereHas(
                'cuatrimestres',
                fn ($c) => $c->where('id_cuatrimestre', $id)
            ))
            ->withCount(['inscripciones as inscritos' => fn ($q) => $q
                ->when($id, fn ($f) => $f->where('id_cuatrimestre', $id))])
            ->get();

        $filas = $cursos->map(fn (Curso $curso) => [
            'curso' => $curso->nombre,
            'inscritos' => (int) $curso->inscritos,
            'cupo' => (int) $curso->limite_estudiantes,
            'libres' => max((int) $curso->limite_estudiantes - (int) $curso->inscritos, 0),
            'ocupacion' => $curso->limite_estudiantes > 0
                ? round((int) $curso->inscritos / (int) $curso->limite_estudiantes * 100, 1)
                : null,
        ])->all();

        // Primero los que estan mas llenos: es lo que la rectora necesita ver.
        usort($filas, fn ($a, $b) => $b['ocupacion'] <=> $a['ocupacion']);

        return $filas;
    }

    /**
     * Rendimiento por estudiante: promedio, aprobadas y reprobadas.
     *
     * No entra en panel(): son 600 filas y el panel es de lectura rapida. Vive
     * aqui para el reporte de rendimiento por estudiante.
     *
     * Toda la agregacion va en subconsultas (withCount / withAvg) y los datos de
     * nombre y carrera se cargan con with(). El numero de consultas es fijo
     * (seis) y no crece con los estudiantes: leer $estudiante->usuario->nombres
     * o $estudiante->calificaciones->count() dentro de un foreach seria un N+1
     * de dos consultas por alumno, unas 1200 en esta base.
     */
    public function rendimientoPorEstudiante(?int $id): array
    {
        $enPeriodo = fn ($q) => $q->when($id, fn ($f) => $f->where('id_cuatrimestre', $id));

        return Estudiante::query()
            ->with(['usuario:id_usuario,nombres,apellidos', 'carrera:id_carrera,nombre'])
            ->withCount(['calificaciones as notas' => $enPeriodo])
            ->withCount(['calificaciones as aprobadas' => fn ($q) => $enPeriodo($q)->whereRaw(self::NOTA_SQL.' >= 6')])
            ->withCount(['calificaciones as reprobadas' => fn ($q) => $enPeriodo($q)->whereRaw(self::NOTA_SQL.' < 6')])
            ->withAvg('calificaciones as promedio', DB::raw(self::NOTA_SQL), $enPeriodo)
            // Solo los alumnos con movimiento en el periodo: matricula o nota.
            ->when($id, fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('inscripciones', fn ($i) => $i->where('id_cuatrimestre', $id))
                ->orWhereHas('calificaciones', fn ($c) => $c->where('id_cuatrimestre', $id))))
            ->get()
            ->map(fn (Estudiante $e) => [
                'id' => $e->id_usuario,
                'estudiante' => trim(($e->usuario?->nombres ?? '').' '.($e->usuario?->apellidos ?? '')),
                'carrera' => $e->carrera?->nombre ?? 'Sin carrera',
                'cedula' => $e->cedula,
                'promedio' => $e->promedio === null ? null : round((float) $e->promedio, 2),
                'aprobadas' => (int) $e->aprobadas,
                'reprobadas' => (int) $e->reprobadas,
            ])
            // Sin nota en el periodo no hay promedio que ordenar: al final.
            ->sortBy([
                fn ($a, $b) => ($a['promedio'] === null) <=> ($b['promedio'] === null),
                fn ($a, $b) => ($a['promedio'] ?? 0) <=> ($b['promedio'] ?? 0),
            ])
            ->values()
            ->all();
    }

    /**
     * Rendimiento por curso: aprobados, reprobados y en curso.
     *
     * "En curso" son las inscripciones del periodo que todavía no tienen nota
     * en ese mismo periodo, no una simple resta de totales: una inscripcion
     * puede no tener nota porque aun no se ha subido, y eso es exactamente lo
     * que el estado "En curso" significa en el modulo de notas.
     */
    public function rendimientoPorCurso(?int $id): array
    {
        $aprobados = $this->conteoPorCurso('calificacion', $id, '*', self::NOTA_SQL.' >= 6');
        $reprobados = $this->conteoPorCurso('calificacion', $id, '*', self::NOTA_SQL.' < 6');
        $inscritos = $this->conteoPorCurso('inscripcion', $id);
        $conNota = $this->conteoPorCurso('calificacion', $id, 'DISTINCT id_estudiante');

        $cursosIds = $this->cursosEnOferta($id);
        $nombres = Curso::whereIn('id_curso', $cursosIds)->pluck('nombre', 'id_curso');

        $filas = [];

        foreach ($cursosIds as $cursoId) {
            $fila = [
                'curso' => $nombres[$cursoId] ?? "Curso {$cursoId}",
                'aprobados' => $aprobados[$cursoId] ?? 0,
                'reprobados' => $reprobados[$cursoId] ?? 0,
                'enCurso' => 0,
            ];

            $inscritosCurso = $inscritos[$cursoId] ?? 0;

            if ($inscritosCurso > 0) {
                // sin nota en el periodo = en curso
                $pendientes = $inscritosCurso - ($conNota[$cursoId] ?? 0);
                $fila['enCurso'] = max($pendientes, 0);
            }

            $filas[] = $fila;
        }

        usort($filas, fn ($a, $b) => $this->volumen($b) <=> $this->volumen($a));

        return $filas;
    }

    /**
     * Asistencia por curso: porcentaje de inasistencia y nivel de alerta.
     *
     * El denominador es el total de clases programadas del curso
     * (curso_cuatrimestre.total_clases), la misma regla que usa el modulo de
     * asistencia para evaluar a un estudiante. Se promedia el porcentaje de
     * cada estudiante del curso para no igualar un alumno con pocas faltas
     * contra uno con el mismo numero en un curso de pocas clases.
     *
     * Se listan TODOS los cursos en oferta, no solo los que tienen faltas
     * capturadas. Un curso sin registros no se descarta: sale con porcentaje
     * nulo y alerta SIN_DATOS, para que el grafico pueda compararse con el de
     * rendimiento sin que le falten filas. Descartarlos en silencio hacia que
     * el contador del panel dijera 27 de 45 sin explicar donde iban los 18.
     */
    public function asistenciaPorCurso(?int $id): array
    {
        $faltas = DB::table('asistencia')
            ->when($id, fn ($q) => $q->where('id_cuatrimestre', $id))
            ->where('presente', false)
            ->groupBy('id_curso')
            ->selectRaw('id_curso, COUNT(*) as total')
            ->get()
            ->mapWithKeys(fn ($f) => [$f->id_curso => (int) $f->total])
            ->all();

        $alumnosPorCurso = DB::table('asistencia')
            ->when($id, fn ($q) => $q->where('id_cuatrimestre', $id))
            ->groupBy('id_curso')
            ->selectRaw('id_curso, COUNT(DISTINCT id_estudiante) as total')
            ->get()
            ->mapWithKeys(fn ($f) => [$f->id_curso => (int) $f->total])
            ->all();

        $cursosIds = $this->cursosEnOferta($id);
        $nombres = Curso::whereIn('id_curso', $cursosIds)->pluck('nombre', 'id_curso');
        $programadas = $this->clasesProgramadas($id);

        $filas = [];

        foreach ($cursosIds as $cursoId) {
            $nombre = $nombres[$cursoId] ?? "Curso {$cursoId}";
            $totalClases = $programadas[$cursoId] ?? null;
            $alumnos = $alumnosPorCurso[$cursoId] ?? 0;

            if (! $totalClases || $alumnos === 0) {
                $filas[] = [
                    'curso' => $nombre,
                    'porcentaje' => null,
                    'alerta' => self::SIN_DATOS,
                ];

                continue;
            }

            $pct = round(($faltas[$cursoId] ?? 0) / ($totalClases * $alumnos) * 100, 1);

            $filas[] = [
                'curso' => $nombre,
                'porcentaje' => $pct,
                'alerta' => $this->nivelAlerta($pct),
            ];
        }

        // De mayor a menor inasistencia, con los cursos sin registros al
        // final. Null no se compara con numeros, asi que se separa antes.
        usort($filas, function (array $a, array $b): int {
            if ($a['porcentaje'] === null) {
                return $b['porcentaje'] === null ? 0 : 1;
            }

            if ($b['porcentaje'] === null) {
                return -1;
            }

            return $b['porcentaje'] <=> $a['porcentaje'];
        });

        return $filas;
    }

    /**
     * Umbrales de alerta de inasistencia: >30% reprobado por faltas,
     * 25-30% cerca del limite, por debajo ok.
     */
    private function nivelAlerta(float $porcentaje): string
    {
        if ($porcentaje > self::UMBRAL_PERDIDA) {
            return 'peligro';
        }

        return $porcentaje >= 25 ? 'advertencia' : 'ok';
    }

    /**
     * Cursos ofrecidos en el periodo (null = todos los que existen).
     */
    private function cursosEnOferta(?int $id): array
    {
        return DB::table('curso_cuatrimestre')
            ->when($id, fn ($q) => $q->where('cuatrimestre_id', $id))
            ->distinct()
            ->pluck('curso_id')
            ->all();
    }

    /**
     * Total de clases programadas por curso en el periodo.
     */
    private function clasesProgramadas(?int $id): array
    {
        return DB::table('curso_cuatrimestre')
            ->when($id, fn ($q) => $q->where('cuatrimestre_id', $id))
            ->whereNotNull('total_clases')
            ->groupBy('curso_id')
            ->selectRaw('curso_id, MAX(total_clases) as total')
            ->get()
            ->mapWithKeys(fn ($f) => [$f->curso_id => (int) $f->total])
            ->all();
    }

    /**
     * Conteo de registros por curso.
     *
     * @param  string  $columna  que contar: "*" filas, "DISTINCT id_estudiante" alumnos unicos
     * @param  string|null  $condicion  filtro SQL adicional sobre la fila
     */
    private function conteoPorCurso(string $tabla, ?int $id, string $columna = '*', ?string $condicion = null): array
    {
        $q = $this->base($tabla, $id);

        if ($condicion !== null) {
            $q->whereRaw($condicion);
        }

        return $q->groupBy('id_curso')
            ->selectRaw("id_curso, COUNT({$columna}) as total")
            ->get()
            ->mapWithKeys(fn ($f) => [$f->id_curso => (int) $f->total])
            ->all();
    }

    /**
     * Volumen de registros de un curso, para ordenar por volumen de datos.
     */
    private function volumen(array $fila): int
    {
        return $fila['aprobados'] + $fila['reprobados'] + $fila['enCurso'];
    }

    /**
     * Consulta base de una tabla, acotada al periodo si se indica.
     */
    private function base(string $tabla, ?int $id)
    {
        return DB::table($tabla)->when($id, fn ($q) => $q->where('id_cuatrimestre', $id));
    }

    /**
     * Cuatrimestres ordenados del más reciente al más antiguo.
     * Solo incluye períodos que ya iniciaron o inician hoy (oculta los futuros).
     */
    public function cuatrimestres()
    {
        return Cuatrimestre::where('fecha_inicio', '<=', now())
            ->orderByDesc('fecha_inicio')
            ->get();
    }
}
