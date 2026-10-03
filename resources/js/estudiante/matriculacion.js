// Cerrar los avisos al pulsar la X
document.querySelectorAll('.alert-close').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var banner = btn.closest('.alert-banner');
        if (banner) banner.remove();
    });
});

// Confirmación con estilo antes de desmatricular
var confirmDialog = document.getElementById('confirm-desinscribir');
var pendingForm = null;

document.querySelectorAll('[data-submit-form]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        pendingForm = document.getElementById(btn.getAttribute('data-submit-form'));
        document.getElementById('confirm-curso').textContent = btn.getAttribute('data-curso');
        confirmDialog.showModal();
    });
});

document.querySelectorAll('[data-close-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        confirmDialog.close();
    });
});

document.getElementById('confirm-submit').addEventListener('click', function () {
    if (pendingForm) pendingForm.submit();
});

// Cerrar si se hace clic fuera del diálogo
confirmDialog.addEventListener('click', function (e) {
    if (e.target === confirmDialog) confirmDialog.close();
});
