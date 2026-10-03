@extends('layouts.app')

@section('titulo', 'Mi Historial Académico')

{{-- Esta página mide su tarjeta con la misma fórmula que el navbar, así que
     necesita el ancho completo en vez del límite de 1280px del <main>. --}}
@section('clase_main', 'hist-main')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a></li>
    <li><a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a></li>
    <li><a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a></li>
    <li><a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a></li>
@endsection

@section('contenido')



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

            @if ($esEgresado ?? false)
                <a href="{{ route('estudiante.certificado') }}" class="btn-certificado" target="_blank" rel="noopener">
                    <i class="fa-solid fa-file-pdf"></i>
                    Certificado <small>(PDF)</small>
                </a>
            @else
                <span class="btn-certificado btn-certificado--bloqueado" title="Se emite al completar todos los cursos de tu carrera">
                    <i class="fa-solid fa-file-pdf"></i>
                    Certificado <small>(al terminar la carrera)</small>
                </span>
            @endif
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
                        @if ($periodo['cuatrimestre']->estado === 'cerrado')
                            <a href="{{ route('estudiante.boleta', $periodo['cuatrimestre']->id_cuatrimestre) }}" class="btn-boleta" title="Boleta imprimible de este lapso académico">
                                <i class="fa-solid fa-print"></i>
                                Boleta
                            </a>
                        @endif
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
                            $pildora = match ($curso['estado']) {
                                'Reprobado (presunto)' => ['presunto', 'Reprobado (presunto)'],
                                'En curso' => ['en-curso', 'En curso'],
                                'Aprobado' => ['aprobado', 'Aprobado'],
                                default => ['reprobado', 'Reprobado'],
                            };
                        @endphp
                        <div class="fila-curso">
                            <div class="celda-curso">{{ $curso['curso'] }}
                                @if ($datos['repetidos']->contains($curso['id_curso']))
                                    <span class="badge-repetida" title="Cursada en más de un cuatrimestre">Repetida</span>
                                @endif
                            </div>

                            <div data-label="Nota">
                                @if ($curso['nota'] === null)
                                    <span class="nota">—</span>
                                @else
                                    <span
                                        class="nota {{ $pildora[0] === 'aprobado' ? 'aprobado' : 'reprobado' }}"
                                    >{{ number_format((float) $curso['nota'], 2) }}</span>
                                    {{-- Las cuatro parciales de 25 % de las que sale el promedio --}}
                                    @if ($curso['tiene_parciales'] ?? false)
                                        <small class="parciales">
                                            @foreach ($curso['parciales'] as $i => $parcial)
                                                P{{ $i + 1 }}: {{ $parcial !== null ? number_format((float) $parcial, 1) : '—' }}
                                            @endforeach
                                        </small>
                                    @endif
                                @endif
                            </div>

                            <div data-label="Inasistencia">
                                @if ($curso['inasistencia'] > 0)
                                    <span class="pct {{ $curso['alerta'] }}">{{ number_format($curso['inasistencia'], 1) }} %</span>
                                @else
                                    <span class="sin-dato">Sin registro</span>
                                @endif
                            </div>

                            <div data-label="Estado">
                                <span class="pill {{ $pildora[0] }}">{{ $pildora[1] }}</span>
                            </div>

                            <div class="sin-dato" data-label="Observaciones">{{ $curso['observaciones'] ?: '—' }}</div>
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
                                @if ($datos['repetidos']->contains($curso['id_curso']))
                                    <span class="badge-repetida" title="Cursada en más de un cuatrimestre">Repetida</span>
                                @endif
                                <em>Cursando</em>
                            </div>

                            <div data-label="Nota"><span class="nota">—</span></div>

                            <div data-label="Inasistencia">
                                @if ($curso['inasistencia'] > 0)
                                    <span class="pct {{ $curso['alerta'] }}">{{ number_format($curso['inasistencia'], 1) }} %</span>
                                @else
                                    <span class="sin-dato">Sin registro</span>
                                @endif
                            </div>

                            <div data-label="Estado"><span class="pill {{ $pildora[0] }}">{{ $pildora[1] }}</span></div>

                            <div class="sin-dato" data-label="Observaciones">—</div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

@endsection

@push('styles')
    @vite('resources/css/estudiante/historial.css')
@endpush