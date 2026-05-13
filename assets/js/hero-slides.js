(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        slides: [],
        editing: null,
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

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) {}
    }

    async function loadSlides() {
        const body = $('#slideRows');
        body.innerHTML = '<tr><td colspan="6" class="muted">Loading...</td></tr>';

        try {
            const slides = await Api.get('/admin/hero-slides');
            state.slides = Array.isArray(slides) ? slides : (slides.data || []);
            $('#slideCount').textContent = state.slides.length + ' slide(s)';
            renderSlides();
        } catch (e) {
            body.innerHTML = `<tr><td colspan="6" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function renderSlides() {
        const body = $('#slideRows');
        if (!state.slides.length) {
            body.innerHTML = '<tr><td colspan="6" class="muted">No hero slides yet. Click "New Slide" to create one.</td></tr>';
            return;
        }

        body.innerHTML = state.slides.map(s => {
            const img = s.image_url
                ? `<img src="${esc(s.image_url)}" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">`
                : `<div style="width:80px;height:50px;background:#f5f5f5;border-radius:4px;display:grid;place-items:center;color:#999;font-size:12px;">No img</div>`;

            const statusBadge = s.is_active
                ? '<span class="badge badge--active">Active</span>'
                : '<span class="badge badge--inactive">Inactive</span>';

            return `
                <tr data-id="${s.id}">
                    <td>${img}</td>
                    <td>
                        <div style="font-weight:700;">${esc(s.title)}</div>
                        <div style="font-size:12px;color:var(--text-muted);">${esc(s.subtitle || '—')}</div>
                    </td>
                    <td style="font-size:12px;">${esc(s.link_url || '/views/customer/search.php')}</td>
                    <td>${s.sort_order}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn--ghost btn--sm" data-action="edit" data-id="${s.id}">Edit</button>
                            <button class="btn btn--danger btn--sm" data-action="delete" data-id="${s.id}">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                if (btn.dataset.action === 'edit') openEdit(id);
                if (btn.dataset.action === 'delete') deleteSlide(id);
            });
        });
    }

    function openModal(title) {
        const modal = $('#slideModal');
        $('#modalTitle').textContent = title;
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#modalError').hidden = true;
    }

    function closeModal() {
        const modal = $('#slideModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
        state.editing = null;
    }

    function resetForm() {
        $('#fId').value = '';
        $('#fTitle').value = '';
        $('#fSubtitle').value = '';
        $('#fDescription').value = '';
        $('#fImageUrl').value = '';
        $('#fLinkUrl').value = '/views/customer/search.php';
        $('#fCtaText').value = 'Shop now';
        $('#fSortOrder').value = '0';
        $('#fStatus').value = '1';
        $('#imgPreview').style.display = 'none';
        $('#imgPreview').style.backgroundImage = '';
    }

    function openCreate() {
        resetForm();
        openModal('New Hero Slide');
    }

    async function openEdit(id) {
        try {
            const s = state.slides.find(x => x.id === id);
            if (!s) {
                toast('Slide not found', true);
                return;
            }
            resetForm();
            state.editing = s.id;
            $('#fId').value = s.id;
            $('#fTitle').value = s.title;
            $('#fSubtitle').value = s.subtitle || '';
            $('#fDescription').value = s.description || '';
            $('#fImageUrl').value = s.image_url;
            $('#fLinkUrl').value = s.link_url || '/views/customer/search.php';
            $('#fCtaText').value = s.cta_text || 'Shop now';
            $('#fSortOrder').value = s.sort_order;
            $('#fStatus').value = s.is_active ? '1' : '0';

            if (s.image_url) {
                $('#imgPreview').style.backgroundImage = `url('${s.image_url}')`;
                $('#imgPreview').style.display = 'block';
            }

            openModal('Edit Hero Slide');
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    async function saveSlide() {
        const errBox = $('#modalError');
        errBox.hidden = true;
        const saveBtn = $('#btnSave');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            const payload = {
                title: $('#fTitle').value.trim(),
                subtitle: $('#fSubtitle').value.trim() || null,
                description: $('#fDescription').value.trim() || null,
                image_url: $('#fImageUrl').value.trim(),
                link_url: $('#fLinkUrl').value.trim() || '/views/customer/search.php',
                cta_text: $('#fCtaText').value.trim() || 'Shop now',
                sort_order: parseInt($('#fSortOrder').value, 10) || 0,
                is_active: $('#fStatus').value === '1',
            };

            if (state.editing) {
                await Api.put('/admin/hero-slides/' + state.editing, payload);
                toast('Slide updated');
            } else {
                await Api.post('/admin/hero-slides', payload);
                toast('Slide created');
            }

            closeModal();
            loadSlides();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    }

    async function deleteSlide(id) {
        const s = state.slides.find(x => x.id === id);
        if (!s) return;
        if (!confirm(`Delete slide "${s.title}"? This cannot be undone.`)) return;

        try {
            await Api.del('/admin/hero-slides/' + id);
            toast('Slide deleted');
            loadSlides();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function bindUi() {
        $('#btnNewSlide').addEventListener('click', openCreate);
        $('#btnRefresh').addEventListener('click', loadSlides);
        $('#btnSave').addEventListener('click', saveSlide);

        document.querySelectorAll('[data-close]').forEach(el =>
            el.addEventListener('click', closeModal));

        $('#slideModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget || e.target.classList.contains('modal__backdrop')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !$('#slideModal').hidden) {
                closeModal();
            }
        });

        $('#fImageUrl').addEventListener('input', (e) => {
            const url = e.target.value.trim();
            if (url) {
                $('#imgPreview').style.backgroundImage = `url('${url}')`;
                $('#imgPreview').style.display = 'block';
            } else {
                $('#imgPreview').style.display = 'none';
            }
        });

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        bindUi();
        closeModal();
        await Promise.all([loadMe(), loadSlides()]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
