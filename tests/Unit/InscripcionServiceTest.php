<?php

use App\Models\Usuario;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Horario;
use App\Models\Lista_espera;
use App\Services\InscripcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

// Función auxiliar corregida para la creación de usuarios y estudiantes
function crearEstudiantePrueba(string $nombre, string $email, bool $deuda = false): Estudiante
{
    $usuario = Usuario::create([
        'nombres'   => $nombre,
        'apellidos' => 'Pérez',
        'telefono'  => '04141234567',
        'email'     => $email,
        'password'  => bcrypt('password'),
    ]);

    return Estudiante::create([
        'id_usuario'       => $usuario->getKey(),
                              'cedula'           => (string) rand(10000000, 99999999),
                              'fecha_nacimiento' => '2000-01-01',
                              'deuda'            => $deuda,
    ]);
}

test('no permite inscripcion si el estudiante tiene deuda', function () {
    $service = new InscripcionService();
    $estudiante = crearEstudiantePrueba('Carlos', 'deudor@test.com', true);

    $curso = Curso::create([
        'nombre'             => 'Matemáticas',
        'limite_estudiantes' => 30,
    ]);

    expect(fn () => $service->inscribir($estudiante, $curso))
    ->toThrow(Exception::class, 'El estudiante posee deudas pendientes y no puede inscribirse.');
});

test('no permite inscripcion por conflicto de horario en el estudiante', function () {
    $service = new InscripcionService();
    $estudiante = crearEstudiantePrueba('Maria', 'maria@test.com');

    $curso1 = Curso::create(['nombre' => 'Física I', 'limite_estudiantes' => 30]);
    Horario::create([
        'id_curso'   => $curso1->getKey(),
                    'dia_semana' => 'Lunes',
                    'hora_inicio'=> '08:00:00',
                    'hora_fin'   => '10:00:00',
    ]);

    $curso2 = Curso::create(['nombre' => 'Química I', 'limite_estudiantes' => 30]);
    Horario::create([
        'id_curso'   => $curso2->getKey(),
                    'dia_semana' => 'Lunes',
                    'hora_inicio'=> '09:00:00',
                    'hora_fin'   => '11:00:00',
    ]);

    $service->inscribir($estudiante, $curso1);

    expect(fn () => $service->inscribir($estudiante, $curso2))
    ->toThrow(Exception::class, 'Existe un conflicto de horario con otra asignatura del estudiante.');
});

test('envia a lista de espera cuando el cupo esta lleno', function () {
    $service = new InscripcionService();
    $curso = Curso::create(['nombre' => 'Programación', 'limite_estudiantes' => 1]);

    $estudiante1 = crearEstudiantePrueba('Alumno 1', 'a1@test.com');
    $service->inscribir($estudiante1, $curso);

    $estudiante2 = crearEstudiantePrueba('Alumno 2', 'a2@test.com');
    $resultadoEspera = $service->inscribir($estudiante2, $curso);

    expect($resultadoEspera)->toBeInstanceOf(Lista_espera::class);
    $this->assertDatabaseHas('lista_espera', [
        'id_estudiante' => $estudiante2->getKey(),
                             'id_curso'      => $curso->getKey(),
    ]);
});

test('promueve desde la lista de espera al desinscribir a un estudiante', function () {
    $service = new InscripcionService();
    $curso = Curso::create(['nombre' => 'Bases de Datos', 'limite_estudiantes' => 1]);

    $estudiante1 = crearEstudiantePrueba('Alumno 1', 'est1@test.com');
    $service->inscribir($estudiante1, $curso);

    $estudiante2 = crearEstudiantePrueba('Alumno 2', 'est2@test.com');
    $service->inscribir($estudiante2, $curso);

    $service->desinscribir($estudiante1, $curso);

    $this->assertDatabaseHas('inscripcion', [
        'id_estudiante' => $estudiante2->getKey(),
                             'id_curso'      => $curso->getKey(),
    ]);

    $this->assertDatabaseMissing('lista_espera', [
        'id_estudiante' => $estudiante2->getKey(),
                                 'id_curso'      => $curso->getKey(),
    ]);
});
