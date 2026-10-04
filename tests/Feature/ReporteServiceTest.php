<?php

use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function adminPanel(): Usuario
{
    $usuario = Usuario::factory()->create();
    $usuario->roles()->attach(Rol::create(['nombre' => 'admin']));

    return $usuario;
}

function periodoPanel(string $inicio, string $fin): Cuatrimestre
{
    return Cuatrimestre::create(['fecha_inicio' => $inicio, 'fecha_fin' => $fin]);
}

function carreraPanel(string $nombre): Carrera
{
    return Carrera::create(['nombre' => $nombre, 'duracion' => 8]);
}

/**
 * Estudiante con carrera: la factory no la asigna y el panel agrupa por ella.
 */
function alumnoPanel(Carrera $carrera, string $cedula = '11111111'): Estudiante
{
    $usuario = Usuario::factory()->create();

    return Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => $cedula,
        'fecha_nacimiento' => '2000-01-01',
        'deuda' => false,
        'id_carrera' => $carrera->id_carrera,
    ]);
}

function cursoPanel(Cuatrimestre $cuatrimestre, int $cupo = 30, ?int $clases = 10, ?string $nombre = null): Curso
{
    $curso = Curso::create([
        'nombre' => $nombre ?? 'Curso '.uniqid(),
        'limite_estudiantes' => $cupo,
    ]);

    $curso->cuatrimestres()->attach($cuatrimestre, ['total_clases' => $clases]);

    return $curso;
}

// ---------------------------------------------------------------- KPIs

it('calcula la tasa de aprobacion sobre el total de notas', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);

    $alumno = alumnoPanel(carreraPanel('Informatica'), '20000001');

    // 3 aprobadas + 1 reprobada = 75%
    foreach ([[8], [9], [10], [4]] as $nota) {
        Calificacion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => $nota[0],
        ]);
    }

    $kpis = app(ReporteService::class)->panel($q->id_cuatrimestre)['kpis'];

    expect($kpis['totalCalificaciones'])->toBe(4)
        ->and($kpis['aprobadas'])->toBe(3)
        ->and($kpis['reprobadas'])->toBe(1)
        ->and($kpis['tasaAprobacion'])->toBe(75.0);
});

it('devuelve null en los porcentajes cuando el periodo no tiene registros', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $kpis = app(ReporteService::class)->panel($q->id_cuatrimestre)['kpis'];

    // null, no 0: la vista distingue "sin datos" de "cero aprobado"
    expect($kpis['tasaAprobacion'])->toBeNull()
        ->and($kpis['pctAsistencia'])->toBeNull()
        ->and($kpis['inasistencia'])->toBeNull()
        ->and($kpis['promedio'])->toBeNull();
});

// ------------------------------------------------- Estudiantes por periodo

it('cuenta solo los estudiantes con movimiento en el cuatrimestre elegido', function () {
    $admin = adminPanel();
    $q1 = periodoPanel('2026-01-05', '2026-04-30');
    $q2 = periodoPanel('2026-05-04', '2026-08-31');

    $carrera = carreraPanel('Turismo');
    $curso1 = cursoPanel($q1);
    $curso2 = cursoPanel($q2);

    $enQ1 = alumnoPanel($carrera, '10000001');
    $enQ2 = alumnoPanel($carrera, '10000002');
    $sinNada = alumnoPanel($carrera, '10000003');

    foreach ([[$enQ1, $q1, $curso1], [$enQ2, $q2, $curso2]] as [$alumno, $periodo, $curso]) {
        Inscripcion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $periodo->id_cuatrimestre,
            'fecha_inscripcion' => $periodo->fecha_inicio->copy()->subDay()->toDateString(),
        ]);
    }

    $servicio = app(ReporteService::class);

    // q1 solo ve al alumno matriculado ahi, no los 3 de la carrera
    expect($servicio->panel($q1->id_cuatrimestre)['kpis']['estudiantes'])->toBe(1)
        ->and($servicio->panel($q2->id_cuatrimestre)['kpis']['estudiantes'])->toBe(1)
        // sin filtro son los 3 de la institucion
        ->and($servicio->panel(null)['kpis']['estudiantes'])->toBe(3);
});

