(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        offers: [],
        replacingOfferId: null,
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

    function fmtPrice(v) {
        return '$' + Number(v || 0).toFixed(2);
    }

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) {}
    }

    async function loadOffers() {
        const body = $('#offerRows');
        body.innerHTML = '<tr><td colspan="6" class="muted">Loading...</td></tr>';

        try {
            const response = await Api.get('/admin/featured-offers');
            state.offers = Array.isArray(response) ? response : (response.data || []);
            $('#offerCount').textContent = state.offers.length + ' featured product(s)';
            renderOffers();
        } catch (e) {
            body.innerHTML = `<tr><td colspan="6" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function renderOffers() {
        const body = $('#offerRows');
        if (!state.offers.length) {
            body.innerHTML = '<tr><td colspan="6" class="muted">No featured offers yet. Click "Add Featured Product" to create one.</td></tr>';
            return;
        }

        body.innerHTML = state.offers.map(o => {
            const p = o.product;
            if (!p) {
                return `<tr><td colspan="6" class="muted">Product not found (ID: ${o.product_id})</td></tr>`;
            }

            const img = p.image_url
                ? `<img src="${esc(p.image_url)}" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">`
                : `<div style="width:60px;height:60px;background:#f5f5f5;border-radius:4px;display:grid;place-items:center;color:#999;font-size:12px;">N/A</div>`;

            const price = p.discount_price
                ? `<span style="color:#c46a08;font-weight:700;">${fmtPrice(p.discount_price)}</span> <del style="color:#999;font-size:12px;">${fmtPrice(p.price)}</del>`
                : fmtPrice(p.price);

            const statusBadge = o.is_active
                ? '<span class="badge badge--active">Active</span>'
                : '<span class="badge badge--inactive">Inactive</span>';

            return `
                <tr data-id="${o.id}">
                    <td>${img}</td>
                    <td>
                        <div style="font-weight:700;">${esc(p.name)}</div>
                        <div style="font-size:12px;color:var(--text-muted);">Product ID: ${p.id} • ${esc(p.category?.name || '—')}</div>
                    </td>
                    <td>${price}</td>
                    <td>${o.sort_order}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn--ghost btn--sm" data-action="edit" data-id="${o.id}">Edit</button>
                            <button class="btn btn--primary btn--sm" data-action="replace" data-id="${o.id}">Replace</button>
                            <button class="btn btn--danger btn--sm" data-action="delete" data-id="${o.id}">Remove</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                if (btn.dataset.action === 'edit') openEdit(id);
                if (btn.dataset.action === 'replace') openReplaceModal(id);
                if (btn.dataset.action === 'delete') deleteOffer(id);
            });
        });
    }

    async function previewProduct() {
        const productId = $('#fProductId').value.trim();
        if (!productId) {
            $('#productPreview').style.display = 'none';
            return;
        }

        try {
            const p = await Api.get('/admin/products/' + productId);
            $('#previewName').textContent = p.name;
            $('#previewPrice').textContent = p.discount_price
                ? `$${p.discount_price} (was $${p.price})`
                : `$${p.price}`;

            if (p.image_url) {
                $('#previewImg').src = p.image_url;
                $('#previewImg').style.display = 'block';
            } else {
                $('#previewImg').style.display = 'none';
            }
            $('#productPreview').style.display = 'block';
        } catch (e) {
            $('#productPreview').style.display = 'none';
        }
    }

    function openModal() {
        const modal = $('#offerModal');
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#modalError').hidden = true;
        $('#productPreview').style.display = 'none';
    }

    function closeModal() {
        const modal = $('#offerModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
    }

    function resetForm() {
        $('#fProductId').value = '';
        $('#fSortOrder').value = '0';
        $('#fStatus').value = '1';
        $('#productPreview').style.display = 'none';
    }

    async function saveOffer() {
        const errBox = $('#modalError');
        errBox.hidden = true;
        const saveBtn = $('#btnSave');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Adding...';

        try {
            const payload = {
                product_id: parseInt($('#fProductId').value, 10),
                sort_order: parseInt($('#fSortOrder').value, 10) || 0,
                is_active: $('#fStatus').value === '1',
            };

            await Api.post('/admin/featured-offers', payload);
            toast('Product added to featured offers');
            closeModal();
            loadOffers();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Add to Featured';
        }
    }

    async function openEdit(id) {
        const o = state.offers.find(x => x.id === id);
        if (!o) return;

        // Simple inline edit for sort_order and status
        const newOrder = prompt('Enter new sort order (0-99):', o.sort_order);
        if (newOrder === null) return;

        const newStatus = confirm('Should this offer be active?\nOK = Active, Cancel = Inactive');

        try {
            await Api.put('/admin/featured-offers/' + id, {
                sort_order: parseInt(newOrder, 10) || 0,
                is_active: newStatus,
            });
            toast('Offer updated');
            loadOffers();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function openReplaceModal(id) {
        state.replacingOfferId = id;
        const modal = $('#replaceModal');
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#replaceError').hidden = true;
        $('#fNewProductId').value = '';
        $('#fNewProductId').focus();
    }

    function closeReplaceModal() {
        const modal = $('#replaceModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
        state.replacingOfferId = null;
    }

    async function confirmReplace() {
        if (!state.replacingOfferId) return;

        const newProductId = $('#fNewProductId').value.trim();
        if (!newProductId) {
            $('#replaceError').textContent = 'Please enter a product ID';
            $('#replaceError').hidden = false;
            return;
        }

        try {
            await Api.post('/admin/featured-offers/' + state.replacingOfferId + '/replace', {
                new_product_id: parseInt(newProductId, 10),
            });
            toast('Product replaced successfully');
            closeReplaceModal();
            loadOffers();
        } catch (e) {
            $('#replaceError').textContent = Auth.formatError(e);
            $('#replaceError').hidden = false;
        }
    }

    async function deleteOffer(id) {
        const o = state.offers.find(x => x.id === id);
        const name = o?.product?.name || 'this offer';
        if (!confirm(`Remove "${name}" from featured offers?`)) return;

        try {
            await Api.del('/admin/featured-offers/' + id);
            toast('Offer removed');
            loadOffers();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function bindUi() {
        $('#btnNewOffer').addEventListener('click', () => {
            resetForm();
            openModal();
        });
        $('#btnRefresh').addEventListener('click', loadOffers);
        $('#btnSave').addEventListener('click', saveOffer);
        $('#btnConfirmReplace').addEventListener('click', confirmReplace);

        document.querySelectorAll('[data-close]').forEach(el =>
            el.addEventListener('click', () => {
                closeModal();
                closeReplaceModal();
            }));

        $('#fProductId').addEventListener('blur', previewProduct);
        $('#fProductId').addEventListener('input', () => {
            // Debounced preview
            clearTimeout(state.previewTimer);
            state.previewTimer = setTimeout(previewProduct, 500);
        });

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        bindUi();
        closeModal();
        closeReplaceModal();
        await Promise.all([loadMe(), loadOffers()]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
