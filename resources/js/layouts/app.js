document.addEventListener('DOMContentLoaded', function() {
    var btnMenu = document.getElementById('btn-toggle-menu');
    var menu = document.getElementById('mobile-menu');
    
    if (btnMenu && menu) {
        btnMenu.addEventListener('click', function() {
            menu.classList.toggle('show-mobile');
            var icon = btnMenu.querySelector('i');
            if (menu.classList.contains('show-mobile')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Filtros que se envían solos al cambiar el valor
    document.querySelectorAll('select[data-auto-submit]').forEach(function (select) {
        select.addEventListener('change', function () { this.form.submit(); });
    });
});

(function () {
    var menu = document.querySelector('.nav-menu');
    var indicator = document.querySelector('.nav-indicator');
    if (!menu || !indicator || window.innerWidth <= 900) return; // Se desactiva en móvil

    var links = Array.prototype.slice.call(menu.querySelectorAll('a.nav-link'));
    if (!links.length) return;

    var active = null;
    for (var i = 0; i < links.length; i++) {
        if (links[i].classList.contains('active')) { active = links[i]; break; }
    }
    var activeIndex = active ? links.indexOf(active) : -1;
    var firstHref = links[0] ? links[0].getAttribute('href') : null;

    function visible(el) { return el.offsetParent !== null; }

    function pos(el) {
        var m = menu.getBoundingClientRect();
        var l = el.getBoundingClientRect();
        return { left: l.left - m.left + (menu.scrollLeft || 0), width: l.width };
    }

    function place(x, w, animate) {
        indicator.style.transition = animate ? '' : 'none';
        indicator.style.opacity = '1';
        indicator.style.transform = 'translateX(' + x + 'px)';
        indicator.style.width = w + 'px';
    }

    function render(animate) {
        if (!active || !visible(menu)) {
            indicator.style.opacity = '0';
            return;
        }
        var p = pos(active);
        place(p.left, p.width, animate);
    }

    var prev = null;
    try { prev = JSON.parse(sessionStorage.getItem('__aulaNavIndicator') || 'null'); } catch (e) {}

    var coinciden = prev && prev.index >= 0 && prev.index < links.length
                 && prev.index !== activeIndex && prev.firstHref === firstHref;

    if (active && coinciden) {
        var from = pos(links[prev.index]);
        place(from.left, from.width, false);
        void indicator.offsetWidth; 
        render(true);
    } else {
        render(false);
    }

    try {
        sessionStorage.setItem('__aulaNavIndicator',
            JSON.stringify({ index: activeIndex, firstHref: firstHref }));
    } catch (e) {}

    window.addEventListener('resize', function () { 
        if(window.innerWidth > 900) render(false); 
    });
})();