it('expone por separado el total de la institucion y el del periodo', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);

    $carrera = carreraPanel('Enfermeria');
    $matriculado = alumnoPanel($carrera, '11000001');
    alumnoPanel($carrera, '11000002');

    Calificacion::create([
        'id_estudiante' => $matriculado->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 8,
    ]);

    $kpis = app(ReporteService::class)->panel($q->id_cuatrimestre)['kpis'];

    expect($kpis['estudiantes'])->toBe(1)
        ->and($kpis['estudiantesTotales'])->toBe(2);
});

it('reporta cero estudiantes en un cuatrimestre que aun no empieza', function () {
    $admin = adminPanel();
    $futuro = periodoPanel('2027-01-11', '2027-04-30');
    $curso = cursoPanel($futuro);

    // hay estudiantes en la institucion, pero ninguno todavia en ese periodo
    alumnoPanel(carreraPanel('Informatica'), '12000001');

    $kpis = app(ReporteService::class)->panel($futuro->id_cuatrimestre)['kpis'];

    expect($kpis['estudiantes'])->toBe(0)
        ->and($kpis['cursosOferta'])->toBe(1);
});

// ------------------------------------------------------------- Indicadores

it('calcula el promedio de notas del periodo', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);
    $carrera = carreraPanel('Contaduria');

    foreach ([10, 8, 6] as $i => $nota) {
        $alumno = alumnoPanel($carrera, '1300000'.$i);
        Calificacion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => $nota,
        ]);
    }

    expect(app(ReporteService::class)->panel($q->id_cuatrimestre)['kpis']['promedio'])->toBe(8.0);
});

// ------------------------------------------- Inscripción por curso: cupos

it('compara lo ocupado contra el cupo de cada curso', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Turismo');
    $curso = cursoPanel($q, cupo: 30);
    $alumno = alumnoPanel($carrera, '14000001');

    foreach (range(1, 12) as $i) {
        Inscripcion::create([
            'id_estudiante' => alumnoPanel($carrera, '1400'.str_pad((string) $i, 4, '0', STR_PAD_LEFT))->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha_inscripcion' => '2026-01-04',
        ]);
    }

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['inscripcionPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['inscritos'])->toBe(12)
        ->and($fila['cupo'])->toBe(30)
        ->and($fila['libres'])->toBe(18)
        ->and($fila['ocupacion'])->toBe(40.0);
});

it('no deja el cupo libre en negativo cuando el curso se desborda', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Contaduria');
    $curso = cursoPanel($q, cupo: 2);

    // Tres inscritos en un curso de dos Cupos: la matricula lo permitio.
    foreach (range(1, 3) as $i) {
        Inscripcion::create([
            'id_estudiante' => alumnoPanel($carrera, '1450'.str_pad((string) $i, 4, '0', STR_PAD_LEFT))->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha_inscripcion' => '2026-01-04',
        ]);
    }

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['inscripcionPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['inscritos'])->toBe(3)
        ->and($fila['libres'])->toBe(0)
        ->and($fila['ocupacion'])->toBe(150.0);
});

