(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        q:          '',
        all:        [],
        editing:    null,
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

    function truncate(s, n = 90) {
        s = String(s ?? '');
        return s.length > n ? s.slice(0, n - 1) + '…' : s;
    }

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) { /* unauthorized already handled */ }
    }

    async function loadCategories() {
        const body = $('#categoryRows');
        body.innerHTML = '<tr><td colspan="5" class="muted">Loading…</td></tr>';

        try {
            const cats = await Api.get('/admin/categories');
            state.all = Array.isArray(cats) ? cats : [];
            render();
        } catch (e) {
            body.innerHTML = `<tr><td colspan="5" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function render() {
        const body = $('#categoryRows');
        const q = state.q.toLowerCase();
        const items = q
            ? state.all.filter(c =>
                (c.name || '').toLowerCase().includes(q) ||
                (c.slug || '').toLowerCase().includes(q) ||
                (c.description || '').toLowerCase().includes(q))
            : state.all;

        if (!items.length) {
            body.innerHTML = '<tr><td colspan="5" class="muted">No categories found</td></tr>';
            return;
        }

        body.innerHTML = items.map(c => `
            <tr data-id="${c.id}">
                <td style="color:var(--text-muted); font-weight:600;">#${c.id}</td>
                <td style="font-weight:700;">${esc(c.name)}</td>
                <td><code style="font-size:12px;color:var(--text-muted);">${esc(c.slug || '—')}</code></td>
                <td>${c.description ? esc(truncate(c.description)) : '<span class="muted" style="padding:0;">—</span>'}</td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn--ghost btn--sm" data-action="edit" data-id="${c.id}">Edit</button>
                        <button class="btn btn--danger btn--sm" data-action="delete" data-id="${c.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        body.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                if (btn.dataset.action === 'edit')   openEdit(id);
                if (btn.dataset.action === 'delete') deleteCategory(id);
            });
        });
    }

    /* Modal */
    function openModal(title) {
        const modal = $('#categoryModal');
        $('#modalTitle').textContent = title;
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#modalError').hidden = true;
        setTimeout(() => $('#fName').focus(), 30);
    }

    function closeModal() {
        const modal = $('#categoryModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
        state.editing = null;
    }

    function resetForm() {
        $('#fId').value          = '';
        $('#fName').value        = '';
        $('#fDescription').value = '';
    }

    function openCreate() {
        resetForm();
        openModal('New Category');
    }

    function openEdit(id) {
        const c = state.all.find(x => x.id === id);
        if (!c) return;
        resetForm();
        state.editing = c.id;
        $('#fId').value          = c.id;
        $('#fName').value        = c.name || '';
        $('#fDescription').value = c.description || '';
        openModal('Edit Category');
    }

    async function saveCategory() {
        const errBox  = $('#modalError');
        errBox.hidden = true;
        const saveBtn = $('#btnSave');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';

        const name = $('#fName').value.trim();
        const description = $('#fDescription').value.trim();

        if (name.length < 2) {
            errBox.textContent = 'Name must be at least 2 characters.';
            errBox.hidden = false;
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
            return;
        }

        const payload = {
            name,
            description: description === '' ? null : description,
        };

        try {
            if (state.editing) {
                await Api.put('/admin/categories/' + state.editing, payload);
                toast('Category updated');
            } else {
                await Api.post('/admin/categories', payload);
                toast('Category created');
            }
            closeModal();
            loadCategories();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    }

    async function deleteCategory(id) {
        const c = state.all.find(x => x.id === id);
        const name = c ? c.name : '#' + id;
        if (!confirm(`Delete category "${name}"?\n\nThis cannot be undone. Categories with existing products cannot be deleted.`)) return;

        try {
            await Api.del('/admin/categories/' + id);
            toast('Category deleted');
            loadCategories();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function bindUi() {
        const modal       = $('#categoryModal');
        const modalDialog = modal.querySelector('.modal__dialog');

        $('#btnNewCategory').addEventListener('click', openCreate);
        $('#btnRefresh').addEventListener('click', loadCategories);
        $('#btnSave').addEventListener('click', saveCategory);

        $('#categoryForm').addEventListener('submit', (e) => {
            e.preventDefault();
            saveCategory();
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
        await Promise.all([loadMe(), loadCategories()]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
