document.querySelectorAll('[data-print]').forEach(function (boton) {
    boton.addEventListener('click', function () { window.print(); });
});