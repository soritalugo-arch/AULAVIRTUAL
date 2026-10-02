@extends('layouts.app')

@section('titulo', 'Mis datos · AulaVirtual')
@section('clase_main', 'max-w-3xl mx-auto px-4 sm:px-6 py-10')

@section('contenido')

    @php
        $etiquetaRol = match ($rol) {
            'admin'     => 'Rectora',
            'profesor'  => 'Profesor',
            'estudiante' => 'Estudiante',
            default     => 'Usuario',
        };
    @endphp

    <div class="mb-6">
        <h1 class="text-3xl sm:text-4xl font-bold text-[#171c7c] font-['Georgia']">Mis datos</h1>
        <p class="text-[#64789f] text-sm sm:text-base mt-1">Tu información dentro de AulaVirtual.</p>
    </div>

    {{-- Datos de la cuenta --}}
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#dce8ff] to-[#c6d8ff] text-[#6382d9] flex items-center justify-center text-2xl shrink-0">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c] break-words">
                    {{ $usuario->nombres }} {{ $usuario->apellidos }}
                </h2>
                <p class="text-[13px] text-[#7a8db5] mt-0.5 break-words">{{ $usuario->email }}</p>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold whitespace-nowrap">{{ $etiquetaRol }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 pt-6 border-t border-[#eef3fb]">
            <div>
                <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Nombre</div>
                <div class="text-[15px] font-semibold text-[#24356e] mt-0.5">{{ $usuario->nombres }} {{ $usuario->apellidos }}</div>
            </div>
            <div>
                <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Correo</div>
                <div class="text-[15px] font-semibold text-[#24356e] mt-0.5 break-words">{{ $usuario->email }}</div>
            </div>
            @if ($datos['cedula'])
                <div>
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Cédula</div>
                    <div class="text-[15px] font-semibold text-[#24356e] mt-0.5">{{ $datos['cedula'] }}</div>
                </div>
            @endif
            @if ($usuario->telefono)
                <div>
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Teléfono</div>
                    <div class="text-[15px] font-semibold text-[#24356e] mt-0.5">{{ $usuario->telefono }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bloque del estudiante --}}
    @if ($rol === 'estudiante')
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mt-6">
            <div class="flex flex-wrap items-center gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-graduation-cap text-[22px] text-[#6382dc]"></i>
                    <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Mi situación académica</h2>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-5">
                @if ($datos['egresado'])
                    <span class="px-3 py-1.5 rounded-full bg-[#e7f7f0] text-[#0a9560] text-xs font-bold">
                        <i class="fa-solid fa-graduation-cap mr-1"></i>Egresado/a
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-[#eef2fc] text-[#4c6fe0] text-xs font-bold">En curso</span>
                @endif
                @if ($datos['deuda'])
                    <span class="px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>Con deuda
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-full bg-[#e7f7f0] text-[#0a9560] text-xs font-bold">Sin deuda</span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Carrera</div>
                    <div class="text-[15px] font-semibold text-[#24356e] mt-0.5">{{ $datos['carrera'] ?? 'Sin asignar' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#9aabd0]">Código de matrícula</div>
                    <div class="text-[15px] font-semibold text-[#24356e] mt-0.5">{{ $usuario->id_usuario }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bloque del profesor --}}
    @if ($rol === 'profesor')
        <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7 mt-6">
            <div class="flex flex-wrap items-center gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-chalkboard-user text-[22px] text-[#6382dc]"></i>
                    <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Mis cursos</h2>
                </div>
            </div>

            @if ($datos['cursos']->isNotEmpty())
                <ul class="space-y-2">
                    @foreach ($datos['cursos'] as $curso)
                        <li class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#f7f9ff] border border-[#e0e8f5] text-[15px] font-semibold text-[#24356e]">
                            <i class="fa-solid fa-book-open text-[#6382dc]"></i>
                            {{ $curso->nombre }}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-[#7a8db5]">Todavía no tienes cursos asignados.</p>
            @endif
        </div>
    @endif

@endsection