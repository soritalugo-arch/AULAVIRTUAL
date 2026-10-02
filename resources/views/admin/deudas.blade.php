@extends('layouts.admin')

@section('titulo', 'Deudas · Panel de la Rectora')
@section('tituloPantalla', 'Deudas')

@section('panel')

    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Estudiantes con deuda</h2>
            <span class="px-3 py-1.5 rounded-full bg-[#ffe7ec] text-[#ec3e67] text-xs font-bold w-fit">{{ $deudores->count() }}</span>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-4">Tienen bloqueada la matrícula mientras no regularicen su estado de cuenta.</p>

        @if ($deudores->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[12px] uppercase tracking-wide text-[#7a8db5] border-b border-[#eef3fb]">
                            <th class="py-3 pr-4 font-semibold">Estudiante</th>
                            <th class="py-3 pr-4 font-semibold">Cédula</th>
                            <th class="py-3 font-semibold">Carrera</th>
                        </tr>
                    </thead>
                    <tbody class="text-[#36487a]">
                        @foreach ($deudores as $d)
                            <tr class="border-b border-[#f2f6fd] last:border-0">
                                <td class="py-3 pr-4 font-semibold text-[#171c7c]">{{ $d->usuario?->nombres }} {{ $d->usuario?->apellidos }}</td>
                                <td class="py-3 pr-4">{{ $d->cedula }}</td>
                                <td class="py-3">{{ $d->carrera?->nombre }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center gap-3 min-h-[150px] p-5 text-center text-[#8a9cc0]">
                <i class="fa-solid fa-circle-check text-3xl text-[#0a9560]"></i>
                <p class="text-sm">No hay estudiantes con deuda registrada.</p>
            </div>
        @endif
    </div>

@endsection