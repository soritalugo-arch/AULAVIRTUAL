<?php

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Usuario;
use App\Services\CalificacionAsistenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function cuatrimestreVigenteDePrueba(): Cuatrimestre
{
    return Cuatrimestre::create([
        'fecha_inicio' => now()->subDays(1)->toDateString(),
        'fecha_fin' => now()->addDays(1)->toDateString(),
    ]);
}

function cuatrimestrePasadoDePrueba(): Cuatrimestre
{
    return Cuatrimestre::create([
        'fecha_inicio' => now()->subMonths(6)->toDateString(),
        'fecha_fin' => now()->subMonths(4)->toDateString(),
    ]);
}

function cursoDeLaCarreraPrueba(): Curso
{
    $curso = Curso::create(['nombre' => 'Curso de Prueba', 'limite_estudiantes' => 30]);
    $curso->cuatrimestres()->attach(cuatrimestreVigenteDePrueba());

    return $curso;
}

function estudianteNotasPrueba(int $index = 1): Estudiante
{
    $usuario = Usuario::create([
        'nombres' => "Alumno $index",
        'apellidos' => 'Prueba',
        'telefono' => '04141234567',
        'email' => "alumno-notas-$index@test.com",
        'password' => bcrypt('password'),
    ]);

    return Estudiante::create([
        'id_usuario' => $usuario->getKey(),
        'cedula' => (string) rand(10000000, 99999999),
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => false,
    ]);
}

test('reglas de estado del estudiante', function () {
    $servicio = new CalificacionAsistenciaService;

    expect($servicio->estadoEstudiante(null, 10.0))->toBe('En curso');
    expect($servicio->estadoEstudiante(null, 31.0))->toBe('Reprobado');
    expect($servicio->estadoEstudiante(6, 30.0))->toBe('Aprobado');
    expect($servicio->estadoEstudiante(5, 10.0))->toBe('Reprobado');
    expect($servicio->estadoEstudiante(6, 30.5))->toBe('Reprobado');
});

test('nivel de alerta: <25 ok, 25-30 advertencia, >30 peligro', function () {
    $servicio = new CalificacionAsistenciaService;

    expect($servicio->nivelAlerta(24.9))->toBe('ok');
    expect($servicio->nivelAlerta(25.0))->toBe('advertencia');
    expect($servicio->nivelAlerta(30.0))->toBe('advertencia');
    expect($servicio->nivelAlerta(30.1))->toBe('peligro');
});

test('el reprobado por faltas es presunto mientras el cuatrimestre esta activo', function () {
    $servicio = new CalificacionAsistenciaService;

    expect($servicio->estadoEstudiante(null, 31.0, false))->toBe('Reprobado (presunto)');
    expect($servicio->estadoEstudiante(null, 31.0, true))->toBe('Reprobado');
    expect($servicio->estadoEstudiante(8, 31.0, false))->toBe('Reprobado (presunto)');
    expect($servicio->estadoEstudiante(6, 10.0, false))->toBe('Aprobado');
});

test('es dia de clase segun el horario del curso', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();

    Horario::create([
        'id_curso' => $curso->getKey(),
        'dia_semana' => 'Lunes',
        'hora_inicio' => '18:00:00',
        'hora_fin' => '20:00:00',
    ]);

    expect($servicio->esDiaDeClase($curso, Carbon::parse('2026-03-09')))->toBeTrue();
    expect($servicio->esDiaDeClase($curso, Carbon::parse('2026-03-10')))->toBeFalse();
});

test('curso sin horario acepta cualquier fecha para asistencia', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();

    expect($servicio->esDiaDeClase($curso, Carbon::parse('2026-03-10')))->toBeTrue();
});

test('cuenta clases dictadas por fechas distintas', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasPrueba();

    foreach (['2026-03-02', '2026-03-02', '2026-03-09', '2026-03-16'] as $fecha) {
        Asistencia::create([
            'id_estudiante' => $estudiante->getKey(),
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => $fecha,
            'presente' => true,
        ]);
    }

    expect($servicio->clasesDictadas($curso->getKey(), $cuatrimestre->getKey()))->toBe(3);
});

