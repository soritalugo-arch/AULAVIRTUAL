<?php

use App\Models\Calificacion;
use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function rolEtapa(string $nombre): Rol
{
    return Rol::create(['nombre' => $nombre]);
}

function usuarioEtapa(string $rolNombre, string $email): Usuario
{
    $usuario = Usuario::factory()->create([
        'email' => $email,
        'nombres' => 'Estudiante',
        'apellidos' => 'De Matrícula',
    ]);
    $usuario->roles()->attach(rolEtapa($rolNombre));

    return $usuario;
}

function estudianteEtapa(Carrera $carrera, string $email = 'etapa@aula.edu'): array
{
    $usuario = usuarioEtapa('estudiante', $email);

    $estudiante = Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => (string) rand(10000000, 99999999),
        'fecha_nacimiento' => '2002-01-01',
        'deuda' => false,
        'id_carrera' => $carrera->id_carrera,
    ]);

    return ['usuario' => $usuario, 'estudiante' => $estudiante];
}

function materiaEtapa(Carrera $carrera, string $nombre, int $etapa): Curso
{
    $curso = Curso::create(['nombre' => $nombre, 'limite_estudiantes' => 30]);
    $carrera->cursos()->attach($curso->id_curso, ['etapa' => $etapa]);

    return $curso;
}

function cuatrimestreEtapaVigente(): Cuatrimestre
{
    return Cuatrimestre::create([
        'fecha_inicio' => now()->subDays(1)->toDateString(),
        'fecha_fin' => now()->addDays(1)->toDateString(),
    ]); // estado por defecto: matriculacion (matrícula abierta)
}

function ofrecerEnVigente(Cuatrimestre $cuatrimestre, Curso ...$cursos): void
{
    foreach ($cursos as $curso) {
        $curso->cuatrimestres()->attach($cuatrimestre);
    }
}

it('muestra en la matrícula solo las materias del cuatrimestre del plan del estudiante', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa1b = materiaEtapa($carrera, 'Ofimática', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    materiaEtapa($carrera, 'Seminario de Grado', 5); // el plan tiene 5 cuatrimestres
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa1b, $etapa2a);

    ['usuario' => $usuario] = estudianteEtapa($carrera); // sin notas: va por el 1ro

    $this->actingAs($usuario)
        ->get(route('estudiante.matriculacion'))
        ->assertOk()
        ->assertSee('1 de 5')
        ->assertSee('Matemática Básica')
        ->assertSee('Ofimática')
        ->assertDontSee('Inglés Técnico');
});

it('dos estudiantes de la misma carrera ven materias distintas si van por cuatrimestres distintos', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa1b = materiaEtapa($carrera, 'Ofimática', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    $etapa2b = materiaEtapa($carrera, 'Fundamentos de Programación', 2);
    materiaEtapa($carrera, 'Seminario de Grado', 5); // el plan tiene 5 cuatrimestres
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa1b, $etapa2a, $etapa2b);

    ['usuario' => $usuario] = estudianteEtapa($carrera, 'primer.etapa@aula.edu'); // va por el 1ro
    ['usuario' => $usuarioDos, 'estudiante' => $estudianteDos] = estudianteEtapa($carrera, 'segunda.etapa@aula.edu');

    // El segundo ya aprobó el 1er cuatrimestre del plan: pasa al 2do.
    Calificacion::create([
        'id_estudiante' => $estudianteDos->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 8,
    ]);
    Calificacion::create([
        'id_estudiante' => $estudianteDos->id_usuario,
        'id_curso' => $etapa1b->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 8,
    ]);

    $this->actingAs($usuario)
        ->get(route('estudiante.matriculacion'))
        ->assertSee('Matemática Básica')
        ->assertDontSee('Inglés Técnico');

    $this->actingAs($usuarioDos)
        ->get(route('estudiante.matriculacion'))
        ->assertSee('2 de 5')
        ->assertSee('Inglés Técnico')
        ->assertDontSee('Matemática Básica');
});

it('el servicio rechaza inscribir una materia que no es del cuatrimestre del plan actual', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa2a);

    ['estudiante' => $estudiante] = estudianteEtapa($carrera);

    expect(fn () => app(InscripcionService::class)->inscribir($estudiante, $etapa2a))
        ->toThrow(Exception::class, 'no corresponde al cuatrimestre del plan');
});

it('el servicio rechaza inscribir cuando el período no está en matrícula', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $cuatrimestre = cuatrimestreEtapaVigente();
    $cuatrimestre->update(['estado' => 'en_curso']);
    ofrecerEnVigente($cuatrimestre, $etapa1a);

    ['estudiante' => $estudiante] = estudianteEtapa($carrera);

    expect(fn () => app(InscripcionService::class)->inscribir($estudiante, $etapa1a))
        ->toThrow(Exception::class, 'matrícula para este período está cerrada');
});

