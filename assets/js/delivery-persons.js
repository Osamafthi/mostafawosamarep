(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        q:        '',
        active:   '',
        all:      [],
        editing:  null,
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
        return d.toLocaleString();
    }

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) { /* api.js redirected */ }
    }

    async function load() {
        const body = $('#deliveryRows');
        body.innerHTML = '<tr><td colspan="7" class="muted">Loading…</td></tr>';

        try {
            const persons = await Api.get('/admin/delivery-persons', {
                query: {
                    q: state.q,
                    active: state.active,
                }
            });
            state.all = Array.isArray(persons) ? persons : [];
            render();
        } catch (e) {
            body.innerHTML = `<tr><td colspan="7" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function render() {
        const body = $('#deliveryRows');
        if (!state.all.length) {
            body.innerHTML = '<tr><td colspan="7" class="muted">No couriers yet. Click "+ New courier" to create the first one.</td></tr>';
            return;
        }

        body.innerHTML = state.all.map(p => {
            const status = p.is_active
                ? '<span class="badge badge--active">Active</span>'
                : '<span class="badge badge--inactive">Inactive</span>';
            const toggleLabel = p.is_active ? 'Deactivate' : 'Activate';
            const toggleClass = p.is_active ? 'btn--ghost' : 'btn--primary';
            return `
                <tr data-id="${p.id}">
                    <td style="color:var(--text-muted); font-weight:600;">#${p.id}</td>
                    <td style="font-weight:700;">${esc(p.name)}</td>
                    <td>${esc(p.email)}</td>
                    <td>${esc(p.phone || '—')}</td>
                    <td>${status}</td>
                    <td style="color:var(--text-muted); font-size:12px;">${esc(fmtDate(p.last_assigned_at))}</td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn--ghost btn--sm" data-action="edit" data-id="${p.id}">Edit</button>
                            <button class="btn ${toggleClass} btn--sm" data-action="toggle" data-id="${p.id}">${toggleLabel}</button>
                            <button class="btn btn--danger btn--sm" data-action="delete" data-id="${p.id}">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                const action = btn.dataset.action;
                if (action === 'edit') openEdit(id);
                if (action === 'toggle') toggleActive(id);
                if (action === 'delete') deletePerson(id);
            });
        });
    }

    function openModal(title) {
        $('#modalTitle').textContent = title;
        $('#modalError').hidden = true;
        const modal = $('#deliveryModal');
        modal.hidden = false;
        modal.classList.add('is-open');
        setTimeout(() => $('#fName').focus(), 30);
    }

    function closeModal() {
        const modal = $('#deliveryModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
        state.editing = null;
    }

    function resetForm() {
        $('#fId').value              = '';
        $('#fName').value            = '';
        $('#fEmail').value           = '';
        $('#fPhone').value           = '';
        $('#fPassword').value        = '';
        $('#fPasswordConfirm').value = '';
        $('#fActive').checked        = true;
    }

    function openCreate() {
        resetForm();
        state.editing = null;
        $('#btnSave').textContent = 'Create courier';
        $('#fPwHint').textContent = '(at least 8 characters)';
        $('#fPassword').required = true;
        $('#fPasswordConfirm').required = true;
        openModal('New courier');
    }

    function openEdit(id) {
        const p = state.all.find(x => x.id === id);
        if (!p) return;
        resetForm();
        state.editing = p.id;
        $('#fId').value     = p.id;
        $('#fName').value   = p.name || '';
        $('#fEmail').value  = p.email || '';
        $('#fPhone').value  = p.phone || '';
        $('#fActive').checked = !!p.is_active;
        $('#btnSave').textContent = 'Save changes';
        $('#fPwHint').textContent = '(leave blank to keep current password)';
        $('#fPassword').required = false;
        $('#fPasswordConfirm').required = false;
        openModal('Edit courier');
    }

    async function save() {
        const errBox = $('#modalError');
        errBox.hidden = true;

        const name             = $('#fName').value.trim();
        const email            = $('#fEmail').value.trim();
        const phone            = $('#fPhone').value.trim();
        const password         = $('#fPassword').value;
        const passwordConfirm  = $('#fPasswordConfirm').value;
        const isActive         = $('#fActive').checked;

        if (name.length < 2) return fail('Name must be at least 2 characters.');
        if (!/^\S+@\S+\.\S+$/.test(email)) return fail('Please enter a valid email address.');

        if (!state.editing) {
            if (password.length < 8) return fail('Password must be at least 8 characters.');
            if (password !== passwordConfirm) return fail('Passwords do not match.');
        } else if (password) {
            if (password.length < 8) return fail('Password must be at least 8 characters.');
            if (password !== passwordConfirm) return fail('Passwords do not match.');
        }

        const btn = $('#btnSave');
        btn.disabled = true;
        const original = btn.textContent;
        btn.textContent = 'Saving…';

        try {
            const payload = {
                name,
                email,
                phone: phone || null,
                is_active: isActive,
            };
            if (password) {
                payload.password = password;
                payload.password_confirmation = passwordConfirm;
            }

            if (state.editing) {
                await Api.patch('/admin/delivery-persons/' + state.editing, payload);
                toast('Courier updated');
            } else {
                await Api.post('/admin/delivery-persons', payload);
                toast('Courier created');
            }
            closeModal();
            load();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            btn.disabled = false;
            btn.textContent = original;
        }

        function fail(msg) {
            errBox.textContent = msg;
            errBox.hidden = false;
        }
    }

    async function toggleActive(id) {
        try {
            await Api.patch('/admin/delivery-persons/' + id + '/toggle-active', null);
            toast('Status updated');
            load();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    async function deletePerson(id) {
        const p = state.all.find(x => x.id === id);
        const label = p ? `${p.name} (${p.email})` : '#' + id;
        if (!confirm(`Delete courier "${label}"?\n\nTheir tokens will be revoked and assigned orders will become unassigned.`)) return;

        try {
            await Api.del('/admin/delivery-persons/' + id);
            toast('Courier deleted');
            load();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function bindUi() {
        const modal = $('#deliveryModal');
        const dialog = modal.querySelector('.modal__dialog');

        $('#btnNewDelivery').addEventListener('click', openCreate);
        $('#btnRefresh').addEventListener('click', load);
        $('#btnSave').addEventListener('click', save);

        $('#deliveryForm').addEventListener('submit', (e) => {
            e.preventDefault();
            save();
        });

        document.querySelectorAll('[data-close]').forEach(el =>
            el.addEventListener('click', closeModal));

        dialog.addEventListener('click', (e) => e.stopPropagation());
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
            load();
        }, 250));

        $('#filterActive').addEventListener('change', (e) => {
            state.active = e.target.value;
            load();
        });

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        bindUi();
        closeModal();
        await loadMe();
        await load();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
