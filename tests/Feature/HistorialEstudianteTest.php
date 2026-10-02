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
use App\Services\CalificacionAsistenciaService;
use App\Services\HistorialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Historial del estudiante y certificado de notas
|--------------------------------------------------------------------------
|
| Lo que se comprueba aca es lo que el enunciado pide del modulo: el alumno ve
| su propio historial de todos los cuatrimestres, el veredicto de cada curso
| sale de las mismas reglas que ve el profesor, y el PDF se puede descargar.
|
*/

function rolEstudiante(): Rol
{
    return Rol::firstOrCreate(['nombre' => 'estudiante']);
}

/**
 * Usuario con rol estudiante. Se usa la factory porque la columna telefono es
 * NOT NULL en el esquema y el historial necesita un usuario real para la sesion.
 */
function usuarioEstudiante(string $nombres, string $apellidos, string $email): Usuario
{
    $usuario = Usuario::factory()->create(compact('nombres', 'apellidos', 'email'));
    $usuario->roles()->attach(rolEstudiante());

    return $usuario;
}

/** duracion es NOT NULL en el esquema. */
function carreraHistorial(string $nombre): Carrera
{
    return Carrera::firstOrCreate(['nombre' => $nombre], ['duracion' => 8]);
}

function estudianteConCarrera(?string $carrera = 'Ingeniería en Sistemas'): Estudiante
{
    $usuario = usuarioEstudiante('Ana', 'Ramírez Solano', 'ana.ramirez-estudiante@aula.edu');

    return Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => '0102030405',
        'fecha_nacimiento' => '2004-03-15',
        'deuda' => false,
        'id_carrera' => carreraHistorial($carrera)->id_carrera,
    ]);
}

function otroEstudiante(): Estudiante
{
    $usuario = usuarioEstudiante('Luis', 'Otra Persona', 'luis.otra-estudiante@aula.edu');

    return Estudiante::create([
        'id_usuario' => $usuario->id_usuario,
        'cedula' => '0607080910',
        'fecha_nacimiento' => '2003-08-02',
        'deuda' => false,
        'id_carrera' => carreraHistorial('Contaduría')->id_carrera,
    ]);
}

function cursoParaHistorial(string $nombre, Cuatrimestre $cuatrimestre, int $limite = 30): Curso
{
    $curso = Curso::create(['nombre' => $nombre, 'limite_estudiantes' => $limite]);

    DB::table('curso_cuatrimestre')->insert([
        'curso_id' => $curso->id_curso,
        'cuatrimestre_id' => $cuatrimestre->id_cuatrimestre,
    ]);

    return $curso;
}

function matricular(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre, string $fecha = '2026-01-15'): void
{
    Inscripcion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'fecha_inscripcion' => $fecha,
    ]);
}

function ponerNota(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre, int $nota, ?string $observaciones = null): void
{
    Calificacion::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'nota' => $nota,
        'observaciones' => $observaciones,
    ]);
}

function falta(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre, string $fecha): void
{
    Asistencia::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'fecha' => $fecha,
        'presente' => false,
    ]);
}

function asiste(Estudiante $estudiante, Curso $curso, Cuatrimestre $cuatrimestre, string $fecha): void
{
    Asistencia::create([
        'id_estudiante' => $estudiante->id_usuario,
        'id_curso' => $curso->id_curso,
        'id_cuatrimestre' => $cuatrimestre->id_cuatrimestre,
        'fecha' => $fecha,
        'presente' => true,
    ]);
}

function fijarClasesProgramadas(Curso $curso, Cuatrimestre $cuatrimestre, int $total): void
{
    DB::table('curso_cuatrimestre')
        ->where('curso_id', $curso->id_curso)
        ->where('cuatrimestre_id', $cuatrimestre->id_cuatrimestre)
        ->update(['total_clases' => $total]);
}

/** Cuatrimestre ya cerrado, para que el veredicto no quede "presunto". */
function cerradoParaHistorial(): Cuatrimestre
{
    return Cuatrimestre::create([
        'fecha_inicio' => now()->subYear()->startOfQuarter(),
        'fecha_fin' => now()->subYear()->endOfQuarter(),
    ]);
}

// ─── El veredicto sale de las reglas que ya existen ──────────────────────────

