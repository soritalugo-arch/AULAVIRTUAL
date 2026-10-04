<?php

namespace App\Services;

use App\Events\CursoPorComenzar;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Lista_espera;
use Exception;
use Illuminate\Support\Facades\DB;

class InscripcionService
{
    /**
     * Inscribir a un estudiante en un curso o enviarlo a la lista de espera.
     */
    public function inscribir(Estudiante $estudiante, Curso $curso)
    {
        return DB::transaction(function () use ($estudiante, $curso) {

            // Bloquear la fila del curso: serializa las inscripciones concurrentes
            // y evita que dos estudiantes ocupen el último cupo al mismo tiempo.
            $curso = Curso::query()->whereKey($curso->id_curso)->lockForUpdate()->first();

            if (! $curso) {
                throw new Exception('El curso no existe.');
            }

            // 1. La asignatura debe pertenecer a la carrera del estudiante
            if (! $this->cursoEsDeLaCarreraDelEstudiante($estudiante, $curso)) {
                throw new Exception('La asignatura no pertenece a la carrera del estudiante.');
            }

            // 1b. Debe ser una materia del cuatrimestre del plan en el que el
            // estudiante está: quien va por el cuatrimestre 4 solo puede
            // inscribir las 2 o 3 materias de esa etapa, no las de la carrera
            // entera. Carreras sin etapas definidas conservan el
            // comportamiento anterior (todas sus materias son inscribibles).
            $this->validarEtapaDelPlan($estudiante, $curso);

            // 2. La asignatura debe ofrecerse en el cuatrimestre vigente
            $cuatrimestre = $this->cuatrimestreVigente();

            if (! $cuatrimestre || ! $curso->cuatrimestres()->whereKey($cuatrimestre->id_cuatrimestre)->exists()) {
                throw new Exception('La asignatura no está disponible en el cuatrimestre vigente.');
            }

            // 2b. Solo se inscribe mientras el período está en MATRÍCULA. Una
            // vez que comienzan las clases (en_curso) la inscripción se cierra,
            // y en un período cerrado no se toca nada: así el momento de
            // inscribirse nunca se mezcla con el momento de cursar.
            if ($cuatrimestre->estado !== Cuatrimestre::ESTADO_MATRICULA) {
                throw new Exception('La matrícula para este período está cerrada.');
            }

            // 3. Bloqueo por deuda pendiente
            if ((bool) $estudiante->deuda === true) {
                throw new Exception('El estudiante posee deudas pendientes y no puede inscribirse.');
            }

            // 4. Ya está inscrito en el curso en ESTE cuatrimestre (en otro
            //    periodo es una repetición válida: reprobar no elimina la materia)
            if ($this->yaInscrito($estudiante, $curso, $cuatrimestre)) {
                throw new Exception('El estudiante ya está inscrito en esta asignatura.');
            }

            // 5. Ya está en la lista de espera del curso
            if ($this->yaEnListaEspera($estudiante, $curso)) {
                throw new Exception('El estudiante ya está en la lista de espera de esta asignatura.');
            }

            // 6. Control de cupo (con el curso bloqueado, el conteo es seguro).
            // Se valida ANTES de los conflictos de horario del estudiante para que
            // el mensaje sea uniforme para todos: estar en lista de espera no
            // compromete horarios y en la promoción ya se salta a los no aptos.
            $inscritosActuales = Inscripcion::where('id_curso', $curso->id_curso)->count();

            if ($inscritosActuales >= $curso->limite_estudiantes) {
                return Lista_espera::create([
                    'id_estudiante' => $estudiante->id_usuario,
                    'id_curso' => $curso->id_curso,
                ]);
            }

            // 7. Conflicto de horario del estudiante
            if ($this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                throw new Exception('Existe un conflicto de horario con otra asignatura del estudiante.');
            }

            // 8. Conflicto de horario del profesor
            if ($this->tieneConflictoHorarioProfesor($curso)) {
                throw new Exception('Existe un conflicto de horario para el profesor asignado a la asignatura.');
            }

            // Registrar inscripción
            $inscripcion = Inscripcion::create([
                'id_estudiante' => $estudiante->id_usuario,
                'id_curso' => $curso->id_curso,
                'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
                'fecha_inscripcion' => now(),
            ]);

            if (method_exists($curso, 'estaPorComenzar') && $curso->estaPorComenzar()) {
                event(new CursoPorComenzar($curso));
            }

            return $inscripcion;
        });
    }

