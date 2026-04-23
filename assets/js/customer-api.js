/**
 * customer-api.js — tiny fetch wrapper for the customer storefront.
 *
 * Mirrors the shape of assets/js/api.js but is intentionally separate so
 * that the public/customer layer never reads or writes the admin token.
 * Unlike the admin wrapper this does NOT redirect to a login page on 401
 * (the storefront allows guest browsing and guest checkout).
 */
(function (global) {
    'use strict';

    const cfg = global.APP_CONFIG || {
        apiBase:  'http://localhost:8000/api/v1',
        basePath: '/mostafawosama',
    };
    const STORAGE_KEY = 'customerToken';

    function getToken() {
        try { return localStorage.getItem(STORAGE_KEY); } catch (_) { return null; }
    }
    function setToken(token) {
        try {
            if (token) localStorage.setItem(STORAGE_KEY, token);
            else localStorage.removeItem(STORAGE_KEY);
        } catch (_) { /* ignore */ }
    }

    async function request(path, opts = {}) {
        const { method = 'GET', body, isForm = false, auth = false, query } = opts;

        let url = cfg.apiBase + path;
        if (query && typeof query === 'object') {
            const qs = new URLSearchParams();
            Object.entries(query).forEach(([k, v]) => {
                if (v !== undefined && v !== null && v !== '') qs.append(k, v);
            });
            const s = qs.toString();
            if (s) url += (url.includes('?') ? '&' : '?') + s;
        }

        const headers = { 'Accept': 'application/json' };
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
            // Clear the stale customer token, but do NOT redirect — the page can
            // continue in a logged-out state.
            setToken(null);
        }

        if (!res.ok) {
            throw json || { success: false, error: 'HTTP ' + res.status };
        }

        return json && json.data !== undefined ? json.data : json;
    }

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

    const CustomerApi = {
        config: cfg,
        getToken,
        setToken,
        isLoggedIn: () => !!getToken(),
        get:   (p, opts = {}) => request(p, { ...opts, method: 'GET' }),
        post:  (p, body, opts = {}) => request(p, { ...opts, method: 'POST',  body }),
        put:   (p, body, opts = {}) => request(p, { ...opts, method: 'PUT',   body }),
        patch: (p, body, opts = {}) => request(p, { ...opts, method: 'PATCH', body }),
        del:   (p, opts = {}) => request(p, { ...opts, method: 'DELETE' }),
        formatError,
    };

    global.CustomerApi = CustomerApi;
})(window);