it('acota los inscritos al cuatrimestre y no suma los demas periodos', function () {
    $admin = adminPanel();
    $q1 = periodoPanel('2026-01-05', '2026-04-30');
    $q2 = periodoPanel('2026-08-03', '2026-11-27');

    $carrera = carreraPanel('Informatica');

    // El mismo curso se ofrece en los dos periodos, con el mismo nombre.
    $curso = cursoPanel($q1, cupo: 50);
    $curso->cuatrimestres()->attach($q2, ['total_clases' => 10]);

    foreach (['14510001', '14510002'] as $cedula) {
        Inscripcion::create([
            'id_estudiante' => alumnoPanel($carrera, $cedula)->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q1->id_cuatrimestre,
            'fecha_inscripcion' => '2026-01-04',
        ]);
    }

    foreach (['14510003', '14510004', '14510005'] as $cedula) {
        Inscripcion::create([
            'id_estudiante' => alumnoPanel($carrera, $cedula)->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q2->id_cuatrimestre,
            'fecha_inscripcion' => '2026-08-02',
        ]);
    }

    $servicio = app(ReporteService::class);

    $enQ1 = collect($servicio->panel($q1->id_cuatrimestre)['inscripcionPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    $enQ2 = collect($servicio->panel($q2->id_cuatrimestre)['inscripcionPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    // Dos inscritos en el primero, tres en el segundo: no se suman.
    expect($enQ1['inscritos'])->toBe(2)
        ->and($enQ2['inscritos'])->toBe(3);
});

it('resuelve el conteo de inscritos sin una consulta por curso', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Turismo');

    // 12 cursos: con el N+1 clasico serian 1 + 12 consultas.
    for ($i = 0; $i < 12; $i++) {
        cursoPanel($q, cupo: 20);
    }

    $conteo = 0;
    DB::listen(function () use (&$conteo) {
        $conteo++;
    });

    $filas = app(ReporteService::class)->inscripcionPorCurso($q->id_cuatrimestre);

    expect($filas)->toHaveCount(12)
        // whereHas (1) + withCount (1) + select (1)
        ->and($conteo)->toBeLessThanOrEqual(3);
});

// ------------------------------------- Rendimiento por estudiante

it('lista el rendimiento por estudiante con el nombre y la carrera', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Electronica');
    $curso = cursoPanel($q, clases: 10);

    $alumno = alumnoPanel($carrera, '16000001');

    foreach ([8, 9, 7] as $nota) {
        Calificacion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => $nota,
        ]);
    }

    Calificacion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 4,
    ]);

    $fila = collect(app(ReporteService::class)->rendimientoPorEstudiante($q->id_cuatrimestre))
        ->firstWhere('carrera', 'Electronica');

    expect($fila['promedio'])->toBe(7.0)          // (8+9+7+4)/4
        ->and($fila['aprobadas'])->toBe(3)
        ->and($fila['reprobadas'])->toBe(1)
        ->and($fila['estudiante'])->not->toBe('')
        ->and($fila['cedula'])->toBe('16000001')
        ->and($fila['id'])->toBe($alumno->id_usuario);
});

it('ordena a los estudiantes de menor a mayor promedio y deja al final los que no tienen nota', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Turismo');
    $curso = cursoPanel($q, clases: 10);

    // Dos con nota, uno solo con matricula (aun sin calificar).
    foreach ([['16100001', 5], ['16100002', 9]] as [$cedula, $nota]) {
        Calificacion::create([
            'id_estudiante' => alumnoPanel($carrera, $cedula)->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => $nota,
        ]);
    }

    Inscripcion::create([
        'id_estudiante' => alumnoPanel($carrera, '16100003')->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'fecha_inscripcion' => '2026-01-04',
    ]);

    $filas = app(ReporteService::class)->rendimientoPorEstudiante($q->id_cuatrimestre);

    expect($filas)->toHaveCount(3)
        ->and($filas[0]['promedio'])->toBe(5.0)
        ->and($filas[1]['promedio'])->toBe(9.0)
        // El que solo tiene matricula no compite en el orden por promedio.
        ->and($filas[2]['promedio'])->toBeNull();
});

it('trae el rendimiento por estudiante sin una consulta por alumno', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Diseno Grafico');
    $curso = cursoPanel($q, clases: 10);

    // 15 alumnos: leer usuario y carrera dentro de un bucle serian 30
    // consultas, y las cuatro agregaciones de withCount otras 60.
    for ($i = 1; $i <= 15; $i++) {
        Calificacion::create([
            'id_estudiante' => alumnoPanel($carrera, '1620'.str_pad((string) $i, 4, '0', STR_PAD_LEFT))->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => 7,
        ]);
    }

    $conteo = 0;
    DB::listen(function () use (&$conteo) {
        $conteo++;
    });

    $filas = app(ReporteService::class)->rendimientoPorEstudiante($q->id_cuatrimestre);

    expect($filas)->toHaveCount(15)
        // usuario (1) + carrera (1) + 4 agregaciones + 2 whereHas + select
        ->and($conteo)->toBeLessThanOrEqual(9);
});

