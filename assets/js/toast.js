(function () {
    'use strict';

    var container = document.getElementById('toast-container');
    if (!container) return;

    window.showToast = function (message, type, duration) {
        type = type || 'success';
        duration = duration || 4000;

        var toast = document.createElement('div');
        toast.className = 'toast-notification toast-' + type;
        toast.setAttribute('role', 'status');

        var text = document.createElement('span');
        text.textContent = message;
        toast.appendChild(text);

        var close = document.createElement('button');
        close.className = 'toast-close';
        close.setAttribute('aria-label', 'Fermer');
        close.innerHTML = '&times;';
        close.addEventListener('click', function () {
            removeToast(toast);
        });
        toast.appendChild(close);

        container.appendChild(toast);

        setTimeout(function () {
            removeToast(toast);
        }, duration);
    };

    function removeToast(toast) {
        if (!toast.parentNode) return;
        toast.classList.add('removing');
        setTimeout(function () {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 250);
    }

    var flashEls = document.querySelectorAll('.js-flash');
    for (var i = 0; i < flashEls.length; i++) {
        var el = flashEls[i];
        var msg = el.textContent.trim();
        if (msg) {
            var type = 'success';
            if (el.classList.contains('alert-danger') || el.classList.contains('alert-error')) {
                type = 'error';
            }
            showToast(msg, type);
        }
        el.remove();
    }
})();
