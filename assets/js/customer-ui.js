/**
 * customer-ui.js — shared UI helpers for every customer page.
 *
 *   - `esc()`               : HTML-escape any string before injecting into markup.
 *   - `money()`             : Format a number as "E£ 1,234.00".
 *   - `toast()`             : Floating notification (success / error / info).
 *   - `productCard()`       : Build a consistent product card used by home
 *                             strips, search results and category pages.
 *   - `bindHeader()`        : Wires the account dropdown, search submit,
 *                             menu drawer and "Sign out" link used by the
 *                             site header on every page.
 *   - `buildPath()`         : Prefix an app-relative path with APP_CONFIG.basePath.
 */
(function (global) {
    'use strict';

    const cfg = global.APP_CONFIG || {};
    const basePath = cfg.basePath || '';

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    function money(n, currency = 'E£') {
        const v = Number(n);
        if (!Number.isFinite(v)) return '';
        return currency + ' ' + v.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function buildPath(p) {
        if (!p) return basePath || '/';
        if (/^https?:\/\//i.test(p)) return p;
        return basePath + (p.startsWith('/') ? p : '/' + p);
    }

    let toastTimer;
    function toast(message, kind = 'info') {
        let el = document.querySelector('#toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast';
            el.className = 'toast';
            document.body.appendChild(el);
        }
        el.classList.remove('is-error', 'is-success');
        if (kind === 'error')   el.classList.add('is-error');
        if (kind === 'success') el.classList.add('is-success');
        el.textContent = message;
        el.classList.add('is-show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => el.classList.remove('is-show'), 2600);
    }

    function discountPct(price, discount) {
        const p = Number(price);
        const d = Number(discount);
        if (!Number.isFinite(p) || p <= 0) return 0;
        if (!Number.isFinite(d) || d <= 0 || d >= p) return 0;
        return Math.round(((p - d) / p) * 100);
    }

    function effective(product) {
        const d = Number(product.discount_price);
        if (Number.isFinite(d) && d > 0) return d;
        const p = Number(product.price);
        return Number.isFinite(p) ? p : 0;
    }

    /**
     * Build a product card with:
     *   - Clickable media/name to open product detail.
     *   - Inline "Add to cart" button so users do not need to open product.php.
     * @param {object} p — a ProductResource (see PublicProductController)
     */
    function productCard(p) {
        if (!p) return '';
        const hasDiscount = p.discount_price != null && Number(p.discount_price) > 0 && Number(p.discount_price) < Number(p.price);
        const price = effective(p);
        const pct = hasDiscount ? discountPct(p.price, p.discount_price) : 0;
        const stock = Number(p.stock) || 0;
        const img = p.image_url
            ? `<img src="${esc(p.image_url)}" alt="${esc(p.name)}" loading="lazy" onerror="var par=this.parentNode;this.onerror=null;this.remove();par.classList.add('card__media--placeholder');par.textContent='No image';">`
            : '<span>No image</span>';
        const mediaExtra = p.image_url ? '' : ' card__media--placeholder';
        const detailHref = esc(buildPath('/views/customer/product.php?id=' + encodeURIComponent(p.id)));
        return `
            <article class="card">
                <a class="card__media${mediaExtra}" href="${detailHref}">
                    ${img}
                    ${pct ? `<span class="card__discount">-${pct}%</span>` : ''}
                </a>
                <div class="card__body">
                    <a class="card__name" href="${detailHref}">${esc(p.name)}</a>
                    <div class="card__price-row">
                        <span class="card__price">${money(price)}</span>
                        ${hasDiscount ? `<span class="card__price-old">${money(p.price)}</span>` : ''}
                    </div>
                    <button
                        type="button"
                        class="card__add"
                        data-add-cart
                        data-id="${esc(String(p.id))}"
                        data-name="${esc(p.name || '')}"
                        data-price="${esc(String(Number(p.price) || 0))}"
                        data-discount="${esc(p.discount_price == null ? '' : String(Number(p.discount_price) || 0))}"
                        data-image="${esc(p.image_url || '')}"
                        ${stock <= 0 ? 'disabled' : ''}
                    >
                        ${stock <= 0 ? 'Out of stock' : 'Add to cart'}
                    </button>
                </div>
            </article>
        `;
    }

    /**
     * Build skeleton placeholders for a product grid or strip.
     */
    function skeletonCards(n = 6) {
        let html = '';
        for (let i = 0; i < n; i++) {
            html += '<div class="card"><div class="skeleton skel-card"></div></div>';
        }
        return html;
    }

    function bindHeader() {
        // Search submit
        const form = document.querySelector('[data-search-form]');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const input = form.querySelector('input[name="q"]');
                const q = (input && input.value || '').trim();
                const url = buildPath('/views/customer/search.php') + (q ? ('?q=' + encodeURIComponent(q)) : '');
                window.location.href = url;
            });
        }

        // Account dropdown
        const acc = document.querySelector('[data-account-toggle]');
        const menu = document.querySelector('[data-account-menu]');
        if (acc && menu) {
            acc.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('is-open');
            });
            document.addEventListener('click', (e) => {
                if (!menu.contains(e.target) && e.target !== acc) {
                    menu.classList.remove('is-open');
                }
            });
            // Adjust labels based on login state.
            const signedIn = !!(global.CustomerApi && global.CustomerApi.isLoggedIn());
            menu.querySelectorAll('[data-when-auth="in"]').forEach(el => el.hidden = !signedIn);
            menu.querySelectorAll('[data-when-auth="out"]').forEach(el => el.hidden = signedIn);

            const title = menu.querySelector('[data-account-title]');
            const accountLabel = acc.querySelector('[data-account-label]');
            const accountIcon = acc.querySelector('[data-account-icon]');

            function applyHeaderIdentity(isSignedIn, name) {
                if (title) {
                    if (!isSignedIn) {
                        title.textContent = 'Welcome, guest (please sign in)';
                    } else {
                        title.textContent = name ? ('Welcome, ' + name) : 'Welcome, customer';
                    }
                }

                if (accountLabel) {
                    accountLabel.textContent = isSignedIn
                        ? (name ? ('Welcome, ' + name) : 'Welcome, customer')
                        : 'Account';
                }

                if (accountIcon) {
                    accountIcon.textContent = isSignedIn ? '✓' : '👤';
                }

                acc.classList.toggle('is-signed-in', !!isSignedIn);
            }

            if (title) {
                if (!signedIn) {
                    applyHeaderIdentity(false, '');
                } else if (global.CustomerApi && typeof global.CustomerApi.get === 'function') {
                    applyHeaderIdentity(true, '');
                    global.CustomerApi.get('/customer/me', { auth: true })
                        .then((me) => {
                            const name = (me && me.name ? String(me.name).trim() : '');
                            applyHeaderIdentity(true, name);
                        })
                        .catch(() => {
                            // Token can be stale; keep a generic label.
                            applyHeaderIdentity(true, '');
                        });
                } else {
                    applyHeaderIdentity(true, '');
                }
            }

            const signOut = menu.querySelector('[data-sign-out]');
            if (signOut) {
                signOut.addEventListener('click', async () => {
                    try { await global.CustomerApi.post('/customer/logout', null, { auth: true }); }
                    catch (_) { /* ignore */ }
                    global.CustomerApi.setToken(null);
                    toast('Signed out', 'success');
                    setTimeout(() => window.location.reload(), 500);
                });
            }
        }

        // Mobile drawer toggle
        const menuBtn = document.querySelector('[data-menu-toggle]');
        const drawer = document.querySelector('[data-drawer]');
        const backdrop = document.querySelector('[data-drawer-backdrop]');
        if (menuBtn && drawer && backdrop) {
            const open = () => { drawer.classList.add('is-open'); backdrop.classList.add('is-open'); };
            const close = () => { drawer.classList.remove('is-open'); backdrop.classList.remove('is-open'); };
            menuBtn.addEventListener('click', open);
            backdrop.addEventListener('click', close);
            drawer.querySelectorAll('[data-drawer-close]').forEach(el => el.addEventListener('click', close));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        }

        // Pre-fill search input from ?q=
        const params = new URLSearchParams(window.location.search);
        const preQ = params.get('q');
        if (preQ) {
            const input = document.querySelector('[data-search-form] input[name="q"]');
            if (input) input.value = preQ;
        }
    }

    function bindCardAddToCart() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-add-cart]');
            if (!btn) return;
            if (btn.disabled) return;

            e.preventDefault();
            e.stopPropagation();

            if (!global.Cart || typeof global.Cart.add !== 'function') {
                toast('Cart is not ready yet', 'error');
                return;
            }

            const id = Number(btn.dataset.id);
            const product = {
                id,
                name: btn.dataset.name || '',
                price: Number(btn.dataset.price) || 0,
                discount_price: btn.dataset.discount === '' ? null : (Number(btn.dataset.discount) || null),
                image_url: btn.dataset.image || null,
            };
            global.Cart.add(id, 1, product);
            toast('Added to cart', 'success');
        });
    }

    const UI = {
        esc,
        money,
        buildPath,
        toast,
        productCard,
        skeletonCards,
        discountPct,
        effective,
        bindHeader,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            bindHeader();
            bindCardAddToCart();
        });
    } else {
        bindHeader();
        bindCardAddToCart();
    }

    global.UI = UI;
})(window);
