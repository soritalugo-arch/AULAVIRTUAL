(function () {
    var triggers = document.querySelectorAll('[data-sel-toggle]');

    function closeAllSel() {
        var open = document.querySelectorAll('.sel.is-open');
        for (var oi = 0; oi < open.length; oi++) {
            open[oi].classList.remove('is-open');
        }
    }

    function closeCal() {
        if (cal) cal.classList.remove('is-open');
    }

    for (var ti = 0; ti < triggers.length; ti++) {
        (function (trigger) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var sel = trigger.closest('.sel');

                if (sel.classList.contains('is-open')) {
                    sel.classList.remove('is-open');
                } else {
                    closeCal();
                    closeAllSel();
                    sel.classList.add('is-open');
                }
            });
        })(triggers[ti]);
    }

    /* =====================================================
       Calendario a medida
    ===================================================== */

    var cal = document.querySelector('[data-cal]');

    if (cal) {
        var title   = cal.querySelector('[data-cal-title]');
        var grid    = cal.querySelector('[data-cal-grid]');
        var label   = cal.querySelector('[data-cal-label]');
        var prev    = cal.querySelector('[data-cal-prev]');
        var next    = cal.querySelector('[data-cal-next]');
        var todayBtn = cal.querySelector('[data-cal-today]');
        var hint    = cal.querySelector('[data-cal-hint]');

        var baseUrl = cal.getAttribute('data-url');
        var cuatr  = cal.getAttribute('data-cuatrimestre');
        var fecha  = cal.getAttribute('data-fecha') || '';
        var minD   = toDate(cal.getAttribute('data-min'));
        var maxD   = toDate(cal.getAttribute('data-max'));

        /* Días de la semana en que el curso tiene clases. getDay(): 0=Dom..6=Sáb (igual que Carbon dayOfWeek) */
        var diasRaw  = (cal.getAttribute('data-dias') || '').trim();
        var diasClase = diasRaw ? diasRaw.split(',').map(function (n) { return parseInt(n, 10); }) : [];

        var DIAS    = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        var MESES   = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
                        'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        var MESES_C = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul',
                        'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        var selected = fecha ? toDate(fecha) : null;
        var viewY = 0, viewM = 0;

        function toDate(s) {
            var p = s.split('-');
            return new Date(+p[0], +p[1] - 1, +p[2]);
        }

        function pad(n) {
            return (n < 10 ? '0' : '') + n;
        }

        function iso(d) {
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        }

        function today() {
            var n = new Date();
            return new Date(n.getFullYear(), n.getMonth(), n.getDate());
        }

        function labelText(d) {
            return DIAS[d.getDay()] + ' ' + d.getDate() + ' ' + MESES_C[d.getMonth()] + ' ' + d.getFullYear();
        }

        function render(y, m) {
            title.textContent = MESES[m] + ' ' + y;

            var first  = new Date(y, m, 1);
            var days   = new Date(y, m + 1, 0).getDate();
            var offset = (first.getDay() + 6) % 7;
            var t      = today();
            var html   = '';

            for (var b = 0; b < offset; b++) {
                html += '<span class="cal-cell"></span>';
            }

            for (var d = 1; d <= days; d++) {
                var dd      = new Date(y, m, d);
                var dentro  = dd >= minD && dd <= maxD;
                var esClase = !diasClase.length || diasClase.indexOf(dd.getDay()) !== -1;
                var dis     = !dentro || !esClase;
                var cls     = 'cal-cell';

                if (selected && iso(dd) === iso(selected)) cls += ' is-selected';
                if (iso(dd) === iso(t)) cls += ' is-today';

                html += '<button type="button" class="' + cls + '" data-fecha="' + iso(dd) + '"'
                    + (dis ? ' disabled' : '') + '>' + d + '</button>';
            }

            grid.innerHTML = html;

            var prevM = m - 1, prevY = y;
            if (prevM < 0) { prevM = 11; prevY--; }
            prev.disabled = new Date(prevY, prevM + 1, 0) < minD;

            var nextM = m + 1, nextY = y;
            if (nextM > 11) { nextM = 0; nextY++; }
            next.disabled = new Date(nextY, nextM, 1) > maxD;
        }

        function openCal() {
            var base = selected || toDate(fecha);
            viewY = base.getFullYear();
            viewM = base.getMonth();
            render(viewY, viewM);
            todayBtn.disabled = hoyNoValido();
            if (hint && diasClase.length) hint.textContent = 'Solo se permiten días de clase';
            cal.classList.add('is-open');
        }

        function hoyNoValido() {
            var t = today();
            if (t < minD || t > maxD) return true;
            if (diasClase.length && diasClase.indexOf(t.getDay()) === -1) return true;
            return false;
        }

        cal.querySelector('.cal-trigger').addEventListener('click', function (e) {
            e.stopPropagation();
            if (cal.classList.contains('is-open')) {
                cal.classList.remove('is-open');
            } else {
                closeAllSel();
                openCal();
            }
        });

        prev.addEventListener('click', function () {
            viewM--;
            if (viewM < 0) { viewM = 11; viewY--; }
            render(viewY, viewM);
        });

        next.addEventListener('click', function () {
            viewM++;
            if (viewM > 11) { viewM = 0; viewY++; }
            render(viewY, viewM);
        });

        grid.addEventListener('click', function (e) {
            var target = e.target.closest ? e.target.closest('.cal-cell') : null;
            if (!target || target.disabled || target.tagName !== 'BUTTON') return;
            location.href = baseUrl + '?cuatrimestre=' + cuatr + '&fecha=' + target.getAttribute('data-fecha');
        });

        todayBtn.addEventListener('click', function () {
            if (hoyNoValido()) return;
            var t = today();
            location.href = baseUrl + '?cuatrimestre=' + cuatr + '&fecha=' + iso(t);
        });

        label.textContent = labelText(selected || toDate(fecha));
    }

    /* Cerrar solo al hacer clic FUERA del dropdown/calendario o con Escape */
    document.addEventListener('click', function (e) {
        var target = e.target;
        var inside = target && target.closest && (target.closest('.sel') || target.closest('.cal'));

        if (!inside) {
            closeAllSel();
            closeCal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllSel();
            closeCal();
        }
    });
})();
