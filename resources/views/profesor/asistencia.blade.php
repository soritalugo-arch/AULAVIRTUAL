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

        z-index: 30;

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

    /* Selector de cuatrimestre dentro del toolbar */

    .fecha-toolbar .toolbar-sep {
        width: 1px;

        height: 26px;

        background: #e0eafa;
    }

    /* Horario del curso */

    .horario-chip {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        height: 36px;

        padding: 0 14px;

        border-radius: 18px;

        background: #eaf1fb;

        color: #4f72b4;

        font-size: 12.5px;

        font-weight: 600;
    }

    .horario-chip i {
        font-size: 12px;
    }

    /* =====================================================
       DROPDOWN PREMIUM (selector a medida)
    ===================================================== */

    .sel {
        position: relative;
    }

    .sel-trigger {
        display: inline-flex;

        align-items: center;

        gap: 10px;

        height: 42px;

        padding: 0 14px 0 8px;

        border: 1px solid #ccd5e3;

        border-radius: 21px;

        background: #ffffff;

        cursor: pointer;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .sel-trigger:hover {
        border-color: #a9bfe2;
    }

    .sel-trigger:focus-visible,
    .sel.is-open .sel-trigger {
        border-color: #5686ef;

        box-shadow:
            0 0 0 3px rgba(86, 134, 239, 0.14);
    }

    .sel-trigger-icon {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        width: 30px;
        height: 30px;

        border-radius: 50%;

        background: #eaf1fb;

        color: #5578e6;

        font-size: 12px;
    }

    .sel-trigger-text {
        display: flex;

        flex-direction: column;

        align-items: flex-start;

        line-height: 1.25;
    }

    .sel-trigger-title {
        color: #243c62;

        font-size: 13px;

        font-weight: 700;
    }

    .sel-trigger-sub {
        color: #8ba0c8;

        font-size: 11px;
    }

    .sel-chevron {
        margin-left: 2px;

        color: #8ba0c8;

        font-size: 10px;

        transition: transform 0.2s ease;
    }

    .sel.is-open .sel-chevron {
        transform: rotate(180deg);
    }

    .sel-menu {
        display: none;

        position: absolute;

        top: calc(100% + 8px);

        left: 0;

        z-index: 40;

        min-width: 270px;

        max-width: calc(100vw - 70px);

        padding: 6px;

        background: #ffffff;

        border: 1px solid #d9e6fb;

        border-radius: 16px;

        box-shadow:
            0 14px 34px rgba(60, 90, 150, 0.16);
    }

    .sel.is-open .sel-menu {
        display: block;

        animation: sel-in 0.14s ease;
    }

    @keyframes sel-in {
        from {
            opacity: 0;

            transform: translateY(-4px);
        }

        to {
            opacity: 1;

            transform: translateY(0);
        }
    }

    .sel-option {
        display: flex;

        align-items: center;

        gap: 10px;

        padding: 10px 12px;

        border-radius: 12px;

        color: #243c62;

        text-decoration: none;

        transition: background 0.12s ease;
    }

    .sel-option:hover {
        background: #f2f7ff;
    }

    .sel-option.is-active {
        background: #eaf1fb;
    }

    .sel-opt-text {
        display: flex;

        flex-direction: column;

        align-items: flex-start;

        line-height: 1.25;

        flex: 1;
    }

    .sel-opt-title {
        font-size: 13px;

        font-weight: 700;
    }

    .sel-opt-sub {
        color: #8ba0c8;

        font-size: 11.5px;
    }

    .sel-opt-check {
        color: #5578e6;

        font-size: 13px;

        opacity: 0;
    }

    .sel-option.is-active .sel-opt-check {
        opacity: 1;
    }


    /* =====================================================
       CALENDARIO A MEDIDA
    ===================================================== */

    .cal {
        position: relative;
    }

    .cal-trigger {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        height: 36px;

        padding: 0 14px;

        border: 1px solid #ccd5e3;

        border-radius: 18px;

        background: #ffffff;

        color: #243c62;

        font-size: 13px;

        cursor: pointer;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .cal-trigger > i:first-child {
        color: #3bb59e;

        font-size: 13px;
    }

    .cal-trigger:hover {
        border-color: #a7cdc3;
    }

    .cal-trigger:focus-visible,
    .cal.is-open .cal-trigger {
        border-color: #3bb59e;

        box-shadow:
            0 0 0 3px rgba(59, 181, 158, 0.15);
    }

    .cal-chevron {
        margin-left: 2px;

        color: #8ba0c8;

        font-size: 10px;

        transition: transform 0.2s ease;
    }

    .cal.is-open .cal-chevron {
        transform: rotate(180deg);
    }

    .cal-panel {
        display: none;

        position: absolute;

        top: calc(100% + 8px);

        left: 0;

        z-index: 40;

        width: 292px;

        max-width: calc(100vw - 70px);

        padding: 14px;

        background: #ffffff;

        border: 1px solid #d9e6fb;

        border-radius: 18px;

        box-shadow:
            0 16px 38px rgba(60, 90, 150, 0.18);
    }

    .cal.is-open .cal-panel {
        display: block;

        animation: sel-in 0.14s ease;
    }

    .cal-head {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 8px;

        margin-bottom: 10px;
    }

    .cal-title {
        color: #243c62;

        font-size: 13.5px;

        font-weight: 700;
    }

    .cal-nav {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        width: 28px;
        height: 28px;

        border: none;

        border-radius: 50%;

        background: #f1f6ff;

        color: #4f72b4;

        font-size: 11px;

        cursor: pointer;

        transition: background 0.12s ease;
    }

    .cal-nav:hover:not(:disabled) {
        background: #dce8ff;
    }

    .cal-nav:disabled {
        opacity: 0.35;

        cursor: default;
    }

    .cal-dow {
        display: grid;

        grid-template-columns: repeat(7, 1fr);

        gap: 2px;

        margin-bottom: 4px;
    }

    .cal-dow span {
        display: flex;

        align-items: center;
        justify-content: center;

        height: 26px;

        color: #8ba0c8;

        font-size: 10.5px;

        font-weight: 700;

        text-transform: uppercase;
    }

    .cal-grid {
        display: grid;

        grid-template-columns: repeat(7, 1fr);

        gap: 2px;
    }

    .cal-cell {
        display: flex;

        align-items: center;
        justify-content: center;

        height: 34px;

        border: none;

        border-radius: 50%;

        background: transparent;

        color: #243c62;

        font-size: 12.5px;

        cursor: pointer;

        transition: background 0.12s ease;
    }

    .cal-cell:hover:not(:disabled) {
        background: #eaf1fb;
    }

    .cal-cell:disabled {
        color: #c7d1e2;

        cursor: default;
    }

    .cal-cell.is-today:not(.is-selected) {
        box-shadow:
            inset 0 0 0 1.5px #7198ef;
    }

    .cal-cell.is-selected {
        background:
            linear-gradient(
                100deg,
                #3bb59e,
                #55cbb5
            );

        color: #ffffff;

        font-weight: 700;

        box-shadow:
            0 4px 10px rgba(59, 181, 158, 0.30);
    }

    .cal-foot {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 8px;

        margin-top: 10px;

        padding-top: 10px;

        border-top: 1px solid #edf2f9;
    }

    .cal-today {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        height: 30px;

        padding: 0 14px;

        border: none;

        border-radius: 15px;

        background: #eaf1fb;

        color: #4f72b4;

        font-size: 12px;

        font-weight: 700;

        cursor: pointer;

        transition: background 0.12s ease;
    }

    .cal-today:hover:not(:disabled) {
        background: #dce8ff;
    }

    .cal-today:disabled {
        opacity: 0.45;

        cursor: default;
    }

    .cal-hint {
        color: #9aa9c4;

        font-size: 11px;
    }

    /* Resumen de clases registradas */

    .registro-clases {
        margin: -10px 0 18px;

        color: #7c8db0;

        font-size: 12.5px;

        text-align: right;
    }

    .registro-clases strong {
        color: #47608f;
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
                            <td>

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
                            <td>

                                <span class="absence {{ $claseFaltas }}">
                                    {{ $est['porcentajeFaltas'] }}%
                                    <span class="absence-detalle">({{ $est['faltas'] }} de {{ $est['totalClases'] }} clases)</span>
                                </span>

                            </td>

                            {{-- Estado de riesgo --}}
                            <td>

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

<script>
    (function () {
        var triggers = document.querySelectorAll('[data-sel-toggle]');

        function closeAllSel() {
            var open = document.querySelectorAll('.sel.is-open');
            for (var oi = 0; oi < open.length; oi++) {
                open[oi].classList.remove('is-open');
            }
        }

        function closeCal() {
            if (cal) cal.classList.remove('is-open');
        }

        for (var ti = 0; ti < triggers.length; ti++) {
            (function (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var sel = trigger.closest('.sel');

                    if (sel.classList.contains('is-open')) {
                        sel.classList.remove('is-open');
                    } else {
                        closeCal();
                        closeAllSel();
                        sel.classList.add('is-open');
                    }
                });
            })(triggers[ti]);
        }

        /* =====================================================
           Calendario a medida
        ===================================================== */

        var cal = document.querySelector('[data-cal]');

        if (cal) {
            var title   = cal.querySelector('[data-cal-title]');
            var grid    = cal.querySelector('[data-cal-grid]');
            var label   = cal.querySelector('[data-cal-label]');
            var prev    = cal.querySelector('[data-cal-prev]');
            var next    = cal.querySelector('[data-cal-next]');
            var todayBtn = cal.querySelector('[data-cal-today]');
            var hint    = cal.querySelector('[data-cal-hint]');

            var baseUrl = cal.getAttribute('data-url');
            var cuatr  = cal.getAttribute('data-cuatrimestre');
            var fecha  = cal.getAttribute('data-fecha') || '';
            var minD   = toDate(cal.getAttribute('data-min'));
            var maxD   = toDate(cal.getAttribute('data-max'));

            /* Días de la semana en que el curso tiene clases. getDay(): 0=Dom..6=Sáb (igual que Carbon dayOfWeek) */
            var diasRaw  = (cal.getAttribute('data-dias') || '').trim();
            var diasClase = diasRaw ? diasRaw.split(',').map(function (n) { return parseInt(n, 10); }) : [];

            var DIAS    = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            var MESES   = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                            'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            var MESES_C = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul',
                            'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            var selected = fecha ? toDate(fecha) : null;
            var viewY = 0, viewM = 0;

            function toDate(s) {
                var p = s.split('-');
                return new Date(+p[0], +p[1] - 1, +p[2]);
            }

            function pad(n) {
                return (n < 10 ? '0' : '') + n;
            }

            function iso(d) {
                return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
            }

            function today() {
                var n = new Date();
                return new Date(n.getFullYear(), n.getMonth(), n.getDate());
            }

            function labelText(d) {
                return DIAS[d.getDay()] + ' ' + d.getDate() + ' ' + MESES_C[d.getMonth()] + ' ' + d.getFullYear();
            }

            function render(y, m) {
                title.textContent = MESES[m] + ' ' + y;

                var first  = new Date(y, m, 1);
                var days   = new Date(y, m + 1, 0).getDate();
                var offset = (first.getDay() + 6) % 7;
                var t      = today();
                var html   = '';

                for (var b = 0; b < offset; b++) {
                    html += '<span class="cal-cell"></span>';
                }

                for (var d = 1; d <= days; d++) {
                    var dd      = new Date(y, m, d);
                    var dentro  = dd >= minD && dd <= maxD;
                    var esClase = !diasClase.length || diasClase.indexOf(dd.getDay()) !== -1;
                    var dis     = !dentro || !esClase;
                    var cls     = 'cal-cell';

                    if (selected && iso(dd) === iso(selected)) cls += ' is-selected';
                    if (iso(dd) === iso(t)) cls += ' is-today';

                    html += '<button type="button" class="' + cls + '" data-fecha="' + iso(dd) + '"'
                        + (dis ? ' disabled' : '') + '>' + d + '</button>';
                }

                grid.innerHTML = html;

                var prevM = m - 1, prevY = y;
                if (prevM < 0) { prevM = 11; prevY--; }
                prev.disabled = new Date(prevY, prevM + 1, 0) < minD;

                var nextM = m + 1, nextY = y;
                if (nextM > 11) { nextM = 0; nextY++; }
                next.disabled = new Date(nextY, nextM, 1) > maxD;
            }

            function openCal() {
                var base = selected || toDate(fecha);
                viewY = base.getFullYear();
                viewM = base.getMonth();
                render(viewY, viewM);
                todayBtn.disabled = hoyNoValido();
                if (hint && diasClase.length) hint.textContent = 'Solo se permiten días de clase';
                cal.classList.add('is-open');
            }

            function hoyNoValido() {
                var t = today();
                if (t < minD || t > maxD) return true;
                if (diasClase.length && diasClase.indexOf(t.getDay()) === -1) return true;
                return false;
            }

            cal.querySelector('.cal-trigger').addEventListener('click', function (e) {
                e.stopPropagation();
                if (cal.classList.contains('is-open')) {
                    cal.classList.remove('is-open');
                } else {
                    closeAllSel();
                    openCal();
                }
            });

            prev.addEventListener('click', function () {
                viewM--;
                if (viewM < 0) { viewM = 11; viewY--; }
                render(viewY, viewM);
            });

            next.addEventListener('click', function () {
                viewM++;
                if (viewM > 11) { viewM = 0; viewY++; }
                render(viewY, viewM);
            });

            grid.addEventListener('click', function (e) {
                var target = e.target.closest ? e.target.closest('.cal-cell') : null;
                if (!target || target.disabled || target.tagName !== 'BUTTON') return;
                location.href = baseUrl + '?cuatrimestre=' + cuatr + '&fecha=' + target.getAttribute('data-fecha');
            });

            todayBtn.addEventListener('click', function () {
                if (hoyNoValido()) return;
                var t = today();
                location.href = baseUrl + '?cuatrimestre=' + cuatr + '&fecha=' + iso(t);
            });

            label.textContent = labelText(selected || toDate(fecha));
        }

        /* Cerrar solo al hacer clic FUERA del dropdown/calendario o con Escape */
        document.addEventListener('click', function (e) {
            var target = e.target;
            var inside = target && target.closest && (target.closest('.sel') || target.closest('.cal'));

            if (!inside) {
                closeAllSel();
                closeCal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllSel();
                closeCal();
            }
        });
    })();
</script>

@endsection