    /**
     * Desinscribir a un estudiante y promover automáticamente desde la lista de espera.
     */
    public function desinscribir(Estudiante $estudiante, Curso $curso)
    {
        return DB::transaction(function () use ($estudiante, $curso) {
            $curso = Curso::query()->whereKey($curso->id_curso)->lockForUpdate()->first();

            if (! $curso) {
                return;
            }

            // Solo se desinscribe la matrícula del periodo vigente: una fila
            // histórica de otro cuatrimestre es un curso ya cursado, no algo
            // que se pueda quitar con este botón.
            $cuatrimestre = $this->cuatrimestreVigente();

            if (! $cuatrimestre) {
                return;
            }

            if ($cuatrimestre->estado !== Cuatrimestre::ESTADO_MATRICULA) {
                throw new Exception('La matrícula para este período está cerrada.');
            }

            $inscripcion = Inscripcion::where('id_estudiante', $estudiante->id_usuario)
                ->where('id_curso', $curso->id_curso)
                ->where('id_cuatrimestre', $cuatrimestre->id_cuatrimestre)
                ->first();

            if ($inscripcion) {
                $inscripcion->delete();

                // Promover al siguiente en lista de espera
                $this->promoverDeListaEspera($curso);
            }
        });
    }

    /**
     * Promover desde la lista de espera (FIFO).
     *
     * Salta a los no aptos (deuda o conflicto de horario) conservando su
     * puesto, elimina a los ya inscritos (fila redundante) y promueve al
     * primer candidato válido.
     */
    public function promoverDeListaEspera(Curso $curso)
    {
        $curso = Curso::query()->whereKey($curso->id_curso)->lockForUpdate()->first();

        if (! $curso) {
            return;
        }

        // Si el cupo sigue lleno, no se promueve nadie
        $inscritosActuales = Inscripcion::where('id_curso', $curso->id_curso)->count();

        if ($inscritosActuales >= $curso->limite_estudiantes) {
            return;
        }

        $cuatrimestre = $this->cuatrimestreVigente();

        if (! $cuatrimestre) {
            return;
        }

        $enCola = Lista_espera::where('id_curso', $curso->id_curso)
            ->orderBy('created_at', 'asc')
            ->orderBy('idlista_espera', 'asc')
            ->get();

        foreach ($enCola as $item) {
            $estudiante = $item->estudiante;

            // Fila redundante: ya está inscrito en el periodo → se elimina
            if ($this->yaInscrito($estudiante, $curso, $cuatrimestre)) {
                $item->delete();

                continue;
            }

            // No apto hoy (deuda o conflicto): se salta y conserva su puesto
            if ($estudiante->deuda || $this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                continue;
            }

            Inscripcion::create([
                'id_estudiante' => $estudiante->id_usuario,
                'id_curso' => $curso->id_curso,
                'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
                'fecha_inscripcion' => now(),
            ]);

            $item->delete();

            return;
        }
    }

    /**
     * Quitar a un estudiante de la lista de espera de un curso.
     *
     * Operación idempotente: devuelve true si eliminó una fila y false si
     * el estudiante no estaba en la lista.
     */
    public function quitarDeListaEspera(Estudiante $estudiante, Curso $curso): bool
    {
        $cuatrimestre = $this->cuatrimestreVigente();

        if ($cuatrimestre && $cuatrimestre->estado !== Cuatrimestre::ESTADO_MATRICULA) {
            throw new Exception('La matrícula para este período está cerrada.');
        }

        return DB::transaction(function () use ($estudiante, $curso) {
            $curso = Curso::query()->whereKey($curso->id_curso)->lockForUpdate()->first();

            if (! $curso) {
                return false;
            }

            return (bool) Lista_espera::where('id_estudiante', $estudiante->id_usuario)
                ->where('id_curso', $curso->id_curso)
                ->delete();
        });
    }

    /**
     * ¿El estudiante ya está inscrito en el curso en un cuatrimestre dado?
     *
     * Se filtra por cuatrimestre a propósito: desde que se permite repetir
     * asignaturas reprobadas, la misma persona puede tener una inscripción
     * antigua en el curso y una nueva en el vigente; lo que no puede tener es
     * dos filas en el mismo periodo (lo refuerza la unicidad de la tabla).
     */
    private function yaInscrito(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre): bool
    {
        return Inscripcion::where('id_estudiante', $estudiante->id_usuario)
            ->where('id_curso', $curso->id_curso)
            ->where('id_cuatrimestre', $cuatrimestre->id_cuatrimestre)
            ->exists();
    }

