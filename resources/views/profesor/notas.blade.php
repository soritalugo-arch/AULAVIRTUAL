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

        min-width: 860px;
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
        width: 27%;
    }

    th:nth-child(2),
    td:nth-child(2) {
        width: 14%;

        text-align: center;
    }

    th:nth-child(3),
    td:nth-child(3) {
        width: 14%;

        text-align: center;
    }

    th:nth-child(4),
    td:nth-child(4) {
        width: 13%;

        text-align: center;
    }

    th:nth-child(5),
    td:nth-child(5) {
        width: 32%;
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

    /* INPUT DE NOTA */

    .grade-input {
        width: 78px;

        height: 34px;

        padding: 0 8px;

        border: 1px solid #ccd5e3;

        border-radius: 8px;

        background: white;

        color: #172f55;

        text-align: center;

        font-size: 14px;

        outline: none;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .grade-input:focus {
        border-color: #5686ef;

        box-shadow:
            0 0 0 2px rgba(86, 134, 239, 0.12);
    }

    /* INPUT DE OBSERVACIÓN */

    .observation {
        width: 100%;

        height: 34px;

        padding: 0 12px;

        border: 1px solid #ccd5e3;

        border-radius: 8px;

        outline: none;

        color: #243c62;

        font-size: 13px;

        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .observation::placeholder {
        color: #9ba7b8;
    }

    .observation:focus {
        border-color: #5686ef;

        box-shadow:
            0 0 0 2px rgba(86, 134, 239, 0.10);
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

        .notas-header h1 {
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

        <form method="POST" action="{{ route('profesor.notas.guardar') }}">
            @csrf
            <input type="hidden" name="id_curso"         value="{{ $curso->id_curso }}">
            <input type="hidden" name="id_cuatrimestre"  value="{{ $cuatrimestre->id_cuatrimestre }}">

            <div class="table-slot">

                <table>

                    <thead>

                        <tr>

                            <th>ESTUDIANTE</th>
                            <th>% FALTAS</th>
                            <th>ESTADO</th>
                            <th>NOTA (1-10)</th>
                            <th>OBSERVACIÓN</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($estudiantes as $i => $est)

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
                            $claseEstado = match($est['estado']) {
                                'Aprobado'  => 'ok',
                                'Reprobado' => 'fail',
                                default     => '',
                            };
                        @endphp

                        <input type="hidden" name="notas[{{ $i }}][id_estudiante]" value="{{ $est['id'] }}">

                        <tr class="{{ $claseFila }}">

                            {{-- Nombre --}}
                            <td>

                                <span class="nombre">{{ $est['nombre'] }}</span>

                                @if($est['alerta'] === 'peligro')
                                    <span class="nombre-warn peligro">Pierde la materia</span>
                                @elseif($est['alerta'] === 'advertencia')
                                    <span class="nombre-warn advertencia">Cerca del limite</span>
                                @endif

                            </td>

                            {{-- % Faltas --}}
                            <td>

                                <span class="absence {{ $claseFaltas }}">
                                    {{ $est['porcentajeFaltas'] }}%
                                    <span class="absence-detalle">({{ $faltasMostradas }}/{{ $est['totalClases'] }})</span>
                                </span>

                            </td>

                            {{-- Estado --}}
                            <td>

                                <span class="status {{ $claseEstado }}">
                                    {{ $est['estado'] }}
                                </span>

                            </td>

                            {{-- Nota --}}
                            <td>

                                <input
                                    type="number"
                                    name="notas[{{ $i }}][nota]"
                                    value="{{ $est['nota'] }}"
                                    min="1" max="10"
                                    placeholder="—"
                                    class="grade-input"
                                >

                            </td>

                            {{-- Observación --}}
                            <td>

                                <input
                                    type="text"
                                    name="notas[{{ $i }}][observaciones]"
                                    value="{{ $est['observaciones'] }}"
                                    placeholder="Observación opcional..."
                                    class="observation"
                                >

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
                    Guardar Notas
                </button>

            </div>

        </form>

        @endif

    </section>

</div>

@endsection