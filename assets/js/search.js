/**
 * search.js — customer search results page.
 *
 * Reads ?q= from the URL, fetches GET /products with q + pagination,
 * and renders a responsive grid. Falls back to "browse all" when no
 * query is provided. Uses the shared Pagination helper.
 */
(function () {
    'use strict';

    const { esc, productCard, skeletonCards } = window.UI;
    const Api = window.CustomerApi;

    const LIMIT = 24;

    const params = new URLSearchParams(window.location.search);
    const q = (params.get('q') || '').trim();
    const state = {
        page: Math.max(1, Number(params.get('page')) || 1),
    };

    const titleEl = document.querySelector('[data-results-title]');
    const subEl   = document.querySelector('[data-results-sub]');
    const gridEl  = document.querySelector('[data-results-grid]');
    const noRes   = document.getElementById('noResults');
    const crumb   = document.querySelector('[data-breadcrumb-current]');

    if (q) {
        document.title = 'Search: ' + q + ' — Mostafa & Osama';
        titleEl.textContent = 'Search results';
        crumb.textContent = 'Search: "' + q + '"';
    } else {
        titleEl.textContent = 'All Products';
        crumb.textContent = 'All Products';
    }

    const pager = new window.Pagination({
        root: '#pagination',
        onPageChange: (p) => {
            state.page = p;
            const usp = new URLSearchParams(window.location.search);
            usp.set('page', String(p));
            history.replaceState(null, '', location.pathname + '?' + usp.toString());
            load();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });

    async function load() {
        gridEl.innerHTML = skeletonCards(LIMIT);
        noRes.hidden = true;
        subEl.textContent = 'Loading…';

        try {
            const res = await Api.get('/products', {
                query: { q: q || undefined, page: state.page, limit: LIMIT },
            });
            const items = Array.isArray(res.items) ? res.items : [];
            const total = Number(res.total) || 0;

            if (!items.length) {
                gridEl.innerHTML = '';
                noRes.hidden = false;
                subEl.textContent = q ? ('No results for "' + q + '"') : 'No products yet.';
                pager.render({ page: 1, last_page: 1 });
                return;
            }

            gridEl.innerHTML = items.map(productCard).join('');
            subEl.textContent = q
                ? ('Showing ' + items.length + ' of ' + total + ' results for "' + q + '"')
                : ('Showing ' + items.length + ' of ' + total + ' products');
            pager.render({ page: res.page, last_page: res.last_page });
        } catch (e) {
            gridEl.innerHTML = '';
            noRes.hidden = false;
            noRes.textContent = 'Failed to load products. ' + (window.CustomerApi.formatError(e) || '');
        }
    }

    load();
})();
