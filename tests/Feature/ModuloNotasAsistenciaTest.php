<?php

use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function cuatrimestreVigenteFeature(): Cuatrimestre
{
    return Cuatrimestre::create([
        'fecha_inicio' => now()->subDays(1)->toDateString(),
        'fecha_fin' => now()->addDays(1)->toDateString(),
        'estado' => 'en_curso',
    ]);
}

function crearProfesorConAcceso(): array
{
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(Rol::create(['nombre' => 'profesor']));

    $profesor = Profesor::factory()->create(['id_usuario' => $usuario->getKey()]);

    return ['usuario' => $usuario, 'profesor' => $profesor];
}

function crearCursoDeProfesor(Profesor $profesor): Curso
{
    $curso = Curso::create(['nombre' => 'Materia del Profesor', 'limite_estudiantes' => 30]);
    $profesor->cursos()->attach($curso);
    $curso->cuatrimestres()->attach(cuatrimestreVigenteFeature());

    return $curso;
}

function estudianteNotasFeature(int $index = 1): Estudiante
{
    $usuario = Usuario::create([
        'nombres' => "Estudiante $index",
        'apellidos' => 'Feature',
        'telefono' => '04141234567',
        'email' => "feature-$index@test.com",
        'password' => bcrypt('password'),
    ]);

    return Estudiante::create([
        'id_usuario' => $usuario->getKey(),
        'cedula' => (string) rand(10000000, 99999999),
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => false,
    ]);
}

function crearCursoConHorario(Profesor $profesor, string $dia): Curso
{
    $cuatrimestre = Cuatrimestre::create([
        'fecha_inicio' => '2026-03-02',
        'fecha_fin' => '2026-03-29',
        'estado' => 'en_curso',
    ]);

    $curso = Curso::create(['nombre' => 'Materia con Horario', 'limite_estudiantes' => 30]);
    $profesor->cursos()->attach($curso);
    $curso->cuatrimestres()->attach($cuatrimestre);

    Horario::create([
        'id_curso' => $curso->getKey(),
        'dia_semana' => $dia,
        'hora_inicio' => '18:00:00',
        'hora_fin' => '20:00:00',
    ]);

    return $curso;
}

function inscribirEstudianteEn(Estudiante $estudiante, Curso $curso): void
{
    Inscripcion::create([
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'fecha_inscripcion' => now(),
    ]);
}

test('profesor accede a notas de su curso con el cuatrimestre vigente', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);

    $this->actingAs($usuario)
        ->get(route('profesor.notas', $curso->getKey()))
        ->assertOk();
});

test('profesor no puede guardar notas de un curso que no es suyo', function () {
    ['usuario' => $usuario] = crearProfesorConAcceso();
    ['profesor' => $otro] = crearProfesorConAcceso();
    $cursoAjeno = Curso::create(['nombre' => 'Curso Ajeno', 'limite_estudiantes' => 30]);
    $otro->cursos()->attach($cursoAjeno);

    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $cursoAjeno->getKey(),
            'id_cuatrimestre' => 1,
            'notas' => [],
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('calificacion', 0);
});

test('profesor no puede usar un cuatrimestre que no pertenece a su curso', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    $cuatrimestreAjeno = Cuatrimestre::create([
        'fecha_inicio' => now()->subMonths(6)->toDateString(),
        'fecha_fin' => now()->subMonths(4)->toDateString(),
    ]);

    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestreAjeno->getKey(),
            'notas' => [
                ['id_estudiante' => $estudiante->getKey(), 'nota' => 7, 'observaciones' => null],
            ],
        ])
        ->assertForbidden();
});

test('profesor guarda una nota a un estudiante inscrito', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                ['id_estudiante' => $estudiante->getKey(), 'nota' => 7, 'observaciones' => 'cumple'],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'nota' => 7,
        'observaciones' => 'cumple',
    ]);
});

test('dejar la nota en blanco elimina la calificacion existente', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    Calificacion::create([
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'nota' => 8,
    ]);

    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                ['id_estudiante' => $estudiante->getKey(), 'nota' => null, 'observaciones' => null],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
    ]);
});

test('profesor no puede registrar asistencia en un curso ajeno', function () {
    ['usuario' => $usuario] = crearProfesorConAcceso();
    ['profesor' => $otro] = crearProfesorConAcceso();
    $cursoAjeno = Curso::create(['nombre' => 'Curso Ajeno', 'limite_estudiantes' => 30]);
    $otro->cursos()->attach($cursoAjeno);

    $this->actingAs($usuario)
        ->post(route('profesor.asistencia.guardar'), [
            'id_curso' => $cursoAjeno->getKey(),
            'id_cuatrimestre' => 1,
            'fecha' => now()->toDateString(),
            'presentes' => [],
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('asistencia', 0);
});

test('no permite registrar asistencia de una fecha fuera del cuatrimestre', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();

    $this->actingAs($usuario)
        ->post(route('profesor.asistencia.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => now()->subMonth(8)->toDateString(),
            'presentes' => [],
        ])
        ->assertStatus(422);
});

test('permite registrar asistencia en el dia del horario del curso', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoConHorario($profesor, 'Lunes');
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    $this->actingAs($usuario)
        ->post(route('profesor.asistencia.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => '2026-03-09',
            'presentes' => [$estudiante->getKey()],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('asistencia', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'fecha' => '2026-03-09',
        'presente' => true,
    ]);
});

test('rechaza registrar asistencia en un dia que no es de clase', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoConHorario($profesor, 'Lunes');
    $cuatrimestre = $curso->cuatrimestres()->first();

    $this->actingAs($usuario)
        ->post(route('profesor.asistencia.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => '2026-03-10',
            'presentes' => [],
        ])
        ->assertStatus(422);
});