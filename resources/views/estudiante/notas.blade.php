@extends('layouts.app')

@section('titulo', 'Mis Notas y Asistencia')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a></li>
    <li><a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a></li>
    <li><a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a></li>
    <li><a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a></li>
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
        color: #19325f;
        background:
            radial-gradient(
                circle at 5% 15%,
                rgba(198, 218, 255, 0.65),
                transparent 32%
            ),
            radial-gradient(
                circle at 95% 85%,
                rgba(190, 212, 255, 0.65),
                transparent 35%
            ),
            #f3f7fd;
        overflow-x: hidden;
    }

    main {
        padding-left: 0;
        padding-right: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    .notas-wrap {
        width: calc(100% - 90px);
        margin: 35px auto 50px;
    }

    /* =====================================================
       TARJETA PRINCIPAL
    ===================================================== */

    .grades-card {
        position: relative;
        min-height: 740px;
        padding: 40px 0 45px;
        overflow: hidden;
        background:
            linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.96),
                rgba(248, 251, 255, 0.94)
            );
        border: 1px solid #d9e6fb;
        border-radius: 28px;
        box-shadow: 0 10px 30px rgba(71, 106, 170, 0.10);
    }

    .grades-card::after {
        content: "";
        position: absolute;
        width: 650px;
        height: 250px;
        right: -170px;
        bottom: -160px;
        border-radius: 50%;
        background: rgba(188, 211, 253, 0.35);
        transform: rotate(-18deg);
        pointer-events: none;
    }

    /* =====================================================
       ENCABEZADO
    ===================================================== */

    .grades-header {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 28px;
        margin: 0 32px 30px;
    }

    .grades-icon {
        position: relative;
        width: 125px;
        height: 125px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: linear-gradient(145deg, #dce8ff, #edf3ff);
        color: #6284df;
        font-size: 51px;
    }

    .grades-icon::before {
        content: "";
        position: absolute;
        width: 112px;
        height: 112px;
        border-radius: 50%;
        background: #e1eaff;
    }

    .grades-icon i {
        position: relative;
        z-index: 1;
    }

    .grades-title h1 {
        margin-bottom: 7px;
        color: #171d7d;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 36px;
        font-weight: 700;
    }

    .grades-title p {
        color: #7087ba;
        font-size: 17px;
    }

    /* =====================================================
       LEYENDA DE ASISTENCIA
    ===================================================== */

    .attendance-legend {
        position: relative;
        z-index: 2;
        margin-left: 185px;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 42px;
    }

    .legend {
        height: 45px;
        padding: 0 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 25px;
        font-size: 13px;
        font-weight: 600;
    }

    .legend small {
        font-size: 13px;
        font-weight: 500;
    }

    .legend-icon {
        width: 23px;
        height: 23px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 12px;
    }

    .legend.green { background: #dcf9e9; color: #0d9261; }
    .legend.green .legend-icon { background: #c5f1db; color: #079263; }
    .legend.yellow { background: #fff4d4; color: #896a1b; }
    .legend.yellow .legend-icon { background: #ffe6a5; color: #c78d18; }
    .legend.red { background: #ffe0e8; color: #ec3e67; }
    .legend.red .legend-icon { background: #ffcbd8; color: #ef3c63; }

    /* =====================================================
       TABLA
    ===================================================== */

    .grades-table {
        position: relative;
        z-index: 2;
        width: calc(100% - 44px);
        margin: 0 22px;
        border: 1px solid #d9e6fb;
        border-radius: 22px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.75);
        box-shadow: 0 5px 15px rgba(85, 115, 170, 0.06);
    }

    .table-header {
        min-height: 72px;
        padding: 0 25px;
        display: grid;
        grid-template-columns: 25% 12% 13% 15% 20% 15%;
        align-items: center;
        background: #f1f6ff;
        color: #6b82b5;
        font-size: 13px;
        font-weight: 700;
    }

    .table-header div {
        text-align: center;
    }

    .table-header div:first-child {
        text-align: left;
    }

    .course-row {
        min-height: 110px;
        padding: 10px 25px;
        display: grid;
        grid-template-columns: 25% 12% 13% 15% 20% 15%;
        align-items: center;
        background: rgba(255, 255, 255, 0.90);
        border-top: 1px solid #e4ebf6;
    }

    /* =====================================================
       CURSO
    ===================================================== */

    .course { display: flex; align-items: center; gap: 18px; }
    .course-icon {
        width: 59px; height: 59px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        border-radius: 15px;
        background: linear-gradient(145deg, #e0ebff, #d2e1ff);
        color: #6080db; font-size: 22px;
    }
    .course-info { display: flex; flex-direction: column; gap: 6px; }
    .course-info strong { color: #1d398d; font-size: 16px; }
    .course-info span { color: #7c91bf; font-size: 14px; }

    /* =====================================================
       NOTA
    ===================================================== */

    .grade { display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .grade-value {
        width: 92px; height: 38px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 22px; background: #edf3fc; color: #6680b5;
        font-size: 18px; font-weight: 500;
    }
    .grade-value.pass { background: #d9f9e8; color: #079263; }
    .grade-value.fail { background: #ffe0e8; color: #d23a5f; }
    .grade em { color: #7890bd; font-size: 13px; }

    /* =====================================================
       FALTAS
    ===================================================== */

    .absences { display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .absence-badge {
        min-width: 75px; padding: 8px 14px; text-align: center;
        border-radius: 22px; background: #d9f9e8; color: #0a9560;
        font-weight: 700; font-size: 14px;
    }
    .absence-badge.yellow { background: #fff4d4; color: #c78d18; }
    .absence-badge.red { background: #ffe0e8; color: #ec3e67; }
    .absences small { color: #70a78f; font-size: 13px; }

    /* =====================================================
       ESTADO
    ===================================================== */

    .status { display: flex; justify-content: center; }
    .status span {
        padding: 9px 18px; border-radius: 22px;
        background: #eaf1fb; color: #4f72b4; font-size: 13px; font-weight: 600;
    }
    .status.ok span { background: #d9f9e8; color: #0a9560; }
    .status.fail span { background: #ffe0e8; color: #ec3e67; }

    /* =====================================================
       PROMEDIO
    ===================================================== */

    .average { display: flex; flex-direction: column; align-items: center; gap: 5px; color: #7790bf; }
    .average span { font-size: 17px; }
    .average em { font-size: 13px; }

    /* =====================================================
       OBSERVACIÓN
    ===================================================== */

    .observation { color: #7b91bc; font-size: 17px; text-align: center; }

    /* =====================================================
       ESTADO VACÍO
    ===================================================== */

    .empty-state {
        position: relative; z-index: 2; padding: 18px 24px;
        margin: 0 32px 20px; border-radius: 18px;
        background: #fff4d4; border: 1px solid #fde68a;
        color: #854d0e; font-size: 15px;
    }

    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media (max-width: 1100px) {
        .attendance-legend {
            margin-left: 0;
            flex-wrap: wrap;
        }
        .table-header,
        .course-row {
            grid-template-columns: 23% 12% 13% 14% 19% 19%;
        }
    }

    @media (max-width: 800px) {
        .grades-header {
            align-items: flex-start;
            margin: 0 16px 25px;
        }

        .grades-icon {
            width: 90px;
            height: 90px;
            font-size: 38px;
        }

        .grades-icon::before {
            width: 80px; height: 80px;
        }

        .grades-title h1 {
            font-size: 28px;
        }

        /* DISEÑO DE TABLA A TARJETA PARA MÓVIL */
        .grades-table {
            width: auto;
            margin: 0 14px;
            background: transparent;
            border: none;
            box-shadow: none;
        }

        .table-header {
            display: none; /* Ocultamos la cabecera en móvil */
        }

        .course-row {
            display: flex;
            flex-direction: column;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #d9e6fb;
            box-shadow: 0 5px 15px rgba(85, 115, 170, 0.08);
            gap: 15px;
        }

        .course {
            width: 100%;
            justify-content: flex-start;
            border-bottom: 2px solid #f1f6ff;
            padding-bottom: 15px;
            margin-bottom: 5px;
        }

        /* Estilo para las demás celdas (Nota, faltas, etc.) */
        .course-row > div:not(.course) {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            text-align: right;
        }

        /* Generar las etiquetas dinámicamente usando data-label */
        .course-row > div:not(.course)::before {
            content: attr(data-label);
            font-size: 13px;
            font-weight: 700;
            color: #6b82b5;
            text-align: left;
            flex-shrink: 0;
            margin-right: 15px;
        }

        /* Ajustes internos para que se vean bien alineados en tarjeta */
        .grade, .absences, .average {
            flex-direction: row; /* Alineación horizontal en tarjeta */
            gap: 10px;
        }
        
        .grade em, .average em, .absences small {
            display: none; /* Ocultamos textos secundarios para no saturar el móvil */
        }
        
        .observation {
            text-align: right;
            font-size: 15px;
        }
    }

    @media (max-width: 550px) {
        .notas-wrap {
            width: calc(100% - 20px);
            margin-top: 20px;
        }
        .grades-card {
            padding: 24px 0 35px;
            border-radius: 20px;
        }
        .grades-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
            margin: 0 12px 20px;
        }
        .grades-table {
            margin: 0 8px;
        }
        .attendance-legend {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 25px;
        }
        .legend {
            justify-content: center;
        }
    }
</style>

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
                <div>NOTA</div>
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

                    <!-- NOTA (agregado data-label) -->
                    <div class="grade" data-label="NOTA">
                        @if($tieneNota)
                            <div class="grade-value {{ $item['nota'] >= 6 ? 'pass' : 'fail' }}">
                                {{ $item['nota'] }}
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