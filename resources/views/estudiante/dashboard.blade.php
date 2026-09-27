@extends('layouts.app')

@section('titulo', 'Panel de estudiante')

@section('menu_extra')
    <li>
        <a href="{{ route('estudiante.matriculacion') }}" class="nav-link">Matriculación</a>
    </li>
@endsection

@section('contenido')
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold text-gray-800">Panel de estudiante</h1>
        <p class="text-gray-600 mt-2">Inscripción, horarios, calificaciones y asistencia.</p>
    </div>
@endsection