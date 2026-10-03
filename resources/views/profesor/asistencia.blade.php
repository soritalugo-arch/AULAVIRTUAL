@extends('layouts.app')

@section('titulo', 'Asistencia — ' . $curso->nombre)

@section('menu_extra')
    <li><a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a></li>
@endsection

@section('contenido')



<div class="cursos-wrap">

    <section class="courses-panel">

        {{-- Encabezado --}}
        <div class="asistencia-header">

            <div>

                <h1>Asistencia — {{ $curso->nombre }}</h1>

                <p class="subtitulo">Cuatrimestre #{{ $cuatrimestre->id_cuatrimestre }}
                    ({{ $cuatrimestre->fecha_inicio }} — {{ $cuatrimestre->fecha_fin }})</p>

            </div>

            <a href="{{ route('profesor.notas', $curso->id_curso) }}" class="btn-extra btn-blue">
                <i class="fa-solid fa-file-lines"></i>
                Ir a Notas
            </a>

        </div>

        {{-- Selección de cuatrimestre y fecha de la clase --}}
        <div class="fecha-toolbar">

            <label for="select-cuatrimestre">
                <i class="fa-solid fa-layer-group"></i>
                Cuatrimestre:
            </label>

            <div class="sel" data-sel>

                <button type="button" class="sel-trigger" id="select-cuatrimestre" data-sel-toggle aria-haspopup="listbox">
                    <span class="sel-trigger-icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span class="sel-trigger-text">
                        <span class="sel-trigger-title">Cuatrimestre #{{ $cuatrimestre->id_cuatrimestre }}</span>
                        <span class="sel-trigger-sub">{{ $cuatrimestre->fecha_inicio }} — {{ $cuatrimestre->fecha_fin }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down sel-chevron"></i>
                </button>

                <div class="sel-menu" role="listbox">
                    @foreach($cuatrimestres as $c)
                        <a class="sel-option {{ $c->id_cuatrimestre === $cuatrimestre->id_cuatrimestre ? 'is-active' : '' }}"
                           role="option"
                           href="{{ route('profesor.asistencia', $curso->id_curso) }}?cuatrimestre={{ $c->id_cuatrimestre }}&amp;fecha={{ $fecha }}">
                            <span class="sel-opt-text">
                                <span class="sel-opt-title">Cuatrimestre #{{ $c->id_cuatrimestre }}</span>
                                <span class="sel-opt-sub">{{ $c->fecha_inicio }} — {{ $c->fecha_fin }}</span>
                            </span>
                            <i class="fa-solid fa-check sel-opt-check"></i>
                        </a>
                    @endforeach
                </div>

            </div>

            <span class="toolbar-sep" aria-hidden="true"></span>

            <label for="fecha-clase">
                <i class="fa-solid fa-calendar-day"></i>
                Fecha de la clase:
            </label>

            @php
                $normDia = function ($v) {
                    return strtr(strtolower(trim($v)), [
                        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
                    ]);
                };
                $mapaDias  = ['domingo' => 0, 'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4, 'viernes' => 5, 'sabado' => 6];
                $diasClase = $horarios
                    ->map(fn($h) => $mapaDias[$normDia($h->dia_semana)] ?? null)
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(',');
            @endphp

            <div class="cal" data-cal
                 data-url="{{ route('profesor.asistencia', $curso->id_curso) }}"
                 data-cuatrimestre="{{ $cuatrimestre->id_cuatrimestre }}"
                 data-fecha="{{ $fecha }}"
                 data-min="{{ $cuatrimestre->fecha_inicio->toDateString() }}"
                 data-max="{{ $cuatrimestre->fecha_fin->toDateString() }}"
                 data-dias="{{ $diasClase }}">

                <button type="button" class="cal-trigger" id="fecha-clase" data-cal-toggle>
                    <i class="fa-solid fa-calendar-day"></i>
                    <span data-cal-label>{{ $fecha }}</span>
                    <i class="fa-solid fa-chevron-down cal-chevron"></i>
                </button>

                <div class="cal-panel">
                    <div class="cal-head">
                        <button type="button" class="cal-nav" data-cal-prev aria-label="Mes anterior">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="cal-title" data-cal-title></span>
                        <button type="button" class="cal-nav" data-cal-next aria-label="Mes siguiente">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="cal-dow">
                        <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                    </div>

                    <div class="cal-grid" data-cal-grid></div>

                    <div class="cal-foot">
                        <button type="button" class="cal-today" data-cal-today>
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                            Hoy
                        </button>
                        <span class="cal-hint" data-cal-hint>Elige un día para cargarlo</span>
                    </div>
                </div>

            </div>

            @if($horarios->isNotEmpty())
                <span class="horario-chip">
                    <i class="fa-solid fa-clock"></i>
                    {{ $horarios->map(fn($h) => $h->dia_semana . ' ' . substr($h->hora_inicio, 0, 5) . '–' . substr($h->hora_fin, 0, 5))->implode(', ') }}
                </span>
            @endif

        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Leyenda de faltas --}}
        <div class="legend">

            <span class="legend-item legend-safe">
                <i class="fa-solid fa-shield-halved"></i>
                Sin riesgo (menos del 25% de faltas)
            </span>

            <span class="legend-item legend-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Atención (entre 25% y 30% de faltas)
            </span>

            <span class="legend-item legend-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $cuatrimestreTerminado ? 'Pierde materia (más del 30% de faltas)' : 'En riesgo (más del 30% de lo dictado)' }}
            </span>

        </div>

        <p class="registro-clases">
            Se han registrado <strong>{{ $clasesRegistradas }}</strong> de <strong>{{ $totalClases }}</strong> clases programadas.
        </p>

        @if($estudiantes->isEmpty())

            <div class="empty-state">
                No hay estudiantes inscritos en este curso.
            </div>

        @else

        <form method="POST" action="{{ route('profesor.asistencia.guardar') }}">
            @csrf
            <input type="hidden" name="id_curso"         value="{{ $curso->id_curso }}">
            <input type="hidden" name="id_cuatrimestre"  value="{{ $cuatrimestre->id_cuatrimestre }}">
            <input type="hidden" name="fecha"            value="{{ $fecha }}">

            <div class="table-slot">

                <table>

                    <thead>

                        <tr>

                            <th>ESTUDIANTE</th>
                            <th>% FALTAS ACUMULADAS</th>
                            <th>ESTADO DE RIESGO</th>
                            <th>PRESENTE HOY</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($estudiantes as $est)

                        @php
                            $claseFila   = match($est['alerta']) {
                                'peligro'     => 'row-peligro',
                                'advertencia' => 'row-advertencia',
                                default       => '',
                            };
                            $claseFaltas = match($est['alerta']) {
                                'peligro'     => 'danger',
                                'advertencia' => 'warning',
                                default       => '',
                            };
                        @endphp

                        <tr class="{{ $claseFila }}">

                            {{-- Nombre --}}
                            <td data-label="Estudiante">

                                <span class="nombre">{{ $est['nombre'] }}</span>

                                @if($est['alerta'] === 'peligro')
                                    <span class="nombre-warn peligro">
                                        {{ $cuatrimestreTerminado ? 'Pierde la materia — superó el 30% de faltas' : 'En riesgo — superó el 30% de lo dictado' }}
                                    </span>
                                @elseif($est['alerta'] === 'advertencia')
                                    <span class="nombre-warn advertencia">Cerca del límite — comuníquese con el estudiante</span>
                                @endif

                            </td>

                            {{-- % Faltas acumuladas --}}
                            <td data-label="Faltas Acumuladas" class="cell-center">

                                <span class="absence {{ $claseFaltas }}">
                                    {{ $est['porcentajeFaltas'] }}%
                                    <span class="absence-detalle">({{ $est['faltas'] }} de {{ $est['totalClases'] }} clases)</span>
                                </span>

                            </td>

                            {{-- Estado de riesgo --}}
                            <td data-label="Estado de Riesgo" class="cell-center">

                                @if($est['alerta'] === 'peligro')
                                    <span class="badge-estado badge-perdida">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        {{ $cuatrimestreTerminado ? 'Pérdida de materia' : 'En Riesgo' }}
                                    </span>
                                @elseif($est['alerta'] === 'advertencia')
                                    <span class="badge-estado badge-alerta">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Alertar al estudiante
                                    </span>
                                @else
                                    <span class="badge-estado badge-normal">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Normal
                                    </span>
                                @endif

                            </td>

                            {{-- Presente hoy --}}
                            <td data-label="Asistencia de Hoy" class="cell-center">

                                <label class="check-wrap {{ $est['presente'] ? '' : 'ausente' }}">

                                    <input
                                        type="checkbox"
                                        name="presentes[]"
                                        value="{{ $est['id'] }}"
                                        {{ $est['presente'] ? 'checked' : '' }}
                                        class="check-presente"
                                    >

                                    <span>{{ $est['presente'] ? 'Presente' : 'Ausente' }}</span>

                                </label>

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{-- Guardar --}}
            <div class="form-actions">

                @if($puedeEditar)
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar Asistencia
                    </button>
                @else
                    <p style="color:#7a8db5;font-size:14px;font-weight:600;margin:0">
                        <i class="fa-solid fa-circle-info" style="margin-right:6px"></i>
                        Este período no está en cursado: la asistencia solo se guarda cuando la rectora lo abre.
                    </p>
                @endif

            </div>

        </form>

        @endif

    </section>

</div>



@endsection

@push('styles')
    @vite('resources/css/profesor/asistencia.css')
@endpush

@push('scripts')
    @vite('resources/js/profesor/asistencia.js')
@endpush
