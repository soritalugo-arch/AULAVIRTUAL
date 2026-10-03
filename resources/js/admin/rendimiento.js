(function () {
    var cuerpo = document.getElementById('cuerpoEstudiantes');
    if (!cuerpo) return;

    var filasOriginales = Array.prototype.slice.call(cuerpo.querySelectorAll('tr[data-id]'));
    if (!filasOriginales.length) return;

    var POR_PAGINA = 25;
    var estado = { q: '', campo: 'promedio', dir: 1, pagina: 1 };

    var input = document.getElementById('buscarEstudiante');
    var anterior = document.getElementById('anteriorEstudiantes');
    var siguiente = document.getElementById('siguienteEstudiantes');
    var contador = document.getElementById('contadorEstudiantes');
    var paginacion = document.getElementById('paginacionEstudiantes');
    var cabeceras = Array.prototype.slice.call(document.querySelectorAll('th[data-col]'));

    function valor(fila, campo) {
        var v = fila.getAttribute('data-' + campo) || '';
        if (campo === 'promedio' || campo === 'aprobadas' || campo === 'reprobadas') {
            return v === '' ? null : parseFloat(v);
        }
        return v.toLowerCase();
    }

    function visibles() {
        var q = estado.q.toLowerCase();

        var filtradas = filasOriginales.filter(function (fila) {
            if (!q) return true;
            return (valor(fila, 'nombre') + ' ' + valor(fila, 'cedula')).indexOf(q) !== -1;
        });

        if (estado.campo) {
            var campo = estado.campo;
            var dir = estado.dir;
            filtradas.sort(function (a, b) {
                var va = valor(a, campo);
                var vb = valor(b, campo);
                if (va === null && vb === null) return 0;
                if (va === null) return 1;      // los sin dato, al final
                if (vb === null) return -1;
                if (va < vb) return -1 * dir;
                if (va > vb) return 1 * dir;
                return 0;
            });
        }

        return filtradas;
    }

    function pintar() {
        var lista = visibles();
        var total = lista.length;
        var paginas = Math.max(1, Math.ceil(total / POR_PAGINA));
        if (estado.pagina > paginas) estado.pagina = paginas;

        var pagina = lista.slice((estado.pagina - 1) * POR_PAGINA, estado.pagina * POR_PAGINA);

        // Quitar lo que haya (filas de la pagina anterior) y borrar el aviso de vacio.
        cuerpo.querySelectorAll('tr[data-id], tr[data-vacio]').forEach(function (tr) { tr.remove(); });

        if (!pagina.length) {
            var tr = document.createElement('tr');
            tr.setAttribute('data-vacio', '1');
            tr.innerHTML = '<td colspan="6" class="py-8 text-center text-[#8a9cc0] text-sm">Ningún estudiante coincide con esa búsqueda.</td>';
            cuerpo.appendChild(tr);
        } else {
            var fragmento = document.createDocumentFragment();
            pagina.forEach(function (tr) { fragmento.appendChild(tr); });
            cuerpo.appendChild(fragmento);
        }

        var desde = total === 0 ? 0 : (estado.pagina - 1) * POR_PAGINA + 1;
        var hasta = Math.min(estado.pagina * POR_PAGINA, total);
        contador.textContent = 'Mostrando ' + (total === 0 ? '0' : desde + '–' + hasta) + ' de ' + total + ' estudiantes';
        paginacion.textContent = 'Página ' + estado.pagina + ' de ' + paginas;
        anterior.disabled = estado.pagina <= 1;
        siguiente.disabled = estado.pagina >= paginas;
    }

    function marcarOrden() {
        cabeceras.forEach(function (th) {
            var flecha = th.querySelector('.flecha-orden');
            if (!flecha) return;
            if (th.getAttribute('data-col') === estado.campo) {
                flecha.textContent = estado.dir === 1 ? '▲' : '▼';
                th.classList.add('text-[#2f55c4]');
                th.classList.remove('text-[#7a8db5]');
            } else {
                flecha.textContent = '';
                th.classList.remove('text-[#2f55c4]');
                th.classList.add('text-[#7a8db5]');
            }
        });
    }

    filasOriginales.forEach(function (tr) {
        tr.addEventListener('click', function (e) {
            if (e.target.closest('a')) return; // el enlace del nombre ya navega
            location.href = tr.getAttribute('data-href');
        });
    });

    cabeceras.forEach(function (th) {
        th.addEventListener('click', function () {
            var campo = th.getAttribute('data-col');
            if (estado.campo === campo) {
                estado.dir = estado.dir === 1 ? -1 : 1;
            } else {
                estado.campo = campo;
                estado.dir = 1;
            }
            estado.pagina = 1;
            marcarOrden();
            pintar();
        });
    });

    input.addEventListener('input', function () {
        estado.q = input.value;
        estado.pagina = 1;
        pintar();
    });

    anterior.addEventListener('click', function () {
        if (estado.pagina > 1) { estado.pagina--; pintar(); }
    });

    siguiente.addEventListener('click', function () {
        if (!siguiente.disabled) { estado.pagina++; pintar(); }
    });

    marcarOrden();
    pintar();
})();