it('reutiliza las reglas del modulo del profesor para decir aprobado y reprobado', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();

    $aprobado = cursoParaHistorial('Curso Aprobado', $q1);
    $reprobado = cursoParaHistorial('Curso Reprobado', $q1);

    matricular($estudiante, $aprobado, $q1);
    matricular($estudiante, $reprobado, $q1);
    ponerNota($estudiante, $aprobado, $q1, 8);
    ponerNota($estudiante, $reprobado, $q1, 4);

    $datos = app(HistorialService::class)->historial($estudiante);
    $estados = $datos['periodos']->first()['cursos']->pluck('estado', 'curso');

    expect($estados['Curso Aprobado'])->toBe('Aprobado')
        ->and($estados['Curso Reprobado'])->toBe('Reprobado');
});

it('reprobado por inasistencia aunque la nota sea aprobatoria, igual que ve el alumno en sus notas', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso con Faltas', $q1);

    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9);
    fijarClasesProgramadas($curso, $q1, 10);

    // 4 faltas de 10 clases = 40 %, por encima del 30 % que corta el curso.
    foreach (['2026-01-06', '2026-01-07', '2026-01-08', '2026-01-09'] as $fecha) {
        falta($estudiante, $curso, $q1, $fecha);
    }
    foreach (['2026-01-05', '2026-01-12', '2026-01-13', '2026-01-14', '2026-01-15', '2026-01-16'] as $fecha) {
        asiste($estudiante, $curso, $q1, $fecha);
    }

    $fila = app(HistorialService::class)->historial($estudiante)['periodos']->first()['cursos']->first();

    expect($fila['nota'])->toBe(9)
        ->and($fila['inasistencia'])->toBe(40.0)
        ->and($fila['alerta'])->toBe('peligro')
        ->and($fila['estado'])->toBe('Reprobado');
});

it('muestra la fila segun el veredicto y no segun la nota suelta', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso con 9 y muchas faltas', $q1);

    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9);
    fijarClasesProgramadas($curso, $q1, 10);

    foreach (['2026-01-05', '2026-01-06', '2026-01-07', '2026-01-08'] as $fecha) {
        falta($estudiante, $curso, $q1, $fecha);
    }

    $datos = app(HistorialService::class)->historial($estudiante);

    // El 9 con 40 % de faltas es reprobado: tambien cuenta como reprobado en los
    // totales, para que el resumen no contradiga a la fila que tiene debajo.
    expect($datos['resumen']['aprobados'])->toBe(0)
        ->and($datos['resumen']['reprobados'])->toBe(1)
        ->and($datos['periodos']->first()['aprobados'])->toBe(0)
        ->and($datos['periodos']->first()['reprobados'])->toBe(1);

    $html = view('estudiante.historial', [
        'estudiante' => $estudiante,
        'datos' => $datos,
    ])->render();

    $certificado = view('reportes.certificado', [
        'estudiante' => $estudiante,
        'datos' => $datos,
        'emitido' => now(),
    ])->render();

    // Se busca el atributo class completo: las reglas .pill.aprobado del CSS
    // estan en las dos salidas y no son el veredicto de la fila.
    foreach (['la vista' => $html, 'el certificado' => $certificado] as $salida) {
        expect($salida)
            ->toContain('class="pill reprobado"')
            ->toContain('class="nota reprobado"')
            ->not->toContain('class="pill aprobado"')
            ->not->toContain('class="nota aprobado"');
    }
});

it('marca el veredicto por inasistencia como presunto mientras el cuatrimestre sigue abierto', function () {
    $estudiante = estudianteConCarrera();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);
    $curso = cursoParaHistorial('Curso en Marcha', $vigente);

    matricular($estudiante, $curso, $vigente);
    ponerNota($estudiante, $curso, $vigente, 7);
    fijarClasesProgramadas($curso, $vigente, 10);

    foreach (['2026-02-02', '2026-02-03', '2026-02-04', '2026-02-05'] as $fecha) {
        falta($estudiante, $curso, $vigente, $fecha);
    }

    $periodo = app(HistorialService::class)->historial($estudiante)['periodos']->first();

    // Con nota cargada el curso ya no esta en el bloque "en curso": sale en el
    // del cuatrimestre, con el veredicto de presuncion porque el periodo sigue abierto.
    expect($periodo['cursos']->pluck('curso')->all())->toBe(['Curso en Marcha'])
        ->and($periodo['cursos']->first()['estado'])->toBe('Reprobado (presunto)');
});

