@extends('layouts.app')

@section('titulo', 'Panel de profesor')

@section('menu_extra')
    <li>
        <a href="{{ route('profesor.cursos') }}" class="nav-link {{ request()->routeIs('profesor.cursos', 'profesor.notas*', 'profesor.asistencia*') ? 'active' : '' }}">Mis Cursos</a>
    </li>
@endsection

@section('contenido')
    <div class="bg-gradient-to-br from-white to-[#f8fbff] rounded-[28px] border border-[#d9e6fb] shadow-[0_10px_30px_rgba(71,106,170,0.10)] p-6 sm:p-8 md:p-10 relative overflow-hidden max-w-5xl mx-auto">
        
        <!-- Círculo decorativo de fondo -->
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-blue-200/30 rounded-full blur-3xl pointer-events-none transform rotate-12"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            
            <!-- Encabezado de la Tarjeta -->
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#dce8ff] to-[#edf3ff] flex items-center justify-center text-[#6284df] text-2xl shrink-0 relative">
                    <!-- Círculo interior -->
                    <div class="absolute inset-0 m-auto w-14 h-14 rounded-full bg-[#e1eaff] z-0"></div>
                    <i class="fa-solid fa-person-chalkboard relative z-10"></i>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-[#171d7d] font-['Georgia'] mb-1">Panel de profesor</h1>
                    <p class="text-[#7087ba] text-sm sm:text-base leading-relaxed">Gestión de cursos, calificaciones y asistencia.</p>
                </div>
            </div>

            <!-- Botones de Acción (Apilados en móvil, en línea en PC) -->
            <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto mt-2 md:mt-0">
                <a href="{{ route('profesor.cursos') }}"
                   class="flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-semibold px-6 py-3.5 rounded-full transition-all shadow-[0_6px_15px_rgba(37,99,235,0.25)] hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(37,99,235,0.35)] w-full sm:w-auto">
                    <i class="fa-solid fa-book-open"></i>
                    Ver mis cursos
                </a>
            </div>
            
        </div>
    </div>
@endsection