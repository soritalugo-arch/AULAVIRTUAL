// Token de seguridad de Laravel obligatorio
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Función para procesar la eliminación sin recargar la página
function quitarMateria(cursoId, profesorId) {
    if(!confirm('¿Estás seguro de que deseas quitar a este profesor de esta unidad curricular?')) return;

    fetch('/admin/api/asignaciones/eliminar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
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
    const listaMaterias = document.getElementById('lista_materias');

    // Los botones de desasignar se dibujan al vuelo, así que se escuchan por delegación
    listaMaterias.addEventListener('click', function (e) {
        var boton = e.target.closest('[data-quitar-materia]');
        if (!boton) return;
        quitarMateria(boton.getAttribute('data-curso'), boton.getAttribute('data-profesor'));
    });
    
    const panelHorarios = document.getElementById('panel_horarios');
    const listaHorarios = document.getElementById('lista_horarios');
    
    const panelMaterias = document.getElementById('panel_materias');

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
                                    <button type="button" data-quitar-materia data-curso="${materia.id_curso}" data-profesor="${profesorId}" class="text-[#ec3e67] hover:bg-[#ffe7ec] p-1.5 rounded-md transition-colors" title="Desasignar materia">
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