test('porcentaje de inasistencia: faltas sobre clases dictadas', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasPrueba();

    $fechas = collect(range(1, 12))
        ->map(fn (int $dia) => \Illuminate\Support\Carbon::create(2026, 3, $dia)->toDateString());

    foreach ($fechas as $fecha) {
        Asistencia::create([
            'id_estudiante' => $estudiante->getKey(),
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'fecha' => $fecha,
            'presente' => true,
        ]);
    }
    foreach ($fechas->take(5) as $fecha) {
        Asistencia::where('id_estudiante', $estudiante->getKey())
            ->where('fecha', $fecha)
            ->update(['presente' => false]);
    }

    expect($servicio->faltasEstudiante($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey()))->toBe(5);
    expect($servicio->porcentajeInasistencia($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey()))->toBe(41.7);
});

test('porcentaje de inasistencia sin clases registradas es 0', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $estudiante = estudianteNotasPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();

    expect($servicio->porcentajeInasistencia($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey()))->toBe(0.0);
});

test('guardar nota es idempotente sobre la misma combinacion', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasPrueba();

    $servicio->guardarNota($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey(), 7, null);
    $servicio->guardarNota($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey(), 9, 'mejoró');

    $this->assertDatabaseCount('calificacion', 1);
    $this->assertDatabaseHas('calificacion', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'nota' => 9,
        'observaciones' => 'mejoró',
    ]);
});

test('quitar nota elimina la fila existente', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasPrueba();

    $servicio->guardarNota($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey(), 7, null);
    $servicio->quitarNota($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey());

    $this->assertDatabaseCount('calificacion', 0);
});

test('registrar asistencia es idempotente sobre la misma fecha', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $estudiante = estudianteNotasPrueba();

    $servicio->registrarAsistencia($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey(), '2026-03-02', true);
    $servicio->registrarAsistencia($estudiante->getKey(), $curso->getKey(), $cuatrimestre->getKey(), '2026-03-02', false);

    $this->assertDatabaseCount('asistencia', 1);
    $this->assertDatabaseHas('asistencia', [
        'id_estudiante' => $estudiante->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $cuatrimestre->getKey(),
        'fecha' => '2026-03-02',
        'presente' => false,
    ]);
});

test('promedio por curso usa solo las notas del curso y cuatrimestre', function () {
    $servicio = new CalificacionAsistenciaService;
    $curso = cursoDeLaCarreraPrueba();
    $cuatrimestre = $curso->cuatrimestres()->first();
    $pasado = cuatrimestrePasadoDePrueba();
    $curso->cuatrimestres()->attach($pasado);

    foreach ([6, 7, 8] as $nota) {
        Calificacion::create([
            'id_estudiante' => estudianteNotasPrueba($nota)->getKey(),
            'id_curso' => $curso->getKey(),
            'id_cuatrimestre' => $cuatrimestre->getKey(),
            'nota' => $nota,
        ]);
    }
    Calificacion::create([
        'id_estudiante' => estudianteNotasPrueba(10)->getKey(),
        'id_curso' => $curso->getKey(),
        'id_cuatrimestre' => $pasado->getKey(),
        'nota' => 1,
    ]);

    expect($servicio->promedioPorCurso($curso->getKey(), $cuatrimestre->getKey()))->toBe(7.0);
    expect($servicio->promedioPorCurso($curso->getKey(), $pasado->getKey()))->toBe(1.0);
    expect($servicio->promedioPorCurso($curso->getKey(), 999))->toBeNull();
});

test('cuatrimestre vigente por rango de fechas', function () {
    $servicio = new CalificacionAsistenciaService;
    expect($servicio->cuatrimestreVigente())->toBeNull();

    $vigente = cuatrimestreVigenteDePrueba();
    expect($servicio->cuatrimestreVigente()?->getKey())->toBe($vigente->getKey());

    $vigente->delete();
    cuatrimestrePasadoDePrueba();
    expect($servicio->cuatrimestreVigente())->toBeNull();
});