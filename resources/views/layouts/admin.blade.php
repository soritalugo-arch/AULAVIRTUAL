@extends('layouts.app')

@section('contenido')

    {{-- Cabecera global: título de la sección y filtro de cuatrimestre --}}
    @php
        $sel = $cuatrimestres->firstWhere('id_cuatrimestre', $idCuatrimestre);
        $periodo = 'Q' . str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT);
        $periodoChip = $periodo . ' · ' . $sel->fecha_inicio->format('d/m/y') . ' – ' . $sel->fecha_fin->format('d/m/y');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-12 pt-8">

        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-[#171c7c] font-['Georgia']">@yield('tituloPantalla', 'Panel de la Rectora')</h1>
                <p class="text-[#64789f] text-sm sm:text-base mt-1">
                    Cuatrimestre {{ str_pad($sel->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                    · {{ $sel->fecha_inicio->format('d/m/Y') }} a {{ $sel->fecha_fin->format('d/m/Y') }}
                </p>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-white/95 border border-[#e0e8f5] rounded-2xl shadow-[0_8px_25px_rgba(70,100,160,0.08)] px-4 sm:px-5 py-3 w-full lg:w-auto">
                <label for="cuatrimestre" class="text-sm font-semibold text-[#5a6f9c] whitespace-nowrap">Cuatrimestre</label>
                <select name="cuatrimestre" id="cuatrimestre" onchange="this.form.submit()"
                        class="flex-grow lg:flex-grow-0 appearance-none border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold px-4 py-2.5 pr-10 cursor-pointer outline-none focus:border-[#4c5bc3]"
                        style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath fill='%235a6f9c' d='M1 1l5 5 5-5'/%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 14px center; background-size: 11px;">
                    @foreach ($cuatrimestres as $c)
                        <option value="{{ $c->id_cuatrimestre }}" @selected($idCuatrimestre === $c->id_cuatrimestre)>
                            Q{{ str_pad($c->id_cuatrimestre, 2, '0', STR_PAD_LEFT) }}
                            ({{ $c->fecha_inicio->format('d/m') }} – {{ $c->fecha_fin->format('d/m/y') }})
                        </option>
                    @endforeach
                </select>
                <noscript>
                    <button type="submit" class="bg-gradient-to-r from-[#4c5bc3] to-[#6e94ee] text-white rounded-xl px-5 py-2.5 text-sm font-semibold shadow-[0_6px_15px_rgba(76,91,195,0.25)]">Ver</button>
                </noscript>
            </form>
        </div>

        <div class="flex flex-col md:flex-row gap-6">

            {{-- Menú lateral: la rectora elige qué sección abrir --}}
            @include('admin.partials.menu')

            {{-- Contenido de la sección actual --}}
            <main class="flex-1 min-w-0 space-y-6 sm:space-y-8">
                @yield('panel')
            </main>
        </div>
    </div>

@endsection