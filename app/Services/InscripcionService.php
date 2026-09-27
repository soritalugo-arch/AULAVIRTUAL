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

            // 1. Bloqueo por deuda pendiente
            if ((bool) $estudiante->deuda === true) {
                throw new Exception("El estudiante posee deudas pendientes y no puede inscribirse.");
            }

            // 2. Conflicto de horario del estudiante
            if ($this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                throw new Exception("Existe un conflicto de horario con otra asignatura del estudiante.");
            }

            // 3. Conflicto de horario del profesor
            if ($this->tieneConflictoHorarioProfesor($curso)) {
                throw new Exception("Existe un conflicto de horario para el profesor asignado a la asignatura.");
            }

            // 4. Control de cupo
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
     * Promover al primer estudiante de la lista de espera (FIFO).
     */
    public function promoverDeListaEspera(Curso $curso)
    {
        $siguienteEnLista = Lista_espera::where('id_curso', $curso->id_curso)
        ->orderBy('created_at', 'asc')
        ->first();

        if ($siguienteEnLista) {
            $estudiante = $siguienteEnLista->estudiante;

            if (!$estudiante->deuda && !$this->tieneConflictoHorarioEstudiante($estudiante, $curso)) {
                Inscripcion::create([
                    'id_estudiante'     => $estudiante->id_usuario,
                    'id_curso'          => $curso->id_curso,
                    'fecha_inscripcion' => now(),
                ]);

                $siguienteEnLista->delete();
            }
        }
    }

    /**
     * Auxiliares para validación de choque de horarios
     */
    private function tieneConflictoHorarioEstudiante(Estudiante $estudiante, Curso $nuevoCurso): bool
    {
        $horariosNuevoCurso = $nuevoCurso->horarios;

        if ($horariosNuevoCurso->isEmpty()) {
            return false;
        }

        // Obtener IDs de cursos donde el estudiante ya está inscrito
        $cursosInscritosIds = $estudiante->inscripciones()->pluck('id_curso');

        foreach ($horariosNuevoCurso as $horarioNuevo) {
            $existeConflicto = DB::table('horario')
            ->whereIn('id_curso', $cursosInscritosIds)
            ->where('dia_semana', $horarioNuevo->dia_semana)
            ->where(function ($q) use ($horarioNuevo) {
                $q->whereBetween('hora_inicio', [$horarioNuevo->hora_inicio, $horarioNuevo->hora_fin])
                ->orWhereBetween('hora_fin', [$horarioNuevo->hora_inicio, $horarioNuevo->hora_fin]);
            })
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
            ->where(function ($q) use ($horario) {
                $q->whereBetween('horario.hora_inicio', [$horario->hora_inicio, $horario->hora_fin])
                ->orWhereBetween('horario.hora_fin', [$horario->hora_inicio, $horario->hora_fin]);
            })
            ->exists();

            if ($conflicto) {
                return true;
            }
        }

        return false;
    }
}
