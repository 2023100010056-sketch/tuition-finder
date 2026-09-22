/* ============================================================
   Tuition Finder - small interface helpers
   1. Mobile navigation toggle
   2. Auto-hide flash messages after a few seconds
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ---- mobile menu ----------------------------------------
    var toggle = document.querySelector('.nav-toggle');
    var nav    = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // ---- flash messages fade away ---------------------------
    var flash = document.querySelector('.alert-success');
    if (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(function () {
                if (flash.parentNode) { flash.parentNode.removeChild(flash); }
            }, 500);
        }, 4000);
    }
});