it('cuenta los cursos que superan el veinticinco por ciento de inasistencia', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $enAlerta = cursoPanel($q, clases: 10);
    $tranquila = cursoPanel($q, clases: 10);
    $carrera = carreraPanel('Marketing Digital');

    $alumno = alumnoPanel($carrera, '15000001');

    foreach (range(1, 10) as $dia) {
        $fecha = $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString();

        // 3 faltas de 10 clases en la primera -> 30% de inasistencia
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $enAlerta->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $fecha,
            'presente' => $dia > 3,
        ]);

        // asistencia perfecta en la segunda
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $tranquila->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $fecha,
            'presente' => true,
        ]);
    }

    $filas = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'])
        ->keyBy('curso');

    // La de 30% entra en advertencia, la de asistencia perfecta se queda en ok.
    expect($filas[$enAlerta->nombre]['alerta'])->toBe('advertencia')
        ->and($filas[$tranquila->nombre]['alerta'])->toBe('ok');
});

it('devuelve todos los cursos del grafico, no solo los doce primeros', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $carrera = carreraPanel('Electronica');

    // 20 cursos: mas que el bloque inicial de 12, para que el boton
    // "ver mas" tenga sentido
    for ($i = 0; $i < 20; $i++) {
        $curso = cursoPanel($q, clases: 10);

        foreach (range(1, 10) as $dia) {
            Asistencia::create([
                'id_estudiante' => alumnoPanel($carrera, '170'.str_pad((string) $i, 5, '0', STR_PAD_LEFT))->id_usuario,
                'id_curso' => $curso->id_curso,
                'id_cuatrimestre' => $q->id_cuatrimestre,
                'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
                'presente' => $dia % 2 === 0,
            ]);
        }
    }

    $filas = app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'];

    expect($filas)->toHaveCount(20)
        ->and(ReporteService::BLOQUE_CURSOS)->toBe(12);
});

it('calcula los cupos libres contra el limite de los cursos en oferta', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $curso = cursoPanel($q, cupo: 20);
    $otra = cursoPanel($q, cupo: 30);

    $alumno = alumnoPanel(carreraPanel('Turismo'), '30000001');

    foreach ([$curso, $otra] as $c) {
        Inscripcion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $c->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha_inscripcion' => '2026-01-04',
        ]);
    }

    $kpis = app(ReporteService::class)->panel($q->id_cuatrimestre)['kpis'];

    expect($kpis['cupoTotal'])->toBe(50)   // 20 + 30
        ->and($kpis['inscritos'])->toBe(2)
        ->and($kpis['cuposLibres'])->toBe(48);
});

// ------------------------------------------------- Estudiantes por carrera

it('cuenta cada estudiante en su propia carrera aunque el curso sea compartido', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $informatica = carreraPanel('Informatica');
    $contabilidad = carreraPanel('Contaduria');

    // Curso compartido: pertenece a las dos carreras (caso real de
    // Matematica Basica, Expresion Oral, Emprendimiento, etc.)
    $compartida = cursoPanel($q);
    $compartida->carreras()->attach([$informatica->id_carrera, $contabilidad->id_carrera]);

    $alumno = alumnoPanel($informatica, '40000001');
    Inscripcion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $compartida->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'fecha_inscripcion' => '2026-01-04',
    ]);

    $porCarrera = app(ReporteService::class)->panel($q->id_cuatrimestre)['inscritosPorCarrera'];
    $conteo = collect($porCarrera)->pluck('total', 'nombre')->all();

    // El alumno pertenece a Informatica: Contaduria no debe contarlo aunque
    // la asignatura tambien sea de esa carrera.
    expect($conteo['Informatica'])->toBe(1)
        ->and($conteo['Contaduria'])->toBe(0);
});

