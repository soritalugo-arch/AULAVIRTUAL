@extends('layouts.app')

@section('titulo', 'Notas — ' . $curso->nombre)

@section('menu_extra')
    <li><a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a></li>
@endsection

@section('contenido')



<div class="cursos-wrap">

    <section class="courses-panel">

        {{-- Encabezado --}}
        <div class="notas-header">

            <div>

                <h1>Notas — {{ $curso->nombre }}</h1>

                <p class="subtitulo">Cuatrimestre #{{ $cuatrimestre->id_cuatrimestre }}
                    ({{ $cuatrimestre->fecha_inicio }} — {{ $cuatrimestre->fecha_fin }})</p>

            </div>

            <a href="{{ route('profesor.asistencia', $curso->id_curso) }}" class="btn-extra btn-green">
                <i class="fa-solid fa-calendar-check"></i>
                Ir a Asistencia
            </a>

        </div>

        {{-- Selector de cuatrimestre --}}
        <div class="cuatrimestre-bar">

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
                           href="{{ route('profesor.notas', $curso->id_curso) }}?cuatrimestre={{ $c->id_cuatrimestre }}">
                            <span class="sel-opt-text">
                                <span class="sel-opt-title">Cuatrimestre #{{ $c->id_cuatrimestre }}</span>
                                <span class="sel-opt-sub">{{ $c->fecha_inicio }} — {{ $c->fecha_fin }}</span>
                            </span>
                            <i class="fa-solid fa-check sel-opt-check"></i>
                        </a>
                    @endforeach
                </div>

            </div>

        </div>

        {{-- Mensajes --}}
        @if(session('success'))
            <div class="alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Si una parcial se sale de la escala (o falta el alumno), el profesor
             tiene que saber qué pasó: sin esto la página se recarga en silencio
             y parece que se guardó. --}}
        @if($errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    No se pudieron guardar las notas:
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
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

        <p class="registro-clases" style="margin:-10px 0 18px;text-align:left">
            Cada parcial vale <strong>25 puntos</strong> sobre 100. Se captura del 0 al 25 y el
            <strong>acumulado</strong> y el <strong>promedio</strong> salen solos; se aprueba con 60 puntos.
        </p>

        @if($estudiantes->isEmpty())

            <div class="empty-state">
                No hay estudiantes inscritos en este curso.
            </div>

        @else

        <form method="POST" action="{{ route('profesor.notas.guardar') }}">
            @csrf
            <input type="hidden" name="id_curso"         value="{{ $curso->id_curso }}">
            <input type="hidden" name="id_cuatrimestre"  value="{{ $cuatrimestre->id_cuatrimestre }}">

        <div class="barra-notas">

                <div class="buscador-notas" data-buscador>

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        placeholder="Buscar un alumno por nombre..."
                        data-buscar
                        aria-label="Buscar un alumno por nombre"
                    >

                    <button type="button" class="limpiar-busqueda" data-limpiar title="Limpiar búsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>

                <p class="registro-clases" style="margin:0">
                    Se han registrado <strong>{{ $clasesRegistradas }}</strong> de <strong>{{ $totalClases }}</strong> clases programadas.
                </p>

            </div>

            <div class="table-slot">

                <table data-tabla-notas>

                    <thead>

                        <tr>

                            <th class="ordenable" data-orden="nombre">
                                ESTUDIANTE<span class="flecha"><i class="fa-solid fa-sort"></i></span>
                            </th>

                            <th>% FALTAS</th>

                            <th>ESTADO</th>

                            <th>P1<br>(25 pts)</th>
                            <th>P2<br>(25 pts)</th>
                            <th>P3<br>(25 pts)</th>
                            <th>P4<br>(25 pts)</th>

                            <th class="ordenable" data-orden="promedio">
                                ACUMULADO<span class="flecha"><i class="fa-solid fa-sort"></i></span>
                            </th>

                            <th class="ordenable" data-orden="promedio">
                                PROMEDIO<span class="flecha"><i class="fa-solid fa-sort"></i></span>
                            </th>

                            <th>OBSERVACIÓN</th>

                        </tr>

                    </thead>

                    <tbody>

                        
                     @foreach($estudiantes as $i => $est)

                      @php
                        $estadoAcademicoReal = is_array($est) ? ($est['estado'] ?? 'en_curso') : ($est->estado ?? 'en_curso');
                        $estadoAcademicoReal = strtolower($estadoAcademicoReal);
    
                        $promedio = $est['nota'] !== null ? (float) $est['nota'] : null;
                        $parcialesLlenas = count(array_filter($est['parciales'], fn($p) => $p !== null && $p !== ''));

                        if ($est['alerta'] === 'peligro') {
                            $estadoAcademicoReal = 'reprobado';
                        } elseif ($promedio !== null) {
                            if ($promedio >= 6) {
                                $estadoAcademicoReal = 'aprobado';
                            } elseif ($parcialesLlenas === 4 && $promedio < 6) {
                                $estadoAcademicoReal = 'reprobado';
                            }
                        }

                        $claseEstado = match($estadoAcademicoReal) {
                            'aprobado'  => 'ok',
                            'reprobado' => 'fail',
                            default     => 'presunto',
                        };

                        $textoEstado = match($estadoAcademicoReal) {
                            'aprobado'  => 'Aprobado',
                            'reprobado' => 'Reprobado',
                            default     => 'En curso',
                        };

                        // --- SOLUCIÓN A LOS ERRORES DE VARIABLES INDEFINIDAS ---
    
                        // Variable para la fila
                        $claseFila = ''; 
    
                        // Variables para los colores de las inasistencias
                        $claseCasillaFaltas = ''; 
                        $claseFaltas = 'ok'; // Por defecto verde para 0%
                        $porcentaje = $est['porcentajeFaltas'] ?? 0;

                        // Lógica basada en tu comentario (25% amarillo, 30% rojo)
                        if ($est['alerta'] === 'peligro' || $porcentaje > 30) {
                            $claseCasillaFaltas = 'alerta-roja';   // Reemplaza con tu clase CSS original si es diferente
                            $claseFaltas = 'fail';                 // Reemplaza con tu clase CSS original si es diferente
                        } elseif ($est['alerta'] === 'advertencia' || $porcentaje >= 25) {
                            $claseCasillaFaltas = 'alerta-amarilla'; // Reemplaza con tu clase CSS original si es diferente
                            $claseFaltas = 'warn';                   // Reemplaza con tu clase CSS original si es diferente
                        }
                     @endphp

                        <input type="hidden" name="notas[{{ $i }}][id_estudiante]" value="{{ $est['id'] }}">

                        <tr
                            class="{{ $claseFila }}"
                            data-nombre="{{ $est['nombre'] }}"
                            data-promedio="{{ $est['nota'] !== null ? (float) $est['nota'] : -1 }}"
                        >

                            {{-- Nombre --}}
                            <td>

                                <span class="nombre">{{ $est['nombre'] }}</span>

                                @if($est['alerta'] === 'peligro')
                                    <span class="nombre-warn peligro">
                                        {{ $cuatrimestreTerminado ? 'Pierde la materia' : 'En riesgo — superó el 30% de faltas' }}
                                    </span>
                                @elseif($est['alerta'] === 'advertencia')
                                    <span class="nombre-warn advertencia">Cerca del limite</span>
                                @endif

                            </td>

                            {{-- % Faltas: la casilla misma se pinta de amarillo al25 %
                                 y se envuelve en rojo al pasar el 30 %. --}}
                            <td class="faltas-cell {{ $claseCasillaFaltas }}">

                                <span class="absence {{ $claseFaltas }}">
                                    {{ $est['porcentajeFaltas'] }}%
                                    <span class="absence-detalle">({{ $est['faltas'] }}/{{ $est['totalClases'] }})</span>
                                </span>

                            </td>

                           {{-- Estado --}}
                            <td>
                                <span class="status {{ $claseEstado }}" data-estado>
                                    {{ $textoEstado }}
                                    </span>
                            </td>

                            {{-- Cuatro parciales de 25 puntos cada una. El acumulado y el promedio de
                                 las dos columnas siguientes salen solos: en el
                                 navegador mientras escribe y otra vez al
                                 guardar, y manda el del servidor. --}}
                            @foreach (['parcial1', 'parcial2', 'parcial3', 'parcial4'] as $indice => $columna)
                                <td>
                                    <span class="parcial-label">Parcial {{ $indice + 1 }}</span>
                                    <input
                                        type="number"
                                        name="notas[{{ $i }}][{{ $columna }}]"
                                        value="{{ $est['parciales'][$indice] !== null ? number_format((float) $est['parciales'][$indice], 1, '.', '') : '' }}"
                                        min="0" max="25" step="0.1"
                                        class="parcial-input"
                                        data-parcial
                                        data-parcial-tope="25"
                                        placeholder="—"
                                        @disabled(!$puedeEditar)
                                    >
                                </td>
                            @endforeach

                            {{-- Acumulado sobre 100 y promedio sobre 10: los dos
                                 en verde cuando el alumno aprueba. --}}
                            @php
                                $promedio   = $est['nota'];
                                $acumulado  = $est['acumulado'] !== null
                                    ? (float) $est['acumulado']
                                    : null;
                                $claseNota  = match (true) {
                                    $promedio === null => '',
                                    $promedio >= 6 => 'aprobado',
                                    default => 'reprobado',
                                };
                            @endphp

                            {{-- Casilla de Acumulado --}}
<td class="acumulado-cell" data-label="Acumulado">
    <span class="acumulado {{ $claseNota ?? '' }}" data-acumulado>
        {{ isset($acumulado) && $acumulado !== null ? number_format($acumulado, 2) : '—' }}
    </span>
</td>

{{-- Casilla de Promedio --}}
<td class="promedio-cell" data-label="Promedio">
    <span class="promedio {{ $claseNota ?? '' }}" data-promedio>
        {{ isset($promedio) && $promedio !== null ? number_format((float) $promedio, 2) : '—' }}
    </span>
</td>

                            {{-- Celda de Observaciones --}}
                                <td class="observaciones-cell">
                                    <div class="observacion-wrapper">
                                        <textarea 
                                            name="notas[{{ $i }}][observaciones]" 
                                            class="input-observacion" 
                                            placeholder="Añadir nota..." 
                                            rows="1" 
                                            data-autogrow>{{ $est['observaciones'] ?? '' }}</textarea>
                                    </div>
                                </td>

                        </tr>

                        @endforeach

                        {{-- Aparece solo si la búsqueda no deja a nadie en la tabla. --}}
                        <tr data-sin-resultados hidden>
                            <td colspan="10">
                                <div class="vacio-busqueda">
                                    Ningún alumno coincide con esa búsqueda.
                                </div>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            {{-- Guardar --}}
            <div class="form-actions">

                @if($puedeEditar)
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar Notas
                    </button>
                @else
                    <p style="color:#7a8db5;font-size:14px;font-weight:600;margin:0">
                        <i class="fa-solid fa-circle-info" style="margin-right:6px"></i>
                        Este período no está en cursado: las notas solo se guardan cuando la rectora lo abre.
                    </p>
                @endif

            </div>

        </form>

        @endif

    </section>

</div>



@endsection

@push('styles')
    @vite('resources/css/profesor/notas.css')
@endpush

@push('scripts')
    @vite('resources/js/profesor/notas.js')
@endpush
