@extends('layouts.app')

@section('titulo', 'Asistencia — ' . $curso->nombre)

@section('menu_extra')
    <li><a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a></li>
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

        padding: 40px 44px;

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

    .asistencia-header {
        position: relative;

        z-index: 2;

        display: flex;

        align-items: flex-start;
        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        margin-bottom: 26px;
    }

    .asistencia-header h1 {
        margin-bottom: 7px;

        color: #171d7d;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 30px;

        font-weight: 700;
    }

    .asistencia-header .subtitulo {
        color: #7690c1;

        font-size: 15px;

        line-height: 1.5;
    }


    /* BOTÓN IR A NOTAS */

    .btn-extra {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        height: 42px;

        padding: 0 20px;

        border: none;

        border-radius: 21px;

        color: white;

        font-size: 13px;

        font-weight: 700;

        text-decoration: none;

        cursor: pointer;

        transition: 0.2s ease;
    }

    .btn-extra i {
        font-size: 13px;
    }

    .btn-blue {
        background:
            linear-gradient(
                100deg,
                #5578e6,
                #7198ef
            );

        box-shadow:
            0 7px 15px rgba(86, 121, 226, 0.23);
    }

    .btn-blue:hover {
        transform: translateY(-2px);

        box-shadow:
            0 10px 20px rgba(86, 121, 226, 0.30);
    }


    /* =====================================================
       SELECCIÓN DE FECHA
    ===================================================== */

    .fecha-toolbar {
        position: relative;

        z-index: 2;

        display: flex;

        align-items: center;

        gap: 16px;

        flex-wrap: wrap;

        margin-bottom: 24px;

        padding: 16px 20px;

        background: rgba(255, 255, 255, 0.80);

        border: 1px solid #e0eafa;

        border-radius: 18px;

        box-shadow:
            0 4px 12px rgba(74, 110, 177, 0.06);
    }

    .fecha-toolbar label {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: #47608f;

        font-size: 13.5px;

        font-weight: 600;
    }

    .fecha-toolbar label i {
        color: #3bb59e;

        font-size: 14px;
    }

    .fecha-form {
        display: flex;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;
    }

    .fecha-input {
        height: 36px;

        padding: 0 12px;

        border: 1px solid #ccd5e3;

        border-radius: 9px;

        font-size: 13px;

        color: #243c62;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .fecha-input:focus {
        border-color: #3bb59e;

        box-shadow:
            0 0 0 2px rgba(59, 181, 158, 0.15);
    }

    .btn-cambiar {
        height: 36px;

        padding: 0 16px;

        border: none;

        border-radius: 18px;

        background: #eaf1fb;

        color: #4f72b4;

        font-size: 12.5px;

        font-weight: 700;

        cursor: pointer;

        transition: 0.15s ease;
    }

    .btn-cambiar:hover {
        background: #dce8ff;
    }


    /* =====================================================
       MENSAJE DE ÉXITO
    ===================================================== */

    .alert-success {
        position: relative;

        z-index: 2;

        margin-bottom: 22px;

        padding: 13px 18px;

        display: flex;

        align-items: center;

        gap: 9px;

        border-radius: 16px;

        background: #dcf9e9;

        border: 1px solid #b8efd3;

        color: #0d9261;

        font-size: 14px;

        font-weight: 500;
    }


    /* =====================================================
       LEYENDA DE FALTAS
    ===================================================== */

    .legend {
        position: relative;

        z-index: 2;

        display: flex;

        flex-wrap: wrap;

        gap: 14px;

        margin-bottom: 24px;
    }

    .legend-item {
        display: inline-flex;

        align-items: center;

        gap: 9px;

        height: 36px;

        padding: 0 15px;

        border-radius: 18px;

        font-size: 12.5px;

        font-weight: 600;

        white-space: nowrap;
    }

    .legend-item i {
        font-size: 12px;
    }

    .legend-safe {
        background: #dcf9e9;

        color: #0d9261;
    }

    .legend-warning {
        background: #fff4d4;

        color: #896a1b;
    }

    .legend-danger {
        background: #ffe0e8;

        color: #ec3e67;
    }


    /* =====================================================
       TABLA
    ===================================================== */

    .table-slot {
        position: relative;

        z-index: 2;

        width: 100%;

        overflow-x: auto;

        background: rgba(255, 255, 255, 0.75);

        border: 1px solid #d9e6fb;

        border-radius: 22px;

        box-shadow:
            0 5px 15px rgba(85, 115, 170, 0.06);
    }

    table {
        width: 100%;

        border-collapse: collapse;

        table-layout: fixed;

        min-width: 780px;
    }

    /* ENCABEZADO */

    thead {
        background: #f1f6ff;
    }

    th {
        height: 48px;

        padding: 0 16px;

        text-align: left;

        color: #6b82b5;

        font-size: 11px;

        font-weight: 700;

        letter-spacing: 0.4px;

        border-bottom: 1px solid #e3e8ef;
    }

    /* ANCHOS */

    th:nth-child(1),
    td:nth-child(1) {
        width: 34%;
    }

    th:nth-child(2),
    td:nth-child(2) {
        width: 20%;

        text-align: center;
    }

    th:nth-child(3),
    td:nth-child(3) {
        width: 27%;

        text-align: center;
    }

    th:nth-child(4),
    td:nth-child(4) {
        width: 19%;

        text-align: center;
    }

    /* FILAS */

    td {
        height: 56px;

        padding: 8px 16px;

        color: #102d59;

        font-size: 14px;

        border-bottom: 1px solid #edf0f5;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #fafcff;
    }

    /* Resaltado sutil según el nivel de alerta */

    tbody tr.row-peligro,
    tbody tr.row-peligro:hover {
        background: rgba(255, 224, 232, 0.30);
    }

    tbody tr.row-advertencia,
    tbody tr.row-advertencia:hover {
        background: rgba(255, 244, 212, 0.32);
    }

    /* NOMBRE */

    .nombre {
        color: #102d59;

        font-weight: 600;
    }

    .nombre-warn {
        display: block;

        margin-top: 2px;

        font-size: 11.5px;

        font-weight: 600;
    }

    .nombre-warn.peligro {
        color: #d42642;
    }

    .nombre-warn.advertencia {
        color: #a16b00;
    }

    /* BADGE DE FALTAS */

    .absence {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        min-width: 64px;

        padding: 5px 10px;

        border-radius: 14px;

        background: #d9f8e7;

        color: #008b4a;

        font-size: 12px;

        font-weight: 600;
    }

    .absence .absence-detalle {
        margin-left: 4px;

        font-weight: 500;
    }

    .absence.warning {
        background: #fff1c9;

        color: #a16b00;
    }

    .absence.danger {
        background: #ffe0e8;

        color: #d42642;
    }

    /* ESTADO DE RIESGO */

    .badge-estado {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 6px 12px;

        border-radius: 14px;

        font-size: 12px;

        font-weight: 600;

        white-space: nowrap;
    }

    .badge-estado i {
        font-size: 11px;
    }

    .badge-normal {
        background: #eaf1fb;

        color: #4f72b4;
    }

    .badge-alerta {
        background: #fff1c9;

        color: #a16b00;
    }

    .badge-perdida {
        background: #ffe0e8;

        color: #d42642;
    }

    /* PRESENTE HOY */

    .check-wrap {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: #426080;

        font-size: 12.5px;

        font-weight: 600;

        cursor: pointer;
    }

    .check-presente {
        width: 19px;

        height: 19px;

        accent-color: #2fb67a;

        border-radius: 5px;

        cursor: pointer;
    }

    .check-wrap.ausente {
        color: #8ca0c4;
    }


    /* =====================================================
       BOTÓN GUARDAR
    ===================================================== */

    .form-actions {
        position: relative;

        z-index: 2;

        margin-top: 26px;

        display: flex;

        justify-content: flex-end;
    }

    .btn-save {
        display: inline-flex;

        align-items: center;

        gap: 9px;

        height: 44px;

        padding: 0 26px;

        border: none;

        border-radius: 22px;

        color: white;

        font-size: 14px;

        font-weight: 700;

        cursor: pointer;

        background:
            linear-gradient(
                100deg,
                #3bb59e,
                #55cbb5
            );

        box-shadow:
            0 7px 15px rgba(60, 178, 156, 0.20);

        transition: 0.2s ease;
    }

    .btn-save:hover {
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

    @media (max-width: 1200px) {

        .cursos-wrap {
            width: calc(100% - 60px);
        }

        .courses-panel {
            padding: 34px;
        }
    }

    @media (max-width: 900px) {

        .cursos-wrap {
            width: calc(100% - 40px);
        }
    }

    @media (max-width: 600px) {

        .cursos-wrap {
            width: calc(100% - 20px);

            margin-top: 20px;
        }

        .courses-panel {
            padding: 26px 16px;

            border-radius: 23px;
        }

        .asistencia-header h1 {
            font-size: 24px;
        }

        .legend {
            gap: 9px;
        }

        .legend-item {
            white-space: normal;
        }
    }
</style>

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

        {{-- Selección de fecha de la clase --}}
        <div class="fecha-toolbar">

            <label for="fecha-clase">
                <i class="fa-solid fa-calendar-day"></i>
                Fecha de la clase:
            </label>

            <form method="GET" action="{{ route('profesor.asistencia', $curso->id_curso) }}" class="fecha-form">

                <input
                    type="date"
                    name="fecha"
                    id="fecha-clase"
                    value="{{ $fecha }}"
                    class="fecha-input"
                >

                <button type="submit" class="btn-cambiar">
                    Cambiar
                </button>

            </form>

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
                Pierde materia (más del 30% de faltas)
            </span>

        </div>

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
                            // El controlador no envía el conteo bruto; se deriva del % y el total
                            $faltasMostradas = $est['totalClases'] > 0
                                ? (int) round(($est['porcentajeFaltas'] / 100) * $est['totalClases'])
                                : 0;

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
                            <td>

                                <span class="nombre">{{ $est['nombre'] }}</span>

                                @if($est['alerta'] === 'peligro')
                                    <span class="nombre-warn peligro">Pierde la materia — superó el 30% de faltas</span>
                                @elseif($est['alerta'] === 'advertencia')
                                    <span class="nombre-warn advertencia">Cerca del límite — comuníquese con el estudiante</span>
                                @endif

                            </td>

                            {{-- % Faltas acumuladas --}}
                            <td>

                                <span class="absence {{ $claseFaltas }}">
                                    {{ $est['porcentajeFaltas'] }}%
                                    <span class="absence-detalle">({{ $faltasMostradas }} de {{ $est['totalClases'] }} clases)</span>
                                </span>

                            </td>

                            {{-- Estado de riesgo --}}
                            <td>

                                @if($est['alerta'] === 'peligro')
                                    <span class="badge-estado badge-perdida">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                        Pérdida de materia
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
                            <td>

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

                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Guardar Asistencia
                </button>

            </div>

        </form>

        @endif

    </section>

</div>

@endsection