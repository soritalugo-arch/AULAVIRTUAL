<?php

use App\Models\Carrera;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function planRol(string $nombre): Rol
{
    return Rol::create(['nombre' => $nombre]);
}

function usuarioPlan(string $rolNombre, string $email, string $nombre = 'Plan Estudiante'): Usuario
{
    $usuario = Usuario::factory()->create([
        'email' => $email,
        'nombres' => $nombre,
        'apellidos' => 'De Prueba',
    ]);
    $usuario->roles()->attach(planRol($rolNombre));

    return $usuario;
}

function carreraPlan(string $nombre = 'Informática', int $duracion = 3): Carrera
{
    return Carrera::create(['nombre' => $nombre, 'duracion' => $duracion]);
}

function estudiantePlan(Carrera $carrera): Estudiante
{
    $usuario = usuarioPlan('estudiante', 'plan.estudiante@aula.edu');

    return Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => '22000001',
        'fecha_nacimiento' => '2002-01-01',
        'deuda' => false,
        'id_carrera' => $carrera->id_carrera,
    ]);
}

function materiaPlan(Carrera $carrera, string $nombre, int $etapa): Curso
{
    $curso = Curso::create(['nombre' => $nombre, 'limite_estudiantes' => 30]);
    $carrera->cursos()->attach($curso->id_curso, ['etapa' => $etapa]);

    return $curso;
}

function cuatrimestrePlan(string $inicio, string $fin): Cuatrimestre
{
    return Cuatrimestre::create(['fecha_inicio' => $inicio, 'fecha_fin' => $fin]);
}

function programarMateria(Curso $curso, Cuatrimestre $cuatrimestre): void
{
    DB::table('curso_cuatrimestre')->insert([
        'curso_id' => $curso->id_curso,
        'cuatrimestre_id' => $cuatrimestre->id_cuatrimestre,
        'total_clases' => 10,
    ]);
}

function matricularPlan(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre): void
{
    Inscripcion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'fecha_inscripcion' => $cuatrimestre->fecha_inicio->format('Y-m-d'),
    ]);
}

function aprobarMateria(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre): void
{
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => 8,
        'observaciones' => 'Muy buen desempeño en la materia.',
    ]);
}

it('redirige al login el plan de estudios sin sesion', function () {
    $this->get(route('estudiante.plan'))->assertRedirect(route('login'));
});

it('muestra un aviso si el estudiante aun no tiene carrera', function () {
    $usuario = usuarioPlan('estudiante', 'plan.sin-carrera@aula.edu');
    Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => '22000002',
        'fecha_nacimiento' => '2002-02-02',
        'deuda' => false,
        'id_carrera' => null,
    ]);

    $this->actingAs($usuario)
        ->get(route('estudiante.plan'))
        ->assertOk()
        ->assertSee('Todavía no tienes una carrera asignada');
});

it('muestra el plan del estudiante agrupado por cuatrimestre del plan', function () {
    $carrera = carreraPlan('Informática', 3);
    $estudiante = estudiantePlan($carrera);

    $m1 = materiaPlan($carrera, 'Matemática Básica', 1);
    $m2 = materiaPlan($carrera, 'Ofimática', 1);
    $m3 = materiaPlan($carrera, 'Base de Datos I', 2);

    $this->actingAs($estudiante->usuario)
        ->get(route('estudiante.plan'))
        ->assertOk()
        ->assertSee('Plan de estudios')
        ->assertSee('Informática')
        ->assertSee('Matemática Básica')
        ->assertSee('Ofimática')
        ->assertSee('Base de Datos I')
        ->assertSee('0 de 3 materias aprobadas')
        ->assertSee('Pendiente');
});

it('distingue la materia aprobada, la que esta en curso y la pendiente', function () {
    $carrera = carreraPlan('Informática', 3);
    $estudiante = estudiantePlan($carrera);

    $cerrado = cuatrimestrePlan(now()->subYear()->startOfQuarter(), now()->subYear()->endOfQuarter());
    $vigente = cuatrimestrePlan(now()->subMonth(), now()->addMonth());

    $aprobada = materiaPlan($carrera, 'Matemática Básica', 1);
    $enCurso = materiaPlan($carrera, 'Inglés Técnico', 1);
    $pendiente = materiaPlan($carrera, 'Programación II', 2);

    // Aprobada: nota en un cuatrimestre ya cerrado.
    programarMateria($aprobada, $cerrado);
    matricularPlan($estudiante, $aprobada, $cerrado);
    aprobarMateria($estudiante, $aprobada, $cerrado);

    // En curso: matricula sin nota en el cuatrimestre vigente.
    programarMateria($enCurso, $vigente);
    matricularPlan($estudiante, $enCurso, $vigente);

    $this->actingAs($estudiante->usuario)
        ->get(route('estudiante.plan'))
        ->assertOk()
        ->assertSee('Matemática Básica')
        ->assertSee('Aprobada')
        ->assertSee('Inglés Técnico')
        ->assertSee('En curso')
        ->assertSee('Programación II')
        ->assertSee('Pendiente')
        // Despues de aprobar 1 de 3, el avance lo dice.
        ->assertSee('1 de 3 materias aprobadas');
});

it('redirige al login el plan de la rectora sin sesion', function () {
    $this->get(route('admin.plan'))->assertRedirect(route('login'));
});

it('la rectora ve el plan de la carrera que elige', function () {
    $admin = usuarioPlan('admin', 'plan.sadmin@aula.edu');

    $cuatrimestre = cuatrimestrePlan(now()->subMonth(), now()->addMonth());

    $carrera = carreraPlan('Turismo', 2);
    materiaPlan($carrera, 'Inglés Técnico', 1);
    materiaPlan($carrera, 'Ecoturismo', 2);

    $otra = carreraPlan('Enfermería', 2);
    materiaPlan($otra, 'Anatomía y Fisiología', 1);

    $this->actingAs($admin)
        ->get(route('admin.plan', ['carrera' => $carrera->id_carrera]))
        ->assertOk()
        ->assertSee('Turismo')
        ->assertSee('Inglés Técnico')
        ->assertSee('Ecoturismo')
        ->assertDontSee('Anatomía y Fisiología')
        ->assertSee('Cuatrimestre del plan');
});