it('la rectora cambia el momento del período desde su panel', function () {
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(rolEtapa('admin'));

    $cuatrimestre = Cuatrimestre::create([
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addMonths(3)->toDateString(),
    ]);

    $this->actingAs($usuario)
        ->get(route('admin.periodo'))
        ->assertOk()
        ->assertSee('matriculacion');

    $this->actingAs($usuario)
        ->post(route('admin.periodo.estado'), [
            'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
            'estado' => 'en_curso',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('cuatrimestre', [
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'estado' => 'en_curso',
    ]);
});

it('profesor no puede guardar notas mientras el período está en matrícula', function () {
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(rolEtapa('profesor'));
    $profesor = Profesor::factory()->create(['id_usuario' => $usuario->id_usuario]);

    $cuatrimestre = cuatrimestreEtapaVigente(); // matrícula abierta

    $curso = Curso::create(['nombre' => 'Materia del Profesor', 'limite_estudiantes' => 30]);
    $profesor->cursos()->attach($curso);
    $curso->cuatrimestres()->attach($cuatrimestre);

    ['estudiante' => $estudiante] = estudianteEtapa(Carrera::create(['nombre' => 'Informática', 'duracion' => 5]));
    Inscripcion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'fecha_inscripcion' => now(),
    ]);

    $this->actingAs($usuario)
        ->post(route('profesor.notas.guardar'), [
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
            'notas' => [
                ['id_estudiante' => $estudiante->id_usuario, 'nota' => 8, 'observaciones' => null],
            ],
        ])
        ->assertStatus(422);

    $this->assertDatabaseCount('calificacion', 0);
});

it('una materia raspada del 1ro abre la ventana 1-2: repite lo pendiente y adelanta el 2do', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    $etapa2b = materiaEtapa($carrera, 'Fundamentos de Programación', 2);
    materiaEtapa($carrera, 'Seminario de Grado', 5);
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa2a, $etapa2b);

    ['usuario' => $usuario, 'estudiante' => $estudiante] = estudianteEtapa($carrera, 'raspada.uno@aula.edu');
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 4, // raspó Matemática Básica del 1er cuatrimestre
    ]);

    $this->actingAs($usuario)
        ->get(route('estudiante.matriculacion'))
        ->assertSee('1-2 de 5')
        ->assertSee('Matemática Básica')
        ->assertSee('Inglés Técnico')
        ->assertSee('Fundamentos de Programación')
        ->assertDontSee('Seminario de Grado');
});

it('al raspár una materia dos veces se queda congelado en 1-2 y no ve el 3ro', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    materiaEtapa($carrera, 'Seminario de Grado', 5);
    $anterior = Cuatrimestre::create([
        'fecha_inicio' => now()->subMonths(4)->toDateString(),
        'fecha_fin' => now()->subMonths(2)->toDateString(),
    ]);
    $cuatrimestre = cuatrimestreEtapaVigente();
    $etapa1a->cuatrimestres()->attach($anterior);
    $etapa2a->cuatrimestres()->attach($anterior);
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa2a);

    ['usuario' => $usuario, 'estudiante' => $estudiante] = estudianteEtapa($carrera, 'raspada.dos@aula.edu');
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $anterior->id_cuatrimestre,
        'nota' => 4, // 1ra vez que raspó
    ]);
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 4, // 2da vez: se queda en el bucle 1-2
    ]);

    $this->actingAs($usuario)
        ->get(route('estudiante.matriculacion'))
        ->assertSee('1-2 de 5')
        ->assertSee('Matemática Básica')
        ->assertSee('Inglés Técnico')
        ->assertDontSee('Seminario de Grado');
});

it('el servicio permite repetir la materia raspada y adelantar la del siguiente cuatrimestre, no más allá', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    $etapa3a = materiaEtapa($carrera, 'Base de Datos I', 3);
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa2a, $etapa3a);

    ['estudiante' => $estudiante] = estudianteEtapa($carrera, 'servicio.raspada@aula.edu');
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 4,
    ]);

    $servicio = app(InscripcionService::class);

    expect(fn () => $servicio->inscribir($estudiante, $etapa1a))->not->toThrow(Exception::class);
    expect(fn () => $servicio->inscribir($estudiante, $etapa2a))->not->toThrow(Exception::class);
    expect(fn () => $servicio->inscribir($estudiante, $etapa3a))
        ->toThrow(Exception::class, 'no corresponde al cuatrimestre del plan');
});

it('mis datos muestran el cuadro de carrera con el formato y los conteos', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 5]);
    $etapa1a = materiaEtapa($carrera, 'Matemática Básica', 1);
    $etapa1b = materiaEtapa($carrera, 'Ofimática', 1);
    $etapa2a = materiaEtapa($carrera, 'Inglés Técnico', 2);
    materiaEtapa($carrera, 'Seminario de Grado', 5);
    $cuatrimestre = cuatrimestreEtapaVigente();
    ofrecerEnVigente($cuatrimestre, $etapa1a, $etapa1b, $etapa2a);

    ['usuario' => $usuario, 'estudiante' => $estudiante] = estudianteEtapa($carrera, 'datos.carrera@aula.edu');
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1b->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 8, // aprobada
    ]);
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $etapa1a->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 4, // reprobada
    ]);

    $this->actingAs($usuario)
        ->get(route('perfil'))
        ->assertOk()
        ->assertSee('Datos de carrera')
        ->assertSee('1-2 de 5')
        ->assertSee('Materias aprobadas')
        ->assertSee('Materias reprobadas');
});