    /**
     * ¿El estudiante ya está en la lista de espera del curso?
     */
    private function yaEnListaEspera(Estudiante $estudiante, Curso $curso): bool
    {
        return Lista_espera::where('id_estudiante', $estudiante->id_usuario)
            ->where('id_curso', $curso->id_curso)
            ->exists();
    }

    /**
     * ¿La asignatura pertenece a la carrera del estudiante?
     */
    private function cursoEsDeLaCarreraDelEstudiante(Estudiante $estudiante, Curso $curso): bool
    {
        if (! $estudiante->id_carrera) {
            return false;
        }

        return $curso->carreras()->whereKey($estudiante->id_carrera)->exists();
    }

    /**
     * Valida que el curso sea una materia de la ventana de cuatrimestres actual.
     *
     * El estudiante solo puede inscribir las materias que le tocan ahora: un
     * cuatrimestre (X) cuando no arrastra materias, o dos (X-Y) cuando repite
     * lo raspado y adelanta el siguiente. Una carrera sin etapas definidas
     * conserva el comportamiento anterior (todas sus materias son
     * inscribibles).
     */
    private function validarEtapaDelPlan(Estudiante $estudiante, Curso $curso): void
    {
        $carrera = $estudiante->carrera;

        if (! $carrera) {
            return;
        }

        $conEtapas = $carrera->cursos()
            ->withPivot('etapa')
            ->get()
            ->contains(fn ($c) => $c->pivot->etapa !== null);

        if (! $conEtapas) {
            return;
        }

        $ventana = app(HistorialService::class)->ventanaEtapas($estudiante);

        if ($ventana === null) {
            throw new Exception('Ya completaste tu carrera: no tienes materias pendientes.');
        }

        $esDeLaVentana = $curso->carreras()
            ->whereKey($carrera->id_carrera)
            ->wherePivot('etapa', '>=', $ventana['desde'])
            ->wherePivot('etapa', '<=', $ventana['hasta'])
            ->exists();

        if (! $esDeLaVentana) {
            throw new Exception('Esta asignatura no corresponde al cuatrimestre del plan en el que estás.');
        }
    }

    /**
     * Cuatrimestre cuyo rango de fechas incluye el día de hoy.
     */
    private function cuatrimestreVigente(): ?Cuatrimestre
    {
        return Cuatrimestre::where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->orderByDesc('fecha_inicio')
            ->first();
    }

    /**
     * Auxiliares para validación de choque de horarios.
     *
     * Dos clases chocan si se solapan de verdad: una termina DESPUÉS de que
     * la otra empieza y una empieza ANTES de que la otra termina.
     * Clases consecutivas (08:00-10:00 y 10:00-12:00) NO son conflicto.
     */
    private function tieneConflictoHorarioEstudiante(Estudiante $estudiante, Curso $nuevoCurso): bool
    {
        $horariosNuevoCurso = $nuevoCurso->horarios;

        if ($horariosNuevoCurso->isEmpty()) {
            return false;
        }

        // Obtener IDs de cursos donde el estudiante ya está inscrito
        $cursosInscritosIds = $estudiante->inscripciones()->pluck('id_curso');

        if ($cursosInscritosIds->isEmpty()) {
            return false;
        }

        foreach ($horariosNuevoCurso as $horarioNuevo) {
            $existeConflicto = DB::table('horario')
                ->whereIn('id_curso', $cursosInscritosIds)
                ->where('dia_semana', $horarioNuevo->dia_semana)
                ->where('hora_inicio', '<', $horarioNuevo->hora_fin)
                ->where('hora_fin', '>', $horarioNuevo->hora_inicio)
                ->exists();

            if ($existeConflicto) {
                return true;
            }
        }

        return false;
    }

    private function tieneConflictoHorarioProfesor(Curso $curso): bool
    {
        $profesoresIds = $curso->profesores()->pluck('profesor.id_usuario');
        $horariosCurso = $curso->horarios;

        if ($profesoresIds->isEmpty() || $horariosCurso->isEmpty()) {
            return false;
        }

        foreach ($horariosCurso as $horario) {
            $conflicto = DB::table('horario')
                ->join('curso_profesor', 'horario.id_curso', '=', 'curso_profesor.curso_id')
                ->whereIn('curso_profesor.profesor_id', $profesoresIds)
                ->where('horario.id_curso', '!=', $curso->id_curso)
                ->where('horario.dia_semana', $horario->dia_semana)
                ->where('horario.hora_inicio', '<', $horario->hora_fin)
                ->where('horario.hora_fin', '>', $horario->hora_inicio)
                ->exists();

            if ($conflicto) {
                return true;
            }
        }

        return false;
    }
}
