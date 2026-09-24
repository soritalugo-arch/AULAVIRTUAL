<?php

namespace Database\Seeders;

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

    private const MATRICULA_EXPLICITA = [
        DatabaseSeeder::EMAIL_ESTUDIANTE => ['Base de Datos I', 'Desarrollo Web'],
        DatabaseSeeder::EMAIL_CONFLICTO => self::CURSOS_CONFLICTO_ESTUDIANTE,
        DatabaseSeeder::EMAIL_INASISTENTE => ['Base de Datos I', 'Sistemas Operativos'],
        DatabaseSeeder::EMAIL_ALERTA => ['Programación II', 'Redes de Computadoras'],
        DatabaseSeeder::EMAIL_REPITIENTE => ['Base de Datos I', 'Programación II'],
        DatabaseSeeder::EMAIL_PUNTUAL => ['Sistemas Operativos', 'Desarrollo Web'],
    ];

    public function run(): void
    {
        $cursos = Curso::orderBy('id_curso')->get();
        $cursosPorNombre = $cursos->keyBy('nombre');
        $cursosPorCarrera = $this->cursosPorCarrera($cursos);
        $capPorCurso = $this->capPorCurso($cursos);

        $estudiantes = Estudiante::where('deuda', false)->orderBy('id_usuario')->pluck('id_usuario');
        $idPorEmail = Usuario::whereIn('email', array_keys(self::CARRERA_DE_EMAIL))
            ->pluck('id_usuario', 'email');
        $fijos = $idPorEmail->values()->all();
        $aleatorios = $estudiantes->reject(fn ($id) => in_array($id, $fijos, true))->values()->all();

        self::$carreraDeEstudiante = $this->carreraDeEstudiante($idPorEmail, $aleatorios);

        $filas = [];

        $estaConflicto = DatabaseSeeder::EMAIL_CONFLICTO;
        $permiteSolape = fn (string $email) => $email === $estaConflicto;

        foreach (self::MATRICULA_EXPLICITA as $email => $nombresCurso) {
            $sid = $idPorEmail[$email];
            foreach ($nombresCurso as $nombre) {
                $this->registrar($sid, $cursosPorNombre[$nombre], $capPorCurso, $filas, $permiteSolape($email));
            }
        }

        foreach ($aleatorios as $sid) {
            if (count(self::$enrolados[$sid] ?? []) >= 2) {
                continue;
            }

            $carrera = self::$carreraDeEstudiante[$sid];
            $candidatos = $cursosPorCarrera[$carrera]->sortBy('id_curso')->values();

            foreach ($candidatos as $curso) {
                if (count(self::$enrolados[$sid] ?? []) >= 2) {
                    break;
                }

                $this->registrar($sid, $curso, $capPorCurso, $filas);
            }
        }

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

    private function cursosPorCarrera($cursos): array
    {
        $mapa = [];

        foreach (CarreraSeeder::CARRERAS as $carrera) {
            $mapa[$carrera['nombre']] = collect();
        }

        foreach (array_values(CursoSeeder::CATALOGO) as $i => $item) {
            foreach ($item['carreras'] as $nombre) {
                $mapa[$nombre]->push($cursos[$i]);
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
            'fecha_inscripcion' => '2026-09-0' . (($sid % 6) + 1),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return true;
    }
}