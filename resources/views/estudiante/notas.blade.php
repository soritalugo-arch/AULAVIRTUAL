@extends('layouts.app')

@section('titulo', 'Mis Notas y Asistencia')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a></li>
    <li><a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a></li>
    <li><a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a></li>
    <li><a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a></li>
@endsection

@section('contenido')



<div class="notas-wrap">

    @if($matriculacionAbierta)
        <div style="background:#e8f0ff;border:1px solid #c5d8fa;border-radius:16px;color:#2f55c4;padding:16px 20px;margin-bottom:22px;display:flex;align-items:center;gap:12px;font-size:15px;font-weight:600">
            <i class="fa-solid fa-circle-info" style="font-size:18px"></i>
            <span>
                Este cuatrimestre está en período de matrícula: las clases aún no comienzan.
                Las notas aparecerán aquí cuando la rectora dé inicio al cursado.
            </span>
        </div>
    @endif

    @php
        $mapaIconos = [
            'programación' => 'fa-code',
            'base de datos' => 'fa-database',
            'base' => 'fa-database',
            'matemá' => 'fa-calculator',
            'cálculo' => 'fa-calculator',
            'diseño' => 'fa-pen-ruler',
            'marketing' => 'fa-bullhorn',
            'turismo' => 'fa-plane',
            'enfermería' => 'fa-user-nurse',
            'electrónica' => 'fa-microchip',
            'instalaciones' => 'fa-bolt',
            'inglés' => 'fa-language',
            'emprendimiento' => 'fa-rocket',
            'anatomía' => 'fa-heart-pulse',
            'contabilidad' => 'fa-coins',
            'ofimática' => 'fa-file-lines',
            'redes' => 'fa-globe',
            'sistema' => 'fa-gear',
            'expresión' => 'fa-comment-dots',
            'física' => 'fa-atom',
            'química' => 'fa-flask',
            'economía' => 'fa-chart-line',
        ];

        $iconoCurso = function (string $nombre) use ($mapaIconos): string {
            $nombre = mb_strtolower($nombre);
            foreach ($mapaIconos as $clave => $icono) {
                if (mb_strpos($nombre, $clave) !== false) {
                    return $icono;
                }
            }
            return 'fa-book-open';
        };
    @endphp

    <!-- ================= CONTENIDO ================= -->

    <section class="grades-card">

        <!-- ENCABEZADO -->
        <div class="grades-header">
            <div class="grades-icon">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div class="grades-title">
                <h1>Mis Notas y Asistencia</h1>
                <p>Resumen de tu rendimiento por curso en el cuatrimestre actual.</p>
            </div>
        </div>

        <!-- INDICADORES -->
        <div class="attendance-legend">
            <div class="legend green">
                <div class="legend-icon">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
                <span>
                    Sin riesgo
                    <small>(menos del 25% de faltas)</small>
                </span>
            </div>
            <div class="legend yellow">
                <div class="legend-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <span>
                    Atención
                    <small>(entre 25% y 30% de faltas)</small>
                </span>
            </div>
            <div class="legend red">
                <div class="legend-icon">
                    <i class="fa-solid fa-exclamation"></i>
                </div>
                <span>
                    {{ $cuatrimestreTerminado ? 'Perdiste la materia' : 'En riesgo de perderla' }}
                    <small>({{ $cuatrimestreTerminado ? 'más del 30% de faltas' : 'más del 30% de lo dictado' }})</small>
                </span>
            </div>
        </div>

        @if($resumen->isEmpty())
            <div class="empty-state">
                No tienes cursos inscritos con datos de notas o asistencia.
            </div>
        @else

        <!-- TABLA -->
        <div class="grades-table">

            <!-- CABECERA -->
            <div class="table-header">
                <div>CURSO</div>
                <div>P1</div>
                <div>P2</div>
                <div>P3</div>
                <div>P4</div>
                <div>PROMEDIO</div>
                <div>% FALTAS</div>
                <div>ESTADO</div>
                <div>PROMEDIO DEL CURSO</div>
                <div>OBSERVACIÓN DEL PROFESOR</div>
            </div>

            @foreach($resumen as $item)

                @php
                    $icono     = $iconoCurso($item['curso']);
                    $tieneNota = ! is_null($item['nota']);

                    $badgeFaltas = match ($item['alerta']) {
                        'peligro'     => 'red',
                        'advertencia' => 'yellow',
                        default       => '',
                    };

                    $estadoClass = match ($item['estado']) {
                        'Aprobado'  => 'ok',
                        'Reprobado' => 'fail',
                        'Reprobado (presunto)' => 'fail',
                        default     => '',
                    };
                @endphp

                <div class="course-row">

                    <!-- CURSO -->
                    <div class="course">
                        <div class="course-icon">
                            <i class="fa-solid {{ $icono }}"></i>
                        </div>
                        <div class="course-info">
                            <strong>{{ $item['curso'] }}</strong>
                            <span>Cuatrimestre #{{ $item['cuatrimestre'] }}</span>
                        </div>
                    </div>

                    <!-- Las cuatro parciales de 25 % y el promedio que sale de ellas -->
                    @foreach (['parcial1', 'parcial2', 'parcial3', 'parcial4'] as $indice => $columna)
                        <div class="parcial" data-label="P{{ $indice + 1 }}">
                            @if ($item['parciales'][$indice] !== null)
                                <span class="parcial-valor">{{ number_format((float) $item['parciales'][$indice], 1) }}</span>
                            @else
                                <span class="parcial-valor vacio">—</span>
                            @endif
                        </div>
                    @endforeach

                    <!-- PROMEDIO (agregado data-label) -->
                    <div class="grade" data-label="PROMEDIO">
                        @if($tieneNota)
                            <div class="grade-value {{ $item['nota'] >= 6 ? 'pass' : 'fail' }}">
                                {{ number_format((float) $item['nota'], 2) }}
                            </div>
                        @else
                            <div class="grade-value">—</div>
                            <em>Sin nota aun</em>
                        @endif
                    </div>

                    <!-- FALTAS (agregado data-label) -->
                    <div class="absences" data-label="% FALTAS">
                        <span class="absence-badge {{ $badgeFaltas }}">
                            {{ $item['porcentajeFaltas'] }}%
                        </span>
                        <small>({{ $item['clasesRegistradas'] }} de {{ $item['totalClases'] }} clases)</small>
                    </div>

                    <!-- ESTADO (agregado data-label) -->
                    <div class="status {{ $estadoClass }}" data-label="ESTADO">
                        <span>{{ $item['estado'] }}</span>
                    </div>

                    <!-- PROMEDIO (agregado data-label) -->
                    <div class="average" data-label="PROMEDIO">
                        @if(! is_null($item['promedioCurso']))
                            <span>{{ $item['promedioCurso'] }}</span>
                            <em>Promedio del curso</em>
                        @else
                            <span>—</span>
                            <em>Sin datos</em>
                        @endif
                    </div>

                    <!-- OBSERVACIÓN (agregado data-label) -->
                    <div class="observation" data-label="OBSERVACIÓN">
                        <span>{{ $item['observaciones'] ?? '—' }}</span>
                    </div>

                </div>
            @endforeach

        </div>
        @endif
    </section>
</div>

@endsection

@push('styles')
    @vite('resources/css/estudiante/notas.css')
@endpush