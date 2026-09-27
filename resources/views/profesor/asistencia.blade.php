@extends('layouts.app')

@section('titulo', 'Asistencia — ' . $curso->nombre)

@section('menu_extra')
    <li><a href="{{ route('profesor.cursos') }}" class="hover:text-blue-700">Mis Cursos</a></li>
@endsection

@section('contenido')

{{-- Encabezado --}}
<div class="flex items-center justify-between mb-6 flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Asistencia — {{ $curso->nombre }}</h1>
        <p class="text-gray-500 text-sm mt-1">Cuatrimestre #{{ $cuatrimestre->id_cuatrimestre }}
            ({{ $cuatrimestre->fecha_inicio }} — {{ $cuatrimestre->fecha_fin }})</p>
    </div>
    <a href="{{ route('profesor.notas', $curso->id_curso) }}"
       class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
        Ir a Notas
    </a>
</div>

{{-- Selector de fecha --}}
<div class="bg-white rounded-xl shadow p-4 mb-4 flex items-center gap-4 flex-wrap">
    <label class="text-sm font-medium text-gray-700">Fecha de la clase:</label>
    <form method="GET" action="{{ route('profesor.asistencia', $curso->id_curso) }}" class="flex items-center gap-2">
        <input type="date" name="fecha" value="{{ $fecha }}"
               class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-1.5 rounded-lg transition">
            Cambiar
        </button>
    </form>
</div>

{{-- Mensajes --}}
@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-3 text-sm">
        {{ session('success') }}
    </div>
@endif

{{-- Leyenda --}}
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

<form method="POST" action="{{ route('profesor.asistencia.guardar') }}">
    @csrf
    <input type="hidden" name="id_curso"        value="{{ $curso->id_curso }}">
    <input type="hidden" name="id_cuatrimestre"  value="{{ $cuatrimestre->id_cuatrimestre }}">
    <input type="hidden" name="fecha"            value="{{ $fecha }}">

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3">Estudiante</th>
                    <th class="px-4 py-3 text-center">% Faltas acumuladas</th>
                    <th class="px-4 py-3 text-center">Estado de riesgo</th>
                    <th class="px-4 py-3 text-center">Presente hoy</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($estudiantes as $est)

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
                @endphp

                <tr class="{{ $rowBg }}">
                    {{-- Nombre --}}
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $est['nombre'] }}
                        @if($est['alerta'] === 'peligro')
                            <div class="text-xs text-red-600 font-semibold mt-0.5">Pierde la materia — supero el 30% de faltas</div>
                        @elseif($est['alerta'] === 'advertencia')
                            <div class="text-xs text-yellow-700 font-semibold mt-0.5">Cerca del limite — comuniquese con el estudiante</div>
                        @endif
                    </td>

                    {{-- % Faltas --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeFaltas }}">
                            {{ $est['porcentajeFaltas'] }}%
                            <span class="font-normal">({{ $est['faltas'] }} de {{ $est['totalClases'] }} clases)</span>
                        </span>
                    </td>

                    {{-- Estado --}}
                    <td class="px-4 py-3 text-center text-xs">
                        @if($est['alerta'] === 'peligro')
                            <span class="text-red-700 font-semibold">Perdida de materia</span>
                        @elseif($est['alerta'] === 'advertencia')
                            <span class="text-yellow-700 font-semibold">Alertar al estudiante</span>
                        @else
                            <span class="text-green-700">Normal</span>
                        @endif
                    </td>

                    {{-- Checkbox Presente --}}
                    <td class="px-4 py-3 text-center">
                        <input
                            type="checkbox"
                            name="presentes[]"
                            value="{{ $est['id'] }}"
                            {{ $est['presente'] ? 'checked' : '' }}
                            class="w-5 h-5 accent-green-600 cursor-pointer"
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
            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">
            Guardar Asistencia
        </button>
    </div>
</form>

@endif

@endsection
