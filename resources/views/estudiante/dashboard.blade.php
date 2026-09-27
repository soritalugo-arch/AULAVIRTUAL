@extends('layouts.app')

@section('titulo', 'Panel de estudiante')

@section('menu_extra')
    <li>
        <a href="{{ route('estudiante.matriculacion') }}" class="hover:text-blue-700">Matriculación</a>
    </li>
    <li>
        <a href="{{ route('estudiante.notas') }}" class="hover:text-blue-700">Mis Notas</a>
    </li>
@endsection

@section('contenido')
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold text-gray-800">Panel de estudiante</h1>
        <p class="text-gray-600 mt-2">Inscripción, horarios, calificaciones y asistencia.</p>
        <div class="flex gap-3 mt-4">
            <a href="{{ route('estudiante.matriculacion') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition">
                Matriculacion
            </a>
            <a href="{{ route('estudiante.notas') }}"
               class="bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2 rounded-lg transition">
                Mis Notas
            </a>
        </div>
    </div>
@endsection