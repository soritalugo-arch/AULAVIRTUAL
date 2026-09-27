<?php

namespace App\Services;

use App\Events\CursoPorComenzar;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Lista_espera;
use Illuminate\Support\Facades\DB;
use Exception;

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

            // 1. Bloqueo por deuda pendiente
            if ((bool) $estudiante->deuda === true) {
                throw new Exception("El estudiante posee deudas pendientes y no puede inscribirse.");
            }

            // 2. Ya está inscrito en el curso
            if ($this->yaInscrito($estudiante, $curso)) {
                throw new Exception("El estudiante ya está inscrito en esta asignatura.");
            }

            // 3. Ya está en la lista de espera del curso
            if ($this->yaEnListaEspera($estudiante, $curso)) {
                throw new Exception("El estudiante ya está en la lista de espera de esta asignatura.");
            }

            // 4. Conflicto de horario del estudiante
            if ($this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                throw new Exception("Existe un conflicto de horario con otra asignatura del estudiante.");
            }

            // 5. Conflicto de horario del profesor
            if ($this->tieneConflictoHorarioProfesor($curso)) {
                throw new Exception("Existe un conflicto de horario para el profesor asignado a la asignatura.");
            }

            // 6. Control de cupo (con el curso bloqueado, el conteo es seguro)
            $inscritosActuales = Inscripcion::where('id_curso', $curso->id_curso)->count();

            if ($inscritosActuales >= $curso->limite_estudiantes) {
                return Lista_espera::create([
                    'id_estudiante' => $estudiante->id_usuario,
                    'id_curso'      => $curso->id_curso,
                ]);
            }

            // Registrar inscripción
            $inscripcion = Inscripcion::create([
                'id_estudiante'     => $estudiante->id_usuario,
                'id_curso'          => $curso->id_curso,
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

            $inscripcion = Inscripcion::where('id_estudiante', $estudiante->id_usuario)
                ->where('id_curso', $curso->id_curso)
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
     * Salta a los estudiantes que ya no son elegibles (deuda, ya inscritos
     * o con conflicto de horario) y los retira de la lista hasta encontrar
     * al primer candidato válido.
     */
    public function promoverDeListaEspera(Curso $curso)
    {
        $curso = Curso::query()->whereKey($curso->id_curso)->lockForUpdate()->first();

        if (! $curso) {
            return;
        }

        while (true) {
            $primero = Lista_espera::where('id_curso', $curso->id_curso)
                ->orderBy('created_at', 'asc')
                ->orderBy('idlista_espera', 'asc')
                ->first();

            if (! $primero) {
                return;
            }

            // Si ya no hay cupo, se detiene
            $inscritosActuales = Inscripcion::where('id_curso', $curso->id_curso)->count();

            if ($inscritosActuales >= $curso->limite_estudiantes) {
                return;
            }

            $estudiante = $primero->estudiante;

            // No es elegible: se retira de la lista y se evalúa al siguiente
            if ($estudiante->deuda || $this->yaInscrito($estudiante, $curso) || $this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                $primero->delete();
                continue;
            }

            Inscripcion::create([
                'id_estudiante'     => $estudiante->id_usuario,
                'id_curso'          => $curso->id_curso,
                'fecha_inscripcion' => now(),
            ]);

            $primero->delete();

            return;
        }
    }

    /**
     * ¿El estudiante ya está inscrito en el curso?
     */
    private function yaInscrito(Estudiante $estudiante, Curso $curso): bool
    {
        return Inscripcion::where('id_estudiante', $estudiante->id_usuario)
            ->where('id_curso', $curso->id_curso)
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