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
                    En <strong>matrícula abierta</strong> los estudiantes solo se inscriben (y pueden retirarse) y el
                    profesor apenas ve su horario. Al pasar a <strong>en cursado</strong> la inscripción se cierra: los
                    profesores ven a sus estudiantes, califican y registran asistencia. Al <strong>cerrarlo</strong> todo
                    queda histórico y nadie puede modificar nada. Cada cambio es al instante.
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
            'matriculacion' => [
                'Matrícula abierta',
                'fa-door-open',
                'bg-[#eef3ff] text-[#2f55c4] border-[#c9d9fb]',
                'Los estudiantes se inscriben y pueden retirarse. El profesor solo ve su horario, no a sus estudiantes.',
            ],
            'en_curso'      => [
                'En cursado',
                'fa-chalkboard-user',
                'bg-[#e8f7ee] text-[#1d7a46] border-[#bfe8cd]',
                'Los profesores ven a sus estudiantes, califican y registran asistencia. El estudiante ve sus notas.',
            ],
            'cerrado'       => [
                'Cerrado',
                'fa-lock',
                'bg-[#eef1f6] text-[#66748f] border-[#dce2ec]',
                'Todo queda histórico: nadie vuelve a modificar notas ni asistencia.',
            ],
        ];

        $tituloPeriodo = fn ($p) => $p->fecha_inicio->format('d/m/Y').' al '.$p->fecha_fin->format('d/m/Y');
    @endphp

    @if ($vigente)
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mb-6 ring-2 ring-[#2f55c4]/30">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-['Georgia'] font-bold text-[#171c7c]">Período en curso</h2>
                    <p class="text-[13px] text-[#7a8db5] mt-1">{{ $tituloPeriodo($vigente) }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border {{ $estados[$vigente->estado][2] }}">
                    <i class="fa-solid {{ $estados[$vigente->estado][1] }}"></i>
                    {{ $estados[$vigente->estado][0] }}
                </span>
            </div>

            <p class="text-[13px] text-[#5a6f9c] font-semibold mb-4">
                Solo hay un modo prendido a la vez. Elegí el momento y presioná "Cambiar modo".
            </p>

            <form method="POST" action="{{ route('admin.periodo.estado') }}">
                @csrf
                <input type="hidden" name="id_cuatrimestre" value="{{ $vigente->id_cuatrimestre }}">

                <fieldset>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($estados as $valor => [$etiqueta, $icono, $color, $detalle])
                            <label class="relative block cursor-pointer select-none">
                                <input type="radio" name="estado" value="{{ $valor }}"
                                       @checked($vigente->estado === $valor)
                                       class="peer sr-only">
                                <span class="absolute top-3 right-3 w-6 h-6 rounded-full bg-[#2f55c4] text-white text-xs flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <div class="border-2 rounded-2xl p-4 pr-10 h-full transition-colors border-[#dce7fa] bg-[#f7f9ff]
                                            hover:border-[#c9d9fb] hover:bg-[#eef3ff]
                                            peer-checked:border-[#2f55c4] peer-checked:bg-[#f4f7ff]">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid {{ $icono }} text-[#2f55c4]"></i>
                                        <span class="text-sm font-bold text-[#24356e]">{{ $etiqueta }}</span>
                                    </div>
                                    <p class="text-xs text-[#7a8db5] mt-2 leading-relaxed">{{ $detalle }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="mt-5">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#4c5bc3] to-[#6e94ee] text-white text-sm font-bold shadow-[0_6px_15px_rgba(76,91,195,0.25)] hover:opacity-95 transition-opacity">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        Cambiar modo
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mb-6">
            <p class="text-sm text-[#7a8db5]">
                No hay un período en curso hoy. Los cuatrimestres del sistema se listan abajo, solo de referencia.
            </p>
        </div>
    @endif

    {{-- Los demás períodos: pasado y próximo, solo de referencia (lectura) --}}
    @if ($periodos->where('id_cuatrimestre', '!=', $vigente?->id_cuatrimestre)->isNotEmpty())
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
            <h3 class="text-base font-['Georgia'] font-bold text-[#171c7c] mb-4">Otros períodos</h3>

            <div class="space-y-3">
                @foreach ($periodos as $periodo)
                    @if ($vigente && $periodo->id_cuatrimestre === $vigente->id_cuatrimestre)
                        @continue
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 border border-[#e0e8f5] rounded-2xl px-4 py-3">
                        <div class="flex items-center gap-3 text-sm font-semibold text-[#24356e]">
                            <i class="fa-regular fa-calendar text-[#7a8db5]"></i>
                            {{ $tituloPeriodo($periodo) }}
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border {{ $estados[$periodo->estado][2] ?? 'bg-[#eef1f6] text-[#66748f] border-[#dce2ec]' }}">
                            <i class="fa-solid {{ $estados[$periodo->estado][1] ?? 'fa-circle-info' }}"></i>
                            {{ $estados[$periodo->estado][0] ?? $periodo->estado }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection