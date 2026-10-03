@extends('layouts.admin')

@section('titulo', 'Deudas · Panel de la Rectora')
@section('tituloPantalla', 'Deudas')

@section('panel')

    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-[22px] text-[#ec3e67]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Estudiantes con deuda</h2>
            </div>
            <span class="sm:ml-auto px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold whitespace-nowrap w-fit">{{ $deudores->count() }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Tienen bloqueada la matrícula mientras no regularicen su estado de cuenta. Haz clic en un estudiante para ver su ficha.</p>

        @if ($deudores->isNotEmpty())
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-[#9aabd0] text-sm pointer-events-none"></i>
                    <input type="search" id="buscarDeudor" placeholder="Buscar por nombre o cédula…"
                           class="w-full border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold pl-11 pr-4 py-2.5 outline-none focus:border-[#4c5bc3] placeholder:text-[#9aabd0]">
                </div>
                <span class="text-[13px] text-[#7a8db5] sm:ml-auto" id="contadorDeudores"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="nombre">Estudiante<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 pr-4 font-semibold cursor-pointer select-none" data-col="cedula">Cédula<span class="flecha-orden ml-1"></span></th>
                            <th class="py-3 font-semibold cursor-pointer select-none" data-col="carrera">Carrera<span class="flecha-orden ml-1"></span></th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoDeudores" class="text-[#36487a]">
                        @foreach ($deudores as $d)
                            @php
                                $ficha = route('admin.rendimiento.estudiante', [
                                    'estudiante' => $d->id_usuario,
                                    'cuatrimestre' => $idCuatrimestre,
                                ]);
                            @endphp
                            <tr data-id="{{ $d->id_usuario }}"
                                data-nombre="{{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}"
                                data-cedula="{{ $d->cedula }}"
                                data-carrera="{{ $d->carrera?->nombre }}"
                                data-href="{{ $ficha }}"
                                class="border-b border-[#f2f6fd] last:border-0 hover:bg-[#f7f9ff] transition-colors cursor-pointer">
                                <td class="py-3 pr-4">
                                    <a href="{{ $ficha }}"
                                       class="font-semibold text-[#171c7c] hover:text-[#2f55c4] underline decoration-transparent hover:decoration-[#2f55c4]/40 underline-offset-2">
                                        {{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">{{ $d->cedula }}</td>
                                <td class="py-3">{{ $d->carrera?->nombre }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-[#eef3fb]">
                <button type="button" id="anteriorDeudores" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">← Anterior</button>
                <span class="text-[13px] text-[#7a8db5]" id="paginacionDeudores"></span>
                <button type="button" id="siguienteDeudores" class="px-4 py-2 text-[13px] font-semibold rounded-xl border border-[#dce7fa] bg-[#f7f9ff] text-[#2f55c4] hover:bg-[#eaf0ff] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">Siguiente →</button>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-circle-check text-3xl text-[#0a9560]"></i>
                <p class="text-sm">No hay estudiantes con deuda registrada.</p>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    @vite('resources/js/admin/deudas.js')
@endpush