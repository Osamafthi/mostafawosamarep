/**
 * cart-page.js — renders the localStorage cart.
 *
 * Lets the customer edit quantity or remove an item, then proceed
 * to checkout. All data lives in the browser; the only server call
 * on this page is the forward navigation to checkout.php.
 */
(function () {
    'use strict';

    const { esc, money, buildPath, toast } = window.UI;
    const Cart = window.Cart;

    const root = document.querySelector('[data-cart-root]');
    if (!root) return;

    function render() {
        const items = Cart.items();
        if (!items.length) {
            root.innerHTML = `
                <div class="cart-empty">
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added anything yet.</p>
                    <a class="btn btn--primary btn--lg" href="${esc(buildPath('/'))}">Start shopping</a>
                </div>
            `;
            return;
        }

        root.innerHTML = `
            <div class="cart-layout">
                <div class="cart-items" data-cart-items>${itemsMarkup(items)}</div>

                <aside class="cart-summary">
                    <h3>Order summary</h3>
                    <div class="cart-summary__row">
                        <span>Items</span>
                        <span data-summary-count>${Cart.count()}</span>
                    </div>
                    <div class="cart-summary__row">
                        <span>Subtotal</span>
                        <span data-summary-subtotal>${money(Cart.subtotal())}</span>
                    </div>
                    <div class="cart-summary__row">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                    <div class="cart-summary__row cart-summary__row--total">
                        <span>Total</span>
                        <span data-summary-total>${money(Cart.subtotal())}</span>
                    </div>
                    <a class="btn btn--primary btn--block btn--lg" href="${esc(buildPath('/views/customer/checkout.php'))}">Proceed to checkout</a>
                    <button type="button" class="btn btn--ghost btn--block" data-clear-cart>Clear cart</button>
                </aside>
            </div>
        `;

        wire();
    }

    function itemsMarkup(items) {
        return items.map(i => {
            const snap = i.snapshot || {};
            const unit = Cart.effectivePrice(snap);
            const lineTotal = unit * (Number(i.qty) || 0);
            const detailHref = buildPath('/views/customer/product.php?id=' + encodeURIComponent(i.product_id));
            const imgMarkup = snap.image_url
                ? `<img src="${esc(snap.image_url)}" alt="" onerror="this.remove();">`
                : '';
            return `
                <div class="cart-row" data-row data-id="${i.product_id}">
                    <a class="cart-row__img" href="${esc(detailHref)}">${imgMarkup}</a>
                    <div class="cart-row__name-wrap">
                        <a class="cart-row__name" href="${esc(detailHref)}">${esc(snap.name || 'Product #' + i.product_id)}</a>
                        <div class="cart-row__price">${money(unit)} each</div>
                    </div>
                    <div class="cart-row__qty">
                        <div class="qty-stepper" aria-label="Quantity">
                            <button type="button" data-qty-delta="-1" aria-label="Decrease">&minus;</button>
                            <input type="number" min="1" max="99" value="${Number(i.qty)}" data-qty-input>
                            <button type="button" data-qty-delta="+1" aria-label="Increase">&#43;</button>
                        </div>
                    </div>
                    <div class="cart-row__total">${money(lineTotal)}</div>
                    <button type="button" class="cart-row__remove" data-remove aria-label="Remove">&times;</button>
                </div>
            `;
        }).join('');
    }

    function wire() {
        root.querySelectorAll('[data-row]').forEach(row => {
            const id = Number(row.dataset.id);
            const input = row.querySelector('[data-qty-input]');
            row.querySelectorAll('[data-qty-delta]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const delta = Number(btn.dataset.qtyDelta) || 0;
                    const next = Math.max(0, (Number(input.value) || 0) + delta);
                    applyQty(id, next);
                });
            });
            input.addEventListener('change', () => {
                applyQty(id, Math.max(0, Math.floor(Number(input.value) || 0)));
            });
            row.querySelector('[data-remove]').addEventListener('click', () => {
                Cart.remove(id);
                toast('Removed from cart');
                render();
            });
        });

        const clearBtn = root.querySelector('[data-clear-cart]');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                if (!confirm('Clear all items from your cart?')) return;
                Cart.clear();
                toast('Cart cleared');
                render();
            });
        }
    }

    function applyQty(productId, qty) {
        if (qty <= 0) {
            Cart.remove(productId);
            toast('Removed from cart');
        } else {
            Cart.setQty(productId, qty);
        }
        render();
    }

    render();
    window.addEventListener('cart:changed', () => {
        // If another tab modified the cart, re-render (but avoid infinite loops
        // from our own writes — render() reads fresh data anyway, so it's fine).
    });
})();
