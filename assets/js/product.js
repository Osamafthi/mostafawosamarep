/**
 * product.js — customer product detail page.
 *
 * - Loads GET /products/{id} for full data (including images + category_name).
 * - Renders gallery, price (with discount), stock state, description.
 * - Provides qty stepper + Add to Cart, which pushes to the localStorage cart.
 * - Loads a small "More in this category" strip from GET /products?category_id=.
 */
(function () {
    'use strict';

    const { esc, money, productCard, toast, buildPath, effective, discountPct } = window.UI;
    const Api = window.CustomerApi;

    const params = new URLSearchParams(window.location.search);
    const productId = Number(params.get('id'));

    const detailEl = document.querySelector('[data-product-detail]');
    const crumbCat = document.querySelector('[data-breadcrumb-category]');
    const crumbCurr = document.querySelector('[data-breadcrumb-current]');

    if (!Number.isFinite(productId) || productId <= 0) {
        detailEl.innerHTML = `<div class="no-results" style="grid-column: 1 / -1;">Invalid product link.</div>`;
        return;
    }

    let currentProduct = null;
    let currentQty = 1;
    let activeImageIdx = 0;

    async function loadProduct() {
        try {
            const p = await Api.get('/products/' + productId);
            if (!p || !p.id) throw new Error('not-found');
            currentProduct = p;
            render();
            loadRelated(p);
        } catch (e) {
            detailEl.innerHTML = `
                <div class="no-results" style="grid-column: 1 / -1;">
                    ${e && e.error === 'Product not found' ? 'This product is no longer available.' : 'Failed to load product.'}
                </div>
            `;
        }
    }

    function imageList(p) {
        const list = [];
        if (Array.isArray(p.images)) {
            p.images.forEach(im => { if (im && im.url) list.push(im.url); });
        }
        if (p.image_url && !list.includes(p.image_url)) list.unshift(p.image_url);
        return list;
    }

    function render() {
        const p = currentProduct;
        document.title = p.name + ' — Mostafa & Osama';

        if (p.category_id && p.category_name) {
            crumbCat.textContent = p.category_name;
            crumbCat.href = buildPath('/views/customer/category.php?category_id=' + encodeURIComponent(p.category_id));
        } else {
            crumbCat.hidden = true;
            crumbCat.nextElementSibling?.remove(); // remove the " › " separator after it
        }
        crumbCurr.textContent = p.name;

        const images = imageList(p);
        const hasDiscount = p.discount_price != null && Number(p.discount_price) > 0 && Number(p.discount_price) < Number(p.price);
        const price = effective(p);
        const pct = hasDiscount ? discountPct(p.price, p.discount_price) : 0;
        const stock = Number(p.stock) || 0;
        const stockClass =
            stock <= 0 ? 'product-info__stock--out' :
            stock < 5 ? 'product-info__stock--low' :
            'product-info__stock--in';
        const stockText =
            stock <= 0 ? 'Out of stock' :
            stock < 5 ? 'Only ' + stock + ' left in stock' :
            'In stock';

        const mainImg = images[0]
            ? `<img src="${esc(images[0])}" alt="${esc(p.name)}" onerror="this.remove();">`
            : `<span style="color: var(--text-muted); font-size: 13px;">No image</span>`;

        const thumbs = images.length > 1
            ? `<div class="product-gallery__thumbs" data-gallery-thumbs>
                ${images.map((u, i) => `
                    <button type="button" class="${i === 0 ? 'is-active' : ''}" data-thumb="${i}" aria-label="Image ${i + 1}">
                        <img src="${esc(u)}" alt="" onerror="this.parentNode.remove();">
                    </button>
                `).join('')}
               </div>`
            : '';

        detailEl.innerHTML = `
            <div class="product-gallery">
                <div class="product-gallery__main" data-gallery-main>${mainImg}</div>
                ${thumbs}
            </div>
            <div class="product-info">
                ${p.category_name ? `<span class="product-info__cat">${esc(p.category_name)}</span>` : ''}
                <h1 class="product-info__name">${esc(p.name)}</h1>
                <div class="product-info__price-row">
                    <span class="product-info__price">${money(price)}</span>
                    ${hasDiscount ? `<span class="product-info__price-old">${money(p.price)}</span>` : ''}
                    ${pct ? `<span class="product-info__discount">-${pct}%</span>` : ''}
                </div>
                <div class="product-info__stock ${stockClass}">${stockText}</div>
                ${p.description ? `<div class="product-info__desc">${esc(p.description)}</div>` : ''}
                <div class="qty-row">
                    <div class="qty-stepper" aria-label="Quantity">
                        <button type="button" data-qty="-1" aria-label="Decrease">&minus;</button>
                        <input type="number" min="1" max="${Math.max(1, stock)}" value="1" data-qty-input>
                        <button type="button" data-qty="+1" aria-label="Increase">&#43;</button>
                    </div>
                    <button type="button" class="btn btn--primary btn--lg" data-add-to-cart ${stock <= 0 ? 'disabled' : ''}>
                        ${stock <= 0 ? 'Unavailable' : 'Add to cart'}
                    </button>
                </div>
            </div>
        `;

        wireGallery(images);
        wireQty(stock);
        wireAddToCart();
    }

    function wireGallery(images) {
        const main = document.querySelector('[data-gallery-main]');
        const thumbs = document.querySelectorAll('[data-thumb]');
        thumbs.forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = Number(btn.dataset.thumb);
                if (!Number.isFinite(idx) || idx === activeImageIdx) return;
                activeImageIdx = idx;
                thumbs.forEach(t => t.classList.toggle('is-active', Number(t.dataset.thumb) === idx));
                main.innerHTML = `<img src="${esc(images[idx])}" alt="" onerror="this.remove();">`;
            });
        });
    }

    function wireQty(stock) {
        const input = document.querySelector('[data-qty-input]');
        const buttons = document.querySelectorAll('[data-qty]');
        const clamp = (v) => Math.max(1, Math.min(stock > 0 ? stock : 1, Math.floor(Number(v) || 1)));
        currentQty = clamp(input.value);
        input.value = currentQty;

        buttons.forEach(b => b.addEventListener('click', () => {
            const delta = Number(b.dataset.qty) || 0;
            currentQty = clamp(currentQty + delta);
            input.value = currentQty;
        }));
        input.addEventListener('change', () => {
            currentQty = clamp(input.value);
            input.value = currentQty;
        });
    }

    function wireAddToCart() {
        const btn = document.querySelector('[data-add-to-cart]');
        if (!btn || btn.hasAttribute('disabled')) return;
        btn.addEventListener('click', () => {
            window.Cart.add(currentProduct.id, currentQty, currentProduct);
            toast('Added to cart', 'success');
        });
    }

    async function loadRelated(p) {
        if (!p.category_id) return;
        const section = document.querySelector('[data-related-strip]');
        const row = document.querySelector('[data-related-row]');
        const link = document.querySelector('[data-related-see-all]');
        if (!section || !row) return;

        try {
            const res = await Api.get('/products', { query: { category_id: p.category_id, limit: 12, page: 1 } });
            const items = Array.isArray(res.items) ? res.items.filter(i => Number(i.id) !== Number(p.id)) : [];
            if (!items.length) return;
            row.innerHTML = items.map(productCard).join('');
            if (link) link.href = buildPath('/views/customer/category.php?category_id=' + encodeURIComponent(p.category_id));
            section.hidden = false;
            wireStripArrows(section);
        } catch (_) { /* silent */ }
    }

    function wireStripArrows(section) {
        const row  = section.querySelector('[data-related-row]');
        const prev = section.querySelector('[data-strip-prev]');
        const next = section.querySelector('[data-strip-next]');
        if (!row || !prev || !next) return;
        const update = () => {
            prev.disabled = row.scrollLeft <= 2;
            next.disabled = row.scrollLeft + row.clientWidth >= row.scrollWidth - 2 || row.scrollWidth <= row.clientWidth;
        };
        prev.addEventListener('click', () => row.scrollBy({ left: -row.clientWidth * 0.9, behavior: 'smooth' }));
        next.addEventListener('click', () => row.scrollBy({ left:  row.clientWidth * 0.9, behavior: 'smooth' }));
        row.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        requestAnimationFrame(update);
    }

    loadProduct();
})();
