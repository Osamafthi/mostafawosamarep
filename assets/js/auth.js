(function () {
    'use strict';

    const cfg = window.APP_CONFIG || {};

    function formatError(err) {
        if (!err) return 'Something went wrong';
        if (typeof err === 'string') return err;
        if (err.errors) {
            const first = Object.values(err.errors)[0];
            if (Array.isArray(first)) return first[0];
        }
        return err.error || 'Something went wrong';
    }

    function initLoginPage() {
        const form = document.getElementById('loginForm');
        if (!form) return;

        if (Api.getToken()) {
            location.href = cfg.basePath + '/views/admin/index.php';
            return;
        }

        const errBox = document.getElementById('formError');
        const btn    = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errBox.hidden = true;
            errBox.textContent = '';
            btn.disabled = true;
            const label = btn.querySelector('.btn__text');
            const prev  = label.textContent;
            label.textContent = 'Signing in…';

            try {
                const data = await Api.post('/auth/admin/login', {
                    email:    document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value,
                }, { auth: false });

                Api.setToken(data.token);
                location.href = cfg.basePath + '/views/admin/index.php';
            } catch (err) {
                errBox.textContent = formatError(err);
                errBox.hidden = false;
                btn.disabled = false;
                label.textContent = prev;
            }
        });
    }

    function guardAdminPage() {
        if (!Api.getToken()) {
            location.href = cfg.basePath + '/views/admin/login.php';
            return false;
        }
        return true;
    }

    function bindLogout(selector) {
        const btn = document.querySelector(selector);
        if (!btn) return;
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            Api.setToken(null);
            location.href = cfg.basePath + '/views/admin/login.php';
        });
    }

    window.Auth = { initLoginPage, guardAdminPage, bindLogout, formatError };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLoginPage);
    } else {
        initLoginPage();
    }
})();
