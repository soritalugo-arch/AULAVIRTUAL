<?php

use App\Mail\NotaPublicada;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

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

test('profesor guarda las parciales de un estudiante inscrito y calcula el promedio', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    // Dos parciales en puntos: 17.5 y 20 de 25. Las otras dos cuentan como cero
    // (opción A), así que el promedio es (17.5 + 20 + 0 + 0) / 10 = 3.75.
    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                [
                    'id_estudiante' => $estudiante->getKey(),
                    'parcial1' => 17.5,
                    'parcial2' => 20,
                    'observaciones' => 'cumple',
                ],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'parcial1' => 17.5,
        'parcial2' => 20,
        'observaciones' => 'cumple',
    ]);

    // El promedio lo calcula el sistema, no el profesor.
    $calificacion = Calificacion::where('id_estudiante', $estudiante->getKey())
        ->where('id_curso', $curso->getKey())
        ->where('id_cuatrimestre', $cuatrimestre->getKey())
        ->first();

    expect((float) $calificacion->promedio)->toBe(3.75)
        ->and((float) $calificacion->notaEfectiva())->toBe(3.75);
});

test('una calificacion vieja de una sola nota sigue valiendo sin inventarle parciales', function () {
    $cuatrimestre = Cuatrimestre::create([
        'fecha_inicio' => now()->subMonths(6)->toDateString(),
        'fecha_fin' => now()->subMonths(4)->toDateString(),
        'estado' => 'cerrado',
    ]);

    $curso = Curso::create(['nombre' => 'Materia Vieja', 'limite_estudiantes' => 30]);
    $curso->cuatrimestres()->attach($cuatrimestre);

    // Como quedo la tabla antes de las parciales: una sola nota final.
    $vieja = Calificacion::create([
        'id_estudiante' => estudianteNotasFeature()->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'nota' => 4,
    ]);

    // No se le inventan parciales: el veredicto sale de la nota que ya estaba.
    $vieja->refresh();

    expect((bool) $vieja->tiene_parciales)->toBeFalse()
        ->and($vieja->parciales())->toBe([null, null, null, null])
        ->and((float) $vieja->notaEfectiva())->toBe(4.0);

    expect(app(CalificacionAsistenciaService::class)
        ->estadoEstudiante($vieja->notaEfectiva(), 0.0, true))->toBe('Reprobado');
});

test('las cuatro parciales completas promedian sobre diez y aprueban', function () {
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
                [
                    'id_estudiante' => $estudiante->getKey(),
                    'parcial1' => 20,
                    'parcial2' => 17.5,
                    'parcial3' => 22.5,
                    'parcial4' => 20,
                ],
            ],
        ])
        ->assertRedirect();

    // 20 + 17.5 + 22.5 + 20 = 80 de 100, y 80 / 10 = 8 de promedio.
    $this->assertDatabaseHas('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'promedio' => 8.0,
        'tiene_parciales' => true,
    ]);

    $calificacion = Calificacion::where('id_estudiante', $estudiante->getKey())
        ->where('id_curso', $curso->getKey())
        ->where('id_cuatrimestre', $cuatrimestre->getKey())
        ->first();

    expect($calificacion->acumulado())->toBe(80.0);
});

test('ninguna parcial puede pasarse de 25 puntos ni del acumulado de 100', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    // 26 ya se sale del parcial: el servidor lo rechaza aunque el navegador
    // dejara escribirlo.
    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                ['id_estudiante' => $estudiante->getKey(), 'parcial1' => 25.5],
            ],
        ])
        ->assertSessionHasErrors('notas.0.parcial1');

    $this->assertDatabaseCount('calificacion', 0);

    // Con las cuatro en el tope el acumulado queda justo en 100.
    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                [
                    'id_estudiante' => $estudiante->getKey(),
                    'parcial1' => 25,
                    'parcial2' => 25,
                    'parcial3' => 25,
                    'parcial4' => 25,
                ],
            ],
        ])
        ->assertRedirect();

    $calificacion = Calificacion::where('id_estudiante', $estudiante->getKey())
        ->where('id_curso', $curso->getKey())
        ->where('id_cuatrimestre', $cuatrimestre->getKey())
        ->first();

    expect($calificacion->acumulado())->toBe(100.0)
        ->and((float) $calificacion->promedio)->toBe(10.0);
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
        'parcial1' => 20,
        'tiene_parciales' => true,
    ]);

    // Las cuatro parciales en blanco borran la calificación.
    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'notas' => [
                ['id_estudiante' => $estudiante->getKey(), 'observaciones' => null],
            ],
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
    ]);
});

// ─── Las cuatro parciales se ven y se avisa al alumno ─────────────────────────

