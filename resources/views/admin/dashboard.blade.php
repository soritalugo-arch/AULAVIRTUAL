@extends('layouts.admin')

@section('titulo', 'Inicio · Panel de la Rectora')
@section('tituloPantalla', 'Inicio / Resumen')

@section('panel')

    @if ($kpis['inscritos'] === 0 && $kpis['totalCalificaciones'] === 0)
        <div class="flex flex-col sm:flex-row items-start gap-3 p-4 sm:p-5 bg-[#f6f9ff] border border-[#dbe6fb] border-l-4 border-l-[#4c6fe0] rounded-2xl text-[#46578a] text-sm sm:text-base leading-relaxed">
            <i class="fa-solid fa-circle-info text-[#4c6fe0] text-lg mt-0.5 shrink-0"></i>
            <div>
                <b class="text-[#171c7c]">Aún no hay información registrada en este cuatrimestre.</b>
                No se han capturado matrículas, notas ni asistencias. Los números de abajo se llenarán solos conforme se registren, y cada sección del menú se poblará con sus propios reportes.
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

    {{-- ============ ALERTAS RÁPIDAS ============ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <a href="{{ route('admin.deudas') }}" class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 flex items-center gap-4 group hover:border-[#f5d3dc] transition-colors">
            <span class="w-12 h-12 shrink-0 flex items-center justify-center rounded-xl bg-[#ffe7ec] text-[#ec3e67] text-xl group-hover:scale-105 transition-transform"><i class="fa-solid fa-money-bill-transfer"></i></span>
            <div class="min-w-0">
                <div class="text-3xl font-['Georgia'] font-bold text-[#ec3e67] leading-tight">{{ $deudoresCount }}</div>
                <div class="text-xs text-[#7a8db5]">Estudiantes con deuda · matrícula bloqueada</div>
            </div>
            <span class="ml-auto text-[#9aabd0] group-hover:text-[#2f55c4] group-hover:translate-x-1 transition-all"><i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="{{ route('admin.asistencia') }}" class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 flex items-center gap-4 group hover:border-[#fbdcba] transition-colors">
            <span class="w-12 h-12 shrink-0 flex items-center justify-center rounded-xl bg-[#fff0e0] text-[#c2560a] text-xl group-hover:scale-105 transition-transform"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <div class="min-w-0">
                <div class="text-3xl font-['Georgia'] font-bold text-[#c2560a] leading-tight">{{ $cursosEnRiesgo }}</div>
                <div class="text-xs text-[#7a8db5]">Cursos con más de 30% de inasistencia</div>
            </div>
            <span class="ml-auto text-[#9aabd0] group-hover:text-[#2f55c4] group-hover:translate-x-1 transition-all"><i class="fa-solid fa-arrow-right"></i></span>
        </a>
    </div>

@endsection