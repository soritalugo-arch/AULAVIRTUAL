@extends('layouts.app')

@section('titulo', 'Plan de estudios · AulaVirtual')
@section('clase_main', 'max-w-5xl mx-auto px-4 sm:px-6 py-10')

@section('menu_extra')
    <li>
        <a href="{{ route('estudiante.matriculacion') }}" class="nav-link {{ request()->routeIs('estudiante.matriculacion') ? 'active' : '' }}">Matriculación</a>
    </li>
    <li>
        <a href="{{ route('estudiante.notas') }}" class="nav-link {{ request()->routeIs('estudiante.notas') ? 'active' : '' }}">Mis Notas</a>
    </li>
    <li>
        <a href="{{ route('estudiante.historial') }}" class="nav-link {{ request()->routeIs('estudiante.historial', 'estudiante.certificado') ? 'active' : '' }}">Mi Historial</a>
    </li>
    <li>
        <a href="{{ route('estudiante.plan') }}" class="nav-link {{ request()->routeIs('estudiante.plan') ? 'active' : '' }}">Plan de Estudios</a>
    </li>
@endsection

@section('contenido')

    <div class="bg-gradient-to-br from-white to-[#f8fbff] rounded-[28px] border border-[#d9e6fb] shadow-[0_10px_30px_rgba(71,106,170,0.10)] p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-blue-200/30 rounded-full blur-3xl pointer-events-none transform rotate-12"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#dce8ff] to-[#edf3ff] flex items-center justify-center text-[#6284df] text-2xl shrink-0">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#171d7d] font-['Georgia'] mb-1">Plan de estudios</h1>
                    <p class="text-[#7087ba] text-sm sm:text-base leading-relaxed">
                        El recorrido de tu carrera, materia por materia.
                    </p>
                </div>
            </div>

            @if ($carrera)
                <span class="w-fit px-4 py-2 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-sm font-bold border border-[#dce7fa]">
                    <i class="fa-solid fa-graduation-cap mr-1.5"></i>{{ $carrera->nombre }}
                    · {{ $carrera->duracion }} cuatrimestres
                </span>
            @endif
        </div>
    </div>

    @if (! $carrera)
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-8 mt-6 text-center">
            <i class="fa-solid fa-circle-question text-3xl text-[#9aabd0]"></i>
            <p class="text-[#64789f] text-sm mt-3">Todavía no tienes una carrera asignada. Cuando te asignen una, aquí verás tu plan de estudios.</p>
        </div>
    @endif

    @if ($carrera)
        {{-- Avance global --}}
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-6 mt-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h2 class="text-lg sm:text-xl font-['Georgia'] font-bold text-[#171c7c]">Tu avance</h2>
                <span class="text-[13px] font-semibold text-[#4c6fe0] bg-[#eef2fc] rounded-full px-3 py-1.5">
                    {{ $aprobadas }} de {{ $totalMaterias }} materias aprobadas
                </span>
            </div>
            <div class="h-3 rounded-full bg-[#eef1f7] overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#4c5bc3] to-[#6e94ee] transition-all"
                     style="width: {{ $totalMaterias > 0 ? round($aprobadas * 100 / $totalMaterias) : 0 }}%"></div>
            </div>
            <p class="text-[13px] text-[#7a8db5] mt-2">
                Cada cuatrimestre del plan lleva de 2 a 3 materias. Aprueba todas para egresar.
            </p>
        </div>

        {{-- Recorrido por cuatrimestre --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
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
                                <div class="min-w-0 flex-1">
                                    <p class="text-[14px] font-semibold text-[#24356e] leading-snug">{{ $materia['nombre'] }}</p>
                                    @if ($materia['base'])
                                        <span class="text-[11px] font-semibold text-[#8a9cc0]">Materia base (compartida)</span>
                                    @endif
                                </div>
                                @if ($materia['estado'] === 'Aprobada')
                                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#e7f7f0] text-[#0a9560] text-[11px] font-bold">
                                        <i class="fa-solid fa-check mr-1"></i>Aprobada
                                    </span>
                                @elseif ($materia['estado'] === 'En curso')
                                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-[11px] font-bold">
                                        <i class="fa-solid fa-book mr-1"></i>En curso
                                    </span>
                                @else
                                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-[#eef1f7] text-[#7a8db5] text-[11px] font-bold">Pendiente</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif

@endsection