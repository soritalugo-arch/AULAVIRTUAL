<?php

use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Lista_espera;
use App\Models\Usuario;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function crearCarreraPrueba(): Carrera
{
    return Carrera::create([
        'nombre' => 'Ingeniería de Prueba',
        'duracion' => 8,
    ]);
}

function cuatrimestreVigentePrueba(): Cuatrimestre
{
    return Cuatrimestre::where('fecha_inicio', '<=', now())
        ->where('fecha_fin', '>=', now())
        ->first() ?? Cuatrimestre::create([
            'fecha_inicio' => now()->subDays(1)->toDateString(),
            'fecha_fin' => now()->addDays(1)->toDateString(),
        ]);
}

function enOfertaDe(Curso $curso, Carrera $carrera): Curso
{
    $curso->carreras()->attach($carrera);
    $curso->cuatrimestres()->attach(cuatrimestreVigentePrueba());

    return $curso;
}

// Función auxiliar para la creación de usuarios y estudiantes
function crearEstudiantePrueba(string $nombre, string $email, bool $deuda = false, ?Carrera $carrera = null): Estudiante
{
    $usuario = Usuario::create([
        'nombres' => $nombre,
        'apellidos' => 'Pérez',
        'telefono' => '04141234567',
        'email' => $email,
        'password' => bcrypt('password'),
    ]);

    return Estudiante::create([
        'id_usuario' => $usuario->getKey(),
        'cedula' => (string) rand(10000000, 99999999),
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => $deuda,
        'id_carrera' => $carrera?->id_carrera,
    ]);
}

test('no permite inscripcion si el estudiante tiene deuda', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $estudiante = crearEstudiantePrueba('Carlos', 'deudor@test.com', true, $carrera);

    $curso = enOfertaDe(Curso::create([
        'nombre' => 'Matemáticas',
        'limite_estudiantes' => 30,
    ]), $carrera);

    expect(fn () => $service->inscribir($estudiante, $curso))
        ->toThrow(Exception::class, 'El estudiante posee deudas pendientes y no puede inscribirse.');
});

test('no permite inscripcion por conflicto de horario en el estudiante', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $estudiante = crearEstudiantePrueba('Maria', 'maria@test.com', false, $carrera);

    $curso1 = enOfertaDe(Curso::create(['nombre' => 'Física I', 'limite_estudiantes' => 30]), $carrera);
    Horario::create([
        'id_curso' => $curso1->getKey(),
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00:00',
        'hora_fin' => '10:00:00',
    ]);

    $curso2 = enOfertaDe(Curso::create(['nombre' => 'Química I', 'limite_estudiantes' => 30]), $carrera);
    Horario::create([
        'id_curso' => $curso2->getKey(),
        'dia_semana' => 'Lunes',
        'hora_inicio' => '09:00:00',
        'hora_fin' => '11:00:00',
    ]);

    $service->inscribir($estudiante, $curso1);

    expect(fn () => $service->inscribir($estudiante, $curso2))
        ->toThrow(Exception::class, 'Existe un conflicto de horario con otra asignatura del estudiante.');
});

test('envia a lista de espera cuando el cupo esta lleno', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $curso = enOfertaDe(Curso::create(['nombre' => 'Programación', 'limite_estudiantes' => 1]), $carrera);

    $estudiante1 = crearEstudiantePrueba('Alumno 1', 'a1@test.com', false, $carrera);
    $service->inscribir($estudiante1, $curso);

    $estudiante2 = crearEstudiantePrueba('Alumno 2', 'a2@test.com', false, $carrera);
    $resultadoEspera = $service->inscribir($estudiante2, $curso);

    expect($resultadoEspera)->toBeInstanceOf(Lista_espera::class);
    $this->assertDatabaseHas('lista_espera', [
        'id_estudiante' => $estudiante2->getKey(),
        'id_curso' => $curso->getKey(),
    ]);
});

