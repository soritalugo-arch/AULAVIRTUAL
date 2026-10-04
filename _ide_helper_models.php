<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id_asistencia
 * @property int $id_estudiante
 * @property int $id_curso
 * @property int $id_cuatrimestre
 * @property string $fecha
 * @property bool $presente
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cuatrimestre $cuatrimestre
 * @property-read \App\Models\Curso $curso
 * @property-read \App\Models\Estudiante $estudiante
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereIdAsistencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereIdCuatrimestre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereIdEstudiante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia wherePresente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Asistencia whereUpdatedAt($value)
 */
	class Asistencia extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_calificacion
 * @property int $id_estudiante
 * @property int $id_curso
 * @property int $id_cuatrimestre
 * @property int $nota
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cuatrimestre $cuatrimestre
 * @property-read \App\Models\Curso $curso
 * @property-read \App\Models\Estudiante $estudiante
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereIdCalificacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereIdCuatrimestre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereIdEstudiante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Calificacion whereUpdatedAt($value)
 */
	class Calificacion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_carrera
 * @property string $nombre
 * @property int $duracion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Curso> $cursos
 * @property-read int|null $cursos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera whereDuracion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera whereIdCarrera($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carrera whereUpdatedAt($value)
 */
	class Carrera extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_cuatrimestre
 * @property \Illuminate\Support\Carbon $fecha_inicio
 * @property \Illuminate\Support\Carbon $fecha_fin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asistencia> $asistencias
 * @property-read int|null $asistencias_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Calificacion> $calificaciones
 * @property-read int|null $calificaciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Curso> $cursos
 * @property-read int|null $cursos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre whereFechaFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre whereFechaInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre whereIdCuatrimestre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cuatrimestre whereUpdatedAt($value)
 */
	class Cuatrimestre extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_curso
 * @property string $nombre
 * @property int $limite_estudiantes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asistencia> $asistencias
 * @property-read int|null $asistencias_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Calificacion> $calificaciones
 * @property-read int|null $calificaciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Carrera> $carreras
 * @property-read int|null $carreras_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cuatrimestre> $cuatrimestres
 * @property-read int|null $cuatrimestres_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Horario> $horarios
 * @property-read int|null $horarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lista_espera> $listaEspera
 * @property-read int|null $lista_espera_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Profesor> $profesores
 * @property-read int|null $profesores_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso whereLimiteEstudiantes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Curso whereUpdatedAt($value)
 */
	class Curso extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_usuario
 * @property \Illuminate\Support\Carbon $fecha_nacimiento
 * @property string $cedula
 * @property bool $deuda
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_carrera
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Asistencia> $asistencias
 * @property-read int|null $asistencias_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Calificacion> $calificaciones
 * @property-read int|null $calificaciones_count
 * @property-read \App\Models\Carrera|null $carrera
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inscripcion> $inscripciones
 * @property-read int|null $inscripciones_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lista_espera> $listaEspera
 * @property-read int|null $lista_espera_count
 * @property-read \App\Models\Usuario $usuario
 * @method static \Database\Factories\EstudianteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereCedula($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereDeuda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereFechaNacimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereIdCarrera($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereIdUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Estudiante whereUpdatedAt($value)
 */
	class Estudiante extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_horario
 * @property int $id_curso
 * @property string $dia_semana
 * @property string $hora_inicio
 * @property string $hora_fin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curso $curso
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereDiaSemana($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereHoraFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereHoraInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereIdHorario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Horario whereUpdatedAt($value)
 */
	class Horario extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_inscripcion
 * @property int $id_estudiante
 * @property int $id_curso
 * @property \Illuminate\Support\Carbon $fecha_inscripcion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $id_cuatrimestre
 * @property-read \App\Models\Cuatrimestre|null $cuatrimestre
 * @property-read \App\Models\Curso $curso
 * @property-read \App\Models\Estudiante $estudiante
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereFechaInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdCuatrimestre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdEstudiante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereIdInscripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inscripcion whereUpdatedAt($value)
 */
	class Inscripcion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $idlista_espera
 * @property int $id_curso
 * @property int $id_estudiante
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curso $curso
 * @property-read \App\Models\Estudiante $estudiante
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera whereIdCurso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera whereIdEstudiante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera whereIdlistaEspera($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lista_espera whereUpdatedAt($value)
 */
	class Lista_espera extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_usuario
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Curso> $cursos
 * @property-read int|null $cursos_count
 * @property-read \App\Models\Usuario $usuario
 * @method static \Database\Factories\ProfesorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor whereIdUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Profesor whereUpdatedAt($value)
 */
	class Profesor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_rol
 * @property string $nombre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Usuario> $usuarios
 * @property-read int|null $usuarios_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereIdRol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rol whereUpdatedAt($value)
 */
	class Rol extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id_usuario
 * @property string $nombres
 * @property string $apellidos
 * @property string $telefono
 * @property string $email
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Estudiante|null $estudiante
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Profesor|null $profesor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rol> $roles
 * @property-read int|null $roles_count
 * @method static \Database\Factories\UsuarioFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereApellidos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereIdUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereNombres($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereTelefono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Usuario whereUpdatedAt($value)
 */
	class Usuario extends \Eloquent {}
}

