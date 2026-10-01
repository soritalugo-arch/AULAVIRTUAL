@extends('layouts.app')

@section('titulo', 'Panel de la Rectora')

@section('contenido')

{{-- Encabezado y filtro de cuatrimestre --}}
@php
    $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
    $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
    $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
@endphp

<div class="max-w-7xl mx-auto space-y-6 sm:space-y-8 pb-10">
    
    <!-- Cabecera -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-2">
        <div>
            <h1 class="text-3xl sm:text-4xl font-bold text-[#171c7c] font-['Georgia'] mb-2">Panel de la Rectora</h1>
            <p class="text-[#64789f] text-sm sm:text-base">
                Cuatrimestre {{ str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                · {{ $sel->fecha_inicio->format('d/m/Y') }} a {{ $sel->fecha_fin->format('d/m/Y') }}
            </p>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-3 bg-white/95 border border-[#e0e8f5] rounded-2xl shadow-[0_8px_25px_rgba(70,100,160,0.08)] px-4 sm:px-5 py-3 w-full md:w-auto">
            <label for="cuatrimestre" class="text-sm font-semibold text-[#5a6f9c] whitespace-nowrap">Cuatrimestre</label>
            <select name="cuatrimestre" id="cuatrimestre" onchange="this.form.submit()" 
                    class="flex-grow sm:flex-grow-0 appearance-none border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold px-4 py-2.5 pr-10 cursor-pointer outline-none focus:border-[#4c5bc3]"
                    style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath fill='%235a6f9c' d='M1 1l5 5 5-5'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 14px center; background-size: 11px;">
                @foreach ($cuatrimestres as $c)
                    <option value="{{ $c->id_cuatrimestre }}" @selected($idCuatrimestre === $c->id_cuatrimestre)>
                        Q{{ str_pad($c->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                        ({{ $c->fecha_inicio->format('d/m') }} – {{ $c->fecha_fin->format('d/m/y') }})
                    </option>
                @endforeach
            </select>
            <noscript><button type="submit" class="bg-gradient-to-r from-[#4c5bc3] to-[#6e94ee] text-white rounded-xl px-5 py-2.5 text-sm font-semibold shadow-[0_6px_15px_rgba(76,91,195,0.25)]">Ver</button></noscript>
        </form>
    </div>

    @php
        $cursosConInscritos = array_filter($inscripcionPorCurso, fn ($f) => $f['inscritos'] > 0);
    @endphp

    @if (! $cursosConInscritos && $kpis['totalCalificaciones'] === 0)
        <div class="flex flex-col sm:flex-row items-start gap-3 p-4 sm:p-5 bg-[#f6f9ff] border border-[#dbe6fb] border-l-4 border-l-[#4c6fe0] rounded-2xl text-[#46578a] text-sm sm:text-base leading-relaxed">
            <i class="fa-solid fa-circle-info text-[#4c6fe0] text-lg mt-0.5 shrink-0"></i>
            <div>
                <b class="text-[#171c7c]">Aún no hay información registrada en este cuatrimestre.</b>
                No se han capturado matrículas, notas ni asistencias, así que los gráficos aparecen vacíos. Se llenarán solos conforme se registren.
            </div>
        </div>
    @endif

    {{-- ============ FILA DE KPIs ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- KPI: Estudiantes -->
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs font-bold tracking-widest uppercase text-[#7a8db5]">Estudiantes</span>
                    <span class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-[#e8efff] text-[#4c6fe0] text-lg"><i class="fa-solid fa-user-graduate"></i></span>
                </div>
                <div class="text-4xl font-['Georgia'] font-bold text-[#171c7c] leading-tight">{{ number_format($kpis['estudiantes']) }}</div>
            </div>
            <div class="mt-3 text-[13px] text-[#64789f]">
                Con matrícula o nota en el cuatrimestre
                @if ($kpis['estudiantes'] === 0)
                    · el periodo aún no empieza
                @else
                    · de {{ number_format($kpis['estudiantesTotales']) }} en total
                @endif
            </div>
        </div>

        <!-- KPI: Cursos en oferta -->
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs font-bold tracking-widest uppercase text-[#7a8db5]">Cursos en oferta</span>
                    <span class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-[#efe9ff] text-[#6b4fd8] text-lg"><i class="fa-solid fa-book-open"></i></span>
                </div>
                <div class="text-4xl font-['Georgia'] font-bold text-[#171c7c] leading-tight">{{ number_format($kpis['cursosOferta']) }}</div>
            </div>
            <div class="mt-3 text-[13px] text-[#64789f]">
                <b class="text-[#2d4b99]">{{ number_format($totalClases) }}</b> clases programadas
            </div>
        </div>

        <!-- KPI: Tasa de aprobación -->
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs font-bold tracking-widest uppercase text-[#7a8db5]">Tasa de aprobación</span>
                    <span class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-[#e3f8ee] text-[#0a9560] text-lg"><i class="fa-solid fa-circle-check"></i></span>
                </div>
                <div class="text-4xl font-['Georgia'] font-bold text-[#171c7c] leading-tight">
                    @if ($kpis['tasaAprobacion'] === null)
                        <span class="text-lg font-sans font-semibold text-[#7a8db5]">Sin datos</span>
                    @else
                        {{ number_format($kpis['tasaAprobacion'], 1) }}<span class="text-lg font-sans font-semibold text-[#7a8db5] ml-1">%</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 text-[13px] text-[#64789f]">
                @if ($kpis['totalCalificaciones'] > 0)
                    <b class="text-[#2d4b99]">{{ number_format($kpis['aprobadas']) }}</b> aprobadas /
                    <b class="text-[#2d4b99]">{{ number_format($kpis['reprobadas']) }}</b> reprobadas
                @else
                    Todavía no hay notas registradas
                @endif
            </div>
        </div>

        <!-- KPI: Asistencia general -->
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="text-xs font-bold tracking-widest uppercase text-[#7a8db5]">Asistencia general</span>
                    <span class="w-11 h-11 shrink-0 flex items-center justify-center rounded-xl bg-[#fff0e0] text-[#c2560a] text-lg"><i class="fa-solid fa-calendar-check"></i></span>
                </div>
                <div class="text-4xl font-['Georgia'] font-bold text-[#171c7c] leading-tight">
                    @if ($kpis['pctAsistencia'] === null)
                        <span class="text-lg font-sans font-semibold text-[#7a8db5]">Sin datos</span>
                    @else
                        {{ number_format($kpis['pctAsistencia'], 1) }}<span class="text-lg font-sans font-semibold text-[#7a8db5] ml-1">%</span>
                    @endif
                </div>
            </div>
            <div class="mt-3 text-[13px] text-[#64789f]">
                @if ($kpis['inasistencia'] !== null)
                    <b class="text-[#2d4b99]">{{ number_format($kpis['inasistencia'], 1) }}%</b> de inasistencia
                @else
                    Todavía no hay asistencias registradas
                @endif
            </div>
        </div>
    </div>

    {{-- ============ MINI INDICADORES: OPERACIÓN ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <div class="bg-white border border-[#e0e8f5] rounded-[20px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-4 flex items-center gap-4">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-[#e8efff] text-[#4c6fe0]"><i class="fa-solid fa-pen-to-square"></i></span>
            <div class="min-w-0">
                <div class="text-[21px] font-bold text-[#1c2a63] leading-tight">{{ number_format($kpis['inscritos']) }}</div>
                <div class="text-xs text-[#7a8db5] truncate">Inscripciones del periodo</div>
            </div>
        </div>

        <div class="bg-white border border-[#e0e8f5] rounded-[20px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-4 flex items-center gap-4">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-[#e3f8ee] text-[#0a9560]"><i class="fa-solid fa-seat"></i></span>
            <div class="min-w-0">
                <div class="text-[21px] font-bold text-[#1c2a63] leading-tight">
                    {{ number_format($kpis['cuposLibres']) }}
                    <span class="text-[13px] text-[#7a8db5] font-semibold">de {{ number_format($kpis['cupoTotal']) }}</span>
                </div>
                <div class="text-xs text-[#7a8db5] truncate">Cupos libres</div>
            </div>
        </div>

        <div class="bg-white border border-[#e0e8f5] rounded-[20px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-4 flex items-center gap-4">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-[#ffe7ec] text-[#ec3e67]"><i class="fa-solid fa-user-xmark"></i></span>
            <div class="min-w-0">
                <div class="text-[21px] font-bold text-[#1c2a63] leading-tight">
                    @if ($kpis['inasistencia'] === null) — @else {{ number_format($kpis['inasistencia'], 1) }}% @endif
                </div>
                <div class="text-xs text-[#7a8db5] truncate">Inasistencia</div>
            </div>
        </div>

        <div class="bg-white border border-[#e0e8f5] rounded-[20px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-4 flex items-center gap-4">
            <span class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl bg-[#efe9ff] text-[#6b4fd8]"><i class="fa-solid fa-calculator"></i></span>
            <div class="min-w-0">
                <div class="text-[21px] font-bold text-[#1c2a63] leading-tight">
                    @if ($kpis['promedio'] === null) — @else {{ number_format($kpis['promedio'], 2) }}<span class="text-[13px] text-[#7a8db5] font-semibold">/10</span> @endif
                </div>
                <div class="text-xs text-[#7a8db5] truncate">Promedio general de notas</div>
            </div>
        </div>
    </div>

    {{-- ============ INSCRIPCIÓN POR CURSO ============ --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chair text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Inscripción por curso: ocupado vs. cupo</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold tracking-wide whitespace-nowrap w-fit">{{ $periodoChip }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-6">Barra azul: lugares ocupados. Barra clara: cupos que quedan libres. Los cursos más llenos primero.</p>

        @if ($cursosConInscritos)
            <div class="relative h-[350px] sm:h-[420px]" id="lienzoCupos">
                <canvas id="grafCupos"></canvas>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-5 pt-5 border-t border-[#eef3fb]">
                <span class="text-[13px] text-[#7a8db5] mr-auto" id="contadorCupos"></span>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl text-[#7a8db5] hover:bg-[#f2f6fd] transition-colors" id="menosCupos" hidden>Ver menos</button>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors" id="masCupos"></button>
            </div>

            <div class="flex flex-wrap gap-4 mt-4 text-[13px] text-[#64789f]">
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#4c6fe0]"></i> Ocupado</span>
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#d6e0f4]"></i> Cupo libre</span>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[180px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-md">
                    @if (count($inscripcionPorCurso))
                        En {{ $periodo }} todavía no hay inscripciones registradas, así que no se puede llenar ningún lugar de los {{ count($inscripcionPorCurso) }} cursos en oferta.
                    @else
                        En {{ $periodo }} no hay cursos en oferta.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- ============ GRÁFICOS SECUNDARIOS ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Estudiantes por carrera (Ocupa 1 columna) --}}
        @php
            $carrerasConAlumnos = array_filter($inscritosPorCarrera, fn ($f) => $f['total'] > 0);
        @endphp
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <i class="fa-solid fa-chart-pie text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Estudiantes por carrera</h2>
                <span class="px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold w-fit mb-2 sm:mb-0">{{ $periodoChip }}</span>
            </div>
            <p class="text-[13px] text-[#7a8db5] mb-6">Con matrícula o nota en el cuatrimestre</p>

            @if ($carrerasConAlumnos)
                <div class="relative h-[280px] sm:h-[330px]"><canvas id="grafCarrera"></canvas></div>
            @else
                <div class="flex flex-col items-center justify-center gap-3 min-h-[180px] p-5 text-center text-[#8a9cc0]">
                    <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                    <p class="text-sm max-w-[250px]">En {{ $periodo }} todavía no hay estudiantes con matrícula ni notas registradas.</p>
                </div>
            @endif
        </div>

        {{-- Rendimiento por curso (Ocupa 2 columnas) --}}
        @php
            $rendimientoConDatos = array_filter($rendimientoPorCurso, fn ($f) => $f['aprobados'] + $f['reprobados'] + $f['enCurso'] > 0);
        @endphp
        <div class="lg:col-span-2 bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-chart-column text-[22px] text-[#6382dc]"></i>
                    <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Rendimiento por curso</h2>
                </div>
                <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap w-fit">{{ $periodoChip }}</span>
            </div>
            <p class="text-[13px] text-[#7a8db5] mb-6">Aprobados, reprobados y en curso, ordenados por movimiento</p>

            @if ($rendimientoConDatos)
                <div class="relative h-[350px] sm:h-[420px]" id="lienzoRendimiento">
                    <canvas id="grafRendimiento"></canvas>
                </div>

                <div class="flex flex-wrap items-center gap-3 mt-5 pt-5 border-t border-[#eef3fb]">
                    <span class="text-[13px] text-[#7a8db5] mr-auto" id="contadorRendimiento"></span>
                    <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl text-[#7a8db5] hover:bg-[#f2f6fd] transition-colors" id="menosRendimiento" hidden>Ver menos</button>
                    <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors" id="masRendimiento"></button>
                </div>
            @else
                <div class="flex flex-col items-center justify-center gap-3 min-h-[180px] p-5 text-center text-[#8a9cc0]">
                    <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                    <p class="text-sm max-w-[250px]">En {{ $periodo }} todavía no hay notas ni inscripciones registradas.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Asistencia por curso --}}
    @php
        $asistenciaConDatos = array_filter($asistenciaPorCurso, fn ($f) => $f['porcentaje'] !== null);
    @endphp
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chart-simple-bar text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Asistencia por curso</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap w-fit">{{ $periodoChip }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-6 max-w-3xl">
            Porcentaje de inasistencia sobre las clases programadas, de mayor a menor.
            @if ($asistenciaConDatos)
                De {{ count($asistenciaConDatos) }} de {{ count($asistenciaPorCurso) }} cursos en oferta; los que aún no tienen asistencias registradas salen sin barra.
            @else
                Todavía no hay asistencias registradas en {{ $periodo }}.
            @endif
        </p>

        @if ($asistenciaConDatos)
            <div class="relative h-[350px] sm:h-[420px]" id="lienzoAsistencia">
                <canvas id="grafAsistencia"></canvas>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-5 pt-5 border-t border-[#eef3fb]">
                <span class="text-[13px] text-[#7a8db5] mr-auto" id="contadorAsistencia"></span>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl text-[#7a8db5] hover:bg-[#f2f6fd] transition-colors" id="menosAsistencia" hidden>Ver menos</button>
                <button type="button" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors" id="masAsistencia"></button>
            </div>

            <div class="flex flex-wrap gap-4 mt-4 text-[13px] text-[#64789f]">
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#0a9560]"></i> Sin riesgo (menos de 25%)</span>
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#c2560a]"></i> Cerca del límite (25–30%)</span>
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#ec3e67]"></i> Pierde el curso (más de 30%)</span>
                <span class="flex items-center gap-2"><i class="w-2.5 h-2.5 rounded-full bg-[#b9c4d8]"></i> Sin asistencias registradas</span>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[180px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-[250px]">En {{ $periodo }} todavía no hay asistencias registradas.</p>
            </div>
        @endif
    </div>

    {{-- ============ RENDIMIENTO POR ESTUDIANTE ============ --}}
    @php
        $rendEstConDatos = array_filter(
            $rendimientoPorEstudiante,
            fn ($f) => $f['promedio'] !== null || ($f['aprobadas'] + $f['reprobadas']) > 0
        );
    @endphp
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-ranking-star text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Rendimiento por estudiante</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap">{{ $periodoChip }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Promedio, aprobadas y reprobadas de cada estudiante con matrícula o notas en el periodo.</p>

        @if ($rendEstConDatos)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold">Estudiante</th>
                            <th class="py-3 pr-4 font-semibold">Carrera</th>
                            <th class="py-3 pr-4 font-semibold text-right">Promedio</th>
                            <th class="py-3 pr-4 font-semibold text-right">Aprobadas</th>
                            <th class="py-3 font-semibold text-right">Reprobadas</th>
                        </tr>
                    </thead>
                    <tbody class="text-[#36487a]">
                        @foreach ($rendimientoPorEstudiante as $fila)
                            @if ($fila['promedio'] === null && ($fila['aprobadas'] + $fila['reprobadas']) === 0)
                                @continue
                            @endif
                            <tr class="border-b border-[#f2f6fd] last:border-0">
                                <td class="py-3 pr-4 font-semibold text-[#171c7c]">{{ $fila['estudiante'] }}</td>
                                <td class="py-3 pr-4">{{ $fila['carrera'] }}</td>
                                <td class="py-3 pr-4 text-right font-bold text-[#2f55c4]">{{ $fila['promedio'] !== null ? number_format($fila['promedio'], 2) : '—' }}</td>
                                <td class="py-3 pr-4 text-right text-[#0a9560]">{{ $fila['aprobadas'] }}</td>
                                <td class="py-3 text-right text-[#ec3e67]">{{ $fila['reprobadas'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-[300px]">En {{ $periodo }} todavía no hay estudiantes con matrícula ni notas registradas.</p>
            </div>
        @endif
    </div>

    {{-- ============ ESTUDIANTES CON DEUDA ============ --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Estudiantes con deuda</h2>
            <span class="px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold w-fit">{{ $deudores->count() }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Tienen bloqueada la matrícula mientras no regularicen su estado de cuenta.</p>

        @if ($deudores->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold">Estudiante</th>
                            <th class="py-3 pr-4 font-semibold">Cédula</th>
                            <th class="py-3 font-semibold">Carrera</th>
                        </tr>
                    </thead>
                    <tbody class="text-[#36487a]">
                        @foreach ($deudores as $d)
                            <tr class="border-b border-[#f2f6fd] last:border-0">
                                <td class="py-3 pr-4 font-semibold text-[#171c7c]">{{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}</td>
                                <td class="py-3 pr-4">{{ $d->cedula }}</td>
                                <td class="py-3">{{ $d->carrera?->nombre }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-circle-check text-3xl text-[#0a9560]"></i>
                <p class="text-sm">No hay estudiantes con deuda registrada.</p>
            </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;

        var azul = '#4c6fe0', verde = '#0a9560', rojo = '#ec3e67', naranja = '#c2560a';
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

        function clonar(objeto) {
            if (Array.isArray(objeto)) return objeto.map(clonar);
            if (objeto === null || typeof objeto !== 'object') return objeto;
            var copia = {};
            Object.keys(objeto).forEach(function (clave) { copia[clave] = clonar(objeto[clave]); });
            return copia;
        }

        var paletaCarrera = ['#4c6fe0', '#6b4fd8', '#0a9560', '#c2560a', '#ec3e67', '#2f9bc4', '#8a7a3f', '#5a6f9c'];

        function seguro(nombre, construir) {
            try { construir(); } catch (e) {
                if (window.console) console.error('No se pudo dibujar el grafico ' + nombre + ':', e);
            }
        }

        var ALTO_BARRA = 30;
        var ALTO_MINIMO = 200;
        var BLOQUE = {{ \App\Services\ReporteService::BLOQUE_CURSOS }};

        function barrasProgressivas(cfg) {
            var canvas = document.getElementById(cfg.canvas);
            if (!canvas) return;

            var total = cfg.datos.length;
            var mostrados = Math.min(BLOQUE, total);
            var serie = cfg.series;
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
                    if (cfg.colorPorFila) dataset.backgroundColor = vista.map(cfg.colorPorFila);
                });

                if (cfg.etiqueta) {
                    op.plugins.tooltip.callbacks = {
                        label: function (contexto) { return cfg.etiqueta(vista[contexto.dataIndex]); }
                    };
                }

                document.getElementById(cfg.lienzo).style.height = Math.max(mostrados * ALTO_BARRA + 90, ALTO_MINIMO) + 'px';
                chart.resize();
                chart.update();

                var restantes = total - mostrados;
                document.getElementById(cfg.contador).textContent = 'Mostrando ' + mostrados + ' de ' + total + ' ' + (cfg.unidad || 'cursos');

                var mas = document.getElementById(cfg.mas);
                var menos = document.getElementById(cfg.menos);
                mas.hidden = restantes === 0;
                mas.textContent = restantes <= BLOQUE ? 'Ver las ' + restantes + ' restantes' : 'Ver ' + BLOQUE + ' más';
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

        // ---------- Inscripción por curso ----------
        seguro('inscripcion por curso', function () {
            var opCupos = clonar(opcionesBase);
            opCupos.scales.x.stacked = true;
            opCupos.scales.y.stacked = true;
            opCupos.scales.y.ticks.font = { size: 11 };
            opCupos.plugins.legend.display = false;
            opCupos.scales.x.beginAtZero = true;
            opCupos.scales.x.title = { display: true, text: 'Estudiantes' };
            opCupos.scales.x.ticks = { font: { size: 11 }, precision: 0 };

            barrasProgressivas({
                canvas: 'grafCupos', lienzo: 'lienzoCupos', contador: 'contadorCupos',
                mas: 'masCupos', menos: 'menosCupos', datos: @json($inscripcionPorCurso),
                series: [
                    { campo: 'inscritos', label: 'Ocupado', color: azul },
                    { campo: 'libres', label: 'Cupo libre', color: '#d6e0f4' }
                ],
                etiqueta: function (fila) {
                    return [ fila.inscritos + ' de ' + fila.cupo + ' lugares', 'Ocupacion: ' + fila.ocupacion + '%' ];
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
                        datasets: [{ data: Object.values(datosCarrera), backgroundColor: paletaCarrera, borderColor: '#fff', borderWidth: 2 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '58%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 11, boxHeight: 11, padding: 12, font: { size: 11, weight: '600' } } } }
                    }
                });
            });
        }

        // ---------- Rendimiento por curso ----------
        seguro('rendimiento por curso', function () {
            var opRend = clonar(opcionesBase);
            opRend.scales.x.stacked = true; opRend.scales.y.stacked = true;
            opRend.scales.y.ticks.font = { size: 11 }; opRend.plugins.legend.position = 'top';

            barrasProgressivas({
                canvas: 'grafRendimiento', lienzo: 'lienzoRendimiento', contador: 'contadorRendimiento',
                mas: 'masRendimiento', menos: 'menosRendimiento', datos: @json($rendimientoPorCurso),
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
            opAsis.scales.x.beginAtZero = true; opAsis.scales.x.suggestedMax = 35;
            opAsis.scales.x.ticks = { font: { size: 11 }, callback: function (v) { return v + '%'; } };

            function colorAlerta(nivel) {
                if (nivel === 'peligro') return rojo;
                if (nivel === 'advertencia') return naranja;
                if (nivel === @json(\App\Services\ReporteService::SIN_DATOS)) return gris;
                return verde;
            }

            barrasProgressivas({
                canvas: 'grafAsistencia', lienzo: 'lienzoAsistencia', contador: 'contadorAsistencia',
                mas: 'masAsistencia', menos: 'menosAsistencia', datos: @json($asistenciaPorCurso),
                series: [{ campo: 'porcentaje', label: '% inasistencia', color: verde }],
                colorPorFila: function (f) { return colorAlerta(f.alerta); },
                etiqueta: function (fila) {
                    if (!fila || fila.porcentaje === null) return 'Sin asistencias registradas';
                    return fila.porcentaje + '% de inasistencia';
                },
                opciones: opAsis
            });
        });
    })();
</script>
@endpush