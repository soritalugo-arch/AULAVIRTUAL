@extends('layouts.app')

@section('titulo', 'Mis Cursos')

@section('menu_extra')
    <li>
        <a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a>
    </li>
@endsection

@section('contenido')



@php
    // Iconos por materia (solo visual)
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

    // Hora "bien redactada": 18:00:00 -> 6:00 pm
    $formatoHora = function (?string $hora): string {
        if (! $hora) {
            return '';
        }

        try {
            return \Carbon\Carbon::createFromFormat('H:i:s', $hora)->format('g:i a');
        } catch (\Throwable $e) {
            return $hora;
        }
    };

    // Durante la matrícula el profesor solo ve su horario, no a sus estudiantes.
    $enMatricula = $momento && $momento->estado === 'matriculacion';
@endphp

<div class="cursos-wrap">

    <section class="courses-panel">

        <!-- TITULO -->

        <div class="panel-header">

            <h1>Mis Cursos</h1>

            @if ($enMatricula)
                <p>La matrícula está abierta: por ahora solo ves el horario de tus cursos. Cuando el período esté en cursado verás a tus estudiantes y podrás calificar.</p>
            @else
                <p>Selecciona un curso para gestionar notas o asistencia.</p>
            @endif

        </div>

        @if (session('success'))
            <div class="flash-banner flash-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flash-banner flash-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($cursos->isEmpty())

            <div class="empty-state">
                No tienes cursos asignados.
            </div>

        @else

        <!-- CURSOS -->

        <div class="courses-grid">

            @foreach($cursos as $curso)

                <article class="course-card">

                    <div class="course-top">

                        <div class="course-icon">

                            <i class="fa-solid {{ $iconoCurso($curso->nombre) }}"></i>

                        </div>

                        <div class="course-info">

                            <h2>{{ $curso->nombre }}</h2>

                            <p>Cupo máximo: {{ $curso->limite_estudiantes }} estudiantes</p>

                        </div>

                    </div>

                    @if ($curso->horarios->isNotEmpty())
                    <div class="course-schedule">

                        @foreach ($curso->horarios as $horario)
                            <span class="schedule-chip">
                                <i class="fa-regular fa-clock"></i>
                                {{ $horario->dia_semana }} ·
                                {{ $formatoHora($horario->hora_inicio) }}
                                a {{ $formatoHora($horario->hora_fin) }}
                            </span>
                        @endforeach

                    </div>
                    @endif

                    <div class="course-actions">

                        @if ($enMatricula)
                            <div class="course-pending">
                                <i class="fa-solid fa-users-slash"></i>
                                Verás a tus estudiantes cuando el período esté en cursado.
                            </div>
                        @else
                            <a href="{{ route('profesor.notas', $curso->id_curso) }}" class="btn-notes">
                                <i class="fa-solid fa-file-lines"></i>
                                Notas
                            </a>

                            <a href="{{ route('profesor.asistencia', $curso->id_curso) }}" class="btn-attendance">
                                <i class="fa-solid fa-user-group"></i>
                                Asistencia
                            </a>
                        @endif

                    </div>

                </article>

            @endforeach

        </div>

        @endif

    </section>

</div>

@endsection

@push('styles')
    @vite('resources/css/profesor/mis-cursos.css')
@endpush