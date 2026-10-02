@extends('layouts.admin')

@section('titulo', 'Inscripciones · Panel de la Rectora')
@section('tituloPantalla', 'Inscripciones')

@section('panel')

    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
        $cursosConInscritos = array_filter($inscripcionPorCurso, fn ($f) => $f['inscritos'] > 0);
        $carrerasConAlumnos = array_filter($inscritosPorCarrera, fn ($f) => $f['total'] > 0);
    @endphp

    {{-- Inscripción por curso: ocupado vs cupo --}}
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

    {{-- Estudiantes por carrera --}}
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

@endsection

@push('scripts')
    @include('admin.partials.graficos', ['graficos' => ['inscripcion' => $inscripcionPorCurso, 'carrera' => $inscritosPorCarrera]])
@endpush