// ─── El historial abarca todos los cuatrimestres ────────────────────────────

it('reune en un solo historial los cuatrimestres cursados', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $q2 = Cuatrimestre::create([
        'fecha_inicio' => now()->subYear()->addMonths(4),
        'fecha_fin' => now()->subYear()->addMonths(7),
    ]);

    $bases = cursoParaHistorial('Bases de Datos', $q1);
    $algoritmos = cursoParaHistorial('Algoritmos', $q2);
    $redes = cursoParaHistorial('Redes', $q2);

    matricular($estudiante, $bases, $q1);
    matricular($estudiante, $algoritmos, $q2);
    matricular($estudiante, $redes, $q2);

    ponerNota($estudiante, $bases, $q1, 7);
    ponerNota($estudiante, $algoritmos, $q2, 9);
    ponerNota($estudiante, $redes, $q2, 3);

    $datos = app(HistorialService::class)->historial($estudiante);

    expect($datos['periodos'])->toHaveCount(2);

    // Del mas reciente al mas antiguo, para leer la carrera hacia atras.
    $codigos = $datos['periodos']->pluck('codigo')->all();

    expect($codigos)->toBe([
        'Q'.str_pad($q2->id_cuatrimestre, 2, '0', STR_PAD_LEFT),
        'Q'.str_pad($q1->id_cuatrimestre, 2, '0', STR_PAD_LEFT),
    ])->and($datos['periodos'][0]['cursos']->pluck('curso')->all())->toBe(['Algoritmos', 'Redes'])
        ->and($datos['resumen']['promedio'])->toBe(6.33)
        ->and($datos['resumen']['aprobados'])->toBe(2)
        ->and($datos['resumen']['reprobados'])->toBe(1)
        ->and($datos['resumen']['cuatrimestres'])->toBe(2);
});

it('calcula el promedio del cuatrimestre y no el promedio acumulado', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $q2 = Cuatrimestre::create([
        'fecha_inicio' => now()->subYear()->addMonths(4),
        'fecha_fin' => now()->subYear()->addMonths(7),
    ]);

    foreach ([[$q1, 10], [$q2, 5]] as [$periodo, $nota]) {
        $curso = cursoParaHistorial('Curso '.$periodo->id_cuatrimestre, $periodo);
        matricular($estudiante, $curso, $periodo);
        ponerNota($estudiante, $curso, $periodo, $nota);
    }

    $datos = app(HistorialService::class)->historial($estudiante);

    expect($datos['periodos'][0]['promedio'])->toBe(5.0)
        ->and($datos['periodos'][1]['promedio'])->toBe(10.0)
        ->and($datos['resumen']['promedio'])->toBe(7.5);
});

it('deja aparte los cursos matriculados que todavia no tienen nota', function () {
    $estudiante = estudianteConCarrera();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);

    $q1 = cerradoParaHistorial();
    $cerrado = cursoParaHistorial('Curso ya Cerrado', $q1);
    matricular($estudiante, $cerrado, $q1);
    ponerNota($estudiante, $cerrado, $q1, 8);

    $enMarcha = cursoParaHistorial('Curso en Marcha', $vigente);
    $otroEnMarcha = cursoParaHistorial('Otro Curso en Marcha', $vigente);
    matricular($estudiante, $enMarcha, $vigente);
    matricular($estudiante, $otroEnMarcha, $vigente);

    $datos = app(HistorialService::class)->historial($estudiante);

    expect($datos['enCurso']->pluck('curso')->all())->toBe(['Curso en Marcha', 'Otro Curso en Marcha'])
        ->and($datos['enCurso']->every(fn ($f) => $f['estado'] === 'En curso' && is_null($f['nota'])))->toBeTrue()
        ->and($datos['resumen']['cursos'])->toBe(1);
});