it('cuenta al estudiante en un periodo cerrado usando sus notas', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $carrera = carreraPanel('Enfermeria');
    $curso = cursoPanel($q);
    $alumno = alumnoPanel($carrera, '50000001');

    // Sin inscripcion (periodo ya cerrado) pero con nota registrada
    Calificacion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 8,
    ]);

    $conteo = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['inscritosPorCarrera'])
        ->pluck('total', 'nombre')
        ->all();

    expect($conteo['Enfermeria'])->toBe(1);
});

// ------------------------------------------------------------ Rendimiento

it('marca como en curso a la matricula que aun no tiene nota en el periodo', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);

    $carrera = carreraPanel('Diseno Grafico');

    $conNota = alumnoPanel($carrera, '60000001');
    $pendiente = alumnoPanel($carrera, '60000002');

    foreach ([$conNota, $pendiente] as $alumno) {
        Inscripcion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha_inscripcion' => '2026-01-04',
        ]);
    }

    Calificacion::create([
        'id_estudiante' => $conNota->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 7,
    ]);

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['rendimientoPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['aprobados'])->toBe(1)
        ->and($fila['reprobados'])->toBe(0)
        ->and($fila['enCurso'])->toBe(1);
});

it('cuenta como reprobada toda nota por debajo de seis', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);
    $carrera = carreraPanel('Electronica');

    foreach ([5, 4] as $i => $nota) {
        $alumno = alumnoPanel($carrera, '7000000'.$i);
        Calificacion::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'nota' => $nota,
        ]);
    }

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['rendimientoPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['aprobados'])->toBe(0)
        ->and($fila['reprobados'])->toBe(2)
        ->and($fila['enCurso'])->toBe(0);
});

// ------------------------------------------------------------ Asistencia

it('calcula la inasistencia sobre las clases programadas y clasifica la alerta', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // 10 clases programadas
    $curso = cursoPanel($q, clases: 10);
    $carrera = carreraPanel('Marketing Digital');
    $alumno = alumnoPanel($carrera, '80000001');

    // 4 faltas de 10 clases = 40% -> supera el 30%, pierde el curso
    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 4,
        ]);
    }

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['porcentaje'])->toBe(40.0)
        ->and($fila['alerta'])->toBe('peligro');
});

it('marca advertencia entre veinticinco y treinta por ciento de inasistencia', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $curso = cursoPanel($q, clases: 10);
    $alumno = alumnoPanel(carreraPanel('Turismo'), '90000001');

    // 3 faltas de 10 = 30% -> borde superior, todavia no pierde el curso
    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 3,
        ]);
    }

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    expect($fila['porcentaje'])->toBe(30.0)
        ->and($fila['alerta'])->toBe('advertencia');
});

it('marca sin datos el curso sin clases programadas en vez de omitirlo', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // total_clases NULL: no hay denominador, no se puede calcular el porcentaje
    $curso = cursoPanel($q, clases: null);
    $alumno = alumnoPanel(carreraPanel('Informatica'), '91000001');

    Asistencia::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'fecha' => '2026-01-05',
        'presente' => false,
    ]);

    $fila = collect(app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'])
        ->firstWhere('curso', $curso->nombre);

    // Se conserva la fila: sin denominator no hay porcentaje, pero tampoco
    // hay motivo para desaparecer del grafico.
    expect($fila)->not->toBeNull()
        ->and($fila['porcentaje'])->toBeNull()
        ->and($fila['alerta'])->toBe(ReporteService::SIN_DATOS);
});

