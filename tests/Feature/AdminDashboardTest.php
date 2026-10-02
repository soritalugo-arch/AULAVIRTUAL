<?php

use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearAdmin(): Usuario
{
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(Rol::create(['nombre' => 'admin']));

    return $usuario;
}

// curso con total_clas es programado (columna nullable: null no suma)
function cursoConClases(Cuatrimestre $cuatrimestre, ?int $totalClases): Curso
{
    $curso = Curso::create(['nombre' => "Curso $totalClases", 'limite_estudiantes' => 30]);
    $curso->cuatrimestres()->attach($cuatrimestre, ['total_clases' => $totalClases]);

    return $curso;
}

it('muestra la suma de clases programadas de todos los cuatrimestres por defecto', function () {
    $admin = crearAdmin();
    $c1 = Cuatrimestre::create(['fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-04-30']);
    $c2 = Cuatrimestre::create(['fecha_inicio' => '2026-05-04', 'fecha_fin' => '2026-08-31']);

    cursoConClases($c1, 10);
    cursoConClases($c1, 8);
    cursoConClases($c2, 12);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('clases programadas')
        ->assertSee('30', false); // 10 + 8 + 12
});

it('filtra el total de clases por el cuatrimestre recibido', function () {
    $admin = crearAdmin();
    $c1 = Cuatrimestre::create(['fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-04-30']);
    $c2 = Cuatrimestre::create(['fecha_inicio' => '2026-05-04', 'fecha_fin' => '2026-08-31']);

    cursoConClases($c1, 10);
    cursoConClases($c1, 8);
    cursoConClases($c2, 12);

    $this->actingAs($admin)->get(route('admin.dashboard', ['cuatrimestre' => $c2->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('12', false); // solo el cuatrimestre 2
});

it('ignora los cursos sin total de clases definido', function () {
    $admin = crearAdmin();
    $c1 = Cuatrimestre::create(['fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-04-30']);

    cursoConClases($c1, 6);
    cursoConClases($c1, null);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('6', false);
});

it('responde 404 con un cuatrimestre inexistente', function () {
    $admin = crearAdmin();

    $this->actingAs($admin)->get(route('admin.dashboard', ['cuatrimestre' => 999]))
        ->assertNotFound();
});

it('expone el total de clases a la vista', function () {
    $admin = crearAdmin();
    $c1 = Cuatrimestre::create(['fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-04-30']);
    cursoConClases($c1, 7);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('totalClases', 7);
});

it('requiere autenticacion y rol de admin', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $visitante = Usuario::factory()->create();
    $this->actingAs($visitante)->get(route('admin.dashboard'))->assertForbidden();
});

it('muestra en su seccion los estudiantes con deuda', function () {
    $admin = crearAdmin();
    $c = Cuatrimestre::create(['fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-04-30']);

    Estudiante::create([
        'id_usuario' => Usuario::factory()->create()->id_usuario,
        'cedula' => '99000001',
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.deudas', ['cuatrimestre' => $c->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('Estudiantes con deuda')
        ->assertSee('99000001');

    // Un estudiante sin deuda no aparece en la lista.
    Estudiante::create([
        'id_usuario' => Usuario::factory()->create()->id_usuario,
        'cedula' => '99000002',
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.deudas', ['cuatrimestre' => $c->id_cuatrimestre]))
        ->assertOk()
        ->assertDontSee('99000002');
});
