<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionSeeder extends Seeder
{
    public static array $carreraDeEstudiante = [];

    public static array $enrolados = [];

    public static array $slotsDeEstudiante = [];

    /**
     * Cuatrimestre del plan en el que está cada estudiante (1 a 5). La usa
     * CalificacionSeeder para aprobarle el recorrido anterior y que la
     * matrícula sea coherente: nadie está inscrito en una materia de una etapa
     * que todavía no le corresponde.
     */
    public static array $etapaDeEstudiante = [];

    /** Cuatrimestre al que pertenece toda la matrícula de este seed. */
    private static int $cuatrimestreId;

    private const CARRERA_DE_EMAIL = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => 'Informática',
        DatabaseSeeder::EMAIL_DEUDA => 'Informática',
        DatabaseSeeder::EMAIL_EGRESADA => DatabaseSeeder::CARRERA_EGRESADA,
        DatabaseSeeder::EMAIL_CONFLICTO => 'Informática',
        DatabaseSeeder::EMAIL_INASISTENTE => 'Informática',
        DatabaseSeeder::EMAIL_ALERTA => 'Informática',
        DatabaseSeeder::EMAIL_REPITIENTE => 'Informática',
        DatabaseSeeder::EMAIL_PUNTUAL => 'Informática',
    ];

    private const CURSOS_CONFLICTO_ESTUDIANTE = [
        'Fundamentos de Programación',
        'Inglés Técnico',
    ];

    /**
     * Matrícula del cuatrimestre vigente, coherente con la etapa del plan:
     * alejandro va por el 1ro (Matemática + Ofimática), luis y carlos por el
     * 2do (Inglés + Fundamentos), andreina y diego por el 3ro (Base de Datos I
     * + Programación II). Cada quien ve y se inscribe solo en lo suyo.
     */
    private const MATRICULA_EXPLICITA = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => ['Matemática Básica', 'Ofimática'],
        DatabaseSeeder::EMAIL_PUNTUAL => ['Ofimática', 'Matemática Básica'],
        DatabaseSeeder::EMAIL_CONFLICTO => self::CURSOS_CONFLICTO_ESTUDIANTE,
        DatabaseSeeder::EMAIL_INASISTENTE => ['Fundamentos de Programación', 'Inglés Técnico'],
        DatabaseSeeder::EMAIL_ALERTA => ['Base de Datos I', 'Programación II'],
        DatabaseSeeder::EMAIL_REPITIENTE => ['Base de Datos I', 'Programación II'],
    ];

    /** Etapa del plan de cada estudiante fijo (Informática). */
    private const ETAPA_DE_EMAIL = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => 1,
        DatabaseSeeder::EMAIL_PUNTUAL => 1,
        DatabaseSeeder::EMAIL_CONFLICTO => 2,
        DatabaseSeeder::EMAIL_INASISTENTE => 2,
        DatabaseSeeder::EMAIL_ALERTA => 3,
        DatabaseSeeder::EMAIL_REPITIENTE => 3,
    ];

    public function run(): void
    {
        $cursos = Curso::orderBy('id_curso')->get();
        $cursosPorNombre = $cursos->keyBy('nombre');
        $cursosPorEtapa = $this->cursosPorEtapa($cursos);
        $capPorCurso = $this->capPorCurso($cursos);

        self::$cuatrimestreId = $this->cuatrimestreDeMatricula()->id_cuatrimestre;

        $estudiantes = Estudiante::where('deuda', false)->orderBy('id_usuario')->pluck('id_usuario');
        $idPorEmail = Usuario::whereIn('email', array_keys(self::CARRERA_DE_EMAIL))
            ->pluck('id_usuario', 'email');
        $fijos = $idPorEmail->values()->all();
        $aleatorios = $estudiantes->reject(fn ($id) => in_array($id, $fijos, true))->values()->all();

        self::$carreraDeEstudiante = $this->carreraDeEstudiante($idPorEmail, $aleatorios);
        $this->persistirCarreras();

        foreach (self::ETAPA_DE_EMAIL as $email => $etapa) {
            self::$etapaDeEstudiante[$idPorEmail[$email]] = $etapa;
        }

        $filas = [];

        $estaConflicto = DatabaseSeeder::EMAIL_CONFLICTO;
        $permiteSolape = fn (string $email) => $email === $estaConflicto;

        foreach (self::MATRICULA_EXPLICITA as $email => $nombresCurso) {
            $sid = $idPorEmail[$email];
            foreach ($nombresCurso as $nombre) {
                $this->registrar($sid, $cursosPorNombre[$nombre], $capPorCurso, $filas, $permiteSolape($email));
            }
        }

        foreach ($aleatorios as $i => $sid) {
            if (count(self::$enrolados[$sid] ?? []) >= 2) {
                continue;
            }

            $carrera = self::$carreraDeEstudiante[$sid];
            $etapa = ($i % 3) + 1; // estudiantes genéricos en el 1ro, 2do o 3ro
            self::$etapaDeEstudiante[$sid] = $etapa;

            $candidatos = $cursosPorEtapa[$carrera][$etapa] ?? [];

            foreach ($candidatos as $curso) {
                if (count(self::$enrolados[$sid] ?? []) >= 2) {
                    break;
                }

                $this->registrar($sid, $curso, $capPorCurso, $filas);
            }
        }

        // Quienes no consiguieron nada (cupos y horarios) piden la primera materia libre.
        foreach ($aleatorios as $sid) {
            if (! empty(self::$enrolados[$sid] ?? [])) {
                continue;
            }

            foreach ($cursos as $curso) {
                if ($this->registrar($sid, $curso, $capPorCurso, $filas)) {
                    break;
                }
            }
        }

        foreach (array_chunk($filas, 500) as $lote) {
            DB::table('inscripcion')->insert($lote);
        }
    }

    /**
     * Cuatrimestre al que corresponde la matrícula: el vigente, o el primero
     * que empieza si aún no hay ninguno en curso (matrícula anticipada).
     */
    private function cuatrimestreDeMatricula(): Cuatrimestre
    {
        $vigente = Cuatrimestre::where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->orderByDesc('fecha_inicio')
            ->first();

        return $vigente ?? Cuatrimestre::orderBy('fecha_inicio')->firstOrFail();
    }

    /**
     * Materias por carrera y por etapa del plan: [carrera => [etapa => Curso...]]
     * para inscribir a cada estudiante solo en lo que le toca.
     */
    private function cursosPorEtapa($cursos): array
    {
        $mapa = [];

        foreach (CarreraSeeder::CARRERAS as $carrera) {
            $mapa[$carrera['nombre']] = [];
        }

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            foreach ($item['carreras'] as $nombre => $etapa) {
                $mapa[$nombre][$etapa][] = $cursos[$i];
            }
        }

        foreach ($mapa as $carrera => $etapas) {
            foreach ($etapas as $etapa => $cursosDeEtapa) {
                $mapa[$carrera][$etapa] = collect($cursosDeEtapa)->sortBy('id_curso')->values()->all();
            }
        }

        return $mapa;
    }

    private function capPorCurso($cursos): array
    {
        $cap = [];

        foreach ($cursos as $curso) {
            $cap[$curso->id_curso] = in_array($curso->nombre, DatabaseSeeder::CURSOS_LLENOS, true)
                ? $curso->limite_estudiantes
                : (int) floor(0.95 * $curso->limite_estudiantes);
        }

        return $cap;
    }

    private function carreraDeEstudiante($idPorEmail, array $aleatorios): array
    {
        $mapa = [];

        foreach (self::CARRERA_DE_EMAIL as $email => $carrera) {
            $mapa[$idPorEmail[$email]] = $carrera;
        }

        foreach ($aleatorios as $i => $sid) {
            $mapa[$sid] = CarreraSeeder::CARRERAS[$i % 8]['nombre'];
        }

        return $mapa;
    }

    /**
     * Persiste en la tabla `estudiante` la carrera de cada estudiante,
     * para poder filtrar la oferta académica por carrera.
     */
    private function persistirCarreras(): void
    {
        $carreraId = Carrera::pluck('id_carrera', 'nombre');

        foreach (self::$carreraDeEstudiante as $sid => $nombreCarrera) {
            DB::table('estudiante')
                ->where('id_usuario', $sid)
                ->update(['id_carrera' => $carreraId[$nombreCarrera] ?? null]);
        }

        // Estudiantes con deuda no entran en el mapa de inscripción: se les asigna carrera por turno.
        $idsSinCarrera = DB::table('estudiante')
            ->whereNull('id_carrera')
            ->orderBy('id_usuario')
            ->pluck('id_usuario');

        foreach ($idsSinCarrera as $i => $sid) {
            $nombreCarrera = CarreraSeeder::CARRERAS[$i % 8]['nombre'];
            DB::table('estudiante')
                ->where('id_usuario', $sid)
                ->update(['id_carrera' => $carreraId[$nombreCarrera]]);
        }
    }

    private function registrar(int $sid, Curso $curso, array &$cap, array &$filas, bool $ignorarSolape = false): bool
    {
        $cid = $curso->id_curso;

        if (in_array($cid, self::$enrolados[$sid] ?? [], true) || ($cap[$cid] ?? 0) < 1) {
            return false;
        }

        $slot = HorarioSeeder::slotDe($curso);

        if (! $ignorarSolape && in_array($slot, self::$slotsDeEstudiante[$sid] ?? [], true)) {
            return false;
        }

        self::$enrolados[$sid][] = $cid;
        self::$slotsDeEstudiante[$sid][] = $slot;
        $cap[$cid]--;

        $filas[] = [
            'id_estudiante' => $sid,
            'id_curso' => $cid,
            'id_cuatrimestre' => self::$cuatrimestreId,
            'fecha_inscripcion' => '2026-09-0'.(($sid % 6) + 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return true;
    }
}