it('no repite en el bloque en curso un curso que ya tiene nota en ese mismo cuatrimestre', function () {
    $estudiante = estudianteConCarrera();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);

    $conNota = cursoParaHistorial('Curso con Nota', $vigente);
    $sinNota = cursoParaHistorial('Curso sin Nota', $vigente);

    matricular($estudiante, $conNota, $vigente);
    matricular($estudiante, $sinNota, $vigente);
    ponerNota($estudiante, $conNota, $vigente, 7);

    $datos = app(HistorialService::class)->historial($estudiante);

    // El curso con nota aparece en el bloque del periodo, no tambien en "en curso".
    expect($datos['enCurso']->pluck('curso')->all())->toBe(['Curso sin Nota'])
        ->and($datos['periodos']->first()['cursos']->pluck('curso')->all())->toBe(['Curso con Nota']);
});

it('avisa en el bloque en curso cuando la inasistencia ya perdio el curso', function () {
    $estudiante = estudianteConCarrera();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);

    $curso = cursoParaHistorial('Curso sin nota y con faltas', $vigente);
    matricular($estudiante, $curso, $vigente);
    fijarClasesProgramadas($curso, $vigente, 10);

    foreach (['2026-02-02', '2026-02-03', '2026-02-04', '2026-02-05', '2026-02-06'] as $fecha) {
        falta($estudiante, $curso, $vigente, $fecha);
    }

    $fila = app(HistorialService::class)->historial($estudiante)['enCurso']->first();

    // Todavia no hay nota que mostrar, pero el 50 % de faltas ya perdio el curso:
    // la fila no puede quedarse en "en curso" como si nada.
    expect($fila['nota'])->toBeNull()
        ->and($fila['estado'])->toBe('Reprobado (presunto)');

    $html = view('estudiante.historial', [
        'estudiante' => $estudiante,
        'datos' => app(HistorialService::class)->historial($estudiante),
    ])->render();

    expect($html)->toContain('class="pill presunto">Reprobado (presunto)');
});

it('calcula la inasistencia con las clases programadas y no con las dictadas', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso Programado', $q1);

    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 7);
    fijarClasesProgramadas($curso, $q1, 20);

    // 20 clases programadas pero solo 4 dictadas: manda el total programado.
    falta($estudiante, $curso, $q1, '2026-01-05');
    asiste($estudiante, $curso, $q1, '2026-01-06');
    falta($estudiante, $curso, $q1, '2026-01-07');
    asiste($estudiante, $curso, $q1, '2026-01-08');

    $fila = app(HistorialService::class)->historial($estudiante)['periodos']->first()['cursos']->first();

    expect($fila['inasistencia'])->toBe(10.0);
});

it('cae a las clases dictadas cuando el curso no tiene total de clases programado', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso Sin Programacion', $q1);

    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 7);

    falta($estudiante, $curso, $q1, '2026-01-05');
    asiste($estudiante, $curso, $q1, '2026-01-06');
    falta($estudiante, $curso, $q1, '2026-01-07');
    asiste($estudiante, $curso, $q1, '2026-01-08');

    $fila = app(HistorialService::class)->historial($estudiante)['periodos']->first()['cursos']->first();

    expect($fila['inasistencia'])->toBe(50.0)
        ->and($fila['alerta'])->toBe('peligro')
        ->and($fila['estado'])->toBe('Reprobado');
});

it('usa el cuatrimestre mas reciente ya empezado cuando hoy cae entre periodos', function () {
    $estudiante = estudianteConCarrera();

    $anterior = cerradoParaHistorial();
    $siguiente = Cuatrimestre::create(['fecha_inicio' => now()->addMonths(6), 'fecha_fin' => now()->addMonths(10)]);

    $cursoAnterior = cursoParaHistorial('Curso del Periodo Anterior', $anterior);
    matricular($estudiante, $cursoAnterior, $anterior);
    ponerNota($estudiante, $cursoAnterior, $anterior, 8);

    matricular($estudiante, cursoParaHistorial('Curso del Periodo Siguiente', $siguiente), $siguiente);

    $datos = app(HistorialService::class)->historial($estudiante);

    // El curso del periodo futuro no es "en curso todavia": no hay en curso.
    expect($datos['enCurso'])->toBeEmpty()
        ->and($datos['enCursoCuatrimestre']->id_cuatrimestre)->toBe($anterior->id_cuatrimestre);
});

