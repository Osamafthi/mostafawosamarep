(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        page:    1,
        limit:   20,
        q:       '',
        cat:     '',
        status:  '',
        categories: [],
        editing: null,
        imageUrl: null,
    };

    const $ = (s) => document.querySelector(s);

    // Pagination is built on the shared base class — swap to Pagination,
    // CompactPagination, or a custom subclass without touching the rest of
    // the page. Instantiated inside init() so the DOM is ready.
    let pager;

    /* Toast helper */
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

    /* Load admin info */
    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) { /* unauthorized already handled */ }
    }

    /* Load stats */
    async function loadStats() {
        try {
            const s = await Api.get('/admin/stats');
            $('#statTotal').textContent  = s.products.total_products;
            $('#statActive').textContent = s.products.active_products;
            $('#statCats').textContent   = s.products.total_categories;
            $('#statLow').textContent    = s.products.low_stock_products;
        } catch (e) { /* ignore */ }
    }

    /* Load categories */
    async function loadCategories() {
        const cats = await Api.get('/admin/categories');
        state.categories = cats;

        const filter = $('#filterCategory');
        filter.innerHTML = '<option value="">All categories</option>' +
            cats.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');

        const formSel = $('#fCategory');
        formSel.innerHTML = cats.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
    }

    /* Products list */
    async function loadProducts() {
        const body = $('#productRows');
        body.innerHTML = '<tr><td colspan="7" class="muted">Loading…</td></tr>';

        try {
            const data = await Api.get('/admin/products', {
                query: {
                    q:           state.q,
                    category_id: state.cat,
                    status:      state.status,
                    page:        state.page,
                    limit:       state.limit,
                }
            });
            renderProducts(data);
            pager.render(data);
        } catch (e) {
            body.innerHTML = `<tr><td colspan="7" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function renderProducts(data) {
        const body = $('#productRows');
        if (!data.items.length) {
            body.innerHTML = '<tr><td colspan="7" class="muted">No products found</td></tr>';
            return;
        }
        body.innerHTML = data.items.map(p => {
            const img = p.image_url
                ? `<img class="thumb" src="${esc(p.image_url)}" alt="">`
                : `<div class="thumb thumb--placeholder">N/A</div>`;
            const price = p.discount_price
                ? `<span class="price">${fmtPrice(p.discount_price)}</span><span class="price--original">${fmtPrice(p.price)}</span>`
                : `<span class="price">${fmtPrice(p.price)}</span>`;
            const stockBadge = p.stock <= 5 ? ` <span class="badge badge--low">Low</span>` : '';
            const statusBadge = p.status === 'active'
                ? '<span class="badge badge--active">Active</span>'
                : '<span class="badge badge--inactive">Inactive</span>';
            return `
                <tr data-id="${p.id}">
                    <td>${img}</td>
                    <td>
                        <div style="font-weight:700;">${esc(p.name)}</div>
                        <div style="font-size:12px;color:var(--text-muted);">#${p.id} • ${esc(p.slug || '')}</div>
                    </td>
                    <td>${esc(p.category_name || '—')}</td>
                    <td style="text-align:right;">${price}</td>
                    <td style="text-align:right;">${p.stock}${stockBadge}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="row-actions">
                            <button class="btn btn--ghost btn--sm" data-action="edit" data-id="${p.id}">Edit</button>
                            <button class="btn btn--danger btn--sm" data-action="delete" data-id="${p.id}">Delete</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                if (btn.dataset.action === 'edit')   openEdit(id);
                if (btn.dataset.action === 'delete') deleteProduct(id);
            });
        });
    }

    /* Modal */
    function openModal(title) {
        const modal = $('#productModal');
        $('#modalTitle').textContent = title;
        modal.hidden = false;
        modal.classList.add('is-open');
        $('#modalError').hidden = true;
    }

    function closeModal() {
        const modal = $('#productModal');
        modal.classList.remove('is-open');
        modal.hidden = true;
        state.editing = null;
        state.imageUrl = null;
    }

    function resetForm() {
        $('#fId').value          = '';
        $('#fName').value        = '';
        $('#fDescription').value = '';
        $('#fPrice').value       = '';
        $('#fDiscount').value    = '';
        $('#fStock').value       = '';
        $('#fStatus').value      = 'active';
        $('#fImage').value       = '';
        $('#imgPreview').style.backgroundImage = '';
        state.imageUrl = null;
    }

    function openCreate() {
        resetForm();
        if (state.categories[0]) $('#fCategory').value = state.categories[0].id;
        openModal('New Product');
    }

    async function openEdit(id) {
        try {
            const p = await Api.get('/admin/products/' + id);
            resetForm();
            state.editing = p.id;
            $('#fId').value          = p.id;
            $('#fName').value        = p.name;
            $('#fDescription').value = p.description || '';
            $('#fPrice').value       = p.price;
            $('#fDiscount').value    = p.discount_price ?? '';
            $('#fStock').value       = p.stock;
            $('#fCategory').value    = p.category_id;
            $('#fStatus').value      = p.status;
            state.imageUrl = p.image_url || null;
            if (p.image_url) {
                $('#imgPreview').style.backgroundImage = `url('${p.image_url}')`;
            }
            openModal('Edit Product');
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    async function saveProduct() {
        const errBox = $('#modalError');
        errBox.hidden = true;
        const saveBtn = $('#btnSave');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';

        try {
            const file = $('#fImage').files[0];
            if (file) {
                const uploaded = await Api.upload('/admin/upload', file);
                state.imageUrl = uploaded.url;
            }

            const payload = {
                name:           $('#fName').value.trim(),
                description:    $('#fDescription').value.trim() || null,
                price:          parseFloat($('#fPrice').value),
                discount_price: $('#fDiscount').value === '' ? null : parseFloat($('#fDiscount').value),
                stock:          parseInt($('#fStock').value, 10),
                category_id:    parseInt($('#fCategory').value, 10),
                status:         $('#fStatus').value,
                image_url:      state.imageUrl,
            };

            if (state.editing) {
                await Api.put('/admin/products/' + state.editing, payload);
                toast('Product updated');
            } else {
                await Api.post('/admin/products', payload);
                toast('Product created');
            }

            closeModal();
            loadProducts();
            loadStats();
        } catch (e) {
            errBox.textContent = Auth.formatError(e);
            errBox.hidden = false;
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    }

    async function deleteProduct(id) {
        if (!confirm('Delete this product? This cannot be undone.')) return;
        try {
            await Api.del('/admin/products/' + id);
            toast('Product deleted');
            loadProducts();
            loadStats();
        } catch (e) {
            toast(Auth.formatError(e), true);
        }
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function bindUi() {
        const productModal = $('#productModal');
        const modalDialog  = productModal.querySelector('.modal__dialog');

        $('#btnNewProduct').addEventListener('click', openCreate);
        $('#btnRefresh').addEventListener('click', () => { loadProducts(); loadStats(); });
        $('#btnSave').addEventListener('click', saveProduct);

        document.querySelectorAll('[data-close]').forEach(el =>
            el.addEventListener('click', closeModal));

        modalDialog.addEventListener('click', (e) => e.stopPropagation());
        productModal.addEventListener('click', (e) => {
            if (e.target === productModal || e.target.classList.contains('modal__backdrop')) {
                closeModal();
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && productModal.classList.contains('is-open')) {
                closeModal();
            }
        });

        $('#searchInput').addEventListener('input', debounce((e) => {
            state.q = e.target.value.trim();
            state.page = 1;
            loadProducts();
        }, 300));

        $('#filterCategory').addEventListener('change', (e) => {
            state.cat = e.target.value;
            state.page = 1;
            loadProducts();
        });

        $('#filterStatus').addEventListener('change', (e) => {
            state.status = e.target.value;
            state.page = 1;
            loadProducts();
        });

        $('#fImage').addEventListener('change', (e) => {
            const f = e.target.files[0];
            if (!f) return;
            const reader = new FileReader();
            reader.onload = () => { $('#imgPreview').style.backgroundImage = `url('${reader.result}')`; };
            reader.readAsDataURL(f);
        });

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        // 200+ products benefits from the ellipsis-style pager.
        const PagerClass = window.CompactPagination || window.Pagination;
        pager = new PagerClass({
            root: '#pagination',
            siblings: 2,
            onPageChange: (page) => {
                state.page = page;
                loadProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        });

        bindUi();
        closeModal();
        await Promise.all([loadMe(), loadCategories(), loadStats()]);
        await loadProducts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
