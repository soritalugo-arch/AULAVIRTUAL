@extends('layouts.admin')

@section('titulo', 'Período académico · Panel de la Rectora')
@section('tituloPantalla', 'Período académico')

@section('panel')

    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-2xl bg-[#eef3ff] flex items-center justify-center shrink-0">
                <i class="fa-solid fa-calendar-days text-[#2f55c4]"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl font-['Georgia'] font-bold text-[#171c7c]">Cada período tiene tres momentos</h2>
                <p class="text-[13px] text-[#7a8db5] mt-1">
                    En <strong>matrícula abierta</strong> los estudiantes solo se inscriben (y pueden retirarse).
                    Al pasar a <strong>en cursado</strong> la inscripción se cierra: los profesores califican y
                    registran asistencia, y el estudiante ve sus notas. Al <strong>cerrarlo</strong> todo queda
                    histórico y nadie puede modificar nada. No hace falta esperar días: cada cambio es al instante.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-[#e8f7ee] border border-[#bfe8cd] text-[#1d7a46] rounded-2xl px-5 py-4 mb-6 text-sm font-semibold flex items-start gap-3">
            <i class="fa-solid fa-circle-check mt-0.5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @php
        $estados = [
            'matriculacion' => ['Matrícula abierta', 'bg-[#eef3ff] text-[#2f55c4] border-[#c9d9fb]'],
            'en_curso'      => ['En cursado', 'bg-[#e8f7ee] text-[#1d7a46] border-[#bfe8cd]'],
            'cerrado'       => ['Cerrado', 'bg-[#eef1f6] text-[#66748f] border-[#dce2ec]'],
        ];
    @endphp

    <div class="grid gap-4">
        @foreach ($periodos as $periodo)
            @php
                $q = 'Q' . str_pad($periodo->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
                [$etiqueta, $badge] = $estados[$periodo->estado];
                $fechas = $periodo->fecha_inicio->format('d/m/Y') . ' al ' . $periodo->fecha_fin->format('d/m/Y');
            @endphp

            <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center gap-5
                        {{ $periodo->id_cuatrimestre === $idCuatrimestre ? 'ring-2 ring-[#2f55c4]/40' : '' }}">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-lg font-['Georgia'] font-bold text-[#171c7c]">{{ $q }}</h3>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border {{ $badge }}">
                            <i class="fa-solid {{
                                $periodo->estado === 'matriculacion' ? 'fa-door-open'
                                : ($periodo->estado === 'en_curso' ? 'fa-chalkboard-user' : 'fa-lock')
                            }}"></i>
                            {{ $etiqueta }}
                        </span>
                    </div>
                    <p class="text-[13px] text-[#7a8db5] mt-1.5">{{ $fechas }}</p>
                </div>

                <form method="POST" action="{{ route('admin.periodo.estado') }}"
                      class="flex flex-wrap items-center gap-3 lg:shrink-0">
                    @csrf
                    <input type="hidden" name="id_cuatrimestre" value="{{ $periodo->id_cuatrimestre }}">
                    <label for="estado-{{ $periodo->id_cuatrimestre }}" class="text-sm font-semibold text-[#5a6f9c] whitespace-nowrap">
                        Momento del período
                    </label>
                    <select name="estado" id="estado-{{ $periodo->id_cuatrimestre }}"
                            class="appearance-none border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold px-4 py-2.5 pr-10 cursor-pointer outline-none focus:border-[#4c5bc3]">
                        @foreach ($estados as $valor => [$nombre, $_])
                            <option value="{{ $valor }}" @selected($periodo->estado === $valor)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2f55c4] text-white text-sm font-bold hover:bg-[#2748ab] transition-colors">
                        <i class="fa-solid fa-check"></i>
                        Cambiar
                    </button>
                </form>
            </div>
        @endforeach
    </div>

@endsection