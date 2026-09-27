@extends('layouts.app')

@section('titulo', 'Matriculación de Asignaturas')

@section('contenido')
<div class="space-y-6">
<!-- Encabezado -->
<div class="bg-white rounded-lg shadow p-6">
<h1 class="text-xl font-semibold text-gray-800">Módulo de Matriculación</h1>
<p class="text-gray-600 mt-1">Gestiona la inscripción y retiro de tus asignaturas.</p>
@if($estudiante->carrera)
<p class="text-sm text-gray-500 mt-1">Carrera: <span class="font-semibold text-gray-700">{{ $estudiante->carrera->nombre }}</span></p>
@endif
@if($cuatrimestreVigente)
<p class="text-sm text-gray-500 mt-1">Período vigente: <span class="font-semibold text-gray-700">{{ $cuatrimestreVigente->fecha_inicio->format('d/m/Y') }} al {{ $cuatrimestreVigente->fecha_fin->format('d/m/Y') }}</span></p>
@endif
</div>

<!-- Mensajes de estado -->
@if(session('success'))
<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
<p>{{ session('success') }}</p>
</div>
@endif

@if(session('info'))
<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded shadow-sm" role="alert">
<p>{{ session('info') }}</p>
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
<p>{{ session('error') }}</p>
</div>
@endif

<!-- Alerta de Deuda -->
@if($estudiante->deuda)
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded shadow-sm">
<p class="font-bold">Atención:</p>
<p>Posees deudas pendientes en el sistema. No podrás inscribir nuevas asignaturas hasta solventar tu estado de cuenta.</p>
</div>
@endif

<!-- Tabla de Oferta Académica -->
<div class="bg-white rounded-lg shadow overflow-hidden">
<div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
<h2 class="text-lg font-medium text-gray-800">Oferta Académica</h2>
</div>

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200">
<thead class="bg-gray-50">
<tr>
<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asignatura</th>
<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cupos</th>
<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
</tr>
</thead>
<tbody class="bg-white divide-y divide-gray-200">
@forelse($cursos as $curso)
<tr>
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
{{ $curso->nombre }}
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
@php $cursoLleno = $curso->inscripciones_count >= $curso->limite_estudiantes; @endphp
{{ $curso->inscripciones_count }} / {{ $curso->limite_estudiantes }} estudiantes
@if($cursoLleno)
<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Lleno</span>
@endif
</td>
<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
@if(in_array($curso->id_curso, $misListaEsperaIds))
<!-- En lista de espera: botón para salir -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 mr-2">En lista de espera</span>
<form action="{{ route('estudiante.quitarListaEspera') }}" method="POST" class="inline">
@csrf
<input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
<button type="submit" class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded transition">
Salir de lista
</button>
</form>
@elseif(in_array($curso->id_curso, $misInscripcionesIds))
<!-- Botón Desmatricular -->
<form action="{{ route('estudiante.desinscribir') }}" method="POST" class="inline">
@csrf
<input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
<button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition" onclick="return confirm('¿Deseas retirar esta asignatura?')">
Desmatricular
</button>
</form>
@else
<!-- Botón Matricular -->
<form action="{{ route('estudiante.inscribir') }}" method="POST" class="inline">
@csrf
<input type="hidden" name="id_curso" value="{{ $curso->id_curso }}">
<button type="submit"
class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
{{ $estudiante->deuda ? 'disabled' : '' }}>
Matricular
</button>
</form>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
No hay asignaturas disponibles para tu carrera en este momento.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
@endsection
