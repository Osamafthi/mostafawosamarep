/**
 * auth.js — shared auth bootstrap used by:
 *   1) Admin pages (`views/admin/*`) through a global `window.Auth`.
 *   2) Customer login/register pages (`views/customer/login.php` / register.php).
 *   3) Delivery pages (`views/delivery/*`) through `window.Auth.guardDeliveryPage`.
 */
(function () {
    'use strict';

    const path = window.location.pathname || '';
    const isAdminPath = /\/views\/admin\//.test(path);
    const isDeliveryPath = /\/views\/delivery\//.test(path);

    if (isAdminPath) {
        bootAdminAuth();
        return;
    }

    if (isDeliveryPath) {
        bootDeliveryAuth();
        return;
    }

    bootCustomerAuth();

    function bootAdminAuth() {
        const Api = window.Api;
        const cfg = (Api && Api.config) || window.APP_CONFIG || { basePath: '/mostafawosama' };

        function formatError(err) {
            if (!err) return 'Something went wrong';
            if (typeof err === 'string') return err;
            if (err.error) return err.error;
            if (err.message) return err.message;
            if (err.errors && typeof err.errors === 'object') {
                const first = Object.values(err.errors)[0];
                if (Array.isArray(first) && first.length) return first[0];
            }
            return 'Something went wrong';
        }

        function forceLoginRedirect() {
            const loginPath = cfg.basePath + '/views/admin/login.php';
            if (!path.endsWith('/views/admin/login.php')) {
                // User requested that protected admin URLs should not render content
                // for unauthenticated sessions.
                document.body.innerHTML = '';
                window.location.replace(loginPath);
            }
        }

        function revealBody() {
            // Pages that opt in to the "verify before render" guard set
            // <body data-guarded>. We reveal it only after /admin/me
            // confirms the token actually belongs to an admin.
            if (document.body) document.body.classList.add('auth-ready');
        }

        function guardAdminPage() {
            const token = Api && typeof Api.getToken === 'function'
                ? Api.getToken()
                : localStorage.getItem('adminToken');
            if (!token) {
                forceLoginRedirect();
                return false;
            }

            // Defense in depth: the API client already kicks 401/403
            // back to login, but we issue an explicit /admin/me round
            // trip up front so a tampered token can never paint the
            // admin UI before being rejected.
            if (Api && typeof Api.get === 'function') {
                Api.get('/admin/me')
                    .then(revealBody)
                    .catch(() => {
                        if (Api && typeof Api.setToken === 'function') {
                            Api.setToken(null);
                        }
                        forceLoginRedirect();
                    });
            } else {
                revealBody();
            }
            return true;
        }

        function bindLogout(selector) {
            const btn = document.querySelector(selector);
            if (!btn) return;
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    if (Api && typeof Api.post === 'function') {
                        await Api.post('/admin/logout', null);
                    }
                } catch (_) {
                    // ignore network/API failures on logout
                }
                if (Api && typeof Api.setToken === 'function') {
                    Api.setToken(null);
                } else {
                    localStorage.removeItem('adminToken');
                }
                window.location.replace(cfg.basePath + '/views/admin/login.php');
            });
        }

        window.Auth = { guardAdminPage, bindLogout, formatError };

        if (!path.endsWith('/views/admin/login.php')) {
            return;
        }

        const form = document.getElementById('loginForm');
        const error = document.getElementById('formError');
        const submitBtn = document.getElementById('submitBtn');
        if (!form || !error || !submitBtn || !Api) return;

        if (Api.getToken && Api.getToken()) {
            window.location.replace(cfg.basePath + '/views/admin/index.php');
            return;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            error.hidden = true;
            submitBtn.disabled = true;

            const email = (document.getElementById('email')?.value || '').trim();
            const password = (document.getElementById('password')?.value || '').trim();

            try {
                const res = await Api.post('/auth/admin/login', { email, password }, { auth: false });
                if (!res || !res.token) {
                    throw new Error('Unexpected response from server');
                }
                Api.setToken(res.token);
                window.location.replace(cfg.basePath + '/views/admin/index.php');
            } catch (err) {
                error.textContent = formatError(err);
                error.hidden = false;
            } finally {
                submitBtn.disabled = false;
            }
        });
    }

    function bootCustomerAuth() {
        // Expose customer-specific page guard before the auth-card check
        // so protected pages (orders.php, etc) can call it even though
        // they don't render an [data-auth-root] login/register form.
        installCustomerGuards();

        const root = document.querySelector('[data-auth-root]');
        if (!root || !window.UI || !window.CustomerApi) return;

        const { esc, buildPath, toast } = window.UI;
        const Api = window.CustomerApi;
        const mode = root.dataset.authRoot === 'register' ? 'register' : 'login';

        if (Api.isLoggedIn()) {
            window.location.replace(redirectTarget());
            return;
        }

        function redirectTarget() {
            const usp = new URLSearchParams(window.location.search);
            const raw = (usp.get('redirect') || '').trim();
            if (raw && raw.startsWith('/') && !raw.startsWith('//')) {
                return raw;
            }
            return buildPath('/');
        }

        function renderLogin() {
            root.innerHTML = `
                <div class="auth-card">
                    <h1 class="auth-card__title">Sign in</h1>
                    <p class="auth-card__lead">Welcome back — please enter your details.</p>

                    <form class="auth-form" data-auth-form novalidate>
                        <div class="form-error" data-form-error hidden></div>
                        <div class="field">
                            <label class="field__label" for="f-email">Email</label>
                            <input id="f-email" name="email" type="email" required maxlength="190" autocomplete="email" autofocus>
                        </div>
                        <div class="field">
                            <label class="field__label" for="f-password">Password</label>
                            <input id="f-password" name="password" type="password" required minlength="8" autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn--primary btn--lg btn--block" data-submit-btn>Sign in</button>
                    </form>

                    <div class="auth-card__switch">
                        New here?
                        <a href="${esc(buildPath('/views/customer/register.php') + redirectQuery())}">Create an account</a>
                    </div>
                </div>
            `;
            wireForm();
        }

        function renderRegister() {
            root.innerHTML = `
                <div class="auth-card auth-card--wide">
                    <h1 class="auth-card__title">Create your account</h1>
                    <p class="auth-card__lead">Save your details for faster checkout next time.</p>

                    <form class="auth-form" data-auth-form novalidate>
                        <div class="form-error" data-form-error hidden></div>
                        <div class="field">
                            <label class="field__label" for="f-name">Full name</label>
                            <input id="f-name" name="name" required minlength="2" maxlength="150" autocomplete="name" autofocus>
                        </div>
                        <div class="field">
                            <label class="field__label" for="f-email">Email</label>
                            <input id="f-email" name="email" type="email" required maxlength="190" autocomplete="email">
                        </div>
                        <div class="auth-grid">
                            <div class="field">
                                <label class="field__label" for="f-password">Password</label>
                                <input id="f-password" name="password" type="password" required minlength="8" autocomplete="new-password">
                            </div>
                            <div class="field">
                                <label class="field__label" for="f-password2">Confirm password</label>
                                <input id="f-password2" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">
                            </div>
                        </div>
                        <div class="field">
                            <label class="field__label" for="f-phone">Phone (optional)</label>
                            <input id="f-phone" name="phone" maxlength="40" autocomplete="tel">
                        </div>
                        <div class="field">
                            <label class="field__label" for="f-address">Default shipping address (optional)</label>
                            <textarea id="f-address" name="default_shipping_address" minlength="5" maxlength="500" rows="3" autocomplete="street-address"></textarea>
                            <div class="field__hint">Saved to your profile so checkout can pre-fill it next time.</div>
                        </div>
                        <button type="submit" class="btn btn--primary btn--lg btn--block" data-submit-btn>Create account</button>
                    </form>

                    <div class="auth-card__switch">
                        Already have an account?
                        <a href="${esc(buildPath('/views/customer/login.php') + redirectQuery())}">Sign in</a>
                    </div>
                </div>
            `;
            wireForm();
        }

        function redirectQuery() {
            const usp = new URLSearchParams(window.location.search);
            const raw = (usp.get('redirect') || '').trim();
            return raw ? ('?redirect=' + encodeURIComponent(raw)) : '';
        }

        function renderRegisterSuccess(res) {
            const email = res && res.customer && res.customer.email ? res.customer.email : '';
            root.innerHTML = `
                <div class="auth-card">
                    <h1 class="auth-card__title">Welcome aboard!</h1>
                    <p class="auth-card__lead">
                        You're signed in. We just sent a verification link to
                        <strong>${esc(email)}</strong> — click it when you have a moment so we
                        can secure your account.
                    </p>
                    <div class="form-ok">
                        You can keep shopping right now; verification happens in the background.
                    </div>
                    <div class="auth-card__actions">
                        <a class="btn btn--primary" href="${esc(redirectTarget())}">Continue shopping</a>
                        <a class="btn btn--ghost" href="${esc(buildPath('/views/customer/orders.php'))}">View my orders</a>
                    </div>
                </div>
            `;
        }

        function wireForm() {
            const form = root.querySelector('[data-auth-form]');
            const btn  = root.querySelector('[data-submit-btn]');
            const err  = root.querySelector('[data-form-error]');
            if (!form || !btn || !err) return;

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                err.hidden = true;
                btn.disabled = true;
                btn.textContent = mode === 'register' ? 'Creating…' : 'Signing in…';

                const data = new FormData(form);
                const payload = {};
                for (const [k, v] of data.entries()) {
                    const s = (v || '').toString().trim();
                    if (s !== '') payload[k] = s;
                }

                if (mode === 'register') {
                    const pw  = payload.password || '';
                    const pw2 = payload.password_confirmation || '';
                    if (pw.length < 8) return fail('Password must be at least 8 characters.');
                    if (pw !== pw2)    return fail('Passwords do not match.');
                }

                try {
                    const endpoint = mode === 'register'
                        ? '/auth/customer/register'
                        : '/auth/customer/login';
                    const res = await Api.post(endpoint, payload);

                    if (!res || !res.token) {
                        return fail('Unexpected response from server.');
                    }
                    Api.setToken(res.token);

                    if (mode === 'register') {
                        renderRegisterSuccess(res);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        toast('Signed in', 'success');
                        window.location.replace(redirectTarget());
                    }
                } catch (e2) {
                    fail(Api.formatError(e2));
                }

                function fail(msg) {
                    err.textContent = msg;
                    err.hidden = false;
                    btn.disabled = false;
                    btn.textContent = mode === 'register' ? 'Create account' : 'Sign in';
                }
            });
        }

        if (mode === 'register') renderRegister();
        else                     renderLogin();
    }

    function installCustomerGuards() {
        const Api = window.CustomerApi;
        const cfg = (Api && Api.config) || window.APP_CONFIG || { basePath: '/mostafawosama' };

        function buildPath(p) {
            const base = (cfg.basePath || '').replace(/\/+$/, '');
            const rel  = String(p || '').replace(/^\/+/, '');
            return base + '/' + rel;
        }

        function loginRedirect() {
            const here = (location.pathname || '/') + (location.search || '');
            location.replace(
                buildPath('/views/customer/login.php') +
                '?redirect=' + encodeURIComponent(here)
            );
        }

        function revealBody() {
            if (document.body) document.body.classList.add('auth-ready');
        }

        function guardCustomerPage() {
            const token = Api && typeof Api.getToken === 'function'
                ? Api.getToken()
                : localStorage.getItem('customerToken');
            if (!token) {
                loginRedirect();
                return false;
            }

            if (Api && typeof Api.get === 'function') {
                Api.get('/customer/me', { auth: true })
                    .then(revealBody)
                    .catch(() => {
                        // The customer-api client already cleared the
                        // token on 401/403; redirect now so the page
                        // never finishes painting.
                        if (Api && typeof Api.setToken === 'function') {
                            Api.setToken(null);
                        }
                        loginRedirect();
                    });
            } else {
                revealBody();
            }
            return true;
        }

        const existing = window.Auth || {};
        window.Auth = Object.assign({}, existing, { guardCustomerPage });
    }

    function bootDeliveryAuth() {
        const Api = window.DeliveryApi;
        const cfg = (Api && Api.config) || window.APP_CONFIG || { basePath: '/mostafawosama' };

        function formatError(err) {
            if (!err) return 'Something went wrong';
            if (typeof err === 'string') return err;
            if (err.error) return err.error;
            if (err.message) return err.message;
            if (err.errors && typeof err.errors === 'object') {
                const first = Object.values(err.errors)[0];
                if (Array.isArray(first) && first.length) return first[0];
            }
            return 'Something went wrong';
        }

        function forceLoginRedirect() {
            const loginPath = cfg.basePath + '/views/delivery/login.php';
            if (!path.endsWith('/views/delivery/login.php')) {
                // Blank out the body before redirecting so a tampered
                // token can never flash protected content.
                document.body.innerHTML = '';
                window.location.replace(loginPath);
            }
        }

        function revealBody() {
            // Same pattern as the admin guard: pages mark themselves
            // with <body data-guarded> and stay invisible until the
            // courier identity is confirmed.
            if (document.body) document.body.classList.add('auth-ready');
        }

        function guardDeliveryPage() {
            const token = Api && typeof Api.getToken === 'function'
                ? Api.getToken()
                : localStorage.getItem('deliveryToken');
            if (!token) {
                forceLoginRedirect();
                return false;
            }

            if (Api && typeof Api.get === 'function') {
                Api.get('/delivery/me')
                    .then(revealBody)
                    .catch(() => {
                        if (Api && typeof Api.setToken === 'function') {
                            Api.setToken(null);
                        }
                        forceLoginRedirect();
                    });
            } else {
                revealBody();
            }
            return true;
        }

        function bindLogout(selector) {
            const btn = document.querySelector(selector);
            if (!btn) return;
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    if (Api && typeof Api.post === 'function') {
                        await Api.post('/delivery/logout', null);
                    }
                } catch (_) {
                    // Ignore network/API failures so logout always
                    // clears the local token.
                }
                if (Api && typeof Api.setToken === 'function') {
                    Api.setToken(null);
                } else {
                    localStorage.removeItem('deliveryToken');
                }
                window.location.replace(cfg.basePath + '/views/delivery/login.php');
            });
        }

        window.Auth = { guardDeliveryPage, bindLogout, formatError };

        // Login page wiring — only runs on /views/delivery/login.php.
        if (!path.endsWith('/views/delivery/login.php')) return;

        const root = document.querySelector('[data-auth-root="delivery-login"]');
        if (!root || !Api) return;

        if (Api.getToken && Api.getToken()) {
            window.location.replace(cfg.basePath + '/views/delivery/orders.php');
            return;
        }

        root.innerHTML = `
            <div class="auth-card">
                <h1 class="auth-card__title">Courier sign in</h1>
                <p class="auth-card__lead">Use the credentials your admin gave you.</p>

                <form class="auth-form" id="deliveryLoginForm" novalidate>
                    <div class="form-error" id="deliveryFormError" hidden></div>
                    <div class="field">
                        <label class="field__label" for="d-email">Email</label>
                        <input id="d-email" name="email" type="email" required maxlength="190" autocomplete="email" autofocus>
                    </div>
                    <div class="field">
                        <label class="field__label" for="d-password">Password</label>
                        <input id="d-password" name="password" type="password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn--primary btn--lg btn--block" id="deliverySubmitBtn">Sign in</button>
                </form>
            </div>
        `;

        const form  = document.getElementById('deliveryLoginForm');
        const error = document.getElementById('deliveryFormError');
        const btn   = document.getElementById('deliverySubmitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            error.hidden = true;
            btn.disabled = true;
            btn.textContent = 'Signing in…';

            const email = (document.getElementById('d-email').value || '').trim();
            const password = (document.getElementById('d-password').value || '').trim();

            try {
                const res = await Api.post('/auth/delivery/login', { email, password }, { auth: false });
                if (!res || !res.token) {
                    throw new Error('Unexpected response from server');
                }
                Api.setToken(res.token);
                window.location.replace(cfg.basePath + '/views/delivery/orders.php');
            } catch (err) {
                error.textContent = formatError(err);
                error.hidden = false;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Sign in';
            }
        });
    }
})();
