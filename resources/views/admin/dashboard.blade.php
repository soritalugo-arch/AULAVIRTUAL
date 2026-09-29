@extends('layouts.app')

@section('titulo', 'Panel de la Rectora')

@section('contenido')

<style>
    /* ================================
       FONDO
    ================================ */

    body {
        font-family: "DM Sans", sans-serif;

        color: #172b5c;

        background:
            radial-gradient(circle at 10% 5%, rgba(194, 216, 255, 0.40), transparent 32%),
            radial-gradient(circle at 92% 92%, rgba(188, 211, 255, 0.45), transparent 32%),
            #f3f6fb;
    }

    /* ================================
       CABECERA + SELECTOR
    ================================ */

    .panel-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 26px;
    }

    .panel-head h1 {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 34px;
        color: #171c7c;
        margin-bottom: 6px;
    }

    .panel-head p {
        color: #64789f;
        font-size: 15px;
    }

    .filtro {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.93);
        border: 1px solid #e0e8f5;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(70, 100, 160, 0.08);
        padding: 10px 12px 10px 20px;
    }

    .filtro label {
        font-size: 13px;
        font-weight: 600;
        color: #5a6f9c;
        white-space: nowrap;
    }

    .filtro select {
        appearance: none;
        border: 1px solid #dce7fa;
        border-radius: 14px;
        background: #f7f9ff;
        color: #24356e;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        padding: 9px 38px 9px 14px;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath fill='%235a6f9c' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        background-size: 11px;
    }

    .filtro button {
        border: none;
        border-radius: 14px;
        padding: 10px 22px;
        background: linear-gradient(100deg, #4c5bc3, #6e94ee);
        color: #fff;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 6px 15px rgba(76, 91, 195, 0.25);
        transition: transform 0.18s ease;
    }

    .filtro button:hover { transform: translateY(-1px); }

    /* ================================
       TARJETA BASE
    ================================ */

    .card {
        background: rgba(255, 255, 255, 0.93);
        border: 1px solid #e0e8f5;
        border-radius: 27px;
        box-shadow: 0 8px 25px rgba(70, 100, 160, 0.08);
    }

    /* ================================
       FILA DE KPIs (CUADRÍCULA COMPACTA)
    ================================ */

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .kpi {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px 22px 22px;
    }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }

    .kpi-label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #7a8db5;
    }

    .kpi-icon {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 17px;
    }

    .kpi-valor {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 38px;
        line-height: 1.05;
        color: #171c7c;
    }

    .kpi-valor small {
        font-family: "DM Sans", sans-serif;
        font-size: 18px;
        font-weight: 600;
        color: #7a8db5;
    }

    .kpi-pie {
        margin-top: 10px;
        font-size: 13px;
        color: #64789f;
    }

    .kpi-pie b { color: #2d4b99; }

    /* Tons: fondo suave + color de icono/borde */
    .ton-azul   { background: #e8efff; color: #4c6fe0; }
    .ton-verde  { background: #e3f8ee; color: #0a9560; }
    .ton-rojo   { background: #ffe7ec; color: #ec3e67; }
    .ton-naranja{ background: #fff0e0; color: #c2560a; }
    .ton-violeta{ background: #efe9ff; color: #6b4fd8; }

    /* ================================
       FILA DE MINI INDICADORES
    ================================ */

    .mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .mini {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 15px 20px;
    }

    .mini-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 15px;
    }

    .mini-texto { min-width: 0; }

    .mini-valor {
        font-size: 21px;
        font-weight: 700;
        color: #1c2a63;
        line-height: 1.15;
    }

    .mini-label {
        font-size: 12px;
        color: #7a8db5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mini-riesgo { color: #ec3e67; }
    .mini-alerta { color: #c2560a; }

    /* ================================
       CONTROLES DE LOS GRÁFICOS
    ================================ */

    .controles {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #eef3fb;
    }

    .contador {
        font-size: 13px;
        color: #7a8db5;
        margin-right: auto;
    }

    .btn-ver {
        border: 1px solid #dce7fa;
        border-radius: 13px;
        background: #f7f9ff;
        color: #2f55c4;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 18px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .btn-ver:hover { background: #eaf0ff; }

    .btn-ver.plano {
        background: transparent;
        color: #7a8db5;
    }

    .btn-ver.plano:hover { background: #f2f6fd; }

    /* ================================
       GRÁFICOS
    ================================ */

    .graficos {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .grafico { padding: 22px 24px 24px; }

    .grafico.ancho { margin-bottom: 20px; }

    .grafico-head {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 6px;
    }

    .grafico-head i {
        font-size: 22px;
        color: #6382dc;
    }

    .grafico-head h2 {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 21px;
        color: #171c7c;
    }

    .grafico-sub {
        font-size: 13px;
        color: #7a8db5;
        margin-bottom: 18px;
    }

    /* Cada grafico dice que periodo esta mostrando: sin esto no se sabe si
       un dato en cero es un problema o un cuatrimestre que aun no empezo. */
    .periodo-chip {
        margin-left: auto;
        padding: 5px 12px;
        border-radius: 999px;
        background: #eef2fc;
        color: #4c6fe0;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    /* Aviso de cuatrimestre sin movimientos registrados */
    .aviso {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 15px 20px;
        margin-bottom: 20px;
        background: #f6f9ff;
        border: 1px solid #dbe6fb;
        border-left: 4px solid #4c6fe0;
        border-radius: 16px;
        color: #46578a;
        font-size: 14px;
        line-height: 1.55;
    }

    .aviso i {
        margin-top: 2px;
        font-size: 18px;
        color: #4c6fe0;
    }

    .aviso b { color: #171c7c; }

    .lienzo { position: relative; height: 330px; }
    .lienzo.alto { height: 420px; }

    .vacio {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 100%;
        min-height: 180px;
        color: #8a9cc0;
        text-align: center;
        padding: 20px;
    }

    .vacio i { font-size: 30px; opacity: 0.55; }
    .vacio p { font-size: 14px; max-width: 420px; line-height: 1.55; }

    /* Leyenda de asistencia */
    .leyenda {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-top: 16px;
        font-size: 13px;
        color: #64789f;
    }

    .leyenda span { display: inline-flex; align-items: center; gap: 7px; }

    .punto {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    /* ================================
       RESPONSIVE
    ================================ */

    @media (max-width: 1100px) {
        .kpi-grid, .mini-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .graficos { grid-template-columns: minmax(0, 1fr); }
    }

    @media (max-width: 620px) {
        .kpi-grid, .mini-grid { grid-template-columns: minmax(0, 1fr); }
        .panel-head h1 { font-size: 27px; }
    }
</style>

{{-- Encabezado y filtro de cuatrimestre --}}
@php
    $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);

    // Etiqueta del periodo, repetida en cada grafico: un cero sin decir de que
    // cuatrimestre es no se puede distinguir de un problema de captura. Lleva
    // las fechas para que la grafica se entienda suelta, sin el filtro arriba.
    $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
    $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y')
        . ' – ' . $sel->fecha_fin->format('d/m/y');
@endphp
<div class="panel-head">
    <div>
        <h1>Panel de la Rectora</h1>
        <p>
            Cuatrimestre {{ str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
            · {{ $sel->fecha_inicio->format('d/m/Y') }} a {{ $sel->fecha_fin->format('d/m/Y') }}
        </p>
    </div>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="filtro">
        <label for="cuatrimestre">Cuatrimestre</label>
        {{-- Sin opcion "Todos": cada grafico compara un solo periodo y mezclar
             dos en la misma barra no produce una cifra interpretable. --}}
        <select name="cuatrimestre" id="cuatrimestre" onchange="this.form.submit()">
            @foreach ($cuatrimestres as $c)
                <option value="{{ $c->id_cuatrimestre }}" @selected($idCuatrimestre === $c->id_cuatrimestre)>
                    Q{{ str_pad($c->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                    ({{ $c->fecha_inicio->format('d/m') }} – {{ $c->fecha_fin->format('d/m/y') }})
                </option>
            @endforeach
        </select>
        <noscript><button type="submit">Ver</button></noscript>
    </form>
</div>

@php
    // El grafico de cupos lista los 45 cursos en oferta, y sin inscripciones el
    //color claro se repite en todas las barras: 45 barras vacias se leen como
    // un fallo de la pagina en vez de como un periodo sin matricula.
    $cursosConInscritos = array_filter(
        $inscripcionPorCurso,
        fn ($f) => $f['inscritos'] > 0
    );
@endphp

@if (! $cursosConInscritos && $kpis['totalCalificaciones'] === 0)
    <div class="aviso">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <b>Aún no hay información registrada en este cuatrimestre.</b>
            No se han capturado matrículas, notas ni asistencias, así que los
            gráficos aparecen vacíos. Se llenarán solos conforme se registren.
        </div>
    </div>
@endif

{{-- ============ FILA DE KPIs ============ --}}
<div class="kpi-grid">

    <div class="card kpi">
        <div>
            <div class="kpi-top">
                <span class="kpi-label">Estudiantes</span>
                <span class="kpi-icon ton-azul"><i class="fa-solid fa-user-graduate"></i></span>
            </div>
            <div class="kpi-valor">{{ number_format($kpis['estudiantes']) }}</div>
        </div>
        <div class="kpi-pie">
            Con matrícula o nota en el cuatrimestre
            @if ($kpis['estudiantes'] === 0)
                · el periodo aún no empieza
            @else
                · de {{ number_format($kpis['estudiantesTotales']) }} en total
            @endif
        </div>
    </div>

    <div class="card kpi">
        <div>
            <div class="kpi-top">
                <span class="kpi-label">Cursos en oferta</span>
                <span class="kpi-icon ton-violeta"><i class="fa-solid fa-book-open"></i></span>
            </div>
            <div class="kpi-valor">{{ number_format($kpis['cursosOferta']) }}</div>
        </div>
        <div class="kpi-pie">
            <b>{{ number_format($totalClases) }}</b> clases programadas
        </div>
    </div>

    <div class="card kpi">
        <div>
            <div class="kpi-top">
                <span class="kpi-label">Tasa de aprobación</span>
                <span class="kpi-icon ton-verde"><i class="fa-solid fa-circle-check"></i></span>
            </div>
            <div class="kpi-valor">
                @if ($kpis['tasaAprobacion'] === null)
                    <small>Sin datos</small>
                @else
                    {{ number_format($kpis['tasaAprobacion'], 1) }}<small>%</small>
                @endif
            </div>
        </div>
        <div class="kpi-pie">
            @if ($kpis['totalCalificaciones'] > 0)
                <b>{{ number_format($kpis['aprobadas']) }}</b> aprobadas /
                <b>{{ number_format($kpis['reprobadas']) }}</b> reprobadas
            @else
                Todavía no hay notas registradas
            @endif
        </div>
    </div>

    <div class="card kpi">
        <div>
            <div class="kpi-top">
                <span class="kpi-label">Asistencia general</span>
                <span class="kpi-icon ton-naranja"><i class="fa-solid fa-calendar-check"></i></span>
            </div>
            <div class="kpi-valor">
                @if ($kpis['pctAsistencia'] === null)
                    <small>Sin datos</small>
                @else
                    {{ number_format($kpis['pctAsistencia'], 1) }}<small>%</small>
                @endif
            </div>
        </div>
        <div class="kpi-pie">
            @if ($kpis['inasistencia'] !== null)
                <b>{{ number_format($kpis['inasistencia'], 1) }}%</b> de inasistencia
            @else
                Todavía no hay asistencias registradas
            @endif
        </div>
    </div>

</div>

{{-- ============ MINI INDICADORES: OPERACIÓN ============ --}}
<div class="mini-grid">

    <div class="card mini">
        <span class="mini-icon ton-azul"><i class="fa-solid fa-pen-to-square"></i></span>
        <div class="mini-texto">
            <div class="mini-valor">{{ number_format($kpis['inscritos']) }}</div>
            <div class="mini-label">Inscripciones del periodo</div>
        </div>
    </div>

    <div class="card mini">
        <span class="mini-icon ton-verde"><i class="fa-solid fa-seat"></i></span>
        <div class="mini-texto">
            <div class="mini-valor">
                {{ number_format($kpis['cuposLibres']) }}
                <small style="font-size:13px;color:#7a8db5;font-weight:600;">de {{ number_format($kpis['cupoTotal']) }}</small>
            </div>
            <div class="mini-label">Cupos libres</div>
        </div>
    </div>

    <div class="card mini">
        <span class="mini-icon ton-rojo"><i class="fa-solid fa-user-xmark"></i></span>
        <div class="mini-texto">
            <div class="mini-valor">
                @if ($kpis['inasistencia'] === null)
                    —
                @else
                    {{ number_format($kpis['inasistencia'], 1) }}%
                @endif
            </div>
            <div class="mini-label">Inasistencia</div>
        </div>
    </div>

    <div class="card mini">
        <span class="mini-icon ton-violeta"><i class="fa-solid fa-calculator"></i></span>
        <div class="mini-texto">
            <div class="mini-valor">
                @if ($kpis['promedio'] === null)
                    —
                @else
                    {{ number_format($kpis['promedio'], 2) }}<small style="font-size:13px;color:#7a8db5;font-weight:600;">/10</small>
                @endif
            </div>
            <div class="mini-label">Promedio general de notas</div>
        </div>
    </div>

</div>

{{-- ============ INSCRIPCIÓN POR CURSO: OCUPADO VS CUPO ============ --}}
<div class="card grafico ancho">
    <div class="grafico-head">
        <i class="fa-solid fa-chair"></i>
        <h2>Inscripción por curso: ocupado vs. cupo</h2>
        <span class="periodo-chip">{{ $periodoChip }}</span>
    </div>
    <p class="grafico-sub">
        Barra azul: lugares ocupados. Barra clara: cupos que quedan libres. Los cursos más llenos primero.
    </p>

    @if ($cursosConInscritos)
        <div class="lienzo alto" id="lienzoCupos">
            <canvas id="grafCupos"></canvas>
        </div>

        <div class="controles">
            <span class="contador" id="contadorCupos"></span>
            <button type="button" class="btn-ver plano" id="menosCupos" hidden>Ver menos</button>
            <button type="button" class="btn-ver" id="masCupos"></button>
        </div>

        <div class="leyenda">
            <span><i class="punto" style="background:#4c6fe0;"></i> Ocupado</span>
            <span><i class="punto" style="background:#d6e0f4;"></i> Cupo libre</span>
        </div>
    @else
        <div class="vacio">
            <i class="fa-solid fa-inbox"></i>
            <p>
                @if (count($inscripcionPorCurso))
                    En {{ $periodo }} todavía no hay inscripciones registradas,
                    así que no se puede llenar ningún lugar de los {{ count($inscripcionPorCurso) }} cursos en oferta.
                @else
                    En {{ $periodo }} no hay cursos en oferta.
                @endif
            </p>
        </div>
    @endif
</div>

{{-- ============ GRÁFICOS ============ --}}
<div class="graficos">

    {{-- Estudiantes por carrera --}}
    @php
        // Con filtro, el LEFT JOIN conserva las carreras sin movimiento, asi que
        // "hay filas" no basta: un pastel de ocho asmaticos en cero informa peor
        // que un estado vacio que diga que no hay nada todavia.
        $carrerasConAlumnos = array_filter(
            $inscritosPorCarrera,
            fn ($f) => $f['total'] > 0
        );
    @endphp
    <div class="card grafico">
        <div class="grafico-head">
            <i class="fa-solid fa-chart-pie"></i>
            <h2>Estudiantes por carrera</h2>
            <span class="periodo-chip">{{ $periodoChip }}</span>
        </div>
        <p class="grafico-sub">
            Con matrícula o nota en el cuatrimestre
        </p>

        @if ($carrerasConAlumnos)
            <div class="lienzo"><canvas id="grafCarrera"></canvas></div>
        @else
            <div class="vacio">
                <i class="fa-solid fa-inbox"></i>
                <p>En {{ $periodo }} todavía no hay estudiantes con matrícula ni notas registradas.</p>
            </div>
        @endif
    </div>

    {{-- Rendimiento por curso --}}
    @php
        // Mismo criterio que los otros graficos: los 45 cursos en oferta salen
        // con cero si el periodo no tiene nada, y 45 barras apiladas vacias se
        // leen igual que un fallo de la pagina.
        $rendimientoConDatos = array_filter(
            $rendimientoPorCurso,
            fn ($f) => $f['aprobados'] + $f['reprobados'] + $f['enCurso'] > 0
        );
    @endphp
    <div class="card grafico">
        <div class="grafico-head">
            <i class="fa-solid fa-chart-column"></i>
            <h2>Rendimiento por curso</h2>
            <span class="periodo-chip">{{ $periodoChip }}</span>
        </div>
        <p class="grafico-sub">
            Aprobados, reprobados y en curso, ordenados por movimiento
        </p>

        @if ($rendimientoConDatos)
            <div class="lienzo alto" id="lienzoRendimiento">
                <canvas id="grafRendimiento"></canvas>
            </div>

            <div class="controles">
                <span class="contador" id="contadorRendimiento"></span>
                <button type="button" class="btn-ver plano" id="menosRendimiento" hidden>Ver menos</button>
                <button type="button" class="btn-ver" id="masRendimiento"></button>
            </div>
        @else
            <div class="vacio">
                <i class="fa-solid fa-inbox"></i>
                <p>En {{ $periodo }} todavía no hay notas ni inscripciones registradas.</p>
            </div>
        @endif
    </div>

</div>

{{-- Asistencia por curso --}}
@php
    // El grafico lista los 45 cursos aunque no tengan asistencias, asi que la
    // condicion es "hay algun dato", no "hay filas": en un cuatrimestre sin
    // capturar faltas (q2, q3) un grafico de 45 barras vacias no informa nada.
    $asistenciaConDatos = array_filter(
        $asistenciaPorCurso,
        fn ($f) => $f['porcentaje'] !== null
    );
@endphp
<div class="card grafico ancho">
    <div class="grafico-head">
        <i class="fa-solid fa-chart-simple-bar"></i>
        <h2>Asistencia por curso</h2>
        <span class="periodo-chip">{{ $periodoChip }}</span>
    </div>
    <p class="grafico-sub">
        Porcentaje de inasistencia sobre las clases programadas, de mayor a menor.
        @if ($asistenciaConDatos)
            De {{ count($asistenciaConDatos) }} de {{ count($asistenciaPorCurso) }} cursos en oferta;
            los que aun no tienen asistencias registradas salen sin barra.
        @else
            Todavía no hay asistencias registradas en {{ $periodo }}.
        @endif
    </p>

    @if ($asistenciaConDatos)
        <div class="lienzo alto" id="lienzoAsistencia">
            <canvas id="grafAsistencia"></canvas>
        </div>

        <div class="controles">
            <span class="contador" id="contadorAsistencia"></span>
            <button type="button" class="btn-ver plano" id="menosAsistencia" hidden>Ver menos</button>
            <button type="button" class="btn-ver" id="masAsistencia"></button>
        </div>

        <div class="leyenda">
            <span><i class="punto" style="background:#0a9560;"></i> Sin riesgo (menos de 25%)</span>
            <span><i class="punto" style="background:#c2560a;"></i> Cerca del límite (25–30%)</span>
            <span><i class="punto" style="background:#ec3e67;"></i> Pierde el curso (más de 30%)</span>
            <span><i class="punto" style="background:#b9c4d8;"></i> Sin asistencias registradas</span>
        </div>
    @else
        <div class="vacio">
            <i class="fa-solid fa-inbox"></i>
            <p>En {{ $periodo }} todavía no hay asistencias registradas.</p>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        // Paleta alineada al resto de la interfaz
        var azul = '#4c6fe0', verde = '#0a9560', rojo = '#ec3e67', naranja = '#c2560a';
        // Gris de "sin dato": legible sobre el blanco de las tarjetas, y
        // deliberadamente apagado para que no se confunda con un estado real.
        var gris = '#b9c4d8';
        var rejilla = 'rgba(224, 232, 245, 0.9)';
        var texto = '#64789f';

        Chart.defaults.font.family = '"DM Sans", sans-serif';
        Chart.defaults.color = texto;

        var opcionesBase = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { boxWidth: 12, boxHeight: 12, font: { size: 12, weight: '600' } } },
                tooltip: { padding: 10, cornerRadius: 10, titleFont: { size: 13 }, bodyFont: { size: 13 } }
            },
            scales: {
                x: { grid: { color: rejilla, drawBorder: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: rejilla, drawBorder: false }, ticks: { font: { size: 11 } } }
            }
        };

        /**
         * Copia profunda de la configuracion de un grafico, conservando las
         * funciones. Un JSON.parse(JSON.stringify()) serviria, pero se lleva por
         * delante los callbacks de ticks y tooltip, que son justo lo que permite
         * pintar el "%" y explicar los cursos sin registros.
         */
        function clonar(objeto) {
            if (Array.isArray(objeto)) {
                return objeto.map(clonar);
            }

            if (objeto === null || typeof objeto !== 'object') {
                return objeto;
            }

            var copia = {};

            Object.keys(objeto).forEach(function (clave) {
                copia[clave] = clonar(objeto[clave]);
            });

            return copia;
        }

        var paletaCarrera = ['#4c6fe0', '#6b4fd8', '#0a9560', '#c2560a', '#ec3e67', '#2f9bc4', '#8a7a3f', '#5a6f9c'];

        /**
         * Envuelve un grafico para que un fallo no se lleve por delante a los
         * demas, y quede anotado en la consola en vez de fallar en silencio.
         */
        function seguro(nombre, construir) {
            try {
                construir();
            } catch (e) {
                if (window.console) {
                    console.error('No se pudo dibujar el grafico ' + nombre + ':', e);
                }
            }
        }

        // Altura por barra visible: mantiene el grafico compacto mientras la
        // rectora no pide ver mas, y crece solo con lo que se revela.
        var ALTO_BARRA = 30;
        var ALTO_MINIMO = 200;
        var BLOQUE = {{ \App\Services\ReporteService::BLOQUE_CURSOS }};

        /**
         * Grafico de barras horizontales que revela de a BLOQUE cursos.
         * Todos los datos llegan al navegador; aqui solo se decide cuantos pintar.
         */
        function barrasProgressivas(cfg) {
            var canvas = document.getElementById(cfg.canvas);
            if (!canvas) return;

            var total = cfg.datos.length;
            var mostrados = Math.min(BLOQUE, total);
            var serie = cfg.series;

            // Chart.js pasa el indice de la vista ya recortada, no el del
            // arreglo completo: el color por fila tiene que leer de aqui.
            var vista = cfg.datos.slice(0, mostrados);

            var op = clonar(cfg.opciones);
            op.indexAxis = 'y';

            var chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: serie.map(function (s) {
                        return {
                            label: s.label,
                            data: [],
                            // Los colores van como arreglo, no como funcion: Chart.js
                            // resuelve las opciones por elemento en varias pasadas
                            // (leyenda incluida) y en algunas el dataIndex llega
                            // indefinido, lo que reventaba el grafico entero.
                            backgroundColor: cfg.colorPorFila ? [] : s.color,
                            borderRadius: 5
                        };
                    })
                },
                options: op
            });

            function pintar() {
                vista = cfg.datos.slice(0, mostrados);

                chart.data.labels = vista.map(function (f) { return f.curso; });

                serie.forEach(function (s, i) {
                    var dataset = chart.data.datasets[i];

                    dataset.data = vista.map(function (f) { return f[s.campo]; });

                    if (cfg.colorPorFila) {
                        dataset.backgroundColor = vista.map(cfg.colorPorFila);
                    }
                });

                if (cfg.etiqueta) {
                    op.plugins.tooltip.callbacks = {
                        label: function (contexto) {
                            return cfg.etiqueta(vista[contexto.dataIndex]);
                        }
                    };
                }

                document.getElementById(cfg.lienzo).style.height =
                    Math.max(mostrados * ALTO_BARRA + 90, ALTO_MINIMO) + 'px';

                chart.resize();
                chart.update();

                var restantes = total - mostrados;

                document.getElementById(cfg.contador).textContent =
                    'Mostrando ' + mostrados + ' de ' + total + ' ' + (cfg.unidad || 'cursos');

                var mas = document.getElementById(cfg.mas);
                var menos = document.getElementById(cfg.menos);

                mas.hidden = restantes === 0;
                mas.textContent = restantes <= BLOQUE
                    ? 'Ver las ' + restantes + ' restantes'
                    : 'Ver ' + BLOQUE + ' más';

                menos.hidden = mostrados <= BLOQUE;
            }

            document.getElementById(cfg.mas).addEventListener('click', function () {
                mostrados = Math.min(mostrados + BLOQUE, total);
                pintar();
            });

            document.getElementById(cfg.menos).addEventListener('click', function () {
                mostrados = Math.min(BLOQUE, total);
                pintar();
            });

            pintar();
        }

        // ---------- Inscripción por curso: ocupado vs cupo ----------
        seguro('inscripcion por curso', function () {
            var opCupos = clonar(opcionesBase);
            opCupos.scales.x.stacked = true;
            opCupos.scales.y.stacked = true;
            opCupos.scales.y.ticks.font = { size: 11 };
            opCupos.plugins.legend.display = false;
            opCupos.scales.x.beginAtZero = true;
            opCupos.scales.x.title = { display: true, text: 'Estudiantes' };
            opCupos.scales.x.ticks = {
                font: { size: 11 },
                precision: 0
            };

            barrasProgressivas({
                canvas: 'grafCupos',
                lienzo: 'lienzoCupos',
                contador: 'contadorCupos',
                mas: 'masCupos',
                menos: 'menosCupos',
                datos: @json($inscripcionPorCurso),
                // Ocupado y libre suman el cupo completo: la barra siempre
                // mide lo mismo y lo que se lee es la parte oscura.
                series: [
                    { campo: 'inscritos', label: 'Ocupado', color: azul },
                    { campo: 'libres', label: 'Cupo libre', color: '#d6e0f4' }
                ],
                etiqueta: function (fila) {
                    return [
                        fila.inscritos + ' de ' + fila.cupo + ' lugares',
                        'Ocupacion: ' + fila.ocupacion + '%'
                    ];
                },
                opciones: opCupos
            });
        });

        // ---------- Estudiantes por carrera ----------
        var elCarrera = document.getElementById('grafCarrera');
        if (elCarrera) {
            var datosCarrera = @json(collect($inscritosPorCarrera)->pluck('total', 'nombre')->all());

            seguro('estudiantes por carrera', function () {
                new Chart(elCarrera, {
                    type: 'doughnut',
                    data: {
                        labels: Object.keys(datosCarrera),
                        datasets: [{
                            data: Object.values(datosCarrera),
                            backgroundColor: paletaCarrera,
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '58%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 11, boxHeight: 11, padding: 12, font: { size: 11, weight: '600' } }
                            }
                        }
                    }
                });
            });
        }

        // ---------- Rendimiento por curso ----------
        seguro('rendimiento por curso', function () {
            var opRend = clonar(opcionesBase);
            opRend.scales.x.stacked = true;
            opRend.scales.y.stacked = true;
            opRend.scales.y.ticks.font = { size: 11 };
            opRend.plugins.legend.position = 'top';

            barrasProgressivas({
                canvas: 'grafRendimiento',
                lienzo: 'lienzoRendimiento',
                contador: 'contadorRendimiento',
                mas: 'masRendimiento',
                menos: 'menosRendimiento',
                datos: @json($rendimientoPorCurso),
                series: [
                    { campo: 'aprobados', label: 'Aprobados', color: verde },
                    { campo: 'reprobados', label: 'Reprobados', color: rojo },
                    { campo: 'enCurso', label: 'En curso', color: azul }
                ],
                opciones: opRend
            });
        });

        // ---------- Asistencia por curso ----------
        seguro('asistencia por curso', function () {
            var opAsis = clonar(opcionesBase);
            opAsis.plugins.legend.display = false;
            opAsis.scales.x.title = { display: true, text: '% de inasistencia' };
            opAsis.scales.x.beginAtZero = true;
            // Techo en 35%: deja ver la distancia hasta las zonas de alerta
            // (25% y 30%) en vez de comprimir todo contra el maximo real.
            opAsis.scales.x.suggestedMax = 35;
            opAsis.scales.x.ticks = {
                font: { size: 11 },
                callback: function (v) { return v + '%'; }
            };

            function colorAlerta(nivel) {
                if (nivel === 'peligro') return rojo;
                if (nivel === 'advertencia') return naranja;
                if (nivel === @json(\App\Services\ReporteService::SIN_DATOS)) return gris;
                return verde;
            }

            barrasProgressivas({
                canvas: 'grafAsistencia',
                lienzo: 'lienzoAsistencia',
                contador: 'contadorAsistencia',
                mas: 'masAsistencia',
                menos: 'menosAsistencia',
                datos: @json($asistenciaPorCurso),
                series: [{ campo: 'porcentaje', label: '% inasistencia', color: verde }],
                colorPorFila: function (f) { return colorAlerta(f.alerta); },
                // El tooltip se arma aqui adentro porque necesita la vista
                // recortada, que solo existe dentro de barrasProgressivas.
                // Un curso sin asistencias no tiene barra: el tooltip explica
                // la ausencia de dato para que no se lea como un cero.
                etiqueta: function (fila) {
                    if (!fila || fila.porcentaje === null) {
                        return 'Sin asistencias registradas';
                    }
                    return fila.porcentaje + '% de inasistencia';
                },
                opciones: opAsis
            });
        });
    })();
</script>
@endpush