it('arma el historial completo sin una consulta por curso', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);

    for ($i = 0; $i < 8; $i++) {
        $curso = cursoParaHistorial('Curso Histórico '.$i, $q1);
        matricular($estudiante, $curso, $q1);
        ponerNota($estudiante, $curso, $q1, 5 + ($i % 5));
        fijarClasesProgramadas($curso, $q1, 12);
        falta($estudiante, $curso, $q1, '2026-01-05');
        asiste($estudiante, $curso, $q1, '2026-01-06');
    }

    for ($i = 0; $i < 4; $i++) {
        matricular($estudiante, cursoParaHistorial('Curso Vigente '.$i, $vigente), $vigente);
    }

    $servicio = app(HistorialService::class);

    // Calentar el esquema y la sesion antes de medir.
    $servicio->historial($estudiante);
    DB::flushQueryLog();
    DB::enableQueryLog();

    $datos = $servicio->historial($estudiante);
    $consultas = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($datos['periodos']->first()['cursos'])->toHaveCount(8)
        ->and($datos['enCurso'])->toHaveCount(4)
        // Faltas y clases llegan agregadas en la consulta de notas, no con dos
        // consultas de asistencia por cada curso del historial.
        ->and($consultas)->toBeLessThanOrEqual(4);
});

// ─── La vista y el permiso ───────────────────────────────────────────────────

it('muestra al estudiante su historial con sus datos y el enlace al certificado', function () {
    $estudiante = estudianteConCarrera('Contaduría');
    $q1 = cerradoParaHistorial();

    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9, 'Participación constante');
    // Aprobó todos los cursos de su pensum: egresa y su botón se habilita.
    $estudiante->carrera->cursos()->attach($curso->id_curso);
    fijarClasesProgramadas($curso, $q1, 10);
    falta($estudiante, $curso, $q1, '2026-01-05');
    for ($i = 6; $i <= 14; $i++) {
        asiste($estudiante, $curso, $q1, "2026-01-$i");
    }

    $html = $this->actingAs($estudiante->usuario)->get(route('estudiante.historial'))->getContent();

    expect($html)->toContain('Mi Historial Académico')
        ->and($html)->toContain('Ana')
        ->and($html)->toContain('Ramírez Solano')
        ->and($html)->toContain('Contaduría')
        ->and($html)->toContain('Bases de Datos')
        ->and($html)->toContain('Aprobado')
        ->and($html)->toContain('Participación constante')
        ->and($html)->toContain(route('estudiante.certificado'));
});

it('dice que no hay historial en vez de mostrar una tabla vacia', function () {
    $estudiante = estudianteConCarrera();

    $html = $this->actingAs($estudiante->usuario)->get(route('estudiante.historial'))->getContent();

    expect($html)->toContain('Todavía no hay nada registrado en tu historial');
});

it('no deja ver el historial de otro estudiante cambiando la url', function () {
    $mio = estudianteConCarrera();
    $ajeno = otroEstudiante();

    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso Privado de Luis', $q1);
    matricular($ajeno, $curso, $q1);
    ponerNota($ajeno, $curso, $q1, 5);

    $html = $this->actingAs($mio->usuario)->get(route('estudiante.historial'))->getContent();

    expect($html)->not->toContain('Curso Privado de Luis')
        ->and($html)->not->toContain('Luis');
});

it('no expone el historial a quien no es estudiante', function () {
    $usuario = Usuario::factory()->create([
        'nombres' => 'Docente',
        'apellidos' => 'De Prueba',
        'email' => 'docente.prueba@aula.edu',
    ]);
    $usuario->roles()->attach(Rol::firstOrCreate(['nombre' => 'profesor']));

    $this->actingAs($usuario)->get(route('estudiante.historial'))->assertForbidden();
    $this->actingAs($usuario)->get(route('estudiante.certificado'))->assertForbidden();
});

// ─── El certificado en PDF ───────────────────────────────────────────────────

it('descarga el certificado en pdf con las notas del estudiante', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();

    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9);
    $estudiante->carrera->cursos()->attach($curso->id_curso);

    $respuesta = $this->actingAs($estudiante->usuario)->get(route('estudiante.certificado'));

    $respuesta->assertOk();
    $respuesta->assertHeader('content-type', 'application/pdf');

    // Firma de archivo real, no un HTML de error disfrazado.
    expect($respuesta->getContent())->toStartWith('%PDF-');
});

