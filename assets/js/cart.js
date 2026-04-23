/**
 * cart.js — client-side shopping cart backed by localStorage.
 *
 * There is no cart endpoint on the Laravel backend, so the cart lives
 * entirely in the browser until the customer checks out. On checkout,
 * items are posted to POST /api/v1/orders.
 *
 * Data shape in localStorage (key: "customerCart"):
 *   [
 *     {
 *       product_id: number,
 *       qty: number,
 *       snapshot: {
 *         name: string,
 *         price: number,
 *         discount_price: number|null,
 *         image_url: string|null,
 *         slug?: string,
 *       }
 *     }
 *   ]
 *
 * Emits a "cart:changed" CustomEvent on `window` whenever the cart
 * changes, so header badges can stay in sync across tabs (via the
 * "storage" event) and within the same tab.
 *
 * Header, toast and also the page-level Cart view all consume this API.
 */
(function (global) {
    'use strict';

    const STORAGE_KEY = 'customerCart';
    const MAX_QTY = 99;

    function read() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return [];
            const data = JSON.parse(raw);
            if (!Array.isArray(data)) return [];
            return data.filter(i => i && Number.isFinite(Number(i.product_id)));
        } catch (_) {
            return [];
        }
    }

    function write(items) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (_) { /* quota / private mode */ }
        emit();
    }

    function emit() {
        try {
            global.dispatchEvent(new CustomEvent('cart:changed'));
        } catch (_) { /* old browsers */ }
    }

    function effectivePrice(snapshot) {
        if (!snapshot) return 0;
        const d = Number(snapshot.discount_price);
        if (Number.isFinite(d) && d > 0) return d;
        const p = Number(snapshot.price);
        return Number.isFinite(p) ? p : 0;
    }

    function normalizeSnapshot(product) {
        if (!product) return null;
        return {
            name: String(product.name || ''),
            price: Number(product.price) || 0,
            discount_price: product.discount_price != null ? Number(product.discount_price) : null,
            image_url: product.image_url || null,
            slug: product.slug || null,
        };
    }

    const Cart = {
        /** @returns {Array} a shallow copy of current items */
        items() { return read(); },

        /** @returns {number} total unit count */
        count() {
            return read().reduce((s, i) => s + (Number(i.qty) || 0), 0);
        },

        /** @returns {number} cart subtotal using effective price */
        subtotal() {
            return read().reduce((s, i) => s + effectivePrice(i.snapshot) * (Number(i.qty) || 0), 0);
        },

        /**
         * Add a product to the cart (or bump qty if it's already there).
         * @param {number} productId
         * @param {number} qty
         * @param {object} product  raw product resource from the API
         * @returns {number} new quantity for that product
         */
        add(productId, qty, product) {
            const id = Number(productId);
            const add = Math.max(1, Math.min(MAX_QTY, Number(qty) || 1));
            const items = read();
            const existing = items.find(i => Number(i.product_id) === id);
            if (existing) {
                existing.qty = Math.min(MAX_QTY, (Number(existing.qty) || 0) + add);
                if (product) existing.snapshot = normalizeSnapshot(product);
            } else {
                items.push({
                    product_id: id,
                    qty: add,
                    snapshot: normalizeSnapshot(product) || { name: '', price: 0, discount_price: null, image_url: null },
                });
            }
            write(items);
            const after = items.find(i => Number(i.product_id) === id);
            return after ? after.qty : add;
        },

        /** Replace the quantity for a product. qty <= 0 removes it. */
        setQty(productId, qty) {
            const id = Number(productId);
            const next = Math.min(MAX_QTY, Math.floor(Number(qty) || 0));
            const items = read();
            const idx = items.findIndex(i => Number(i.product_id) === id);
            if (idx === -1) return 0;
            if (next <= 0) items.splice(idx, 1);
            else items[idx].qty = next;
            write(items);
            return next > 0 ? next : 0;
        },

        /** Remove a product from the cart entirely. */
        remove(productId) {
            const id = Number(productId);
            const items = read().filter(i => Number(i.product_id) !== id);
            write(items);
        },

        /** Empty the cart. */
        clear() { write([]); },

        /** Refresh any element with [data-cart-count] to reflect current count. */
        refreshBadges() {
            const n = Cart.count();
            document.querySelectorAll('[data-cart-count]').forEach(el => {
                el.textContent = n > 99 ? '99+' : String(n);
                el.hidden = n === 0;
            });
        },

        effectivePrice,
    };

    // Keep the badge in sync across tabs.
    global.addEventListener('storage', (e) => {
        if (e.key === STORAGE_KEY) Cart.refreshBadges();
    });
    // And in the same tab.
    global.addEventListener('cart:changed', () => Cart.refreshBadges());

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => Cart.refreshBadges());
    } else {
        Cart.refreshBadges();
    }

    global.Cart = Cart;
})(window);
