@extends('layouts.admin')

@section('titulo', 'Asistencia · Panel de la Rectora')
@section('tituloPantalla', 'Asistencia')

@section('panel')

    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
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

@endsection

@push('scripts')
    @include('admin.partials.graficos', ['graficos' => ['asistencia' => $asistenciaPorCurso]])
@endpush