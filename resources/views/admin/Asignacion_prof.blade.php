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

<script>
    // Función para procesar la eliminación sin recargar la página
    function quitarMateria(cursoId, profesorId) {
        if(!confirm('¿Estás seguro de que deseas quitar a este profesor de esta unidad curricular?')) return;

        fetch('/admin/api/asignaciones/eliminar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Token de seguridad de Laravel obligatorio
            },
            body: JSON.stringify({
                curso_id: cursoId,
                profesor_id: profesorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Recarga la lista visualmente simulando un cambio en el selector
                document.getElementById('profesor_id').dispatchEvent(new Event('change'));
            }
        })
        .catch(error => console.error('Error al eliminar:', error));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectCurso = document.getElementById('curso_id');
        const selectProfesor = document.getElementById('profesor_id');
        
        const panelHorarios = document.getElementById('panel_horarios');
        const listaHorarios = document.getElementById('lista_horarios');
        
        const panelMaterias = document.getElementById('panel_materias');
        const listaMaterias = document.getElementById('lista_materias');

       // Escuchar el cambio en el selector de cursos
        selectCurso.addEventListener('change', function() {
            const cursoId = this.value;
            if(cursoId) {
                fetch(`/admin/api/curso/${cursoId}/horarios`)
                    .then(response => response.json())
                    .then(data => {
                        listaHorarios.innerHTML = ''; 
                        const infoProfesor = document.getElementById('info_profesor_actual');
                        
                        // 1. Mostrar el profesor actual
                        if (data.profesor_actual) {
                            infoProfesor.innerHTML = `<i class="fa-solid fa-user-tie text-[#6382dc] mr-2"></i> <strong>Docente actual:</strong> ${data.profesor_actual.nombres} ${data.profesor_actual.apellidos}`;
                        } else {
                            infoProfesor.innerHTML = `<i class="fa-solid fa-circle-info text-[#0a9560] mr-2"></i> <strong>Docente actual:</strong> Sin asignar`;
                        }

                        // 2. Mostrar los horarios
                        if(data.horarios.length > 0) {
                            data.horarios.forEach(horario => {
                                listaHorarios.innerHTML += `<li>${horario}</li>`;
                            });
                        } else {
                            listaHorarios.innerHTML = '<li class="text-[#7a8db5] italic">No hay horarios registrados para este curso.</li>';
                        }
                        
                        panelHorarios.classList.remove('hidden');
                    })
                    .catch(error => console.error('Error al cargar horarios:', error));
            } else {
                panelHorarios.classList.add('hidden');
            }
        });

        // Escuchar el cambio en el selector de profesores
        selectProfesor.addEventListener('change', function() {
            const profesorId = this.value;
            if(profesorId) {
                fetch(`/admin/api/profesor/${profesorId}/materias`)
                    .then(response => response.json())
                    .then(data => {
                        listaMaterias.innerHTML = ''; 
                        
                        if(data.length > 0) {
                            // Cambiamos la forma de renderizar la lista para incluir el botón de eliminar
                            data.forEach(materia => {
                                listaMaterias.innerHTML += `
                                    <li class="flex justify-between items-center py-1 border-b border-[#eef3fb] last:border-0">
                                        <span>${materia.nombre}</span>
                                        <button type="button" onclick="quitarMateria(${materia.id_curso}, ${profesorId})" class="text-[#ec3e67] hover:bg-[#ffe7ec] p-1.5 rounded-md transition-colors" title="Desasignar materia">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </li>`;
                            });
                        } else {
                            listaMaterias.innerHTML = '<li class="text-[#0a9560]"><i class="fa-solid fa-check-circle mr-1"></i> El docente tiene total disponibilidad (0 materias).</li>';
                        }
                        
                        panelMaterias.classList.remove('hidden');
                    })
                    .catch(error => console.error('Error al cargar materias:', error));
            } else {
                panelMaterias.classList.add('hidden');
            }
        });
    });
</script>