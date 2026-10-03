document.addEventListener('DOMContentLoaded', function() {
    const radiosEstados = document.querySelectorAll('.radio-estado-periodo');
    const btnCrearPeriodo = document.getElementById('btnCrearPeriodo');
    const leyendaNuevoPeriodo = document.getElementById('leyendaNuevoPeriodo');

    function evaluarEstado() {
        if(!btnCrearPeriodo) return;
        
        // Buscar cuál radio button está seleccionado actualmente
        let estadoSeleccionado = '';
        radiosEstados.forEach(radio => {
            if(radio.checked) {
                estadoSeleccionado = radio.value;
            }
        });
        
        // Si el estado seleccionado es 'cerrado'
        if (estadoSeleccionado === 'cerrado') {
            btnCrearPeriodo.disabled = false;
            btnCrearPeriodo.classList.remove('opacity-40', 'cursor-not-allowed');
            btnCrearPeriodo.classList.add('hover:bg-[#08774d]', 'shadow-[0_6px_15px_rgba(10,149,96,0.25)]');
            if(leyendaNuevoPeriodo) leyendaNuevoPeriodo.textContent = "Listo para apertura.";
            if(leyendaNuevoPeriodo) leyendaNuevoPeriodo.classList.add('text-[#0a9560]');
        } else {
            btnCrearPeriodo.disabled = true;
            btnCrearPeriodo.classList.add('opacity-40', 'cursor-not-allowed');
            btnCrearPeriodo.classList.remove('hover:bg-[#08774d]', 'shadow-[0_6px_15px_rgba(10,149,96,0.25)]');
            if(leyendaNuevoPeriodo) leyendaNuevoPeriodo.textContent = "Cierra el período actual para habilitar esta opción.";
            if(leyendaNuevoPeriodo) leyendaNuevoPeriodo.classList.remove('text-[#0a9560]');
        }
    }

    // Agregar el listener a cada radio button
    radiosEstados.forEach(radio => {
        radio.addEventListener('change', evaluarEstado);
    });

    // Ambos botones de la vista piden la clave de seguridad antes de abrir la
    // pantalla de creación. data-clave-requerida distingue el caso con clave.
    document.querySelectorAll('[data-crear-periodo]').forEach(boton => {
        boton.addEventListener('click', function () {
            const url = boton.getAttribute('data-crear-periodo');

            if (!boton.hasAttribute('data-clave-requerida')) {
                window.location.href = url;
                return;
            }

            const pin = prompt('Ingrese la clave de seguridad para apertura:');
            if (pin) window.location.href = url + '?clave=' + pin;
        });
    });

    // Ejecutar al cargar la página
    evaluarEstado();
});
