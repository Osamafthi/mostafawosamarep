/**
 * my-orders.js — customer-facing "My Orders" page.
 *
 * Relies on the existing GET /customer/orders endpoint which now
 * accepts `window` (6m|1y|all) and `status` query params, plus
 * `page` / `limit` for pagination.
 *
 * Redirects to the login page when no customer token is present so
 * the page is effectively guarded. A ?redirect= param makes the
 * login return the user back here after signing in.
 */
(function () {
    'use strict';

    const { esc, money, buildPath, toast } = window.UI;
    const Api = window.CustomerApi;

    // Hard-guard: window.Auth.guardCustomerPage() is added by auth.js
    // and verifies the customer token against /customer/me before any
    // UI is revealed (the body stays visibility:hidden until the
    // server confirms the token actually carries the customer
    // ability). Falls back to the legacy token-presence redirect if
    // auth.js failed to load.
    if (window.Auth && typeof window.Auth.guardCustomerPage === 'function') {
        if (!window.Auth.guardCustomerPage()) return;
    } else if (!Api.isLoggedIn()) {
        const here = location.pathname;
        window.location.replace(
            buildPath('/views/customer/login.php') +
            '?redirect=' + encodeURIComponent(here)
        );
        return;
    }

    const state = {
        page: 1,
        limit: 10,
        status: '',
        window: '6m',
        openId: null,
    };

    const root   = document.querySelector('[data-orders-root]');
    const subEl  = document.querySelector('[data-orders-sub]');
    const tabs   = document.querySelector('[data-orders-tabs]');
    const winEl  = document.querySelector('[data-orders-window]');
    if (!root) return;

    const pager = new window.Pagination({
        root: '#pagination',
        siblings: 1,
        onPageChange: (p) => {
            state.page = p;
            load();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });

    function fmtDate(s) {
        if (!s) return '';
        try {
            return new Date(s).toLocaleString(undefined, {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit',
            });
        } catch (_) { return s; }
    }

    function statusBadge(s) {
        const label = String(s || '').charAt(0).toUpperCase() + String(s || '').slice(1);
        return `<span class="order-badge order-badge--${esc(s)}">${esc(label)}</span>`;
    }

    function renderLoading() {
        root.innerHTML = `<div class="orders-empty">Loading your orders…</div>`;
    }

    function renderError(msg) {
        root.innerHTML = `<div class="orders-empty orders-empty--error">${esc(msg)}</div>`;
    }

    function renderEmpty() {
        const hint = state.status || state.window !== '6m'
            ? 'Try clearing your filters or expanding the time window.'
            : 'When you place an order it will appear here.';
        root.innerHTML = `
            <div class="orders-empty">
                <h3>No orders found</h3>
                <p>${esc(hint)}</p>
                <a class="btn btn--primary" href="${esc(buildPath('/'))}">Browse products</a>
            </div>
        `;
    }

    function renderList(data) {
        subEl.textContent = formatSubtitle(data);
        const items = Array.isArray(data.items) ? data.items : [];
        if (!items.length) { renderEmpty(); return; }

        root.innerHTML = `
            <ul class="orders-list" role="list">
                ${items.map(orderRow).join('')}
            </ul>
        `;

        root.querySelectorAll('[data-toggle-details]').forEach(btn => {
            btn.addEventListener('click', () => toggleDetails(Number(btn.dataset.id)));
        });
    }

    function orderRow(o) {
        const itemCount = Array.isArray(o.items) ? o.items.length : null;
        return `
            <li class="order-card" data-order-id="${o.id}">
                <header class="order-card__head">
                    <div class="order-card__head-main">
                        <div class="order-card__num">${esc(o.order_number || ('#' + o.id))}</div>
                        <div class="order-card__date">${esc(fmtDate(o.created_at))}</div>
                    </div>
                    <div class="order-card__head-meta">
                        ${statusBadge(o.status)}
                        <div class="order-card__total">${money(Number(o.total || 0))}</div>
                    </div>
                </header>
                <div class="order-card__body">
                    <div class="order-card__line">
                        <span class="muted">Payment</span>
                        <span>${statusBadge(o.payment_status)}</span>
                    </div>
                    ${itemCount !== null ? `
                    <div class="order-card__line">
                        <span class="muted">Items</span>
                        <span>${itemCount}</span>
                    </div>` : ''}
                    <div class="order-card__actions">
                        <button type="button" class="btn btn--ghost btn--sm" data-toggle-details data-id="${o.id}">
                            View details
                        </button>
                    </div>
                </div>
                <div class="order-card__details" data-details-for="${o.id}" hidden></div>
            </li>
        `;
    }

    function formatSubtitle(data) {
        const total = Number(data.total) || 0;
        const winLabel = state.window === '6m' ? 'the last 6 months'
                      : state.window === '1y' ? 'the last year'
                      : 'all time';
        const statusLabel = state.status ? `${state.status} ` : '';
        if (total === 0) return `No ${statusLabel}orders in ${winLabel}.`;
        return `Showing ${statusLabel}orders from ${winLabel} — ${total} total.`;
    }

    async function toggleDetails(id) {
        const panel = root.querySelector(`[data-details-for="${id}"]`);
        if (!panel) return;

        if (!panel.hidden) {
            panel.hidden = true;
            return;
        }

        panel.hidden = false;
        panel.innerHTML = `<div class="muted" style="padding: 12px;">Loading…</div>`;

        try {
            const o = await Api.get('/customer/orders/' + id, { auth: true });
            panel.innerHTML = renderDetailMarkup(o);
        } catch (e) {
            panel.innerHTML = `<div class="form-error">${esc(Api.formatError(e))}</div>`;
        }
    }

    function renderDetailMarkup(o) {
        const items = Array.isArray(o.items) ? o.items : [];
        const rows = items.map(it => `
            <tr>
                <td>${esc(it.product_name || 'Product')}</td>
                <td style="text-align:right;">${Number(it.quantity)}</td>
                <td style="text-align:right;">${money(Number(it.unit_price || 0))}</td>
                <td style="text-align:right;">${money(Number(it.subtotal || 0))}</td>
            </tr>
        `).join('');

        return `
            <div class="order-detail">
                <div class="order-detail__grid">
                    <div>
                        <div class="order-detail__label">Shipping to</div>
                        <div class="order-detail__value">${esc(o.customer_name || '')}</div>
                        <div class="order-detail__value muted">${esc(o.customer_email || '')}${o.customer_phone ? (' · ' + esc(o.customer_phone)) : ''}</div>
                        <pre class="order-detail__addr">${esc(o.shipping_address || '')}</pre>
                    </div>
                    <div>
                        <div class="order-detail__label">Summary</div>
                        <div class="order-detail__line"><span>Subtotal</span><span>${money(Number(o.subtotal || 0))}</span></div>
                        <div class="order-detail__line order-detail__line--total"><span>Total</span><span>${money(Number(o.total || 0))}</span></div>
                    </div>
                </div>
                ${items.length ? `
                    <table class="order-detail__items">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="text-align:right;">Qty</th>
                                <th style="text-align:right;">Price</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                ` : ''}
            </div>
        `;
    }

    async function load() {
        renderLoading();
        try {
            const data = await Api.get('/customer/orders', {
                auth: true,
                query: {
                    page:   state.page,
                    limit:  state.limit,
                    status: state.status,
                    window: state.window,
                },
            });
            renderList(data);
            pager.render(data);
        } catch (e) {
            if (e && e.error && /unauth/i.test(e.error)) {
                Api.setToken(null);
                window.location.replace(buildPath('/views/customer/login.php'));
                return;
            }
            renderError(Api.formatError(e));
            pager.render({ page: 1, last_page: 1 });
        }
    }

    function bindUi() {
        tabs.addEventListener('click', (e) => {
            const btn = e.target.closest('.tab');
            if (!btn) return;
            tabs.querySelector('.tab.is-active')?.classList.remove('is-active');
            btn.classList.add('is-active');
            state.status = btn.dataset.status || '';
            state.page = 1;
            load();
        });

        winEl.addEventListener('change', () => {
            state.window = winEl.value || '6m';
            state.page = 1;
            load();
        });
    }

    bindUi();
    load();
})();
