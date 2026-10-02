@extends('layouts.app')

@section('titulo', 'Boleta de notas · Lapso académico')

{{-- La boleta usa todo el ancho como el historial (misma fórmula del navbar). --}}
@section('clase_main', 'hist-main')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a></li>
    <li><a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a></li>
    <li><a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.boleta', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a></li>
    <li><a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a></li>
@endsection

@section('contenido')

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    .hist-main { max-width: none; padding-inline: 0; }

    body {
        color: #19366d;
        background:
            radial-gradient(circle at 5% 15%, rgba(194, 217, 255, 0.65), transparent 32%),
            radial-gradient(circle at 95% 90%, rgba(190, 214, 255, 0.65), transparent 38%),
            #f3f7fd;
    }

    .boleta-wrap {
        width: min(900px, calc(100% - 40px));
        margin: 35px auto 55px;
    }

    /* ── Botones de acción (solo en pantalla) ─────────────────────── */

    .boleta-acciones {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 22px;
    }

    .btn-imprimir {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        height: 44px;
        padding: 0 22px;
        border: none;
        border-radius: 22px;
        background: linear-gradient(100deg, #5862e5, #668cf0);
        color: white;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 5px 12px rgba(91, 111, 224, 0.25);
        cursor: pointer;
        transition: 0.2s ease;
    }

    .btn-imprimir:hover { transform: translateY(-1px); }

    .btn-volver {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        height: 44px;
        padding: 0 22px;
        border-radius: 22px;
        border: 1.5px solid #c9d9fb;
        background: white;
        color: #4f72b4;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        transition: 0.2s ease;
    }

    .btn-volver:hover { background: #eaf1fb; }

    /* ── La boleta ────────────────────────────────────────────────── */

    .boleta {
        background: white;
        border: 1px solid #d9e6fb;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(74, 110, 177, 0.10);
        overflow: hidden;
    }

    .boleta-cabecera {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 26px 34px 20px;
        border-bottom: 1px solid #e2eaf9;
    }

    .boleta-logo {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .boleta-logo-icono {
        width: 48px;
        height: 48px;
        border-radius: 15px;
        background: linear-gradient(145deg, #e1ebff, #d3e1ff);
        color: #3b5cd6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .boleta-logo h1 {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 21px;
        font-weight: 700;
        color: #171d7d;
    }

    .boleta-logo p {
        color: #7a90bf;
        font-size: 12.5px;
    }

    .boleta-titulo {
        text-align: right;
    }

    .boleta-titulo h2 {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 17px;
        font-weight: 700;
        color: #24356e;
    }

    .boleta-titulo p {
        color: #7a90bf;
        font-size: 12.5px;
        margin-top: 4px;
    }

    .boleta-periodo {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        padding: 16px 34px;
        background: #f4f8ff;
        border-bottom: 1px solid #e2eaf9;
    }

    .boleta-chip {
        padding: 6px 14px;
        border-radius: 20px;
        background: #eaf1fb;
        color: #4f72b4;
        font-size: 13px;
        font-weight: 700;
    }

    .boleta-fechas { color: #6d86b8; font-size: 13px; }

    .boleta-chip-cerrado {
        margin-left: auto;
        padding: 5px 13px;
        border-radius: 20px;
        background: #e8f7ee;
        border: 1px solid #bfe8cd;
        color: #1d7a46;
        font-size: 12px;
        font-weight: 700;
    }

    /* ── Datos del estudiante ─────────────────────────────────────── */

    .boleta-datos {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        padding: 24px 34px;
        border-bottom: 1px solid #e2eaf9;
    }

    .boleta-dato label {
        display: block;
        color: #9aabd0;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 4px;
    }

    .boleta-dato span {
        color: #24356e;
        font-size: 14.5px;
        font-weight: 700;
    }

    /* ── Tabla de materias ────────────────────────────────────────── */

    .boleta-tabla-titulo {
        padding: 20px 34px 12px;
        color: #4f72b4;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .boleta-fila {
        display: grid;
        grid-template-columns: minmax(0, 2.6fr) 80px 120px 150px minmax(0, 1.4fr);
        gap: 12px;
        align-items: center;
        padding: 12px 34px;
        border-top: 1px solid #eaf1fb;
    }

    .boleta-fila--head {
        background: #f8faff;
        color: #7a90bf;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .boleta-fila--head div:first-child { color: #7a90bf; }

    .boleta-curso { color: #24356e; font-size: 14px; font-weight: 600; }

    .boleta-nota {
        display: inline-block;
        min-width: 42px;
        text-align: center;
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 800;
    }

    .boleta-nota--aprobado { background: #e8f7ee; color: #1d7a46; }
    .boleta-nota--reprobado { background: #fdeeee; color: #b42318; }

    .boleta-pct { color: #24356e; font-size: 13px; font-weight: 700; }
    .boleta-pct--sin { color: #9aabd0; font-weight: 500; }

    .boleta-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 700;
    }

    .boleta-pill--aprobado { background: #e8f7ee; color: #1d7a46; }
    .boleta-pill--reprobado { background: #fdeeee; color: #b42318; }
    .boleta-pill--presunto { background: #fff4d4; color: #854d0e; }

    .boleta-obs { color: #7a90bf; font-size: 12.5px; }

    /* ── Resumen ──────────────────────────────────────────────────── */

    .boleta-resumen {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 22px 34px;
        border-top: 1px solid #e2eaf9;
        background: #f8faff;
    }

    .resumen-chip {
        background: white;
        border: 1px solid #e0e8f5;
        border-radius: 16px;
        padding: 12px 18px;
        min-width: 140px;
    }

    .resumen-chip label {
        display: block;
        color: #9aabd0;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .resumen-chip span {
        display: block;
        color: #24356e;
        font-size: 19px;
        font-weight: 800;
        margin-top: 3px;
    }

    .resumen-chip span small { color: #7a90bf; font-size: 12px; font-weight: 600; }

    /* ── Pie ──────────────────────────────────────────────────────── */

    .boleta-pie {
        padding: 18px 34px 24px;
        color: #9aabd0;
        font-size: 11.5px;
        line-height: 1.6;
    }

    /* ── Impresión: solo queda la boleta ──────────────────────────── */

    @media print {
        body {
            background: white;
        }

        header.navbar-card,
        .boleta-acciones {
            display: none !important;
        }

        .hist-main {
            display: block;
        }

        .boleta-wrap {
            width: 100%;
            margin: 0;
        }

        .boleta {
            border: none;
            box-shadow: none;
            border-radius: 0;
        }

        .boleta-cabecera {
            padding: 8px 0 16px;
        }

        .boleta-periodo,
        .boleta-datos {
            padding-left: 0;
            padding-right: 0;
        }

        .boleta-fila {
            padding-left: 0;
            padding-right: 0;
        }

        .boleta-resumen {
            padding-left: 0;
            padding-right: 0;
        }

        .boleta-pie {
            padding-left: 0;
            padding-right: 0;
        }
    }
</style>

<div class="boleta-wrap">

    <div class="boleta-acciones">
        <button type="button" class="btn-imprimir" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            Imprimir boleta
        </button>
        <a href="{{ route('estudiante.historial') }}" class="btn-volver">
            <i class="fa-solid fa-arrow-left"></i>
            Volver al historial
        </a>
    </div>

    <section class="boleta">

        <div class="boleta-cabecera">
            <div class="boleta-logo">
                <div class="boleta-logo-icono"><i class="fa-solid fa-graduation-cap"></i></div>
                <div>
                    <h1>AulaVirtual</h1>
                    <p>Boleta de notas del lapso académico</p>
                </div>
            </div>
            <div class="boleta-titulo">
                <h2>{{ $periodo['codigo'] }}</h2>
                <p>
                    {{ $cuatrimestre->fecha_inicio->format('d/m/Y') }}
                    al {{ $cuatrimestre->fecha_fin->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="boleta-periodo">
            <span class="boleta-chip">{{ $periodo['codigo'] }} · lapso académico</span>
            <span class="boleta-fechas">{{ $cuatrimestre->fecha_inicio->format('d/m/Y') }} – {{ $cuatrimestre->fecha_fin->format('d/m/Y') }}</span>
            <span class="boleta-chip-cerrado"><i class="fa-solid fa-lock"></i> Período cerrado</span>
        </div>

        <div class="boleta-datos">
            <div class="boleta-dato">
                <label>Estudiante</label>
                <span>{{ $estudiante->usuario->nombres }} {{ $estudiante->usuario->apellidos }}</span>
            </div>
            <div class="boleta-dato">
                <label>Cédula</label>
                <span>{{ $estudiante->cedula }}</span>
            </div>
            <div class="boleta-dato">
                <label>Carrera</label>
                <span>{{ $estudiante->carrera->nombre ?? '—' }}</span>
            </div>
        </div>

        <div class="boleta-tabla-titulo">Materias del lapso</div>

        <div class="boleta-fila boleta-fila--head">
            <div>Curso</div>
            <div>Nota</div>
            <div>Inasistencia</div>
            <div>Estado</div>
            <div>Observaciones</div>
        </div>

        @foreach ($periodo['cursos'] as $curso)
            @php
                $aprobado = $curso['estado'] === 'Aprobado';
                $presunto = $curso['estado'] === 'Reprobado (presunto)';
            @endphp
            <div class="boleta-fila">
                <div class="boleta-curso">{{ $curso['curso'] }}</div>
                <div>
                    @if ($curso['nota'] !== null)
                        <span class="boleta-nota {{ $aprobado ? 'boleta-nota--aprobado' : 'boleta-nota--reprobado' }}">
                            {{ $curso['nota'] }}
                        </span>
                    @else
                        <span class="boleta-nota--sin">—</span>
                    @endif
                </div>
                <div>
                    @if ($curso['inasistencia'] > 0)
                        <span class="boleta-pct">{{ number_format($curso['inasistencia'], 1) }} %</span>
                    @else
                        <span class="boleta-pct boleta-pct--sin">Sin registro</span>
                    @endif
                </div>
                <div>
                    <span class="boleta-pill {{ $aprobado ? 'boleta-pill--aprobado' : ($presunto ? 'boleta-pill--presunto' : 'boleta-pill--reprobado') }}">
                        {{ $curso['estado'] }}
                    </span>
                </div>
                <div class="boleta-obs">{{ $curso['observaciones'] ?: '—' }}</div>
            </div>
        @endforeach

        <div class="boleta-resumen">
            <div class="resumen-chip">
                <label>Promedio del lapso</label>
                <span>{{ $periodo['promedio'] !== null ? number_format($periodo['promedio'], 2) : '—' }}</span>
            </div>
            <div class="resumen-chip">
                <label>Aprobadas</label>
                <span>{{ $periodo['aprobados'] }}</span>
            </div>
            <div class="resumen-chip">
                <label>Reprobadas</label>
                <span>{{ $periodo['reprobados'] }}</span>
            </div>
            <div class="resumen-chip">
                <label>Materias</label>
                <span>{{ $periodo['total'] }}</span>
            </div>
        </div>

        <div class="boleta-pie">
            Documento generado el {{ now()->isoFormat('LL') }} por AulaVirtual. Boleta informativa del lapso académico:
            sus materias, notas, inasistencia y estado al cierre del período. El certificado oficial, con todas las
            materias de la carrera, se emite al completar el plan de estudios.
        </div>

    </section>

</div>

@endsection