@extends('layouts.app')

@section('titulo', 'Panel de profesor')

@section('menu_extra')
    <li>
        <a href="{{ route('profesor.cursos') }}" class="hover:text-blue-700">Mis Cursos</a>
    </li>
@endsection

@section('contenido')
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold text-gray-800">Panel de profesor</h1>
        <p class="text-gray-600 mt-2">Gestión de cursos, calificaciones y asistencia.</p>
        <div class="mt-4">
            <a href="{{ route('profesor.cursos') }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded-lg transition">
                Ver mis cursos
            </a>
        </div>
    </div>
@endsection