it('no emite el certificado a quien aún no completa la carrera', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9);

    // El pensum de su carrera tiene además una materia que dejó pendiente:
    // aprobó una, pero todavía no egresa.
    $estudiante->carrera->cursos()->attach(
        Curso::create(['nombre' => 'Auditoría (pendiente)', 'limite_estudiantes' => 30])->id_curso
    );

    $this->actingAs($estudiante->usuario)->get(route('estudiante.certificado'))->assertForbidden();
});

it('bloquea el botón del certificado en el historial mientras no egresa', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 5);

    $html = $this->actingAs($estudiante->usuario)->get(route('estudiante.historial'))->getContent();

    // Sin aprobar la carrera no sale el enlace descargable.
    expect($html)->not->toContain(route('estudiante.certificado'))
        ->and($html)->toContain('al terminar la carrera');
});

it('no deja el recorte de la fuente dependiendo del temporal del sistema', function () {
    $opciones = app('dompdf')->getOptions();
    $temporal = $opciones->getTempDir();

    // dompdf escribe la fuente recortada en un temporal. Con el del sistema, que
    // no siempre es escribible, la descarga revienta con "Path must not be empty".
    expect($temporal)->toBe(storage_path('framework/dompdf'))
        ->and(is_dir($temporal))->toBeTrue()
        ->and($opciones->getIsFontSubsettingEnabled())->toBeTrue();
});

it('le pasa el temporal propio al pdf que dompdf construye de verdad', function () {
    // Cpdf copia la ruta del temporal en su constructor, que corre dentro de
    // "new Dompdf". Ajustar la opcion despues llega tarde: la opcion dice una
    // cosa y el pdf sigue escribiendo en %TEMP%, que es justo lo que falla.
    $cpdf = app('dompdf')->getCanvas()->get_cpdf();

    expect($cpdf->tmp)->toBe(storage_path('framework/dompdf'))
        ->and($cpdf->tmp)->not->toBe(sys_get_temp_dir());
});

it('no infla el certificado incrustando la fuente completa', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();

    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 9);

    $respuesta = $this->actingAs($estudiante->usuario)->get(route('estudiante.certificado'));

    // Sin subsetting dompdf mete la DejaVu Sans entera y el PDF de una pagina
    // ronda los 880 KB. Recortando la fuente, el mismo documento baja a 30 KB.
    expect(strlen($respuesta->getContent()))->toBeLessThan(300 * 1024);
});

it('nombra el certificado con la cedula y lo abre en el navegador', function () {
    $estudiante = estudianteConCarrera();

    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 6);
    $estudiante->carrera->cursos()->attach($curso->id_curso);

    $respuesta = $this->actingAs($estudiante->usuario)->get(route('estudiante.certificado'));

    $respuesta->assertHeader(
        'content-disposition',
        'inline; filename=certificado-notas-0102030405.pdf'
    );
});

it('incluye en el pdf los datos del estudiante y una fila por curso y cuatrimestre', function () {
    $estudiante = estudianteConCarrera('Contaduría');

    $q1 = cerradoParaHistorial();
    $q2 = Cuatrimestre::create([
        'fecha_inicio' => now()->subYear()->addMonths(4),
        'fecha_fin' => now()->subYear()->addMonths(7),
    ]);

    $bases = cursoParaHistorial('Bases de Datos', $q1);
    $auditoria = cursoParaHistorial('Auditoría', $q2);

    matricular($estudiante, $bases, $q1);
    matricular($estudiante, $auditoria, $q2);
    ponerNota($estudiante, $bases, $q1, 8);
    ponerNota($estudiante, $auditoria, $q2, 4);

    $html = view('reportes.certificado', [
        'estudiante' => $estudiante,
        'datos' => app(HistorialService::class)->historial($estudiante),
        'emitido' => now(),
    ])->render();

    expect($html)->toContain('CERTIFICADO DE NOTAS')
        ->and($html)->toContain('Ana')
        ->and($html)->toContain('0102030405')
        ->and($html)->toContain('Contaduría')
        ->and($html)->toContain('Bases de Datos')
        ->and($html)->toContain('Auditoría')
        ->and($html)->toContain('Aprobado')
        ->and($html)->toContain('Reprobado')
        ->and($html)->toContain('Promedio general')
        ->and($html)->toContain('Secretar')
        ->and($html)->toContain('Fecha de emisi');
});

