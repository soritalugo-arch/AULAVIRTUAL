@extends('layouts.app')

@section('titulo', 'Notas — ' . $curso->nombre)

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

    .notas-header {
        position: relative;

        z-index: 2;

        display: flex;

        align-items: flex-start;
        justify-content: space-between;

        gap: 20px;

        flex-wrap: wrap;

        margin-bottom: 26px;
    }

    .notas-header h1 {
        margin-bottom: 7px;

        color: #171d7d;

        font-family: Georgia, "Times New Roman", serif;

        font-size: 30px;

        font-weight: 700;
    }

    .notas-header .subtitulo {
        color: #7690c1;

        font-size: 15px;

        line-height: 1.5;
    }


    /* BOTÓN IR A ASISTENCIA */

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

    .btn-green {
        background:
            linear-gradient(
                100deg,
                #27a564,
                #3fc287
            );

        box-shadow:
            0 7px 15px rgba(40, 168, 101, 0.22);
    }

    .btn-green:hover {
        transform: translateY(-2px);

        box-shadow:
            0 10px 20px rgba(40, 168, 101, 0.30);
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
       MENSAJE DE ERROR
    ===================================================== */

    .alert-error {
        position: relative;

        z-index: 2;

        margin-bottom: 22px;

        padding: 13px 18px;

        display: flex;

        align-items: flex-start;

        gap: 9px;

        border-radius: 16px;

        background: #ffe6ec;

        border: 1px solid #ffc6d5;

        color: #c31f4c;

        font-size: 14px;

        font-weight: 500;
    }

    .alert-error ul {
        margin: 0;

        padding-left: 18px;
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

        min-width: 1080px;
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
        width: 18%;
    }

    th:nth-child(2),
    td:nth-child(2) {
        width: 10%;

        text-align: center;
    }

    th:nth-child(3),
    td:nth-child(3) {
        width: 11%;

        text-align: center;
    }

    /* Las cuatro parciales */

    th:nth-child(4),
    td:nth-child(4),
    th:nth-child(5),
    td:nth-child(5),
    th:nth-child(6),
    td:nth-child(6),
    th:nth-child(7),
    td:nth-child(7) {
        width: 8%;

        text-align: center;
    }

    /* Acumulado (8) y Promedio (9) */
    th:nth-child(8),
    td:nth-child(8),
    th:nth-child(9),
    td:nth-child(9) {
        width: 9%;
        text-align: center;
    }

    /* Observación (10) */
    th:nth-child(10),
    td:nth-child(10) {
        width: 11%; /* Ajustado para que el total de las columnas sume 100% exacto */
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

    /* La casilla de faltas avisa sola: amarilla al llegar al 25 % y envuelta
       en rojo al pasar el 30 %. El aviso es la propia casilla, sin correo. */

    .faltas-cell {
        text-align: center;
    }

    .faltas-cell.advertencia {
        background: rgba(255, 241, 201, 0.55);
    }

    .faltas-cell.peligro {
        background: rgba(255, 224, 232, 0.55);
    }

    .faltas-cell.peligro .absence {
        outline: 2px solid #d42642;

        outline-offset: 2px;
    }

    /* BUSCADOR Y ORDEN DE LA TABLA */

    .barra-notas {
        display: flex;

        align-items: center;

        justify-content: space-between;

        flex-wrap: wrap;

        gap: 12px;

        margin-bottom: 16px;
    }

    .buscador-notas {
        position: relative;

        flex: 1 1 260px;

        max-width: 340px;
    }

    .buscador-notas input {
        width: 100%;

        height: 38px;

        padding: 0 36px 0 36px;

        border: 1px solid #c6d3ee;

        border-radius: 19px;

        background: #ffffff;

        color: #172f55;

        font-size: 13.5px;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .buscador-notas input:focus {
        border-color: #5686ef;

        box-shadow: 0 0 0 3px rgba(86, 134, 239, 0.14);
    }

    .buscador-notas i {
        position: absolute;

        top: 50%;

        left: 14px;

        transform: translateY(-50%);

        color: #8ba0c8;

        font-size: 13px;

        pointer-events: none;
    }

    .buscador-notas .limpiar-busqueda {
        position: absolute;

        top: 50%;

        right: 6px;

        transform: translateY(-50%);

        display: none;

        width: 26px;

        height: 26px;

        border: none;

        border-radius: 50%;

        background: #eef2fa;

        color: #4a628f;

        font-size: 11px;

        cursor: pointer;
    }

    .buscador-notas.buscando .limpiar-busqueda {
        display: block;
    }

    .vacio-busqueda {
        padding: 26px 10px;

        text-align: center;

        color: #7a8db5;

        font-size: 13.5px;

        font-weight: 600;
    }

    th.ordenable {
        cursor: pointer;

        user-select: none;

        white-space: nowrap;
    }

    th.ordenable:hover {
        color: #2f6fd0;
    }

    th.ordenable .flecha {
        margin-left: 5px;

        color: #b9c6e2;

        font-size: 10px;
    }

    th.ordenable.asc .flecha,
    th.ordenable.desc .flecha {
        color: #2f6fd0;
    }

    /* ESTADO */

    .status {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        padding: 6px 12px;

        border-radius: 14px;

        background: #f0f2f5;

        color: #426080;

        font-size: 12px;

        white-space: nowrap;
    }

    .status.ok {
        background: #d9f9e8;

        color: #0a9560;
    }

    .status.fail {
        background: #ffe0e8;

        color: #ec3e67;
    }

    .status.presunto {
        background: #ffe5d1;

        color: #c2560a;
    }

    /* INPUT DE NOTA */

    .grade-input {
        width: 78px;

        height: 38px;

        padding: 0 8px;

        border: 1px solid #c6d3ee;

        border-radius: 15px;

        background:
            linear-gradient(
                180deg,
                #ffffff,
                #f2f6ff
            );

        color: #172f55;

        text-align: center;

        font-size: 14px;

        outline: none;

        box-shadow:
            inset 0 1px 2px rgba(86, 122, 190, 0.08),
            0 1px 1px rgba(255, 255, 255, 0.8);

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease,
            background 0.15s ease;
    }

    .grade-input:focus {
        border-color: #5686ef;

        background: #ffffff;

        box-shadow:
            0 0 0 3px rgba(86, 134, 239, 0.14),
            inset 0 1px 2px rgba(86, 122, 190, 0.05);
    }

    /* INPUT DE PARCIAL: cuatro por alumno, mas angosto que el de nota */

    .parcial-input {
        width: 100%;

        max-width: 62px;

        height: 34px;

        padding: 0 4px;

        border: 1px solid #c6d3ee;

        border-radius: 11px;

        background:
            linear-gradient(
                180deg,
                #ffffff,
                #f2f6ff
            );

        color: #172f55;

        text-align: center;

        font-size: 13px;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .parcial-input:focus {
        border-color: #5686ef;

        background: #ffffff;

        box-shadow:
            0 0 0 3px rgba(86, 134, 239, 0.14);
    }

    /* Etiqueta de la parcial, arriba del input (version movil) */

    .parcial-label {
        display: none;

        font-size: 10px;

        font-weight: 700;

        color: #8ba0c8;

        text-transform: uppercase;
    }

    /* PROMEDIO: se calcula solo, no es un campo editable */

    .promedio-cell {
        text-align: center;
    }

    .promedio {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 52px;

        padding: 5px 8px;

        border-radius: 13px;

        background: #eef2fa;

        color: #4a628f;

        font-size: 13.5px;

        font-weight: 700;
    }

    .promedio.aprobado {
        background: #d9f9e8;
        color: #0a9560;
    }

    .promedio.reprobado {
        background: #ffe0e8;
        color: #ec3e67;
    }

    /* Grupo de las cuatro parciales */

    .parciales-celda {
        display: flex;

        align-items: center;

        justify-content: center;

        gap: 6px;
    }

    /* INPUT DE OBSERVACIÓN */

    .observation {
        width: 100%;

        height: 38px;

        padding: 0 14px;

        border: 1px solid #c6d3ee;

        border-radius: 15px;

        background:
            linear-gradient(
                180deg,
                #ffffff,
                #f2f6ff
            );

        outline: none;

        color: #243c62;

        font-size: 13px;

        box-shadow:
            inset 0 1px 2px rgba(86, 122, 190, 0.08),
            0 1px 1px rgba(255, 255, 255, 0.8);

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease,
            background 0.15s ease;
    }

    .observation::placeholder {
        color: #9ba7b8;
    }

    .observation:focus {
        border-color: #5686ef;

        background: #ffffff;

        box-shadow:
            0 0 0 3px rgba(86, 134, 239, 0.14),
            inset 0 1px 2px rgba(86, 122, 190, 0.05);
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
                #5578e6,
                #7198ef
            );

        box-shadow:
            0 7px 15px rgba(86, 121, 226, 0.23);

        transition: 0.2s ease;
    }

    .btn-save:hover {
        transform: translateY(-2px);

        box-shadow:
            0 10px 20px rgba(86, 121, 226, 0.30);
    }


    /* =====================================================
       SELECTOR DE CUATRIMESTRE
    ===================================================== */

    .cuatrimestre-bar {
        position: relative;

        z-index: 30;

        display: flex;

        align-items: center;

        gap: 12px;

        flex-wrap: wrap;

        margin-bottom: 22px;

        padding: 12px 18px;

        background: rgba(255, 255, 255, 0.80);

        border: 1px solid #e0eafa;

        border-radius: 16px;
    }

    .cuatrimestre-bar label {
        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: #47608f;

        font-size: 13px;

        font-weight: 600;
    }

    .cuatrimestre-bar label i {
        color: #5578e6;

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
       RESPONSIVE (CON TABLA TIPO TARJETA)
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

        /* Apilar encabezado y controles */
        .notas-header {
            flex-direction: column;
            gap: 16px;
        }
        .btn-extra {
            width: 100%;
            justify-content: center;
        }
        .cuatrimestre-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .sel-trigger {
            width: 100%;
            justify-content: space-between; 
        }
        .sel-menu {
            width: 100%;
            max-width: 100%;
        }

        /* =========================================
           TRANSFORMAR TABLA EN TARJETAS (MOBILE)
        ========================================= */
        .table-slot {
            background: transparent;
            border: none;
            box-shadow: none;
            overflow-x: hidden; /* Elimina definitivamente el scroll horizontal */
        }
        
        table, thead, tbody, tr, th, td {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
        }

        /* Ocultar encabezados de la tabla tradicional */
        thead { 
            display: none; 
        }

        /* Cada fila de estudiante es ahora una tarjeta individual */
        tbody tr {
            margin-bottom: 20px;
            background: #ffffff;
            border: 1px solid #d9e6fb;
            border-radius: 16px;
            box-shadow: 0 5px 15px rgba(85, 115, 170, 0.05);
            padding: 4px 0;
        }

        /* Rejilla interna para cada celda de la tarjeta */
        td {
            display: grid;
            grid-template-columns: 45% 55%; /* Divide el espacio: Título | Valor */
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid #edf0f5;
            height: auto;
            text-align: right;
        }

        td:last-child { 
            border-bottom: none; 
        }

        /* Generar los títulos de cada columna dinámicamente con CSS */
        td::before {
            grid-column: 1;
            grid-row: 1 / span 5; /* Evita que el texto de la derecha lo empuje */
            text-align: left;
            font-size: 11.5px;
            font-weight: 700;
            color: #6b82b5;
            text-transform: uppercase;
            align-self: center;
        }

        /* Asignar el nombre a cada campo */
        td:nth-child(1)::before { content: "Estudiante"; align-self: start; margin-top: 4px; }
        td:nth-child(2)::before { content: "% Faltas"; }
        td:nth-child(3)::before { content: "Estado"; }
        td:nth-child(4)::before { content: "Parcial 1"; }
        td:nth-child(5)::before { content: "Parcial 2"; }
        td:nth-child(6)::before { content: "Parcial 3"; }
        td:nth-child(7)::before { content: "Parcial 4"; }
        td:nth-child(8)::before { content: "Acumulado"; }
        td:nth-child(9)::before { content: "Promedio"; }
        /* Alinear el contenido (los datos) a la derecha */
        td > * {
            grid-column: 2;
            justify-self: end;
        }
        
        /* Contenedor de la celda para manejar el espacio */
    .observaciones-cell {
        min-width: 200px; /* Asegura un ancho mínimo aceptable en PC */
        width: 25%; /* Toma una buena porción de la tabla */
        vertical-align: top; /* Mantiene el textarea arriba si las otras columnas crecen */
        padding: 8px !important;
    }

    /* El contenedor del textarea */
    .observacion-wrapper {
            position: relative;
        width: 100%;
    }

    /* El área de texto */
    .input-observacion {
        width: 100%;
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid #e0e7ff; /* Un borde sutil azul claro, similar al estilo de tus botones */
        border-radius: 8px; /* Bordes redondeados */
        background-color: #f8fafc; /* Fondo un poco más gris/azul para diferenciarlo */
        font-size: 0.85rem;
        color: #334155;
        resize: none; /* Evitamos que el usuario cambie el tamaño manualmente, el JS lo hace */
        overflow: hidden; /* Oculta la barra de desplazamiento si el JS falla */
        transition: border-color 0.2s, background-color 0.2s;
        box-sizing: border-box;
    }

    /* Efecto al hacer clic en el textarea */
    .input-observacion:focus {
        outline: none;
        border-color: #93c5fd; /* Azul más fuerte al seleccionar */
        background-color: #ffffff; /* Fondo blanco al escribir */
        box-shadow: 0 0 0 2px rgba(147, 197, 253, 0.2); /* Sombra exterior suave */
    }

    /* Ajustes para pantallas pequeñas (Móviles/Tablets) */
    @media (max-width: 768px) { 
        .observaciones-cell {
           min-width: 150px;
        }
    }
    @media (max-width: 600px) {
        .cursos-wrap {
            width: 100%; 
            margin-top: 0;
            margin-bottom: 0;
        }
        .courses-panel {
            padding: 24px 12px;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }
        .courses-panel::after { display: none; }
        
        .notas-header h1 { font-size: 24px; }
        
        /* Apilar la leyenda de riesgo */
        .legend { flex-direction: column; gap: 10px; }
        .legend-item { white-space: normal; height: auto; padding: 10px 14px; width: 100%; }
        
        /* Botón de guardar gigante para tocar con el dedo */
        .form-actions { margin-top: 20px; }
        .btn-save { width: 100%; justify-content: center; height: 48px; font-size: 15px; }
    }
</style>

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
                                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'">{{ $est['observaciones'] ?? '' }}</textarea>
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

<script>
    (function () {
        var triggers = document.querySelectorAll('[data-sel-toggle]');

        function closeAllSel() {
            var open = document.querySelectorAll('.sel.is-open');
            for (var oi = 0; oi < open.length; oi++) {
                open[oi].classList.remove('is-open');
            }
        }

        for (var ti = 0; ti < triggers.length; ti++) {
            (function (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var sel = trigger.closest('.sel');

                    if (sel.classList.contains('is-open')) {
                        sel.classList.remove('is-open');
                    } else {
                        closeAllSel();
                        sel.classList.add('is-open');
                    }
                });
            })(triggers[ti]);
        }

        /* Cerrar solo al hacer clic FUERA del dropdown o con Escape */
        document.addEventListener('click', function (e) {
            var target = e.target;
            var inside = target && target.closest && target.closest('.sel');

            if (!inside) closeAllSel();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllSel();
        });
    })();

    /* =====================================================
       ACUMULADO Y PROMEDIO EN VIVO
       Cada parcial vale 25 puntos sobre 100: el acumulado es la suma de
       las cuatro y el promedio es ese acumulado dividido entre 10. Las
       parciales vacías cuentan como cero (opción A), el mismo cálculo que
       hace el servidor al guardar. Es solo una ayuda visual mientras el
       profesor escribe: al guardar manda el valor del servidor.
       ===================================================== */

    (function () {
        // Seleccionamos todas las filas de la tabla de estudiantes
var filas = document.querySelectorAll('tbody tr'); // Ajusta este selector si tus filas tienen una clase específica, ej: '.fila-estudiante'

filas.forEach(function(fila) {
    // 1. Capturar los elementos de la fila actual
    var parciales = fila.querySelectorAll('input[type="number"]'); // Las cajas de texto de las notas
    var salidaAcumulado = fila.querySelector('[data-acumulado]');
    var salidaPromedio = fila.querySelector('[data-promedio]');
    var salidaEstado = fila.querySelector('[data-estado]'); 

    // Si la fila no tiene estos elementos, la saltamos
    if (!parciales.length || !salidaAcumulado || !salidaPromedio) return;

    // 2. Función auxiliar para pintar colores en los promedios
    function pintar(salida, texto, aprobado) {
        salida.textContent = texto;
        salida.className = 'promedio ' + (aprobado ? 'aprobado' : 'reprobado');
    }

    // 3. Función principal que recalcula todo para esta fila
    function recalcular() {
        var suma = 0;
        var hayAlgo = false;
        var ingresadas = 0; 

        // Sumar los valores de los inputs
        for (var i = 0; i < parciales.length; i++) {
            var valor = parseFloat(parciales[i].value);

            if (!isNaN(valor)) {
                suma += valor;
                hayAlgo = true;
                ingresadas++; 
            }
        }

        // Si borraron todas las notas, devolver al estado por defecto
        if (!hayAlgo) {
            salidaAcumulado.textContent = '—';
            salidaAcumulado.className = 'promedio';
            salidaPromedio.textContent = '—';
            salidaPromedio.className = 'promedio';
            fila.setAttribute('data-promedio', '-1');
            
            if (salidaEstado) {
                salidaEstado.textContent = 'En curso';
                salidaEstado.className = 'status presunto';
            }
            return;
        }

        // Calcular puntaje final
        var acumulado = Math.round(suma * 100) / 100;
        var promedio  = Math.round((acumulado / 10) * 100) / 100;
        var aprueba   = promedio >= 6; // Verifica si aprueba (6.00 o más)

        // Actualizar textos en pantalla del acumulado y promedio
        pintar(salidaAcumulado, acumulado.toFixed(2), aprueba);
        pintar(salidaPromedio, promedio.toFixed(2), aprueba);
        fila.setAttribute('data-promedio', promedio.toFixed(2));

        // Actualizar la pastilla de estado visualmente en tiempo real
        if (salidaEstado) {
            var esPeligro = fila.querySelector('.nombre-warn.peligro'); // Evalúa límite de faltas

            if (esPeligro) {
                salidaEstado.textContent = 'Reprobado';
                salidaEstado.className = 'status fail';
            } else if (promedio >= 6) {
                salidaEstado.textContent = 'Aprobado';
                salidaEstado.className = 'status ok';
            } else if (ingresadas === 4 && promedio < 6) { // Si ya llenó las 4 notas y no llega a 6
                salidaEstado.textContent = 'Reprobado';
                salidaEstado.className = 'status fail';
            } else {
                salidaEstado.textContent = 'En curso';
                salidaEstado.className = 'status presunto';
            }
        }
    }

    // 4. Asignar el evento 'input' a cada caja de texto para que reaccione al escribir
    parciales.forEach(function(input) {
        input.addEventListener('input', recalcular);
    });
});
    })();

    /* =====================================================
       BUSCAR Y ORDENAR LA TABLA
       Todo en el navegador: el filtro esconde las filas que no
       coinciden y el orden mueve las que quedan, sin volver a
       pedir nada al servidor. Los numbers de las notas no se
       tocan, siguen siendo lo que el profesor escribió.
       ===================================================== */

    (function () {
        var tabla = document.querySelector('[data-tabla-notas]');

        if (!tabla) return;

        var cuerpo   = tabla.querySelector('tbody');
        var filas    = Array.prototype.slice.call(cuerpo.querySelectorAll('tr[data-nombre]'));
        var sinFilas = cuerpo.querySelector('[data-sin-resultados]');
        var buscador = document.querySelector('[data-buscar]');
        var caja     = document.querySelector('[data-buscador]');
        var limpiar  = document.querySelector('[data-limpiar]');
        var cabeceras = Array.prototype.slice.call(tabla.querySelectorAll('th.ordenable'));
        var orden = { columna: null, sentido: 'asc' };

        /* Para que "Peña" se encuentre escribiendo "pena": se comparan los
           nombres sin tildes ni mayusculas. */
        function normalizar(texto) {
            return (texto || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '');
        }

        function visibles() {
            var termino = normalizar(buscador ? buscador.value : '').trim();
            var quedan = 0;

            for (var i = 0; i < filas.length; i++) {
                var coincide = termino === ''
                    || normalizar(filas[i].getAttribute('data-nombre')).indexOf(termino) !== -1;

                filas[i].hidden = !coincide;

                if (coincide) quedan++;
            }

            if (sinFilas) sinFilas.hidden = quedan > 0;

            if (caja) caja.classList.toggle('buscando', termino !== '');

            ordenar();
        }

        function ordenar() {
            if (!orden.columna) return;

            var columna = orden.columna;
            var factor  = orden.sentido === 'asc' ? 1 : -1;

            /* Se ordena sobre una copia: sort() mueve el orden de las filas
               del DOM, pero los indices del array se mantienen porque el
               foreach va sobre una copia. */
            var ordenadas = filas.slice().sort(function (a, b) {
                if (columna === 'nombre') {
                    return normalizar(a.getAttribute('data-nombre')).localeCompare(
                        normalizar(b.getAttribute('data-nombre'))
                    ) * factor;
                }

                return (parseFloat(a.getAttribute('data-promedio')) - parseFloat(b.getAttribute('data-promedio'))) * factor;
            });

            for (var i = 0; i < ordenadas.length; i++) {
                cuerpo.insertBefore(ordenadas[i], sinFilas);
            }
        }

        if (buscador) {
            buscador.addEventListener('input', visibles);
        }

        if (limpiar) {
            limpiar.addEventListener('click', function () {
                if (!buscador) return;

                buscador.value = '';
                buscador.focus();
                visibles();
            });
        }

        for (var c = 0; c < cabeceras.length; c++) {
            (function (th) {
                th.addEventListener('click', function () {
                    var columna = th.getAttribute('data-orden');

                    /* Volver a apretar la misma columna da vuelta el sentido. */
                    if (orden.columna === columna) {
                        orden.sentido = orden.sentido === 'asc' ? 'desc' : 'asc';
                    } else {
                        orden.columna = columna;
                        orden.sentido = columna === 'nombre' ? 'asc' : 'desc';
                    }

                    for (var k = 0; k < cabeceras.length; k++) {
                        cabeceras[k].classList.remove('asc', 'desc');
                    }

                    th.classList.add(orden.sentido);
                    th.querySelector('.flecha').innerHTML =
                        '<i class="fa-solid fa-sort' + (orden.sentido === 'asc' ? '-up' : '-down') + '"></i>';

                    ordenar();
                });
            })(cabeceras[c]);
        }
    })();
</script>

@endsection
