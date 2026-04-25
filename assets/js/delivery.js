/**
 * delivery.js — courier dashboard (orders list + status updates).
 *
 * Boot sequence:
 *   1. Auth.guardDeliveryPage() bails out (clears token + redirects)
 *      if no `deliveryToken` is present.
 *   2. We then call `/delivery/me` and bail out the same way on
 *      401/403 — defense in depth, so a tampered token (e.g. an admin
 *      token pasted into localStorage.deliveryToken) gets kicked out
 *      before any UI renders.
 *   3. Only then do we render orders.
 */
(function () {
    'use strict';

    if (!window.Auth || !Auth.guardDeliveryPage()) return;
    const Api = window.DeliveryApi;
    if (!Api) return;

    const $ = (s, ctx) => (ctx || document).querySelector(s);

    const state = {
        filter: 'active',
        orders: [],
        openId: null,
        me: null,
    };

    let toastTimer;
    function toast(msg, isError = false) {
        const el = $('#toast');
        if (!el) return;
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
    function fmtDate(s) {
        if (!s) return '';
        const d = new Date(String(s).replace(' ', 'T'));
        return d.toLocaleString();
    }
    function statusLabel(s) {
        if (s === 'shipped') return 'Out for delivery';
        return (s || '').replace(/^./, (c) => c.toUpperCase());
    }

    async function loadMe() {
        try {
            const me = await Api.get('/delivery/me');
            state.me = me;
            const nameEl = $('[data-courier-name]');
            const emailEl = $('[data-courier-email]');
            if (nameEl) nameEl.textContent = me.name || 'Courier';
            if (emailEl) emailEl.textContent = me.email || '';
            return me;
        } catch (_) {
            // The API client redirects to login on 401/403.
            return null;
        }
    }

    async function loadOrders() {
        const list = $('[data-orders-cards]');
        const loading = $('[data-loading]');
        const empty = $('[data-empty]');
        if (!list) return;
        if (loading) loading.hidden = false;
        if (empty)   empty.hidden = true;
        list.innerHTML = '';

        try {
            const data = await Api.get('/delivery/orders', { query: { filter: state.filter, limit: 50 } });
            state.orders = (data && data.items) || [];
            renderOrders(state.orders);
        } catch (e) {
            if (loading) loading.hidden = true;
            list.innerHTML = `<div class="delivery-error">${esc(Api.formatError(e))}</div>`;
        } finally {
            if (loading) loading.hidden = true;
        }
    }

    async function refreshCounts() {
        try {
            const [active, done] = await Promise.all([
                Api.get('/delivery/orders', { query: { filter: 'active', limit: 1 } }),
                Api.get('/delivery/orders', { query: { filter: 'delivered', limit: 1 } }),
            ]);
            const a = $('[data-count-active]');
            const d = $('[data-count-delivered]');
            if (a) a.textContent = String(active.total ?? '');
            if (d) d.textContent = String(done.total ?? '');
        } catch (_) { /* ignore — counts are decoration */ }
    }

    function renderOrders(orders) {
        const list  = $('[data-orders-cards]');
        const empty = $('[data-empty]');
        if (!list) return;

        if (!orders.length) {
            if (empty) empty.hidden = false;
            list.innerHTML = '';
            return;
        }
        if (empty) empty.hidden = true;

        list.innerHTML = orders.map(cardMarkup).join('');

        list.querySelectorAll('[data-card]').forEach(el => {
            el.addEventListener('click', () => openSheet(Number(el.dataset.card)));
        });

        // Tel + maps clicks shouldn't bubble up to the card.
        list.querySelectorAll('[data-stop]').forEach(el => {
            el.addEventListener('click', (e) => e.stopPropagation());
        });
    }

    function cardMarkup(o) {
        const items = Array.isArray(o.items) ? o.items : [];
        const itemCount = items.reduce((n, i) => n + (Number(i.quantity) || 0), 0);
        const phone = o.customer_phone ? esc(o.customer_phone) : '';
        const phoneLink = phone
            ? `<a class="delivery-card__phone" href="tel:${phone}" data-stop>📞 ${phone}</a>`
            : '<span class="delivery-card__phone delivery-card__phone--missing">No phone</span>';
        const mapsHref = o.customer_location && o.customer_location.maps_url
            ? esc(o.customer_location.maps_url)
            : '';
        const mapsBtn = mapsHref
            ? `<a class="delivery-card__map" href="${mapsHref}" target="_blank" rel="noopener" data-stop>Open in Maps</a>`
            : '';
        const gpsBadge = o.customer_location && o.customer_location.source === 'gps'
            ? '<span class="delivery-pill delivery-pill--gps">GPS shared</span>'
            : '';

        return `
            <article class="delivery-card" data-card="${o.id}" tabindex="0">
                <div class="delivery-card__top">
                    <div>
                        <div class="delivery-card__order">${esc(o.order_number || '#' + o.id)}</div>
                        <div class="delivery-card__date">${esc(fmtDate(o.created_at))}</div>
                    </div>
                    <div class="delivery-card__status">
                        <span class="delivery-pill delivery-pill--${esc(o.status)}">${esc(statusLabel(o.status))}</span>
                        ${gpsBadge}
                    </div>
                </div>
                <div class="delivery-card__customer">
                    <div class="delivery-card__name">${esc(o.customer_name || '')}</div>
                    <div class="delivery-card__row">${phoneLink}</div>
                </div>
                <div class="delivery-card__address">${esc(o.shipping_address || '')}</div>
                <div class="delivery-card__bottom">
                    <div class="delivery-card__meta">
                        <span>${itemCount} item${itemCount === 1 ? '' : 's'}</span>
                        <span class="delivery-card__total">${fmtMoney(o.total)}</span>
                    </div>
                    ${mapsBtn}
                </div>
            </article>
        `;
    }

    async function openSheet(id) {
        const sheet = $('#orderSheet');
        const body  = $('[data-sheet-body]', sheet);
        const footer = $('[data-sheet-footer]', sheet);
        const number = $('[data-sheet-number]', sheet);
        if (!sheet) return;

        state.openId = id;
        sheet.hidden = false;
        sheet.setAttribute('aria-hidden', 'false');
        sheet.classList.add('is-open');
        document.body.classList.add('no-scroll');
        body.innerHTML = '<div class="delivery-loading">Loading…</div>';
        footer.innerHTML = '';

        try {
            const order = await Api.get('/delivery/orders/' + id);
            number.textContent = order.order_number || '#' + order.id;
            $('#sheetTitle').textContent = 'Placed ' + fmtDate(order.created_at);
            renderSheet(order, body, footer);
        } catch (e) {
            body.innerHTML = `<div class="delivery-error">${esc(Api.formatError(e))}</div>`;
        }
    }

    function closeSheet() {
        const sheet = $('#orderSheet');
        if (!sheet) return;
        sheet.classList.remove('is-open');
        sheet.hidden = true;
        sheet.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('no-scroll');
        state.openId = null;
    }

    function renderSheet(o, body, footer) {
        const items = (o.items || []).map(it => `
            <li class="delivery-item">
                <span class="delivery-item__qty">${Number(it.quantity)}×</span>
                <span class="delivery-item__name">${esc(it.product_name)}</span>
                <span class="delivery-item__sub">${fmtMoney(it.subtotal)}</span>
            </li>
        `).join('') || '<li class="delivery-item delivery-item--empty">No items</li>';

        const phone = o.customer_phone ? esc(o.customer_phone) : '';
        const phoneRow = phone
            ? `<a class="delivery-link delivery-link--phone" href="tel:${phone}">📞 Call ${phone}</a>`
            : '<div class="delivery-row__missing">No phone provided</div>';

        const mapsHref = o.customer_location && o.customer_location.maps_url
            ? esc(o.customer_location.maps_url) : '';
        const mapsRow = mapsHref
            ? `<a class="delivery-link delivery-link--map" href="${mapsHref}" target="_blank" rel="noopener">🗺️ Open in Maps</a>`
            : '<div class="delivery-row__missing">No location available</div>';
        const sourceLine = o.customer_location && o.customer_location.source === 'gps'
            ? '<div class="delivery-row__hint">Customer shared GPS coordinates at checkout.</div>'
            : (o.customer_location ? '<div class="delivery-row__hint">Map opens for the typed shipping address.</div>' : '');

        body.innerHTML = `
            <section class="delivery-row">
                <h3>Status</h3>
                <span class="delivery-pill delivery-pill--${esc(o.status)} delivery-pill--lg">${esc(statusLabel(o.status))}</span>
            </section>

            <section class="delivery-row">
                <h3>Customer</h3>
                <div class="delivery-row__name">${esc(o.customer_name || '')}</div>
                ${phoneRow}
                <div class="delivery-row__address">${esc(o.shipping_address || '')}</div>
                ${mapsRow}
                ${sourceLine}
            </section>

            <section class="delivery-row">
                <h3>Items (${(o.items || []).length})</h3>
                <ul class="delivery-items">${items}</ul>
                <div class="delivery-row__total"><span>Total</span><strong>${fmtMoney(o.total)}</strong></div>
            </section>
        `;

        footer.innerHTML = actionButtonsMarkup(o.status);

        footer.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', () => {
                changeStatus(o.id, btn.dataset.action);
            });
        });
    }

    /**
     * Returns the buttons available to the courier given the order's
     * current status. Mirrors the server-side state machine in
     * OrderStatusTransitionService::DELIVERY_TRANSITIONS — the server is
     * the source of truth, this is just UX.
     */
    function actionButtonsMarkup(status) {
        if (status === 'pending' || status === 'processing') {
            return `<button type="button" class="btn btn--primary btn--lg btn--block" data-action="shipped">
                Mark picked up
            </button>`;
        }
        if (status === 'shipped') {
            return `
                <button type="button" class="btn btn--primary btn--lg btn--block" data-action="delivered">
                    Mark delivered
                </button>
                <button type="button" class="btn btn--ghost btn--block" data-action="processing">
                    Couldn't deliver — return to store
                </button>
            `;
        }
        if (status === 'delivered') {
            return `<div class="delivery-row__hint" style="text-align:center;">This order has been delivered.</div>`;
        }
        if (status === 'cancelled') {
            return `<div class="delivery-row__hint" style="text-align:center;">This order was cancelled.</div>`;
        }
        return '';
    }

    async function changeStatus(id, newStatus) {
        const footer = $('[data-sheet-footer]');
        const buttons = footer ? footer.querySelectorAll('button') : [];
        buttons.forEach(b => b.disabled = true);
        try {
            const updated = await Api.patch('/delivery/orders/' + id + '/status', { status: newStatus });
            toast('Status updated');
            // Refresh both the open sheet and the underlying list.
            renderSheet(updated, $('[data-sheet-body]'), footer);
            await Promise.all([loadOrders(), refreshCounts()]);
        } catch (e) {
            toast(Api.formatError(e), true);
            buttons.forEach(b => b.disabled = false);
        }
    }

    function bindUi() {
        document.querySelectorAll('[data-tabs] [data-filter]').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('[data-tabs] [data-filter]').forEach(b => {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-selected', 'false');
                });
                btn.classList.add('is-active');
                btn.setAttribute('aria-selected', 'true');
                state.filter = btn.dataset.filter;
                loadOrders();
            });
        });

        document.querySelectorAll('[data-sheet-close]').forEach(el => {
            el.addEventListener('click', closeSheet);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && state.openId) closeSheet();
        });

        Auth.bindLogout('#deliveryLogoutBtn');
    }

    async function init() {
        bindUi();
        const me = await loadMe();
        if (!me) return; // already redirected
        await Promise.all([loadOrders(), refreshCounts()]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
