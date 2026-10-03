    (function () {
        var triggers = document.querySelectorAll('[data-sel-toggle]');

        function closeAllSel() {
            var open = document.querySelectorAll('.sel.is-open');
            for (var oi = 0; oi < open.length; oi++) {
                open[oi].classList.remove('is-open');
            }
        }

        for (var ti = 0; ti < triggers.length; ti++) {
            (function (trigger) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var sel = trigger.closest('.sel');

                    if (sel.classList.contains('is-open')) {
                        sel.classList.remove('is-open');
                    } else {
                        closeAllSel();
                        sel.classList.add('is-open');
                    }
                });
            })(triggers[ti]);
        }

        /* Cerrar solo al hacer clic FUERA del dropdown o con Escape */
        document.addEventListener('click', function (e) {
            var target = e.target;
            var inside = target && target.closest && target.closest('.sel');

            if (!inside) closeAllSel();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAllSel();
        });
    })();

    /* =====================================================
       ACUMULADO Y PROMEDIO EN VIVO
       Cada parcial vale 25 puntos sobre 100: el acumulado es la suma de
       las cuatro y el promedio es ese acumulado dividido entre 10. Las
       parciales vacías cuentan como cero (opción A), el mismo cálculo que
       hace el servidor al guardar. Es solo una ayuda visual mientras el
       profesor escribe: al guardar manda el valor del servidor.
       ===================================================== */

    (function () {
        // Seleccionamos todas las filas de la tabla de estudiantes
var filas = document.querySelectorAll('tbody tr'); // Ajusta este selector si tus filas tienen una clase específica, ej: '.fila-estudiante'

filas.forEach(function(fila) {
    // 1. Capturar los elementos de la fila actual
    var parciales = fila.querySelectorAll('input[type="number"]'); // Las cajas de texto de las notas
    var salidaAcumulado = fila.querySelector('[data-acumulado]');
    var salidaPromedio = fila.querySelector('[data-promedio]');
    var salidaEstado = fila.querySelector('[data-estado]'); 

    // Si la fila no tiene estos elementos, la saltamos
    if (!parciales.length || !salidaAcumulado || !salidaPromedio) return;

    // 2. Función auxiliar para pintar colores en los promedios
    function pintar(salida, texto, aprobado) {
        salida.textContent = texto;
        salida.className = 'promedio ' + (aprobado ? 'aprobado' : 'reprobado');
    }

    // 3. Función principal que recalcula todo para esta fila
    function recalcular() {
        var suma = 0;
        var hayAlgo = false;
        var ingresadas = 0; 

        // Sumar los valores de los inputs
        for (var i = 0; i < parciales.length; i++) {
            var valor = parseFloat(parciales[i].value);

            if (!isNaN(valor)) {
                suma += valor;
                hayAlgo = true;
                ingresadas++; 
            }
        }

        // Si borraron todas las notas, devolver al estado por defecto
        if (!hayAlgo) {
            salidaAcumulado.textContent = '—';
            salidaAcumulado.className = 'promedio';
            salidaPromedio.textContent = '—';
            salidaPromedio.className = 'promedio';
            fila.setAttribute('data-promedio', '-1');
            
            if (salidaEstado) {
                salidaEstado.textContent = 'En curso';
                salidaEstado.className = 'status presunto';
            }
            return;
        }

        // Calcular puntaje final
        var acumulado = Math.round(suma * 100) / 100;
        var promedio  = Math.round((acumulado / 10) * 100) / 100;
        var aprueba   = promedio >= 6; // Verifica si aprueba (6.00 o más)

        // Actualizar textos en pantalla del acumulado y promedio
        pintar(salidaAcumulado, acumulado.toFixed(2), aprueba);
        pintar(salidaPromedio, promedio.toFixed(2), aprueba);
        fila.setAttribute('data-promedio', promedio.toFixed(2));

        // Actualizar la pastilla de estado visualmente en tiempo real
        if (salidaEstado) {
            var esPeligro = fila.querySelector('.nombre-warn.peligro'); // Evalúa límite de faltas

            if (esPeligro) {
                salidaEstado.textContent = 'Reprobado';
                salidaEstado.className = 'status fail';
            } else if (promedio >= 6) {
                salidaEstado.textContent = 'Aprobado';
                salidaEstado.className = 'status ok';
            } else if (ingresadas === 4 && promedio < 6) { // Si ya llenó las 4 notas y no llega a 6
                salidaEstado.textContent = 'Reprobado';
                salidaEstado.className = 'status fail';
            } else {
                salidaEstado.textContent = 'En curso';
                salidaEstado.className = 'status presunto';
            }
        }
    }

    // 4. Asignar el evento 'input' a cada caja de texto para que reaccione al escribir
    parciales.forEach(function(input) {
        input.addEventListener('input', recalcular);
    });
});
    })();

    /* =====================================================
       OBSERVACIONES QUE CRECEN CON EL TEXTO
       El textarea se estira hacia abajo a medida que el
       profesor escribe, en vez de dejar el scrollbar.
       ===================================================== */

    document.querySelectorAll('textarea[data-autogrow]').forEach(function (area) {
        area.addEventListener('input', function () {
            this.style.height = '';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    /* =====================================================
       BUSCAR Y ORDENAR LA TABLA
       Todo en el navegador: el filtro esconde las filas que no
       coinciden y el orden mueve las que quedan, sin volver a
       pedir nada al servidor. Los numbers de las notas no se
       tocan, siguen siendo lo que el profesor escribió.
       ===================================================== */

    (function () {
        var tabla = document.querySelector('[data-tabla-notas]');

        if (!tabla) return;

        var cuerpo   = tabla.querySelector('tbody');
        var filas    = Array.prototype.slice.call(cuerpo.querySelectorAll('tr[data-nombre]'));
        var sinFilas = cuerpo.querySelector('[data-sin-resultados]');
        var buscador = document.querySelector('[data-buscar]');
        var caja     = document.querySelector('[data-buscador]');
        var limpiar  = document.querySelector('[data-limpiar]');
        var cabeceras = Array.prototype.slice.call(tabla.querySelectorAll('th.ordenable'));
        var orden = { columna: null, sentido: 'asc' };

        /* Para que "Peña" se encuentre escribiendo "pena": se comparan los
           nombres sin tildes ni mayusculas. */
        function normalizar(texto) {
            return (texto || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '');
        }

        function visibles() {
            var termino = normalizar(buscador ? buscador.value : '').trim();
            var quedan = 0;

            for (var i = 0; i < filas.length; i++) {
                var coincide = termino === ''
                    || normalizar(filas[i].getAttribute('data-nombre')).indexOf(termino) !== -1;

                filas[i].hidden = !coincide;

                if (coincide) quedan++;
            }

            if (sinFilas) sinFilas.hidden = quedan > 0;

            if (caja) caja.classList.toggle('buscando', termino !== '');

            ordenar();
        }

        function ordenar() {
            if (!orden.columna) return;

            var columna = orden.columna;
            var factor  = orden.sentido === 'asc' ? 1 : -1;

            /* Se ordena sobre una copia: sort() mueve el orden de las filas
               del DOM, pero los indices del array se mantienen porque el
               foreach va sobre una copia. */
            var ordenadas = filas.slice().sort(function (a, b) {
                if (columna === 'nombre') {
                    return normalizar(a.getAttribute('data-nombre')).localeCompare(
                        normalizar(b.getAttribute('data-nombre'))
                    ) * factor;
                }

                return (parseFloat(a.getAttribute('data-promedio')) - parseFloat(b.getAttribute('data-promedio'))) * factor;
            });

            for (var i = 0; i < ordenadas.length; i++) {
                cuerpo.insertBefore(ordenadas[i], sinFilas);
            }
        }

        if (buscador) {
            buscador.addEventListener('input', visibles);
        }

        if (limpiar) {
            limpiar.addEventListener('click', function () {
                if (!buscador) return;

                buscador.value = '';
                buscador.focus();
                visibles();
            });
        }

        for (var c = 0; c < cabeceras.length; c++) {
            (function (th) {
                th.addEventListener('click', function () {
                    var columna = th.getAttribute('data-orden');

                    /* Volver a apretar la misma columna da vuelta el sentido. */
                    if (orden.columna === columna) {
                        orden.sentido = orden.sentido === 'asc' ? 'desc' : 'asc';
                    } else {
                        orden.columna = columna;
                        orden.sentido = columna === 'nombre' ? 'asc' : 'desc';
                    }

                    for (var k = 0; k < cabeceras.length; k++) {
                        cabeceras[k].classList.remove('asc', 'desc');
                    }

                    th.classList.add(orden.sentido);
                    th.querySelector('.flecha').innerHTML =
                        '<i class="fa-solid fa-sort' + (orden.sentido === 'asc' ? '-up' : '-down') + '"></i>';

                    ordenar();
                });
            })(cabeceras[c]);
        }
    })();