test('promueve desde la lista de espera al desinscribir a un estudiante', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $curso = enOfertaDe(Curso::create(['nombre' => 'Bases de Datos', 'limite_estudiantes' => 1]), $carrera);

    $estudiante1 = crearEstudiantePrueba('Alumno 1', 'est1@test.com', false, $carrera);
    $service->inscribir($estudiante1, $curso);

    $estudiante2 = crearEstudiantePrueba('Alumno 2', 'est2@test.com', false, $carrera);
    $service->inscribir($estudiante2, $curso);

    $service->desinscribir($estudiante1, $curso);

    $this->assertDatabaseHas('inscripcion', [
        'id_estudiante' => $estudiante2->getKey(),
        'id_curso' => $curso->getKey(),
    ]);

    $this->assertDatabaseMissing('lista_espera', [
        'id_estudiante' => $estudiante2->getKey(),
        'id_curso' => $curso->getKey(),
    ]);
});

test('no permite inscribirse dos veces en el mismo curso', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $estudiante = crearEstudiantePrueba('Ana', 'ana@test.com', false, $carrera);

    $curso = enOfertaDe(Curso::create([
        'nombre' => 'Historia',
        'limite_estudiantes' => 30,
    ]), $carrera);

    $service->inscribir($estudiante, $curso);

    expect(fn () => $service->inscribir($estudiante, $curso))
        ->toThrow(Exception::class, 'El estudiante ya está inscrito en esta asignatura.');
});

test('no permite entrar dos veces a la lista de espera del mismo curso', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $curso = enOfertaDe(Curso::create(['nombre' => 'Curso Lleno', 'limite_estudiantes' => 1]), $carrera);

    $titular = crearEstudiantePrueba('Titular', 'titular@test.com', false, $carrera);
    $service->inscribir($titular, $curso);

    $enEspera = crearEstudiantePrueba('Espera', 'espera@test.com', false, $carrera);
    $service->inscribir($enEspera, $curso);

    // Segundo clic en "Matricular": no debe crear otra fila en la lista
    expect(fn () => $service->inscribir($enEspera, $curso))
        ->toThrow(Exception::class, 'El estudiante ya está en la lista de espera de esta asignatura.');

    $this->assertDatabaseCount('lista_espera', 1);
});

test('no considera conflicto cuando una clase termina y otra empieza a la misma hora', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $estudiante = crearEstudiantePrueba('Luis', 'luis@test.com', false, $carrera);

    $curso1 = enOfertaDe(Curso::create(['nombre' => 'Matemática I', 'limite_estudiantes' => 30]), $carrera);
    Horario::create([
        'id_curso' => $curso1->getKey(),
        'dia_semana' => 'Lunes',
        'hora_inicio' => '08:00:00',
        'hora_fin' => '10:00:00',
    ]);

    $curso2 = enOfertaDe(Curso::create(['nombre' => 'Matemática II', 'limite_estudiantes' => 30]), $carrera);
    Horario::create([
        'id_curso' => $curso2->getKey(),
        'dia_semana' => 'Lunes',
        'hora_inicio' => '10:00:00',
        'hora_fin' => '12:00:00',
    ]);

    $service->inscribir($estudiante, $curso1);

    $inscripcion = $service->inscribir($estudiante, $curso2);

    $this->assertNotNull($inscripcion);
    $this->assertDatabaseHas('inscripcion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso2->getKey(),
    ]);
});

test('no permite inscribirse en una asignatura de otra carrera', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $otraCarrera = Carrera::create(['nombre' => 'Diseño Gráfico', 'duracion' => 8]);

    $estudiante = crearEstudiantePrueba('Ana', 'otra-carrera@test.com', false, $carrera);
    $cursoAjeno = enOfertaDe(Curso::create(['nombre' => 'Diseño', 'limite_estudiantes' => 30]), $otraCarrera);

    expect(fn () => $service->inscribir($estudiante, $cursoAjeno))
        ->toThrow(Exception::class, 'La asignatura no pertenece a la carrera del estudiante.');
});

