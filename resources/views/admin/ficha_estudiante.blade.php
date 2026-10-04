@extends('layouts.admin')

@section('titulo', 'Ficha del estudiante · Panel de la Rectora')
@section('tituloPantalla', 'Ficha del estudiante')

@section('panel')

    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $codigo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $codigo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
        $fmt = fn ($v) => $v !== null ? number_format($v, 2) : '—';

        $claseEstado = function (string $estado): string {
            if (str_contains($estado, 'Aprobado')) return 'text-[#0a9560] bg-[#e7f7f0]';
            if (str_contains($estado, 'Reprobado')) return 'text-[#ec3e67] bg-[#ffe7ec]';
            return 'text-[#4c6fe0] bg-[#eef2fc]';
        };
    @endphp

    <a href="{{ route('admin.rendimiento', ['cuatrimestre' => $idCuatrimestre]) }}"
       class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#4c6fe0] hover:text-[#2f55c4] transition-colors mb-4">
        <i class="fa-solid fa-arrow-left"></i> Volver a Rendimiento
    </a>

    {{-- Datos del estudiante --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#dce8ff] to-[#c6d8ff] text-[#6382d9] flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c] break-words">
                    {{ $estudiante->usuario->nombres }} {{ $estudiante->usuario->apellidos }}
                </h2>
                <p class="text-[13px] text-[#7a8db5] mt-0.5">
                    Cédula {{ $estudiante->cedula }} · {{ $estudiante->carrera?->nombre ?? 'Sin carrera' }}
                    @if ($estudiante->carrera)
                        ({{ $estudiante->carrera->duracion }} cuatrimestres)
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:ml-auto">
                @if ($egresado)
                    <span class="px-3 py-1.5 rounded-full bg-[#e7f7f0] text-[#0a9560] text-xs font-bold whitespace-nowrap">
                        <i class="fa-solid fa-graduation-cap mr-1"></i>Egresado/a
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap">En curso</span>
                @endif
                @if ($estudiante->deuda)
                    <span class="px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold whitespace-nowrap">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>Con deuda
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-[#e7f7f0] text-[#0a9560] text-xs font-bold whitespace-nowrap">Sin deuda</span>
                @endif
                <span class="px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap">{{ $periodoChip }}</span>
            </div>
        </div>
    </div>

    {{-- Cifras del periodo y de toda la carrera --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5">
            <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Promedio · {{ $codigo }}</div>
            <div class="text-2xl font-bold text-[#171c7c] mt-1">{{ $fmt($periodo['promedio']) }}</div>
            <div class="text-[12px] text-[#7a8db5] mt-1">{{ $periodo['aprobadas'] }} aprobadas · {{ $periodo['reprobadas'] }} reprobadas</div>
        </div>
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5">
            <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Promedio general</div>
            <div class="text-2xl font-bold text-[#171c7c] mt-1">{{ $fmt($carrera['promedio']) }}</div>
            <div class="text-[12px] text-[#7a8db5] mt-1">Toda su carrera, con nota registrada</div>
        </div>
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 col-span-2 lg:col-span-1">
            <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Cursadas en total</div>
            <div class="text-2xl font-bold text-[#171c7c] mt-1">{{ $carrera['aprobadas'] + $carrera['reprobadas'] }}</div>
            <div class="text-[12px] text-[#7a8db5] mt-1">
                <span class="text-[#0a9560] font-semibold">{{ $carrera['aprobadas'] }} aprobadas</span>
                <span class="mx-1">·</span>
                <span class="text-[#ec3e67] font-semibold">{{ $carrera['reprobadas'] }} reprobadas</span>
            </div>
        </div>
    </div>

    {{-- Materias del periodo --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-book-open text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Materias de {{ $periodoChip }}</h2>
            </div>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Su estado según las mismas reglas del aula: nota ≥ 6 y faltas ≤ 30%.</p>

        @if ($materias->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold">Materia</th>
                            <th class="py-3 pr-4 font-semibold text-right">Promedio</th>
                            <th class="py-3 pr-4 font-semibold text-right">Inasistencia</th>
                            <th class="py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="text-[#36487a]">
                        @foreach ($materias as $m)
                            <tr class="border-b border-[#f2f6fd] last:border-0">
                                <td class="py-3 pr-4 font-semibold text-[#171c7c]">{{ $m['curso'] }}</td>
                                <td class="py-3 pr-4 text-right font-bold text-[#2f55c4]">{{ $m['nota'] !== null ? number_format((float) $m['nota'], 2) : '—' }}</td>
                                <td class="py-3 pr-4 text-right">{{ $m['inasistencia'] }}%</td>
                                <td class="py-3">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $claseEstado($m['estado']) }}">{{ $m['estado'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[140px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-inbox text-3xl opacity-55"></i>
                <p class="text-sm max-w-[280px]">Sin materias matriculadas en este cuatrimestre.</p>
            </div>
        @endif
    </div>

@endsection