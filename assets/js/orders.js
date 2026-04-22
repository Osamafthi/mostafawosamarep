(function () {
    'use strict';

    if (!Auth.guardAdminPage()) return;

    const state = {
        page: 1, limit: 20,
        q: '', status: '', paymentStatus: '',
        openId: null,
    };

    const $ = (s) => document.querySelector(s);

    // Shared pagination component — instantiated inside init() so the DOM
    // is ready. Swap PagerClass to CompactPagination for ellipsis-style nav.
    let pager;

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

    function fmtMoney(v) { return '$' + Number(v || 0).toFixed(2); }
    function fmtDate(s)  {
        if (!s) return '';
        const d = new Date(s.replace(' ', 'T'));
        return d.toLocaleString();
    }

    async function loadMe() {
        try {
            const me = await Api.get('/admin/me');
            $('#adminName').textContent   = me.name || 'Admin';
            $('#adminAvatar').textContent = (me.name || 'A').slice(0, 1).toUpperCase();
        } catch (_) {}
    }

    async function loadStats() {
        try {
            const s = await Api.get('/admin/orders/stats');
            $('#statTotal').textContent      = s.total_orders;
            $('#statPending').textContent    = s.pending_orders;
            $('#statProcessing').textContent = s.processing_orders;
            $('#statDelivered').textContent  = s.delivered_orders;
            $('#statRevenue').textContent    = fmtMoney(s.revenue);
        } catch (_) {}
    }

    async function loadOrders() {
        const body = $('#orderRows');
        body.innerHTML = '<tr><td colspan="7" class="muted">Loading…</td></tr>';
        try {
            const data = await Api.get('/admin/orders', {
                query: {
                    q:              state.q,
                    status:         state.status,
                    payment_status: state.paymentStatus,
                    page:           state.page,
                    limit:          state.limit,
                }
            });
            renderOrders(data);
            pager.render(data);
        } catch (e) {
            body.innerHTML = `<tr><td colspan="7" class="muted">${esc(Auth.formatError(e))}</td></tr>`;
        }
    }

    function renderOrders(data) {
        const body = $('#orderRows');
        if (!data.items.length) {
            body.innerHTML = '<tr><td colspan="7" class="muted">No orders found</td></tr>';
            return;
        }
        body.innerHTML = data.items.map(o => `
            <tr data-id="${o.id}">
                <td><strong>${esc(o.order_number)}</strong></td>
                <td>
                    <div style="font-weight:600;">${esc(o.customer_name)}</div>
                    <div style="font-size:12px; color:var(--text-muted);">${esc(o.customer_email)}</div>
                </td>
                <td style="text-align:right; font-weight:700;">${fmtMoney(o.total)}</td>
                <td>${statusBadge(o.status)}</td>
                <td>${paymentBadge(o.payment_status)}</td>
                <td style="font-size:12px; color:var(--text-muted);">${esc(fmtDate(o.created_at))}</td>
                <td>
                    <div class="row-actions">
                        <button class="btn btn--ghost btn--sm" data-action="view" data-id="${o.id}">View</button>
                    </div>
                </td>
            </tr>
        `).join('');

        body.querySelectorAll('[data-action="view"]').forEach(btn =>
            btn.addEventListener('click', () => openDrawer(Number(btn.dataset.id))));
    }

    function statusBadge(s) {
        return `<span class="badge badge--${esc(s)}">${esc(capitalize(s))}</span>`;
    }
    function paymentBadge(s) {
        return `<span class="badge badge--${esc(s)}">${esc(capitalize(s))}</span>`;
    }
    function capitalize(s) { return String(s || '').charAt(0).toUpperCase() + String(s || '').slice(1); }

    async function openDrawer(id) {
        state.openId = id;
        $('#drawerError').hidden = true;
        $('#orderDrawer').hidden = false;
        try {
            const o = await Api.get('/admin/orders/' + id);
            $('#dOrderNumber').textContent = o.order_number;
            $('#dTitle').textContent       = 'Placed ' + fmtDate(o.created_at);
            $('#dName').textContent    = o.customer_name;
            $('#dEmail').textContent   = o.customer_email;
            $('#dPhone').textContent   = o.customer_phone || '—';
            $('#dAddress').textContent = o.shipping_address;
            $('#dStatus').value        = o.status;
            $('#dPayment').value       = o.payment_status;
            $('#dTotal').textContent   = fmtMoney(o.total);

            $('#dItems').innerHTML = (o.items || []).map(it => `
                <tr>
                    <td>${esc(it.product_name)}</td>
                    <td style="text-align:right;">${it.quantity}</td>
                    <td style="text-align:right;">${fmtMoney(it.unit_price)}</td>
                    <td style="text-align:right;">${fmtMoney(it.subtotal)}</td>
                </tr>
            `).join('') || '<tr><td colspan="4" class="muted">No items</td></tr>';
        } catch (e) {
            toast(Auth.formatError(e), true);
            closeDrawer();
        }
    }

    function closeDrawer() {
        $('#orderDrawer').hidden = true;
        state.openId = null;
    }

    async function saveOrder() {
        if (!state.openId) return;
        const btn = $('#btnUpdateOrder');
        const err = $('#drawerError');
        err.hidden = true;
        btn.disabled = true;
        btn.textContent = 'Saving…';
        try {
            await Api.patch('/admin/orders/' + state.openId + '/status', {
                status: $('#dStatus').value,
            });
            await Api.patch('/admin/orders/' + state.openId + '/payment-status', {
                payment_status: $('#dPayment').value,
            });
            toast('Order updated');
            closeDrawer();
            loadOrders();
            loadStats();
        } catch (e) {
            err.textContent = Auth.formatError(e);
            err.hidden = false;
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save changes';
        }
    }

    function debounce(fn, ms) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function bindUi() {
        $('#btnRefresh').addEventListener('click', () => { loadOrders(); loadStats(); });
        $('#btnUpdateOrder').addEventListener('click', saveOrder);
        document.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeDrawer));

        $('#statusTabs').addEventListener('click', (e) => {
            const tab = e.target.closest('.tab');
            if (!tab) return;
            $('#statusTabs .tab.is-active').classList.remove('is-active');
            tab.classList.add('is-active');
            state.status = tab.dataset.status || '';
            state.page = 1;
            loadOrders();
        });

        $('#searchInput').addEventListener('input', debounce((e) => {
            state.q = e.target.value.trim();
            state.page = 1;
            loadOrders();
        }, 300));

        $('#filterPayment').addEventListener('change', (e) => {
            state.paymentStatus = e.target.value;
            state.page = 1;
            loadOrders();
        });

        Auth.bindLogout('#logoutBtn');
    }

    async function init() {
        const PagerClass = window.Pagination;
        pager = new PagerClass({
            root: '#pagination',
            siblings: 2,
            onPageChange: (page) => {
                state.page = page;
                loadOrders();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
        });

        bindUi();
        await Promise.all([loadMe(), loadStats()]);
        await loadOrders();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