test('no permite inscribirse en una asignatura fuera del cuatrimestre vigente', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $estudiante = crearEstudiantePrueba('Pedro', 'finalizado@test.com', false, $carrera);

    $cuatrimestrePasado = Cuatrimestre::create([
        'fecha_inicio' => now()->subMonths(6)->toDateString(),
        'fecha_fin' => now()->subMonths(4)->toDateString(),
    ]);

    $cursoPasado = Curso::create(['nombre' => 'Ya Dictado', 'limite_estudiantes' => 30]);
    $cursoPasado->carreras()->attach($carrera);
    $cursoPasado->cuatrimestres()->attach($cuatrimestrePasado);

    expect(fn () => $service->inscribir($estudiante, $cursoPasado))
        ->toThrow(Exception::class, 'La asignatura no está disponible en el cuatrimestre vigente.');
});

test('promocion salta al de la lista de espera con conflicto de horario y promueve al siguiente', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();

    // Curso con cupo 1 y horario los miércoles
    $curso = enOfertaDe(Curso::create(['nombre' => 'SQL Avanzado', 'limite_estudiantes' => 1]), $carrera);
    Horario::create([
        'id_curso' => $curso->getKey(),
        'dia_semana' => 'Miercoles',
        'hora_inicio' => '08:00:00',
        'hora_fin' => '10:00:00',
    ]);

    $titular = crearEstudiantePrueba('Titular', 'titular2@test.com', false, $carrera);
    $service->inscribir($titular, $curso);

    $enEspera = crearEstudiantePrueba('Espera Conflicto', 'espera_conflicto@test.com', false, $carrera);
    $otro = crearEstudiantePrueba('Otro Alumno', 'otro@test.com', false, $carrera);

    $service->inscribir($enEspera, $curso); // 1º en lista
    $service->inscribir($otro, $curso);     // 2º en lista

    // El primero en espera se inscribe en un curso que choca con SQL Avanzado
    $cursoChoque = enOfertaDe(Curso::create(['nombre' => 'Física Avanzada', 'limite_estudiantes' => 30]), $carrera);
    Horario::create([
        'id_curso' => $cursoChoque->getKey(),
        'dia_semana' => 'Miercoles',
        'hora_inicio' => '09:00:00',
        'hora_fin' => '11:00:00',
    ]);
    $service->inscribir($enEspera, $cursoChoque);

    // Al liberarse el cupo, se salta al que choca y entra el segundo
    $service->desinscribir($titular, $curso);

    $this->assertDatabaseHas('inscripcion', [
        'id_estudiante' => $otro->getKey(),
        'id_curso' => $curso->getKey(),
    ]);

    $this->assertDatabaseHas('lista_espera', [
        'id_estudiante' => $enEspera->getKey(),
        'id_curso' => $curso->getKey(),
    ]);
});

test('promocion conserva en la lista a quien quedo con deuda y promueve al siguiente', function () {
    $service = new InscripcionService;
    $carrera = crearCarreraPrueba();
    $curso = enOfertaDe(Curso::create(['nombre' => 'Contabilidad', 'limite_estudiantes' => 1]), $carrera);

    $titular = crearEstudiantePrueba('Titular', 'titular3@test.com', false, $carrera);
    $service->inscribir($titular, $curso);

    $deudor = crearEstudiantePrueba('Deudor Posterior', 'deudor_post@test.com', false, $carrera);
    $libre = crearEstudiantePrueba('Alumno Libre', 'libre@test.com', false, $carrera);

    $service->inscribir($deudor, $curso); // 1º en lista (aún sin deuda)
    $service->inscribir($libre, $curso);  // 2º en lista

    // El primero de la lista adquiere deuda después de anotarse
    Estudiante::whereKey($deudor->getKey())->update(['deuda' => true]);
    $deudor->refresh();

    $service->desinscribir($titular, $curso);

    $this->assertDatabaseHas('inscripcion', [
        'id_estudiante' => $libre->getKey(),
        'id_curso' => $curso->getKey(),
    ]);

    $this->assertDatabaseHas('lista_espera', [
        'id_estudiante' => $deudor->getKey(),
        'id_curso' => $curso->getKey(),
    ]);
});