it('aclara en el certificado cuando no hay ninguna nota cargada', function () {
    $estudiante = estudianteConCarrera();

    $html = view('reportes.certificado', [
        'estudiante' => $estudiante,
        'datos' => app(HistorialService::class)->historial($estudiante),
        'emitido' => now(),
    ])->render();

    expect($html)->toContain('No hay notas registradas a la fecha de emisión');
});

it('deja fuera del certificado los cursos en curso y lo dice', function () {
    $estudiante = estudianteConCarrera();
    $vigente = Cuatrimestre::create(['fecha_inicio' => now()->subMonth(), 'fecha_fin' => now()->addMonths(2)]);
    $q1 = cerradoParaHistorial();

    $cerrado = cursoParaHistorial('Curso Cerrado', $q1);
    matricular($estudiante, $cerrado, $q1);
    ponerNota($estudiante, $cerrado, $q1, 7);

    matricular($estudiante, cursoParaHistorial('Curso Todavía en Marcha', $vigente), $vigente);

    $html = view('reportes.certificado', [
        'estudiante' => $estudiante,
        'datos' => app(HistorialService::class)->historial($estudiante),
        'emitido' => now(),
    ])->render();

    expect($html)->toContain('Curso Cerrado')
        ->and($html)->toContain('sin nota registrada; no forman parte de este certificado')
        ->and(substr_count($html, 'Curso Todavía en Marcha'))->toBe(0);
});

it('imprime el pdf aunque los nombres traigan tildes y enye', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();

    $curso = cursoParaHistorial('Diseño y ñandú', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 7, 'Observación: áéíóúñ');
    $estudiante->carrera->cursos()->attach($curso->id_curso);

    $respuesta = $this->actingAs($estudiante->usuario)->get(route('estudiante.certificado'));

    $respuesta->assertOk();
    expect(strlen($respuesta->getContent()))->toBeGreaterThan(1000);
});

it('el servicio de reglas sigue calculando la inasistencia igual que antes del refactor', function () {
    $estudiante = estudianteConCarrera();
    $q1 = cerradoParaHistorial();
    $curso = cursoParaHistorial('Curso de Referencia', $q1);

    fijarClasesProgramadas($curso, $q1, 8);
    falta($estudiante, $curso, $q1, '2026-01-05');
    falta($estudiante, $curso, $q1, '2026-01-06');
    asiste($estudiante, $curso, $q1, '2026-01-07');
    asiste($estudiante, $curso, $q1, '2026-01-08');

    $reglas = app(CalificacionAsistenciaService::class);

    expect($reglas->porcentajeInasistencia($estudiante->id_usuario, $curso->id_curso, $q1->id_cuatrimestre))->toBe(25.0)
        ->and($reglas->inasistenciaDesdeConteos(2, 8, 4))->toBe(25.0)
        // Sin total programado manda el conteo de clases dictadas.
        ->and($reglas->inasistenciaDesdeConteos(2, 0, 4))->toBe(50.0)
        // Sin ninguna clase registrada no se divide por cero.
        ->and($reglas->inasistenciaDesdeConteos(0, 0, 0))->toBe(0.0);
});

it('el certificado mantiene la estructura original del documento de notas', function () {
    $estudiante = estudianteConCarrera('Contaduría');
    $q1 = cerradoParaHistorial();

    $curso = cursoParaHistorial('Bases de Datos', $q1);
    matricular($estudiante, $curso, $q1);
    ponerNota($estudiante, $curso, $q1, 8);

    $html = view('reportes.certificado', [
        'estudiante' => $estudiante,
        'datos' => app(HistorialService::class)->historial($estudiante),
        'emitido' => now(),
    ])->render();

    // La estructura acordada: bloque de datos, resumen de 4 cifras y tabla por
    // cuatrimestre con Curso/Nota/Inasistencia/Estado. Sin constancia, sin fila
    // de estatus, sin columnas extra.
    expect($html)
        ->toContain('CERTIFICADO DE NOTAS')
        ->toContain('Promedio general')
        ->toContain('Cursos aprobados')
        ->toContain('Cursos reprobados')
        ->toContain('Cuatrimestres cursados')
        ->toContain('0102030405')
        ->not->toContain('Constancia de egresado')
        ->not->toContain('Estatus')
        ->not->toContain('Observaciones');
    expect(substr_count($html, '<th '))->toBe(4);
});
