@extends('layouts.admin')

@section('titulo', 'Asignaciones Docentes · Panel de la Rectora')
@section('tituloPantalla', 'Gestión de Asignaciones')

@section('panel')
    <div class="bg-white border border-[#e0e8f5] rounded-[24px] shadow-[0_8px_25px_rgba(70,100,160,0.08)] p-5 sm:p-7">
        
        {{-- Encabezado de la Tarjeta --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chalkboard-user text-[22px] text-[#6382dc]"></i>
                <h2 class="text-xl sm:text-2xl font-['Georgia'] font-bold text-[#171c7c]">Gestión de Asignaciones Docentes</h2>
            </div>
        </div>
        <p class="text-[13px] text-[#7a8db5] mb-6">Selecciona una unidad curricular y asigna al docente correspondiente.</p>

        {{-- Alertas --}}
        @if($errors->has('error_horario'))
            <div class="mb-6 flex items-start gap-3 p-4 bg-[#ffe7ec] border border-[#f5d3dc] rounded-2xl text-[#ec3e67] text-sm">
                <i class="fa-solid fa-triangle-exclamation text-lg shrink-0 mt-0.5"></i>
                <div>{{ $errors->first('error_horario') }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 flex items-start gap-3 p-4 bg-[#e7f7f0] border border-[#c3eed9] rounded-2xl text-[#0a9560] text-sm">
                <i class="fa-solid fa-circle-check text-lg shrink-0 mt-0.5"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- Formulario --}}
        <form action="{{ route('admin.asignaciones.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Columna 1: Unidad Curricular --}}
                <div class="flex flex-col">
                    <label for="curso_id" class="block text-[12px] font-bold tracking-wide uppercase text-[#7a8db5] mb-2">
                        Unidad Curricular / Curso
                    </label>
                    <div class="relative">
                        <select name="curso_id" id="curso_id" autocomplete="off" required
                                class="w-full border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold pl-4 pr-10 py-3 outline-none focus:border-[#4c5bc3] appearance-none cursor-pointer">
                            <option value="" disabled selected>Seleccione un curso...</option>
                           @foreach($cursos as $curso)
                                <option value="{{ $curso->id_curso }}" {{ old('curso_id') == $curso->id_curso ? 'selected' : '' }}>
                                    {{ $curso->asignado == 0 ? '🔴 ' : '' }}{{ $curso->nombre }} {{ $curso->asignado == 0 ? '(Sin docente asignado)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#9aabd0] text-sm pointer-events-none"></i>
                    </div>

                    {{-- Panel dinámico de Horarios del Curso --}}
                    <div id="panel_horarios" class="hidden mt-4 p-4 bg-[#f8fbff] border border-[#dce7fa] rounded-xl flex-grow">
                        
                        {{-- NUEVO: Información del Profesor Actual --}}
                        <div id="info_profesor_actual" class="mb-3 pb-3 border-b border-[#dce7fa] text-sm text-[#24356e]">
                        </div>

                        <h4 class="text-[11px] font-bold tracking-wide uppercase text-[#5a6f9c] mb-2 flex items-center gap-2">
                            <i class="fa-regular fa-clock text-[#6382dc]"></i> Horario Establecido
                        </h4>
                        <ul id="lista_horarios" class="text-sm text-[#3b4c7a] space-y-1.5 list-disc list-inside">
                            {{-- Se llena con JS --}}
                        </ul>
                    </div>
                </div>

                {{-- Columna 2: Profesor --}}
                <div class="flex flex-col">
                    <label for="profesor_id" class="block text-[12px] font-bold tracking-wide uppercase text-[#7a8db5] mb-2">
                        Profesor a Asignar
                    </label>
                    <div class="relative">
                        <select name="profesor_id" id="profesor_id" autocomplete="off" required
                                class="w-full border border-[#dce7fa] rounded-xl bg-[#f7f9ff] text-[#24356e] text-sm font-semibold pl-4 pr-10 py-3 outline-none focus:border-[#4c5bc3] appearance-none cursor-pointer">
                            <option value="" disabled selected>Seleccione un docente...</option>
                            @foreach($profesores as $profesor)
                                <option value="{{ $profesor->id_usuario }}" {{ old('profesor_id') == $profesor->id_usuario ? 'selected' : '' }}>
                                    {{ $profesor->cant_materias == 0 ? '🟢 ' : '' }}{{ $profesor->nombres }} {{ $profesor->apellidos }} {{ $profesor->cant_materias == 0 ? '(Disponible - Sin materias)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#9aabd0] text-sm pointer-events-none"></i>
                    </div>

                    {{-- Panel dinámico de Materias del Profesor --}}
                    <div id="panel_materias" class="hidden mt-4 p-4 bg-[#f8fbff] border border-[#dce7fa] rounded-xl flex-grow">
                        <h4 class="text-[11px] font-bold tracking-wide uppercase text-[#5a6f9c] mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-book-open text-[#6382dc]"></i> Materias ya asignadas
                        </h4>
                        <ul id="lista_materias" class="text-sm text-[#3b4c7a] space-y-1.5 list-disc list-inside">
                            {{-- Se llena con JS --}}
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Botón de envío --}}
            <div class="mt-8 flex justify-end pt-5 border-t border-[#eef3fb]">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#4c6fe0] hover:bg-[#2f55c4] text-white text-[13px] font-bold transition-colors shadow-sm">
                    <i class="fa-solid fa-save"></i> Asignar Profesor
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/admin/asignacion-prof.js')
@endpush