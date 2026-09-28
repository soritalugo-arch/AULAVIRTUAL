@extends('layouts.app')

@section('titulo', 'Mis Notas')

@section('menu_extra')
    <li><a href="{{ route('estudiante.matriculacion') }}" class="hover:text-blue-700">Matriculacion</a></li>
    <li><a href="{{ route('estudiante.notas') }}"        class="hover:text-blue-700">Mis Notas</a></li>
@endsection

@section('contenido')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Mis Notas y Asistencia</h1>
    <p class="text-gray-500 text-sm mt-1">Resumen de tu rendimiento por curso en el cuatrimestre actual.</p>
</div>

{{-- Leyenda --}}
<div class="flex gap-3 mb-5 text-xs flex-wrap">
    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Sin riesgo (menos del 25% de faltas)</span>
    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full font-medium">Atencion (entre 25% y 30% de faltas)</span>
    <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Perdiste la materia (mas del 30% de faltas)</span>
</div>

@if($resumen->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4">
        No tienes cursos inscritos con datos de notas o asistencia.
    </div>
@else

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
            <tr>
                <th class="px-4 py-3">Curso</th>
                <th class="px-4 py-3 text-center">Nota</th>
                <th class="px-4 py-3 text-center">% Faltas</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-4 py-3 text-center">Promedio del curso</th>
                <th class="px-4 py-3">Observacion del profesor</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($resumen as $item)

            @php
                $rowBg = match($item['alerta']) {
                    'peligro'     => 'bg-red-50',
                    'advertencia' => 'bg-yellow-50',
                    default       => '',
                };
                $badgeFaltas = match($item['alerta']) {
                    'peligro'     => 'bg-red-100 text-red-800',
                    'advertencia' => 'bg-yellow-100 text-yellow-800',
                    default       => 'bg-green-100 text-green-800',
                };
                $badgeEstado = match($item['estado']) {
                    'Aprobado'  => 'bg-green-100 text-green-800',
                    'Reprobado' => 'bg-red-100 text-red-800',
                    default     => 'bg-gray-100 text-gray-600',
                };
            @endphp

            <tr class="{{ $rowBg }}">
                {{-- Curso --}}
                <td class="px-4 py-3 font-medium text-gray-800">
                    {{ $item['curso'] }}
                    <div class="text-xs text-gray-400 font-normal">Cuatrimestre #{{ $item['cuatrimestre'] }}</div>
                </td>

                {{-- Nota --}}
                <td class="px-4 py-3 text-center">
                    @if(!is_null($item['nota']))
                        <span class="text-xl font-bold {{ $item['nota'] >= 6 ? 'text-green-700' : 'text-red-600' }}">
                            {{ $item['nota'] }}
                        </span>
                        <span class="text-gray-400 text-xs">/10</span>
                    @else
                        <span class="text-gray-400 italic text-xs">Sin nota aun</span>
                    @endif
                </td>

                {{-- % Faltas --}}
                <td class="px-4 py-3 text-center">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeFaltas }}">
                        {{ $item['porcentajeFaltas'] }}%
                        <span class="font-normal">({{ $item['totalClases'] }} clases)</span>
                    </span>
                    @if($item['alerta'] === 'peligro')
                        <div class="text-red-600 text-xs font-semibold mt-1">Superaste el 30% de faltas</div>
                    @elseif($item['alerta'] === 'advertencia')
                        <div class="text-yellow-700 text-xs font-semibold mt-1">Estas cerca del limite</div>
                    @endif
                </td>

                {{-- Estado --}}
                <td class="px-4 py-3 text-center">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $badgeEstado }}">
                        {{ $item['estado'] }}
                    </span>
                </td>

                {{-- Promedio del curso --}}
                <td class="px-4 py-3 text-center">
                    @if(!is_null($item['promedioCurso']))
                        <span class="text-gray-700 font-semibold">{{ $item['promedioCurso'] }}</span>
                        <span class="text-gray-400 text-xs">/10</span>
                    @else
                        <span class="text-gray-400 italic text-xs">Sin datos</span>
                    @endif
                </td>

                {{-- Observacion --}}
                <td class="px-4 py-3 text-gray-600 italic text-xs">
                    {{ $item['observaciones'] ?? '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif

@endsection
