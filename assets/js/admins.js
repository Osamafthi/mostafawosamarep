(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        q:   '',
        all: [],
        me:  null,
    };

    const $ = (s) => document.querySelector(s);

    let toastTimer;
    function toast(msg, isError = false) {
        const el = $('#toast');
        el.textContent = msg;
        el.classList.toggle('is-error', !!isError);
        el.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { el.hidden = true; }, 2800);
    }

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        if (isNaN(d.getTime())) return '—';
        return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    }

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            state.me = me;
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) { /* unauthorized already handled */ }
    }

    async function loadAdmins() {
        const body = $('#adminRows');
        body.innerHTML = '<tr><td colspan="5" class="muted">Loading…</td></tr>';

        try {
            const admins = await Api.get('/admin/admins');
            state.all = Array.isArray(admins) ? admins : [];
            render();
        } catch (e) {
            body.innerHTML = `<tr><td colspan="5" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function render() {
        const body = $('#adminRows');
        const q = state.q.toLowerCase();
        const items = q
            ? state.all.filter(a =>
                (a.name || '').toLowerCase().includes(q) ||
                (a.email || '').toLowerCase().includes(q))
            : state.all;

        if (!items.length) {
            body.innerHTML = '<tr><td colspan="5" class="muted">No admins found</td></tr>';
            return;
        }

        const meId = state.me ? state.me.id : null;
        body.innerHTML = items.map(a => {
            const isSelf = meId === a.id;
            const selfBadge = isSelf ? ' <span class="badge badge--active">You</span>' : '';
            const deleteBtn = isSelf
                ? `<button class="btn btn--danger btn--sm" disabled title="You cannot delete your own account">Delete</button>`
                : `<button class="btn btn--danger btn--sm" data-action="delete" data-id="${a.id}">Delete</button>`;
            return `
                <tr data-id="${a.id}">
                    <td style="color:var(--text-muted); font-weight:600;">#${a.id}</td>
                    <td style="font-weight:700;">${esc(a.name)}${selfBadge}</td>
                    <td>${esc(a.email)}</td>
                    <td>${fmtDate(a.created_at)}</td>
                    <td>
                        <div class="row-actions">${deleteBtn}</div>
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => deleteAdmin(Number(btn.dataset.id)));
        });
    }

    /* Modal */
    function openModal() {
        const modal = $('#adminModal');
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#modalError').hidden = true;
        setTimeout(() => $('#fName').focus(), 30);
    }

    function closeModal() {
        const modal = $('#adminModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
    }

    function resetForm() {
        $('#fName').value            = '';
        $('#fEmail').value           = '';
        $('#fPassword').value        = '';
        $('#fPasswordConfirm').value = '';
    }

    function openCreate() {
        resetForm();
        openModal();
    }

    async function saveAdmin() {
        const errBox  = $('#modalError');
        errBox.hidden = true;
        const saveBtn = $('#btnSave');

        const name     = $('#fName').value.trim();
        const email    = $('#fEmail').value.trim();
        const password = $('#fPassword').value;
        const confirm  = $('#fPasswordConfirm').value;

        if (name.length < 2) {
            errBox.textContent = 'Name must be at least 2 characters.';
            errBox.hidden = false;
            return;
        }
        if (!/^\S+@\S+\.\S+$/.test(email)) {
            errBox.textContent = 'Please enter a valid email address.';
            errBox.hidden = false;
            return;
        }
        if (password.length < 8) {
            errBox.textContent = 'Password must be at least 8 characters.';
            errBox.hidden = false;
            return;
        }
        if (password !== confirm) {
            errBox.textContent = 'Passwords do not match.';
            errBox.hidden = false;
            return;
        }

        saveBtn.disabled = true;
        saveBtn.textContent = 'Creating…';

        try {
            await Api.post('/admin/admins', {
                name,
                email,
                password,
                password_confirmation: confirm,
            });
            toast('Admin account created');
            closeModal();
            loadAdmins();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Create admin';
        }
    }

    async function deleteAdmin(id) {
        const a = state.all.find(x => x.id === id);
        const label = a ? `${a.name} (${a.email})` : '#' + id;
        if (!confirm(`Delete admin account "${label}"?\n\nThey will be signed out immediately and cannot sign back in.`)) return;

        try {
            await Api.del('/admin/admins/' + id);
            toast('Admin deleted');
            loadAdmins();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function bindUi() {
        const modal       = $('#adminModal');
        const modalDialog = modal.querySelector('.modal__dialog');

        $('#btnNewAdmin').addEventListener('click', openCreate);
        $('#btnRefresh').addEventListener('click', loadAdmins);
        $('#btnSave').addEventListener('click', saveAdmin);

        $('#adminForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveAdmin();
        });

        document.querySelectorAll('[data-close]').forEach(el =>
            el.addEventListener('click', closeModal));

        modalDialog.addEventListener('click', (e) => e.stopPropagation());
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.classList.contains('modal__backdrop')) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        $('#searchInput').addEventListener('input', debounce((e) => {
            state.q = e.target.value.trim();
            render();
        }, 200));

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        bindUi();
        closeModal();
        await loadMe();
        await loadAdmins();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
