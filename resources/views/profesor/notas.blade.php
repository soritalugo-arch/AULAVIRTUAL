@extends('layouts.app')

@section('titulo', 'Notas — ' . $curso->nombre)

@section('menu_extra')
    <li><a href="{{ route('profesor.cursos') }}" class="hover:text-blue-700">Mis Cursos</a></li>
@endsection

@section('contenido')

{{-- Encabezado --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Notas — {{ $curso->nombre }}</h1>
        <p class="text-gray-500 text-sm mt-1">Cuatrimestre #{{ $cuatrimestre->id_cuatrimestre }}
            ({{ $cuatrimestre->fecha_inicio }} — {{ $cuatrimestre->fecha_fin }})</p>
    </div>
    <a href="{{ route('profesor.asistencia', $curso->id_curso) }}"
       class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
        Ir a Asistencia
    </a>
</div>

{{-- Mensajes --}}
@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Leyenda colores --}}
<div class="flex gap-3 mb-4 text-xs flex-wrap">
    <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Sin riesgo (menos del 25% de faltas)</span>
    <span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full font-medium">Atencion (entre 25% y 30% de faltas)</span>
    <span class="inline-flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Pierde materia (mas del 30% de faltas)</span>
</div>

@if($estudiantes->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4">
        No hay estudiantes inscritos en este curso.
    </div>
@else

<form method="POST" action="{{ route('profesor.notas.guardar') }}">
    @csrf
    <input type="hidden" name="id_curso"        value="{{ $curso->id_curso }}">
    <input type="hidden" name="id_cuatrimestre"  value="{{ $cuatrimestre->id_cuatrimestre }}">

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">Estudiante</th>
                    <th class="px-4 py-3 text-center">% Faltas</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Nota (1-10)</th>
                    <th class="px-4 py-3">Observacion</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($estudiantes as $i => $est)

                @php
                    $rowBg = match($est['alerta']) {
                        'peligro'     => 'bg-red-50',
                        'advertencia' => 'bg-yellow-50',
                        default       => '',
                    };
                    $badgeFaltas = match($est['alerta']) {
                        'peligro'     => 'bg-red-100 text-red-800',
                        'advertencia' => 'bg-yellow-100 text-yellow-800',
                        default       => 'bg-green-100 text-green-800',
                    };
                    $badgeEstado = match($est['estado']) {
                        'Aprobado'  => 'bg-green-100 text-green-800',
                        'Reprobado' => 'bg-red-100 text-red-800',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                @endphp

                <input type="hidden" name="notas[{{ $i }}][id_estudiante]" value="{{ $est['id'] }}">

                <tr class="{{ $rowBg }}">
                    {{-- Nombre --}}
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $est['nombre'] }}
                        @if($est['alerta'] === 'peligro')
                            <span class="ml-1 text-red-600 text-xs font-semibold">Pierde la materia</span>
                        @elseif($est['alerta'] === 'advertencia')
                            <span class="ml-1 text-yellow-700 text-xs font-semibold">Cerca del limite</span>
                        @endif
                    </td>

                    {{-- % Faltas --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeFaltas }}">
                            {{ $est['porcentajeFaltas'] }}%
                            <span class="font-normal">({{ $est['faltas'] }}/{{ $est['totalClases'] }})</span>
                        </span>
                    </td>

                    {{-- Estado --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeEstado }}">
                            {{ $est['estado'] }}
                        </span>
                    </td>

                    {{-- Input Nota --}}
                    <td class="px-4 py-3 text-center">
                        <input
                            type="number"
                            name="notas[{{ $i }}][nota]"
                            value="{{ $est['nota'] }}"
                            min="1" max="10"
                            placeholder="—"
                            class="w-20 text-center border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        >
                    </td>

                    {{-- Observacion --}}
                    <td class="px-4 py-3">
                        <input
                            type="text"
                            name="notas[{{ $i }}][observaciones]"
                            value="{{ $est['observaciones'] }}"
                            placeholder="Observacion opcional..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                        >
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Boton guardar --}}
    <div class="mt-4 flex justify-end">
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
            Guardar Notas
        </button>
    </div>
</form>

@endif

@endsection
