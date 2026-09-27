@extends('layouts.app')

@section('titulo', 'Mis Cursos')

@section('menu_extra')
    <li>
        <a href="{{ route('profesor.cursos') }}" class="hover:text-blue-700">Mis Cursos</a>
    </li>
@endsection

@section('contenido')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Mis Cursos</h1>
        <p class="text-gray-500 mt-1">Selecciona un curso para gestionar notas o asistencia.</p>
    </div>

    @if($cursos->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4">
            No tienes cursos asignados.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($cursos as $curso)
                <div class="bg-white rounded-xl shadow hover:shadow-md transition p-6 flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $curso->nombre }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Cupo maximo: {{ $curso->limite_estudiantes }} estudiantes
                        </p>
                    </div>
                    <div class="flex gap-3 mt-auto">
                        {{-- Boton Notas --}}
                        <a href="{{ route('profesor.notas', $curso->id_curso) }}"
                           class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition">
                            Notas
                        </a>
                        {{-- Boton Asistencia --}}
                        <a href="{{ route('profesor.asistencia', $curso->id_curso) }}"
                           class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-3 rounded-lg transition">
                            Asistencia
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