test('la tabla de notas muestra una casilla por parcial y el promedio del alumno', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    Calificacion::create([
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'parcial1' => 20,
        'parcial3' => 15,
        'promedio' => 3.5,
        'tiene_parciales' => true,
    ]);

    $html = $this->actingAs($usuario)
        ->get(route('profesor.notas', $curso->getKey()))
        ->assertOk()
        ->getContent();

    // Una casilla por parcial, con el valor ya puesto en la que el profe capturó.
    expect(substr_count($html, 'name="notas[0][parcial'))->toBe(4)
        ->and($html)->toContain('value="20.0"')
        ->and($html)->toContain('value="15.0"')
        // El promedio se muestra calculado, con dos decimales.
        ->and($html)->toContain('3.50')
        ->and($html)->toContain('data-promedio');
});

test('la tabla muestra el acumulado sobre 100 junto al promedio y el buscador', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    // 80 de 100 es un promedio de 8: el alumno aprueba y las dos celdas van
    // en verde.
    Calificacion::create([
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'parcial1' => 20,
        'parcial2' => 20,
        'parcial3' => 20,
        'parcial4' => 20,
        'promedio' => 8.0,
        'tiene_parciales' => true,
    ]);

    $html = $this->actingAs($usuario)
        ->get(route('profesor.notas', $curso->getKey()))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('ACUMULADO')
        ->and($html)->toContain('data-acumulado')
        ->and($html)->toContain('80.00')
        ->and($html)->toContain('8.00')
        // Las dos celdas en verde porque el alumno aprueba.
        ->and($html)->toContain('promedio aprobado')
        // Buscador por nombre y encabezados ordenables por nombre y promedio.
        ->and($html)->toContain('data-buscar')
        ->and($html)->toContain('data-orden="nombre"')
        ->and($html)->toContain('data-orden="promedio"')
        // La leyenda vieja de los 25 % ya no está.
        ->and($html)->not->toContain('Cuatro parciales de 25 % cada una');
});

test('la casilla de faltas se pinta en la propia celda segun el nivel de alerta', function () {
    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    // 2 faltas de 8 clases son 25 %: la casilla va en amarillo de atención.
    foreach (range(1, 8) as $dia) {
        Asistencia::create([
            'id_estudiante' => $estudiante->getKey(),
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => now()->startOfWeek()->addDays($dia)->toDateString(),
            'presente' => $dia > 2,
        ]);
    }

    $html = $this->actingAs($usuario)
        ->get(route('profesor.notas', $curso->getKey()))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('faltas-cell advertencia')
        ->and($html)->toContain('absence warning');
});

test('el alumno recibe un correo por cada parcial que se registra o se corrige', function () {
    Mail::fake();

    ['usuario' => $usuario, 'profesor' => $profesor] = crearProfesorConAcceso();
    $curso = crearCursoDeProfesor($profesor);
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasFeature();
    inscribirEstudianteEn($estudiante, $curso);

    $guardar = fn (array $parciales) => $this->actingAs($usuario)->post(route('profesor.notas.guardar'), [
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'notas' => [
            ['id_estudiante' => $estudiante->getKey()] + $parciales,
        ],
    ]);

    // La primera parcial se avisa.
    $guardar(['parcial1' => 20]);
    Mail::assertQueued(NotaPublicada::class, 1);

    // La segunda también: es una evaluación nueva.
    $guardar(['parcial1' => 20, 'parcial2' => 22.5]);
    Mail::assertQueued(NotaPublicada::class, 2);

    // Reenviar el formulario sin tocar nada no vuelve a escribirle al alumno.
    $guardar(['parcial1' => 20, 'parcial2' => 22.5]);
    Mail::assertQueued(NotaPublicada::class, 2);

    // Corregir una parcial sí vuelve a avisar, y el correo nombra cuál.
    $guardar(['parcial1' => 17.5, 'parcial2' => 22.5]);
    Mail::assertQueued(NotaPublicada::class, fn (NotaPublicada $correo) => $correo->parciales === ['Parcial 1']);

    // El navegador manda las cuatro casillas siempre, las vacías como "": eso
    // no puede contar como una parcial nueva ni disparar otro correo.
    $guardar(['parcial1' => 17.5, 'parcial2' => 22.5, 'parcial3' => '', 'parcial4' => '']);
    Mail::assertQueued(NotaPublicada::class, 3);

    // Borrar todas las parciales elimina la nota; al alumno no se le avisa de
    // una evaluación que ya no existe.
    $guardar(['parcial1' => '', 'parcial2' => '', 'parcial3' => '', 'parcial4' => '']);
    Mail::assertQueued(NotaPublicada::class, 3);

    $this->assertDatabaseMissing('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
    ]);
});

test('una parcial fuera de la escala se rechaza y no se guarda nada', function () {
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
                ['id_estudiante' => $estudiante->getKey(), 'parcial2' => 30],
            ],
        ])
        ->assertSessionHasErrors('notas.0.parcial2');

    $this->assertDatabaseCount('calificacion', 0);
});

test('el profesor ve cual parcial se salio de la escala en vez de un guardado en silencio', function () {
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
                ['id_estudiante' => $estudiante->getKey(), 'parcial2' => 30],
            ],
        ]);

    $this->actingAs($usuario)
        ->get(route('profesor.notas', $curso->getKey()))
        ->assertOk()
        ->assertSee('No se pudieron guardar las notas');
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