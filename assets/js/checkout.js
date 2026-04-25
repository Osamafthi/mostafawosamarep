/**
 * checkout.js — guest/customer checkout form.
 *
 * Posts to POST /api/v1/orders. Payload shape matches
 * App\Http\Requests\V1\Order\StoreOrderRequest:
 *
 *   {
 *     customer_name, customer_email, customer_phone?, shipping_address,
 *     customer_latitude?, customer_longitude?,
 *     items: [{ product_id, quantity }]
 *   }
 *
 * If the browser has a `customerToken`, it is sent as Authorization
 * so the order is attached to that customer (see PublicOrderController).
 * Otherwise we submit as a guest — both paths are already supported
 * server-side.
 */
(function () {
    'use strict';

    const { esc, money, buildPath, toast } = window.UI;
    const Api = window.CustomerApi;
    const Cart = window.Cart;

    const root = document.querySelector('[data-checkout-root]');
    if (!root) return;

    // Captured by the "Share my precise location" button. Stays null
    // unless the customer explicitly grants geolocation; the courier UI
    // falls back to the text address in that case.
    const gps = { lat: null, lng: null };

    function renderEmpty() {
        root.innerHTML = `
            <div class="cart-empty">
                <h3>Your cart is empty</h3>
                <p>Add some products before checking out.</p>
                <a class="btn btn--primary btn--lg" href="${esc(buildPath('/'))}">Browse products</a>
            </div>
        `;
    }

    function renderSuccess(order) {
        const num  = order.order_number ? ('Order ' + order.order_number) : ('Order #' + order.id);
        const tot  = money(Number(order.total || 0));
        const mail = order.customer_email ? ' · ' + esc(order.customer_email) : '';
        root.innerHTML = `
            <div class="cart-empty" style="text-align: left; padding: 32px; max-width: 640px; margin: 0 auto;">
                <h3 style="text-align: center;">Thanks for your order!</h3>
                <p style="text-align: center; margin-bottom: 24px;">We received your order and will email a confirmation shortly${mail}.</p>
                <div class="cart-summary__row" style="margin-bottom: 10px;"><span>Reference</span><strong>${esc(num)}</strong></div>
                <div class="cart-summary__row"><span>Status</span><span>${esc(order.status || 'pending')}</span></div>
                <div class="cart-summary__row cart-summary__row--total"><span>Total paid</span><strong>${tot}</strong></div>
                <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
                    <a class="btn btn--primary" href="${esc(buildPath('/'))}">Keep shopping</a>
                </div>
            </div>
        `;
    }

    // Pulls the signed-in customer's profile so the form can be
    // prefilled. Silently returns null on error (guest checkout still
    // works). Only called when a customer token is present.
    async function fetchProfile() {
        if (!Api.isLoggedIn()) return null;
        try {
            return await Api.get('/customer/me', { auth: true });
        } catch (_) {
            return null;
        }
    }

    async function renderForm() {
        const items = Cart.items();
        if (!items.length) { renderEmpty(); return; }

        const me = await fetchProfile();

        const name    = esc(me && me.name    ? me.name    : '');
        const email   = esc(me && me.email   ? me.email   : '');
        const phone   = esc(me && me.phone   ? me.phone   : '');
        const address = me && me.default_shipping_address ? esc(me.default_shipping_address) : '';

        const signedInBanner = me ? `
            <div class="form-ok" style="margin-bottom: 14px;">
                Signed in as <strong>${esc(me.email || '')}</strong>${me.email_verified === false ? ' · please verify your email soon' : ''}
            </div>
        ` : '';

        root.innerHTML = `
            <form class="checkout-layout" data-checkout-form novalidate>
                <div class="form-card">
                    <h3>Shipping details</h3>
                    ${signedInBanner}
                    <div class="form-error" data-form-error hidden></div>

                    <div class="field">
                        <label class="field__label" for="f-name">Full name</label>
                        <input id="f-name" name="customer_name" required minlength="2" maxlength="200" autocomplete="name" value="${name}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="f-email">Email</label>
                        <input id="f-email" name="customer_email" type="email" required maxlength="190" autocomplete="email" value="${email}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="f-phone">Phone (optional)</label>
                        <input id="f-phone" name="customer_phone" maxlength="40" autocomplete="tel" value="${phone}">
                    </div>
                    <div class="field">
                        <label class="field__label" for="f-address">Shipping address</label>
                        <textarea id="f-address" name="shipping_address" required minlength="5" rows="3" autocomplete="street-address">${address}</textarea>
                    </div>

                    <div class="geo-share" data-geo-share>
                        <div class="geo-share__copy">
                            <strong>Help your courier find you</strong>
                            <span>Share your precise location so the delivery person can navigate straight to your door. Optional.</span>
                        </div>
                        <button type="button" class="btn btn--ghost" data-geo-btn>Share my precise location</button>
                        <div class="geo-share__status" data-geo-status hidden></div>
                    </div>

                    <button type="submit" class="btn btn--primary btn--lg btn--block" data-submit-btn>Place order</button>
                </div>

                <aside class="cart-summary">
                    <h3>Your order</h3>
                    <div class="mini-items">${miniItemsMarkup(items)}</div>
                    <div class="cart-summary__row">
                        <span>Items</span>
                        <span>${Cart.count()}</span>
                    </div>
                    <div class="cart-summary__row cart-summary__row--total">
                        <span>Total</span>
                        <span>${money(Cart.subtotal())}</span>
                    </div>
                    <a class="btn btn--ghost btn--block" href="${esc(buildPath('/views/customer/cart.php'))}">Edit cart</a>
                </aside>
            </form>
        `;

        wireForm();
    }

    function miniItemsMarkup(items) {
        return items.map(i => {
            const snap = i.snapshot || {};
            const unit = Cart.effectivePrice(snap);
            const line = unit * (Number(i.qty) || 0);
            const img = snap.image_url
                ? `<img src="${esc(snap.image_url)}" alt="" onerror="this.remove();">`
                : '';
            return `
                <div class="mini-item">
                    <div class="mini-item__img">${img}</div>
                    <div>
                        <div class="mini-item__name">${esc(snap.name || 'Product #' + i.product_id)}</div>
                        <div class="mini-item__qty">Qty ${Number(i.qty)} × ${money(unit)}</div>
                    </div>
                    <div class="mini-item__total">${money(line)}</div>
                </div>
            `;
        }).join('');
    }

    function wireForm() {
        const form = root.querySelector('[data-checkout-form]');
        const btn  = root.querySelector('[data-submit-btn]');
        const err  = root.querySelector('[data-form-error]');

        wireGeoShare();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            err.hidden = true;
            btn.disabled = true;
            btn.textContent = 'Placing order…';

            const data = new FormData(form);
            const payload = {
                customer_name:   (data.get('customer_name')    || '').toString().trim(),
                customer_email:  (data.get('customer_email')   || '').toString().trim(),
                customer_phone:  (data.get('customer_phone')   || '').toString().trim() || null,
                shipping_address:(data.get('shipping_address') || '').toString().trim(),
                items: Cart.items().map(i => ({
                    product_id: Number(i.product_id),
                    quantity:   Number(i.qty) || 1,
                })),
            };

            if (gps.lat !== null && gps.lng !== null) {
                payload.customer_latitude  = gps.lat;
                payload.customer_longitude = gps.lng;
            }

            if (!payload.customer_name || payload.customer_name.length < 2) {
                return fail('Please enter your full name.');
            }
            if (!payload.customer_email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(payload.customer_email)) {
                return fail('Please enter a valid email address.');
            }
            if (!payload.shipping_address || payload.shipping_address.length < 5) {
                return fail('Please enter a shipping address (at least 5 characters).');
            }
            if (!payload.items.length) {
                return fail('Your cart is empty.');
            }

            try {
                const order = await Api.post('/orders', payload, { auth: true });
                Cart.clear();
                renderSuccess(order || {});
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {
                fail(Api.formatError(e));
            }

            function fail(msg) {
                err.textContent = msg;
                err.hidden = false;
                btn.disabled = false;
                btn.textContent = 'Place order';
            }
        });
    }

    function wireGeoShare() {
        const wrap   = root.querySelector('[data-geo-share]');
        const btn    = root.querySelector('[data-geo-btn]');
        const status = root.querySelector('[data-geo-status]');
        if (!btn || !status) return;

        // Browsers refuse the Geolocation API on insecure origins. Show a
        // polite hint instead of silently disabling the button.
        const isSecure = window.isSecureContext
            || ['localhost', '127.0.0.1'].includes(location.hostname);

        if (!('geolocation' in navigator) || !isSecure) {
            btn.disabled = true;
            showStatus('Location sharing requires HTTPS or localhost. The courier will use your address instead.', 'info');
            return;
        }

        btn.addEventListener('click', () => {
            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = 'Locating…';
            status.hidden = true;

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    gps.lat = +pos.coords.latitude.toFixed(7);
                    gps.lng = +pos.coords.longitude.toFixed(7);
                    btn.textContent = 'Update location';
                    btn.disabled = false;
                    showStatus('Location shared — the courier will get a Maps link.', 'ok');
                    if (wrap) wrap.classList.add('is-shared');
                },
                (err) => {
                    btn.textContent = original;
                    btn.disabled = false;
                    const msg = err && err.code === 1
                        ? 'Permission denied. The courier will use your address instead.'
                        : 'Could not read your location. The courier will use your address instead.';
                    showStatus(msg, 'info');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        });

        function showStatus(message, kind) {
            status.textContent = message;
            status.hidden = false;
            status.dataset.kind = kind;
        }
    }

    renderForm();
})();
