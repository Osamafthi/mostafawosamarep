(function (global) {
    'use strict';

    const cfg      = global.APP_CONFIG || { apiBase: 'http://localhost:8000/api/v1', basePath: '/mostafawosama' };
    const STORAGE_KEY = 'adminToken';

    function getToken() {
        return localStorage.getItem(STORAGE_KEY);
    }
    function setToken(token) {
        if (token) localStorage.setItem(STORAGE_KEY, token);
        else localStorage.removeItem(STORAGE_KEY);
    }

    async function request(path, opts = {}) {
        const { method = 'GET', body, isForm = false, auth = true, query } = opts;

        let url = cfg.apiBase + path;
        if (query && typeof query === 'object') {
            const qs = new URLSearchParams();
            Object.entries(query).forEach(([k, v]) => {
                if (v !== undefined && v !== null && v !== '') qs.append(k, v);
            });
            const s = qs.toString();
            if (s) url += (url.includes('?') ? '&' : '?') + s;
        }

        const headers = {};
        if (auth) {
            const token = getToken();
            if (token) headers['Authorization'] = 'Bearer ' + token;
        }

        let payload = body;
        if (!isForm && body !== undefined && body !== null) {
            headers['Content-Type'] = 'application/json';
            payload = JSON.stringify(body);
        }

        let res;
        try {
            res = await fetch(url, { method, headers, body: payload });
        } catch (e) {
            throw { success: false, error: 'Network error: ' + e.message };
        }

        let json = null;
        const text = await res.text();
        try { json = text ? JSON.parse(text) : null; } catch (_) { /* non-json */ }

        if (res.status === 401 && auth) {
            setToken(null);
            if (!location.pathname.endsWith('/login.php')) {
                location.href = cfg.basePath + '/views/admin/login.php';
            }
        }

        if (!res.ok) {
            throw json || { success: false, error: 'HTTP ' + res.status };
        }

        return json && json.data !== undefined ? json.data : json;
    }

    const Api = {
        config: cfg,
        getToken,
        setToken,
        get:    (p, opts = {}) => request(p, { ...opts, method: 'GET' }),
        post:   (p, body, opts = {}) => request(p, { ...opts, method: 'POST', body }),
        put:    (p, body, opts = {}) => request(p, { ...opts, method: 'PUT', body }),
        patch:  (p, body, opts = {}) => request(p, { ...opts, method: 'PATCH', body }),
        del:    (p, opts = {}) => request(p, { ...opts, method: 'DELETE' }),
        upload: (p, file) => {
            const fd = new FormData();
            fd.append('file', file);
            return request(p, { method: 'POST', body: fd, isForm: true });
        },
    };

    global.Api = Api;
})(window);
