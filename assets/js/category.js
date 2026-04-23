/**
 * category.js — full listing for a single category (the "See all" target).
 *
 * Reads ?category_id=... from the URL, resolves the category name via
 * GET /categories, then paginates products with GET /products.
 */
(function () {
    'use strict';

    const { esc, productCard, skeletonCards } = window.UI;
    const Api = window.CustomerApi;

    const LIMIT = 24;

    const params = new URLSearchParams(window.location.search);
    const categoryId = Number(params.get('category_id'));
    const state = {
        page: Math.max(1, Number(params.get('page')) || 1),
        category: null,
    };

    const titleEl = document.querySelector('[data-category-title]');
    const subEl   = document.querySelector('[data-results-sub]');
    const gridEl  = document.querySelector('[data-results-grid]');
    const noRes   = document.getElementById('noResults');
    const crumb   = document.querySelector('[data-breadcrumb-current]');

    if (!Number.isFinite(categoryId) || categoryId <= 0) {
        titleEl.textContent = 'Category not found';
        subEl.textContent = 'Please pick a category from the sidebar.';
        return;
    }

    const pager = new window.Pagination({
        root: '#pagination',
        onPageChange: (p) => {
            state.page = p;
            const usp = new URLSearchParams(window.location.search);
            usp.set('page', String(p));
            history.replaceState(null, '', location.pathname + '?' + usp.toString());
            loadProducts();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });

    async function loadCategory() {
        try {
            const cats = await Api.get('/categories');
            state.category = Array.isArray(cats) ? cats.find(c => Number(c.id) === categoryId) : null;
        } catch (_) { state.category = null; }

        if (state.category) {
            const name = state.category.name;
            document.title = name + ' — Mostafa & Osama';
            titleEl.textContent = name;
            crumb.textContent = name;
            if (state.category.description) {
                subEl.textContent = state.category.description;
            }
        } else {
            titleEl.textContent = 'Category #' + categoryId;
            crumb.textContent = 'Category';
        }
    }

    async function loadProducts() {
        gridEl.innerHTML = skeletonCards(LIMIT);
        noRes.hidden = true;

        try {
            const res = await Api.get('/products', {
                query: { category_id: categoryId, page: state.page, limit: LIMIT },
            });
            const items = Array.isArray(res.items) ? res.items : [];
            const total = Number(res.total) || 0;

            if (!items.length) {
                gridEl.innerHTML = '';
                noRes.hidden = false;
                subEl.textContent = 'No products in this category yet.';
                pager.render({ page: 1, last_page: 1 });
                return;
            }

            gridEl.innerHTML = items.map(productCard).join('');
            const base = state.category && state.category.description ? state.category.description + ' · ' : '';
            subEl.textContent = base + 'Showing ' + items.length + ' of ' + total + ' products';
            pager.render({ page: res.page, last_page: res.last_page });
        } catch (e) {
            gridEl.innerHTML = '';
            noRes.hidden = false;
            noRes.textContent = 'Failed to load products. ' + (window.CustomerApi.formatError(e) || '');
        }
    }

    (async function init() {
        await loadCategory();
        await loadProducts();
    })();
})();