it('lista tambien los cursos ofrecidos sin una sola asistencia registrada', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // Dos cursos en oferta. Solo uno tiene asistencias capturadas.
    $conDatos = cursoPanel($q, clases: 10);
    $sinDatos = cursoPanel($q, clases: 10);

    $alumno = alumnoPanel(carreraPanel('Turismo'), '92000001');

    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $conDatos->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 4,
        ]);
    }

    $panel = app(ReporteService::class)->panel($q->id_cuatrimestre);
    $filas = $panel['asistenciaPorCurso'];

    $nombres = collect($filas)->pluck('curso');

    expect($nombres)->toContain($conDatos->nombre)
        ->and($nombres)->toContain($sinDatos->nombre);

    $filaVacia = collect($filas)->firstWhere('curso', $sinDatos->nombre);

    expect($filaVacia['porcentaje'])->toBeNull()
        ->and($filaVacia['alerta'])->toBe(ReporteService::SIN_DATOS);

    // El curso sin dato se cuenta aparte: no es un curso en riesgo, es uno
    // del que no se puede afirmar nada.
    expect($panel['kpis']['cursosSinDatos'])->toBe(1);
});

it('coloca al final los cursos sin registros y no los cuenta como cero', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $conPocasFaltas = cursoPanel($q, clases: 10);
    $sinNinguna = cursoPanel($q, clases: 10);
    $alumno = alumnoPanel(carreraPanel('Contaduria'), '93000001');

    // 1 falta de 10 = 10%: el menor porcentaje con dato real
    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $conPocasFaltas->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 1,
        ]);
    }

    $filas = app(ReporteService::class)->panel($q->id_cuatrimestre)['asistenciaPorCurso'];

    // El ultimo elemento es el que no tiene dato, y no vale 0.
    expect(end($filas)['curso'])->toBe($sinNinguna->nombre)
        ->and(end($filas)['porcentaje'])->toBeNull();
});

// ------------------------------------------------------- Filtro y vista

it('acota todas las cifras al cuatrimestre seleccionado', function () {
    $admin = adminPanel();
    $q1 = periodoPanel('2026-01-05', '2026-04-30');
    $q2 = periodoPanel('2026-05-04', '2026-08-31');

    $curso = cursoPanel($q2, clases: 12);
    $carrera = carreraPanel('Contaduria');
    $alumno = alumnoPanel($carrera, '92000001');

    Calificacion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q2->id_cuatrimestre,
        'nota' => 9,
    ]);

    $servicio = app(ReporteService::class);

    // La nota es de q2: q1 no debe verla
    expect($servicio->panel($q1->id_cuatrimestre)['kpis']['totalCalificaciones'])->toBe(0)
        ->and($servicio->panel($q2->id_cuatrimestre)['kpis']['totalCalificaciones'])->toBe(1)
        ->and($servicio->panel(null)['kpis']['totalCalificaciones'])->toBe(1);
});

