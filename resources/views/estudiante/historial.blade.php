@extends('layouts.app')

@section('titulo', 'Mi Historial Académico')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a></li>
    <li><a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a></li>
    <li><a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a></li>
@endsection

@section('contenido')

<style>
    /* Mismo sistema visual que Matriculación y Mis Notas: tarjeta blanca con
       esquinas de 28px, borde azul pálido y las píldoras de estado verdes,
       amarillas y rojas que ya usa la plataforma. */

    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* Margen lateral amplio, igual que Mis Notas y Matriculación: la tarjeta
       queda centrada y con los mismos bordes que la barra de navegación. */
    .hist-wrap {
        width: calc(100% - 90px);

        margin: 35px auto 50px;
    }

    /* ── Tarjeta principal ─────────────────────────────────────────── */

    .hist-card {
        position: relative;

        padding: 34px 30px 40px;

        overflow: hidden;

        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 251, 255, 0.94));

        border: 1px solid #d9e6fb;

        border-radius: 28px;

        box-shadow: 0 10px 30px rgba(71, 106, 170, 0.10);
    }

    .hist-card::after {
        content: "";

        position: absolute;

        width: 620px;
        height: 240px;

        right: -180px;
        bottom: -170px;

        border-radius: 50%;

        background: rgba(188, 211, 253, 0.35);

        transform: rotate(-18deg);

        pointer-events: none;
    }

    .hist-head {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;

        gap: 24px;

        margin-bottom: 26px;
    }

    .hist-icon {
        position: relative;

        width: 96px;
        height: 96px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: linear-gradient(145deg, #dce8ff, #edf3ff);

        color: #6284df;

        font-size: 40px;
    }

    .hist-icon::before {
        content: "";

        position: absolute;

        width: 86px;
        height: 86px;

        border-radius: 50%;

        background: #e1eaff;
    }

    .hist-icon i { position: relative; z-index: 1; }

    .hist-titulo { flex: 1; }

    .hist-titulo h1 {
        margin-bottom: 5px;

        color: #171d7d;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 32px;

        font-weight: 700;
    }

    .hist-titulo p { color: #7087ba; font-size: 16px; }

    /* ── Botón del certificado ─────────────────────────────────────── */

    .btn-certificado {
        display: inline-flex;
        align-items: center;

        gap: 9px;

        height: 44px;
        padding: 0 20px;

        border-radius: 22px;

        background: linear-gradient(100deg, #5862e5, #668cf0);

        color: white;

        font-weight: 700;
        font-size: 14px;

        box-shadow: 0 5px 12px rgba(91, 111, 224, 0.25);

        white-space: nowrap;

        transition: 0.2s ease;
    }

    .btn-certificado:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 17px rgba(91, 111, 224, 0.30);
    }

    .btn-certificado small {
        font-weight: 500;
        font-size: 12px;
        opacity: 0.85;
    }

    /* ── Resumen ───────────────────────────────────────────────────── */

    .resumen {
        position: relative;
        z-index: 2;

        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));

        gap: 14px;

        margin-bottom: 26px;
    }

    .resumen-item {
        padding: 16px 18px;

        border: 1px solid #d9e6fb;
        border-radius: 20px;

        background: rgba(255, 255, 255, 0.75);
    }

    .resumen-item span {
        display: block;

        margin-bottom: 6px;

        color: #7b91bc;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .resumen-item b {
        color: #171d7d;
        font-size: 26px;
        font-weight: 700;
    }

    .resumen-item em {
        display: block;
        margin-top: 3px;
        color: #8ba0c8;
        font-size: 12px;
        font-style: normal;
    }

    /* ── Leyenda de asistencia ────────────────────────────────────── */

    .leyenda {
        position: relative;
        z-index: 2;

        display: flex;
        flex-wrap: wrap;
        align-items: center;

        gap: 12px;

        margin-bottom: 24px;
    }

    .leyenda .chip {
        display: flex;
        align-items: center;

        gap: 8px;

        height: 38px;
        padding: 0 16px;

        border-radius: 20px;

        font-size: 12px;
        font-weight: 600;
    }

    /* Los sufijos son los que devuelve CalificacionAsistenciaService::nivelAlerta(),
       para no tener que traducir el veredicto otra vez en la vista. */
    .chip.ok { background: #dcf9e9; color: #0d9261; }
    .chip.advertencia { background: #fff4d4; color: #896a1b; }
    .chip.peligro { background: #ffe0e8; color: #ec3e67; }

    .leyenda-nota { color: #8ba0c8; font-size: 12px; }

    /* ── Bloque por cuatrimestre ──────────────────────────────────── */

    .periodo {
        position: relative;
        z-index: 2;

        margin-bottom: 20px;

        border: 1px solid #d9e6fb;
        border-radius: 22px;

        background: rgba(255, 255, 255, 0.75);

        overflow: hidden;
    }

    .periodo-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;

        gap: 12px;

        padding: 16px 20px;

        border-bottom: 1px solid #d9e6fb;

        background: rgba(237, 243, 252, 0.7);
    }

    .periodo-codigo {
        padding: 6px 14px;

        border-radius: 20px;

        background: #eaf1fb;
        color: #4f72b4;

        font-size: 13px;
        font-weight: 700;
    }

    .periodo-fechas { color: #6d86b8; font-size: 13px; }

    .periodo-vacio {
        margin-left: auto;

        padding: 5px 13px;

        border-radius: 20px;

        background: #eaf1fb;
        color: #4f72b4;

        font-size: 12px;
        font-weight: 600;
    }

    /* ── Tabla ─────────────────────────────────────────────────────── */

    .fila-cabecera,
    .fila-curso {
        display: grid;

        grid-template-columns: minmax(0, 2.6fr) 90px 140px 150px minmax(0, 1.4fr);

        gap: 12px;

        align-items: center;

        padding: 0 20px;
    }

    .fila-cabecera {
        padding-top: 12px;
        padding-bottom: 12px;

        color: #7b91bc;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .fila-curso {
        padding-top: 13px;
        padding-bottom: 13px;

        border-top: 1px solid rgba(217, 230, 251, 0.8);

        font-size: 14px;
    }

    .fila-curso:first-of-type { border-top: 0; }

    .celda-curso {
        color: #19325f;
        font-weight: 600;
    }

    .celda-curso em {
        display: block;
        margin-top: 2px;
        color: #8ba0c8;
        font-size: 12px;
        font-style: normal;
        font-weight: 500;
    }

    .nota {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        min-width: 56px;
        height: 34px;
        padding: 0 10px;

        border-radius: 18px;

        background: #edf3fc;
        color: #6680b5;

        font-size: 16px;
        font-weight: 700;
    }

    .nota.aprobado { background: #d9f9e8; color: #079263; }
    .nota.reprobado { background: #ffe0e8; color: #d23a5f; }

    .pct { font-weight: 600; }
    .pct.ok { color: #0d9261; }
    .pct.advertencia { color: #c78d18; }
    .pct.peligro { color: #ec3e67; }

    .sin-dato { color: #a7b7d3; font-size: 13px; }

    /* ── Píldoras de estado ────────────────────────────────────────── */

    .pill {
        display: inline-flex;
        align-items: center;

        padding: 7px 15px;

        border-radius: 20px;

        font-size: 12px;
        font-weight: 600;

        white-space: nowrap;
    }

    .pill.aprobado { background: #d9f9e8; color: #079263; }
    .pill.reprobado { background: #ffe0e8; color: #ec3e67; }
    .pill.en-curso { background: #eaf1fb; color: #4f72b4; }
    .pill.presunto { background: #fff4d4; color: #c78d18; }

    /* ── Sin historial ─────────────────────────────────────────────── */

    .sin-historial {
        position: relative;
        z-index: 2;

        padding: 46px 30px;

        text-align: center;
    }

    .sin-historial i {
        display: block;
        margin-bottom: 16px;

        color: #a9bde4;

        font-size: 46px;
    }

    .sin-historial b {
        display: block;
        margin-bottom: 8px;

        color: #19325f;
        font-size: 18px;
    }

    .sin-historial p {
        max-width: 470px;
        margin: 0 auto;

        color: #7b91bc;
        font-size: 15px;
        line-height: 1.55;
    }

    @media (max-width: 900px) {
        .fila-cabecera { display: none; }

        .fila-curso { grid-template-columns: 1fr 1fr; row-gap: 10px; }
    }

    @media (max-width: 700px) {
        .hist-wrap {
            width: calc(100% - 40px);

            margin-top: 20px;
        }
    }

    @media (max-width: 480px) {
        .hist-wrap { width: calc(100% - 20px); }
    }
</style>

<div class="hist-wrap">
    <div class="hist-card">

        <div class="hist-head">
            <div class="hist-icon"><i class="fa-solid fa-folder-open"></i></div>

            <div class="hist-titulo">
                <h1>Mi Historial Académico</h1>
                <p>
                    {{ $estudiante->usuario->nombres }} {{ $estudiante->usuario->apellidos }}
                    @if ($estudiante->carrera)
                        · {{ $estudiante->carrera->nombre }}
                    @endif
                </p>
            </div>

            <a href="{{ route('estudiante.certificado') }}" class="btn-certificado" target="_blank" rel="noopener">
                <i class="fa-solid fa-file-pdf"></i>
                Certificado <small>(PDF)</small>
            </a>
        </div>

        @php $resumen = $datos['resumen']; @endphp

        <div class="resumen">
            <div class="resumen-item">
                <span>Promedio general</span>
                <b>{{ $resumen['promedio'] !== null ? number_format($resumen['promedio'], 2) : '—' }}</b>
                <em>Sobre {{ $resumen['cursos'] }} {{ $resumen['cursos'] === 1 ? 'curso' : 'cursos' }} con nota</em>
            </div>
            <div class="resumen-item">
                <span>Aprobados</span>
                <b>{{ number_format($resumen['aprobados']) }}</b>
                <em>Nota 6 o superior y sin excesos de faltas</em>
            </div>
            <div class="resumen-item">
                <span>Reprobados</span>
                <b>{{ number_format($resumen['reprobados']) }}</b>
                <em>Nota inferior a 6 o más del 30 % de faltas</em>
            </div>
            <div class="resumen-item">
                <span>Inasistencia media</span>
                <b>{{ $resumen['inasistencia'] !== null ? number_format($resumen['inasistencia'], 1).' %' : '—' }}</b>
                <em>Sobre los cursos ya cerrados</em>
            </div>
            <div class="resumen-item">
                <span>Cuatrimestres cursados</span>
                <b>{{ number_format($resumen['cuatrimestres']) }}</b>
                <em>
                    @if ($datos['enCurso']->isNotEmpty())
                        {{ $datos['enCurso']->count() }} {{ $datos['enCurso']->count() === 1 ? 'curso' : 'cursos' }} aun en marcha
                    @else
                        Todos con nota registrada
                    @endif
                </em>
            </div>
        </div>

        @if ($datos['periodos']->isEmpty() && $datos['enCurso']->isEmpty())
            <div class="sin-historial">
                <i class="fa-regular fa-file-lines"></i>
                <b>Todavía no hay nada registrado en tu historial</b>
                <p>
                    Tu historial se llena solo: cada nota que registre un profesor
                    y cada clase con asistencia aparece aquí. Por ahora no hay
                    notas cargadas, así que el certificado sale en blanco.
                </p>
            </div>
        @else
            <div class="leyenda">
                <div class="chip ok"><i class="fa-solid fa-check"></i> Menos del 25 % de inasistencia</div>
                <div class="chip aviso"><i class="fa-solid fa-triangle-exclamation"></i> Entre 25 % y 30 %</div>
                <div class="chip peligro"><i class="fa-solid fa-circle-xmark"></i> Más del 30 %</div>
                <span class="leyenda-nota">Mismos límites que usa el módulo del profesor.</span>
            </div>

            @foreach ($datos['periodos'] as $periodo)
                <div class="periodo">
                    <div class="periodo-head">
                        <span class="periodo-codigo">{{ $periodo['codigo'] }}</span>
                        <span class="periodo-fechas">
                            {{ $periodo['cuatrimestre']->fecha_inicio->format('d/m/Y') }}
                            a
                            {{ $periodo['cuatrimestre']->fecha_fin->format('d/m/Y') }}
                        </span>
                        <span class="periodo-vacio">
                            Promedio {{ $periodo['promedio'] !== null ? number_format($periodo['promedio'], 2) : '—' }}
                            · {{ $periodo['aprobados'] }} aprobado{{ $periodo['aprobados'] === 1 ? '' : 's' }}
                            @if ($periodo['reprobados'] > 0)
                                · {{ $periodo['reprobados'] }} reprobado{{ $periodo['reprobados'] === 1 ? '' : 's' }}
                            @endif
                        </span>
                    </div>

                    <div class="fila-cabecera">
                        <div>Curso</div>
                        <div>Nota</div>
                        <div>Inasistencia</div>
                        <div>Estado</div>
                        <div>Observaciones</div>
                    </div>

                    @foreach ($periodo['cursos'] as $curso)
                        @php
                            // Todo sale del veredicto que ya dio el servicio de
                            // reglas. No se vuelve a comparar la nota con el 6 aca:
                            // un 9 con 40 % de faltas es reprobado, y pintarlo de
                            // aprobado contradiria lo que ve el alumno en sus notas.
                            $pildora = match ($curso['estado']) {
                                'Reprobado (presunto)' => ['presunto', 'Reprobado (presunto)'],
                                'En curso' => ['en-curso', 'En curso'],
                                'Aprobado' => ['aprobado', 'Aprobado'],
                                default => ['reprobado', 'Reprobado'],
                            };
                        @endphp
                        <div class="fila-curso">
                            <div class="celda-curso">{{ $curso['curso'] }}</div>

                            <div>
                                @if ($curso['nota'] === null)
                                    <span class="nota">—</span>
                                @else
                                    <span
                                        class="nota {{ $pildora[0] === 'aprobado' ? 'aprobado' : 'reprobado' }}"
                                    >{{ $curso['nota'] }}</span>
                                @endif
                            </div>

                            <div>
                                @if ($curso['inasistencia'] > 0)
                                    <span class="pct {{ $curso['alerta'] }}">{{ number_format($curso['inasistencia'], 1) }} %</span>
                                @else
                                    <span class="sin-dato">Sin registro</span>
                                @endif
                            </div>

                            <div>
                                <span class="pill {{ $pildora[0] }}">{{ $pildora[1] }}</span>
                            </div>

                            <div class="sin-dato">{{ $curso['observaciones'] ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            @if ($datos['enCurso']->isNotEmpty())
                <div class="periodo">
                    <div class="periodo-head">
                        <span class="periodo-codigo">
                            {{ 'Q'.str_pad((int) $datos['enCursoCuatrimestre']->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="periodo-fechas">
                            {{ $datos['enCursoCuatrimestre']->fecha_inicio->format('d/m/Y') }}
                            a
                            {{ $datos['enCursoCuatrimestre']->fecha_fin->format('d/m/Y') }}
                        </span>
                        <span class="periodo-vacio">En curso · aún sin nota</span>
                    </div>

                    <div class="fila-cabecera">
                        <div>Curso</div>
                        <div>Nota</div>
                        <div>Inasistencia</div>
                        <div>Estado</div>
                        <div>Observaciones</div>
                    </div>

                    @foreach ($datos['enCurso'] as $curso)
                        @php
                            // Sin nota cargada, pero el veredicto ya puede estar
                            // perdido por inasistencia: se dice lo mismo que el
                            // servicio, no "en curso" a toda costa.
                            $pildora = match ($curso['estado']) {
                                'Reprobado (presunto)' => ['presunto', 'Reprobado (presunto)'],
                                'En curso' => ['en-curso', 'En curso'],
                                'Aprobado' => ['aprobado', 'Aprobado'],
                                default => ['reprobado', 'Reprobado'],
                            };
                        @endphp
                        <div class="fila-curso">
                            <div class="celda-curso">
                                {{ $curso['curso'] }}
                                <em>Cursando</em>
                            </div>

                            <div><span class="nota">—</span></div>

                            <div>
                                @if ($curso['inasistencia'] > 0)
                                    <span class="pct {{ $curso['alerta'] }}">{{ number_format($curso['inasistencia'], 1) }} %</span>
                                @else
                                    <span class="sin-dato">Sin registro</span>
                                @endif
                            </div>

                            <div><span class="pill {{ $pildora[0] }}">{{ $pildora[1] }}</span></div>

                            <div class="sin-dato">—</div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
