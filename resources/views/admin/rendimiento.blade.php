@extends('layouts.admin')

@section('titulo', 'Rendimiento · Panel de la Rectora')
@section('tituloPantalla', 'Rendimiento')

@section('panel')

    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
        $rendimientoConDatos = array_filter($rendimientoPorCurso, fn ($f) => $f['aprobados'] + $f['reprobados'] + $f['enCurso'] > 0);
        $rendEstConDatos = array_filter(
            $rendimientoPorEstudiante,
            fn ($f) => $f['promedio'] !== null || ($f['aprobadas'] + $f['reprobadas']) > 0
        );
    @endphp

    {{-- Rendimiento por curso --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
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

    {{-- Rendimiento por estudiante --}}
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

@endsection

@push('scripts')
    @include('admin.partials.graficos', ['graficos' => ['rendimiento' => $rendimientoPorCurso]])
@endpush