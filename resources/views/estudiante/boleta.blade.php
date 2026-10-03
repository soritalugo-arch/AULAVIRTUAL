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



<div class="boleta-wrap">

    <div class="boleta-acciones">
        <button type="button" class="btn-imprimir" data-print>
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
                            {{ number_format((float) $curso['nota'], 2) }}
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

@push('styles')
    @vite('resources/css/estudiante/boleta.css')
@endpush

@push('scripts')
    @vite('resources/js/estudiante/boleta.js')
@endpush