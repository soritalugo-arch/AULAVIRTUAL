<?php

use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearPerfilRol(string $nombreRol): Usuario
{
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(Rol::create(['nombre' => $nombreRol]));

    return $usuario;
}

it('redirige al login al abrir Mis datos sin sesion', function () {
    $this->get(route('perfil'))->assertRedirect(route('login'));
});

it('muestra los datos basicos de cualquier rol conectado', function () {
    $usuario = crearPerfilRol('admin');

    $this->actingAs($usuario)
        ->get(route('perfil'))
        ->assertOk()
        ->assertSee($usuario->nombres)
        ->assertSee($usuario->email);
});

it('muestra la carrera, la cedula y el estado del estudiante', function () {
    $carrera = Carrera::create(['nombre' => 'Informática', 'duracion' => 6]);
    $usuario = crearPerfilRol('estudiante');

    Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => '77000001',
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => true,
        'id_carrera' => $carrera->id_carrera,
    ]);

    $this->actingAs($usuario)
        ->get(route('perfil'))
        ->assertOk()
        ->assertSee('Informática')
        ->assertSee('77000001')
        ->assertSee('Con deuda')
        ->assertSee('Mi situación académica');
});

it('muestra los cursos que dicta el profesor', function () {
    $usuario = crearPerfilRol('profesor');
    $profesor = Profesor::create(['id_usuario' => $usuario->id_usuario]);

    $curso = Curso::create(['nombre' => 'Programación II', 'limite_estudiantes' => 25]);
    $profesor->cursos()->attach($curso);

    $this->actingAs($usuario)
        ->get(route('perfil'))
        ->assertOk()
        ->assertSee('Mis cursos')
        ->assertSee('Programación II');
});