it('renderiza los cuatro graficos en sus respectivas secciones', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);
    $carrera = carreraPanel('Informatica');
    $alumno = alumnoPanel($carrera, '93000001');

    Inscripcion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'fecha_inscripcion' => $q->fecha_inicio->copy()->subDay()->toDateString(),
    ]);

    Calificacion::create([
        'id_estudiante' => $alumno->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 10,
    ]);

    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $curso->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 2,
        ]);
    }

    // Inscripciones: ocupado vs cupo y la torta por carrera.
    $this->actingAs($admin)
        ->get(route('admin.inscripciones', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('<canvas id="grafCupos"', false)
        ->assertSee('<canvas id="grafCarrera"', false)
        ->assertSee('chart.umd.min.js', false);

    // Rendimiento: aprobados / reprobados / en curso.
    $this->actingAs($admin)
        ->get(route('admin.rendimiento', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('<canvas id="grafRendimiento"', false);

    // Asistencia: porcentaje de inasistencia por curso.
    $this->actingAs($admin)
        ->get(route('admin.asistencia', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('<canvas id="grafAsistencia"', false);
});

it('identifica el cuatrimestre en cada grafico', function () {
    $admin = adminPanel();
    $q1 = periodoPanel('2026-01-05', '2026-04-30');
    $q2 = periodoPanel('2026-08-03', '2026-11-27');

    cursoPanel($q1);
    cursoPanel($q2, nombre: 'Curso Solo Q2');

    $chip = 'Q'.str_pad($q2->id_cuatrimestre, 2, '0', STR_PAD_LEFT)
        .' · '.$q2->fecha_inicio->format('d/m/y');

    // Inscripciones lleva dos chips (cupo por curso y la torta por carrera).
    $htmlInsc = $this->actingAs($admin)
        ->get(route('admin.inscripciones', ['cuatrimestre' => $q2->id_cuatrimestre]))
        ->getContent();

    expect(substr_count($htmlInsc, $chip))->toBeGreaterThanOrEqual(2);

    // Rendimiento y Asistencia llevan un chip cada una.
    foreach (['admin.rendimiento', 'admin.asistencia'] as $ruta) {
        $html = $this->actingAs($admin)
            ->get(route($ruta, ['cuatrimestre' => $q2->id_cuatrimestre]))
            ->getContent();

        expect(substr_count($html, $chip))->toBeGreaterThanOrEqual(1);
    }
});

it('no ofrece la opcion de ver todos los cuatrimestres juntos', function () {
    $admin = adminPanel();
    $q1 = periodoPanel('2026-01-05', '2026-04-30');
    $q2 = periodoPanel('2026-08-03', '2026-11-27');

    cursoPanel($q1);
    cursoPanel($q2, nombre: 'Curso Solo Q2');

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();

    // Mezclar dos periodos en una misma barra de ocupacion o inasistencia no
    // produce una cifra interpretable, asi que la opcion no existe.
    expect($html)->not->toContain('Todos los cuatrimestres')
        ->and($html)->not->toContain('>Todos<')
        ->and($html)->not->toContain('value=""');
});

it('abre en el cuatrimestre vigente y no en uno futuro todavia vacio', function () {
    $admin = adminPanel();

    $pasado = periodoPanel(now()->subYear()->startOfYear()->toDateString(), now()->subMonths(6)->toDateString());
    $vigente = periodoPanel(now()->subMonths(3)->toDateString(), now()->addMonths(3)->toDateString());
    $futuro = periodoPanel(now()->addMonths(6)->toDateString(), now()->addYear()->toDateString());

    cursoPanel($pasado);
    cursoPanel($vigente);
    cursoPanel($futuro);

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->getContent();

    $chip = fn ($q) => 'Q'.str_pad($q->id_cuatrimestre, 2, '0', STR_PAD_LEFT)
        .' · '.$q->fecha_inicio->format('d/m/y');

    // El encabezado del Inicio dice el vigente, no el futuro: el selector si
    // lista los tres, pero abrir el panel en un periodo sin movimientos seria
    // mostrarle a la rectora una pantalla en blanco.
    expect($html)->toContain(
        'Cuatrimestre '.str_pad($vigente->id_cuatrimestre, 2, '0', STR_PAD_LEFT)
    )->and($html)->not->toContain($chip($futuro));

    // Las secciones tambien abren en el vigente, con su chip en el grafico.
    $htmlInsc = $this->actingAs($admin)->get(route('admin.inscripciones'))->getContent();

    expect($htmlInsc)->toContain($chip($vigente))
        ->and($htmlInsc)->not->toContain($chip($futuro));
});

it('rechaza un cuatrimestre que no existe en vez de caer al vigente', function () {
    $admin = adminPanel();
    periodoPanel('2026-01-05', '2026-04-30');

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['cuatrimestre' => 999]))
        ->assertNotFound();
});

it('avisa que un cuatrimestre aun no tiene informacion en vez de pintar graficos vacios', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // Tres cursos en oferta y nada capturado: el aviso tiene que aparecer.
    cursoPanel($q);
    cursoPanel($q);
    cursoPanel($q);

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('Aún no hay información registrada en este cuatrimestre');

    // Cada seccion cae a su propio estado vacio: ningun canvas se pinta.
    foreach (['admin.inscripciones', 'admin.rendimiento', 'admin.asistencia'] as $ruta) {
        $this->actingAs($admin)
            ->get(route($ruta, ['cuatrimestre' => $q->id_cuatrimestre]))
            ->assertOk()
            ->assertDontSee('<canvas id="graf', false);
    }
});

it('no saca el aviso cuando el cuatrimestre si tiene notas registradas', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');
    $curso = cursoPanel($q);

    // Solo notas, sin matricula: el aviso general no aplica porque el periodo
    // si tiene informacion, aunque el grafico de cupos no tenga nada que pintar.
    Calificacion::create([
        'id_estudiante' => alumnoPanel(carreraPanel('Turismo'), '96000001')->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $q->id_cuatrimestre,
        'nota' => 8,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertDontSee('Aún no hay información registrada en este cuatrimestre');

    // El grafico de cupos cae al estado vacio por su propia cuenta...
    $this->actingAs($admin)
        ->get(route('admin.inscripciones', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertDontSee('<canvas id="grafCupos"', false)
        ->assertSee('todavía no hay inscripciones registradas');

    // ...mientras rendimiento si pinta su grafico (la nota existe).
    $this->actingAs($admin)
        ->get(route('admin.rendimiento', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('<canvas id="grafRendimiento"', false);
});

it('no deja el grafico de cupos en gris cuando el periodo no tiene matricula', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // Es el caso que reporto el usuario: courses en oferta, cero inscritos.
    // El grafico de barras apiladas saldia con las 45 barras en el color claro
    // de "cupo libre", lo que se leia como un fallo de la pagina.
    cursoPanel($q, cupo: 30);
    cursoPanel($q, cupo: 30);

    $this->actingAs($admin)
        ->get(route('admin.inscripciones', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertDontSee('<canvas id="grafCupos"', false)
        ->assertSee('todavía no hay inscripciones registradas')
        // Y dice cuantos cursos hay, para que el cero tenga contexto.
        ->assertSee('ningún lugar de los 2 cursos en oferta');
});

it('no dibuja el grafico de asistencia si ningun curso tiene registros', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    // Dos cursos en oferta, ninguna asistencia capturada: el grafico tiene que
    // caer al estado vacio y no mostrar 45 barras sin nada pintado.
    cursoPanel($q, clases: 10);
    cursoPanel($q, clases: 10);

    $this->actingAs($admin)
        ->get(route('admin.asistencia', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertDontSee('<canvas id="grafAsistencia"', false)
        ->assertSee('fa-inbox', false);
});

it('dibuja el grafico de asistencia aunque algunos cursos no tengan datos', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $conDatos = cursoPanel($q, clases: 10);
    $sinDatos = cursoPanel($q, clases: 10);

    $alumno = alumnoPanel(carreraPanel('Turismo'), '95000001');

    foreach (range(1, 10) as $dia) {
        Asistencia::create([
            'id_estudiante' => $alumno->id_usuario,
            'id_curso' => $conDatos->id_curso,
            'id_cuatrimestre' => $q->id_cuatrimestre,
            'fecha' => $q->fecha_inicio->copy()->addDays($dia - 1)->toDateString(),
            'presente' => $dia > 4,
        ]);
    }

    $html = $this->actingAs($admin)
        ->get(route('admin.asistencia', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('<canvas id="grafAsistencia"', false)
        ->getContent();

    // Con un solo curso con dato, el subtexto lo dice en vez de mentir.
    expect($html)->toContain('De 1 de 2 cursos en oferta');
});

it('muestra el estado vacio cuando el cuatrimestre no tiene datos', function () {
    $admin = adminPanel();
    $q = periodoPanel('2026-01-05', '2026-04-30');

    $this->actingAs($admin)
        ->get(route('admin.inscripciones', ['cuatrimestre' => $q->id_cuatrimestre]))
        ->assertOk()
        ->assertSee('todavía')
        ->assertSee('fa-inbox', false);
});

it('no expone el panel a un usuario sin rol de admin', function () {
    $visitante = Usuario::factory()->create();

    $this->actingAs($visitante)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
