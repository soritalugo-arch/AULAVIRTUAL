@extends('layouts.app')

@section('titulo', 'Mis Cursos')

@section('menu_extra')
    <li>
        <a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a>
    </li>
@endsection

@section('contenido')

<style>
    /* =====================================================
       CONFIGURACIÓN GENERAL
    ===================================================== */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {

        font-family: Arial, Helvetica, sans-serif;

        min-height: 100vh;

        color: #19366d;

        background:
            radial-gradient(
                circle at 5% 15%,
                rgba(194, 217, 255, 0.65),
                transparent 32%
            ),

            radial-gradient(
                circle at 95% 90%,
                rgba(190, 214, 255, 0.65),
                transparent 38%
            ),

            #f3f7fd;

        overflow-x: hidden;
    }

    /* El main del layout no agrega padding propio en esta pagina */
    main {
        padding-left: 0;
        padding-right: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    /* Contenedor con margen lateral amplio (35px a cada lado) */
    .cursos-wrap {
        width: calc(100% - 90px);

        margin: 35px auto 50px;
    }


    /* =====================================================
       PANEL PRINCIPAL
    ===================================================== */

    .courses-panel {
        position: relative;

        min-height: 610px;

        padding: 48px 68px;

        overflow: hidden;

        background:
            linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.97),
                rgba(247, 251, 255, 0.95)
            );

        border: 1px solid #d8e6fb;

        border-radius: 31px;

        box-shadow:
            0 10px 30px rgba(70, 106, 175, 0.10);
    }


    /* Decoración inferior */

    .courses-panel::after {

        content: "";

        position: absolute;

        width: 650px;
        height: 250px;

        right: -170px;
        bottom: -150px;

        border-radius: 50%;

        background:
            rgba(187, 211, 253, 0.38);

        transform: rotate(-18deg);

        pointer-events: none;
    }


    /* =====================================================
       ENCABEZADO
    ===================================================== */

    .panel-header {
        position: relative;

        z-index: 2;

        margin-bottom: 42px;
    }

    .panel-header h1 {
        margin-bottom: 10px;

        color: #171d7d;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 38px;

        font-weight: 700;
    }

    .panel-header p {
        color: #7690c1;

        font-size: 19px;
    }


    /* =====================================================
       GRID DE CURSOS
    ===================================================== */

    .courses-grid {
        position: relative;

        z-index: 2;

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 26px;

        max-width: 1080px;
    }


    /* =====================================================
       TARJETA DE CURSO
    ===================================================== */

    .course-card {
        min-height: 168px;

        padding: 22px 26px;

        display: flex;

        flex-direction: column;

        justify-content: space-between;

        background:
            rgba(255, 255, 255, 0.93);

        border: 1px solid #e0eafa;

        border-radius: 22px;

        box-shadow:
            0 9px 25px rgba(74, 110, 177, 0.09);

        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .course-card:hover {

        transform: translateY(-3px);

        box-shadow:
            0 14px 30px rgba(74, 110, 177, 0.14);
    }


    /* =====================================================
       PARTE SUPERIOR DE LA TARJETA
    ===================================================== */

    .course-top {
        display: flex;

        align-items: center;

        gap: 18px;
    }


    /* =====================================================
       ICONO DEL CURSO
    ===================================================== */

    .course-icon {
        width: 62px;
        height: 62px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background:
            linear-gradient(
                145deg,
                #e1ebff,
                #d3e1ff
            );

        color: #6785dc;

        font-size: 24px;
    }


    /* =====================================================
       INFORMACIÓN DEL CURSO
    ===================================================== */

    .course-info {
        display: flex;

        flex-direction: column;

        gap: 6px;
    }

    .course-info h2 {
        color: #17408e;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 19px;

        font-weight: 700;

        line-height: 1.2;
    }

    .course-info p {
        color: #8298c3;

        font-size: 13px;
    }


    /* =====================================================
       BOTONES
    ===================================================== */

    .course-actions {
        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 12px;

        margin-left: 80px;
    }

    .btn-notes,
    .btn-attendance {
        height: 40px;

        border: none;

        border-radius: 20px;

        display: flex;
        align-items: center;
        justify-content: center;

        gap: 8px;

        color: white;

        font-size: 13px;

        font-weight: 700;

        text-decoration: none;

        cursor: pointer;

        transition: 0.2s ease;
    }

    .btn-notes i,
    .btn-attendance i {
        font-size: 13px;
    }

    /* NOTAS */

    .btn-notes {
        background:
            linear-gradient(
                100deg,
                #5578e6,
                #7198ef
            );

        box-shadow:
            0 7px 15px rgba(86, 121, 226, 0.23);
    }

    .btn-notes:hover {
        transform: translateY(-2px);

        box-shadow:
            0 10px 20px rgba(86, 121, 226, 0.30);
    }

    /* ASISTENCIA */

    .btn-attendance {
        background:
            linear-gradient(
                100deg,
                #3bb59e,
                #55cbb5
            );

        box-shadow:
            0 7px 15px rgba(60, 178, 156, 0.20);
    }

    .btn-attendance:hover {
        transform: translateY(-2px);

        box-shadow:
            0 10px 20px rgba(60, 178, 156, 0.27);
    }


    /* =====================================================
       ESTADO VACÍO
    ===================================================== */

    .empty-state {
        position: relative;

        z-index: 2;

        padding: 18px 24px;

        border-radius: 18px;

        background: #fff4d4;

        border: 1px solid #fde68a;

        color: #854d0e;

        font-size: 15px;
    }


    /* =====================================================
       RESPONSIVE
    ===================================================== */
    
    /* Prevenir que anchos fijos o paddings rompan el diseño */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    @media (max-width: 1200px) {
        .cursos-wrap {
            width: calc(100% - 60px);
        }
        .courses-panel {
            padding: 40px;
        }
        .course-actions {
            margin-left: 0;
        }
    }

    @media (max-width: 900px) {
        .cursos-wrap {
            width: calc(100% - 40px);
        }
        .courses-grid {
            /* Una sola columna en tablets/teléfonos grandes */
            grid-template-columns: 1fr;
        }
        
        .course-card {
            min-height: auto;
        }
        
        /* Reacomodar el botón en tablets para que no flote de forma extraña */
        .course-actions {
            margin-top: 20px;
            margin-left: 0;
            display: flex; /* En lugar de grid para distribuir mejor */
            gap: 12px;
        }
        
        .btn-notes, .btn-attendance {
            flex: 1; /* Ambos botones toman el 50% de la tarjeta */
        }
    }

    @media (max-width: 600px) {
        .cursos-wrap {
            width: 100%; /* Aprovechar toda la pantalla, sin márgenes laterales externos */
            margin-top: 0;
            margin-bottom: 0;
        }

        .courses-panel {
            padding: 24px 16px; 
            border-radius: 0; /* Quitar borde redondeado para aprovechar esquinas completas en móviles */
            border: none;
            box-shadow: none;
            min-height: auto; /* Dejar de forzar 610px */
        }
        
        /* Ocultar el adorno decorativo en móviles para evitar scroll horizontal fantasma */
        .courses-panel::after {
             display: none;
        }

        .panel-header h1 {
            font-size: 28px; 
        }

        .panel-header p {
            font-size: 15px;
            line-height: 1.4;
        }

        .courses-grid {
            gap: 16px; /* Menos espacio entre tarjetas */
        }

        .course-card {
            padding: 16px;
            min-height: auto; /* Dejar que la tarjeta crezca naturalmente */
        }

        .course-top {
            gap: 12px;
            align-items: flex-start; /* Alinear el texto e icono arriba */
        }

        .course-icon {
            width: 48px;
            height: 48px;
            font-size: 18px;
        }

        .course-info h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .course-info p {
            font-size: 12px;
        }

        .course-actions {
            margin-top: 16px;
            flex-direction: column; /* Apilar los botones en móviles estrechos */
            gap: 10px;
        }

        .btn-notes,
        .btn-attendance {
            height: 44px; /* Un poco más alto para tocar con el dedo (ley de Fitts) */
            font-size: 14px;
        }
    }
</style>

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
@endphp

<div class="cursos-wrap">

    <section class="courses-panel">

        <!-- TITULO -->

        <div class="panel-header">

            <h1>Mis Cursos</h1>

            <p>Selecciona un curso para gestionar notas o asistencia.</p>

        </div>

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

                    <div class="course-actions">

                        <a href="{{ route('profesor.notas', $curso->id_curso) }}" class="btn-notes">
                            <i class="fa-solid fa-file-lines"></i>
                            Notas
                        </a>

                        <a href="{{ route('profesor.asistencia', $curso->id_curso) }}" class="btn-attendance">
                            <i class="fa-solid fa-user-group"></i>
                            Asistencia
                        </a>

                    </div>

                </article>

            @endforeach

        </div>

        @endif

    </section>

</div>

@endsection