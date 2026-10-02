@extends('layouts.admin')

@section('titulo', 'Plan de estudios · Panel de la Rectora')
@section('tituloPantalla', 'Plan de estudios')

@section('panel')

    @php
        $totalMaterias = $etapas->sum(fn ($e) => $e['materias']->count());
    @endphp

    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center gap-5">
            <div class="min-w-0">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-book-open text-[22px] text-[#6382dc]"></i>
                    <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">{{ $seleccionada->nombre }}</h2>
                </div>
                <p class="text-[13px] text-[#7a8db5] mt-1.5">
                    {{ $seleccionada->duracion }} cuatrimestres · {{ $totalMaterias }} materias en total ·
                    de 2 a 3 por cuatrimestre. Las marcadas como base se comparten entre carreras.
                </p>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-3 lg:ml-auto">
                <label for="carrera" class="text-sm font-semibold text-[#5a6f9c] whitespace-nowrap">Carrera</label>
                <select name="carrera" id="carrera" onchange="this.form.submit()"
                        class="appearance-none border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold px-4 py-2.5 pr-10 cursor-pointer outline-none focus:border-[#4c5bc3]"
                        style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath fill='%235a6f9c' d='M1 1l5 5 5-5'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 14px center; background-size: 11px;">
                    @foreach ($carreras as $c)
                        <option value="{{ $c->id_carrera }}" @selected($seleccionada->id_carrera === $c->id_carrera)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
                <noscript>
                    <button type="submit" class="bg-gradient-to-r from-[#4c5bc3] to-[#6e94ee] text-white rounded-xl px-5 py-2.5 text-sm font-semibold shadow-[0_6px_15px_rgba(76,91,195,0.25)]">Ver</button>
                </noscript>
            </form>
        </div>
    </div>

    @if ($etapas->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
            <i class="fa-solid fa-book-open text-3xl opacity-55"></i>
            <p class="text-sm max-w-[300px]">Esta carrera todavía no tiene materias asignadas al plan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach ($etapas as $etapa)
                <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="flex items-center gap-2.5 text-[15px] font-bold text-[#171c7c]">
                            <span class="w-8 h-8 rounded-full bg-gradient-to-br from-[#e5edff] to-[#d3e1ff] text-[#4c6fe0] text-[13px] flex items-center justify-center shrink-0">
                                {{ $etapa['numero'] }}
                            </span>
                            Cuatrimestre del plan
                        </h3>
                        <span class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">{{ $etapa['materias']->count() }} materias</span>
                    </div>

                    <ul class="space-y-2.5">
                        @foreach ($etapa['materias'] as $materia)
                            <li class="flex items-center gap-3 px-3.5 py-3 rounded-2xl border border-[#eef3fb] bg-[#fafcff]">
                                <i class="fa-solid fa-book-open text-[#a7b8da] text-sm shrink-0"></i>
                                <p class="min-w-0 flex-1 text-[14px] font-semibold text-[#24356e] leading-snug">{{ $materia->nombre }}</p>
                                @if (in_array($materia->nombre, \Database\Seeders\CursoSeeder::materiasBase(), true))
                                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-[11px] font-bold">Base</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

@endsection