/**
 * home.js — storefront home page.
 *
 *   1. Load public categories and render the sidebar (+ mobile drawer).
 *   2. Render a static hero slider (banner data is NOT in the backend).
 *   3. For each category, fetch its first page of products and render a
 *      horizontal product strip with prev/next arrows and a "See all" CTA.
 */
(function () {
    'use strict';

    const { esc, productCard, skeletonCards, toast, buildPath } = window.UI;
    const Api = window.CustomerApi;

    const STRIP_LIMIT = 12;

    /* ----------------------- Hero slider ----------------------- */

    const HERO_SLIDES = [
        {
            kicker: 'Featured',
            title: 'Everyday Essentials',
            desc:  'Top brands across beauty, home and tech. Fresh picks every week.',
            cta:   'Shop now',
            href:  '/views/customer/search.php',
            art:   'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=60',
        },
        {
            kicker: 'Smart Home',
            title: 'Upgrade Your Living Room',
            desc:  'Smart TVs, speakers and more — now up to 40% off.',
            cta:   'Explore deals',
            href:  '/views/customer/search.php',
            art:   'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=60',
        },
        {
            kicker: 'Beauty & Care',
            title: 'Glow Up Essentials',
            desc:  'Curated skincare, haircare and wellness products.',
            cta:   'Browse beauty',
            href:  '/views/customer/search.php',
            art:   'https://images.unsplash.com/photo-1522335789203-aaa6f4d2b46d?auto=format&fit=crop&w=1200&q=60',
        },
    ];

    let heroIndex = 0;
    let heroTimer = null;

    function renderHero() {
        const wrap = document.querySelector('[data-hero-slides]');
        const dots = document.querySelector('[data-hero-dots]');
        if (!wrap || !dots) return;

        wrap.innerHTML = HERO_SLIDES.map((s, i) => `
            <div class="hero-slide ${i === 0 ? 'is-active' : ''}" data-hero-slide>
                <div class="hero-slide__body">
                    <span class="hero-slide__kicker">${esc(s.kicker)}</span>
                    <h2 class="hero-slide__title">${esc(s.title)}</h2>
                    <p class="hero-slide__desc">${esc(s.desc)}</p>
                    <a class="hero-slide__cta" href="${esc(buildPath(s.href))}">${esc(s.cta)} &rarr;</a>
                </div>
                <div class="hero-slide__art">
                    <img src="${esc(s.art)}" alt="" loading="${i === 0 ? 'eager' : 'lazy'}" onerror="this.remove();">
                </div>
            </div>
        `).join('');

        dots.innerHTML = HERO_SLIDES.map((_, i) => `
            <button type="button" class="${i === 0 ? 'is-active' : ''}" data-hero-go="${i}" aria-label="Slide ${i + 1}"></button>
        `).join('');

        dots.querySelectorAll('[data-hero-go]').forEach(btn => {
            btn.addEventListener('click', () => {
                goHero(Number(btn.dataset.heroGo));
                restartHeroTimer();
            });
        });

        document.querySelector('[data-hero-prev]')?.addEventListener('click', () => {
            goHero(heroIndex - 1);
            restartHeroTimer();
        });
        document.querySelector('[data-hero-next]')?.addEventListener('click', () => {
            goHero(heroIndex + 1);
            restartHeroTimer();
        });

        restartHeroTimer();
    }

    function goHero(idx) {
        const slides = document.querySelectorAll('[data-hero-slide]');
        const dots = document.querySelectorAll('[data-hero-go]');
        if (!slides.length) return;
        const n = slides.length;
        heroIndex = ((idx % n) + n) % n;
        slides.forEach((el, i) => el.classList.toggle('is-active', i === heroIndex));
        dots.forEach((el, i) => el.classList.toggle('is-active', i === heroIndex));
    }

    function restartHeroTimer() {
        if (heroTimer) clearInterval(heroTimer);
        heroTimer = setInterval(() => goHero(heroIndex + 1), 6000);
    }

    /* ----------------------- Categories sidebar ----------------------- */

    async function loadCategories() {
        const sidebar = document.querySelector('[data-categories-list]');
        const drawerBody = document.querySelector('[data-drawer-cats]');

        let cats = [];
        try {
            cats = await Api.get('/categories');
            if (!Array.isArray(cats)) cats = [];
        } catch (e) {
            if (sidebar) sidebar.innerHTML = `<div class="cat-link" style="color: var(--text-muted);">Unable to load categories</div>`;
            if (drawerBody) drawerBody.innerHTML = `<div class="cat-link" style="color: var(--text-muted);">Unable to load categories</div>`;
            return [];
        }

        const markup = cats.map(c => `
            <a class="cat-link" href="${esc(buildPath('/views/customer/category.php?category_id=' + encodeURIComponent(c.id)))}">
                <span>${esc(c.name)}</span>
                <span class="chev" aria-hidden="true">&rsaquo;</span>
            </a>
        `).join('') || `<div class="cat-link" style="color: var(--text-muted);">No categories yet</div>`;

        if (sidebar) sidebar.innerHTML = markup;
        if (drawerBody) {
            drawerBody.innerHTML = `
                <div class="cats-sidebar__title">Shop by Category</div>
                ${markup}
            `;
        }
        return cats;
    }

    /* ----------------------- Per-category product strips ----------------------- */

    async function renderStrips(categories) {
        const host = document.querySelector('[data-category-strips]');
        if (!host) return;

        if (!categories.length) {
            host.innerHTML = `
                <div class="no-results" style="margin-top: 24px;">
                    No categories available yet. Check back soon.
                </div>
            `;
            return;
        }

        // Render shells first (so user sees structure immediately).
        host.innerHTML = categories.map(c => `
            <section class="strip" data-strip="${c.id}">
                <header class="strip__head">
                    <div class="strip__title">${esc(c.name)}</div>
                    <a class="strip__see-all" href="${esc(buildPath('/views/customer/category.php?category_id=' + encodeURIComponent(c.id)))}">
                        See all &rarr;
                    </a>
                </header>
                <div class="strip__body">
                    <button type="button" class="strip__arrow strip__arrow--prev" data-strip-prev aria-label="Scroll left" disabled>&lsaquo;</button>
                    <div class="strip__row" data-strip-row>${skeletonCards(6)}</div>
                    <button type="button" class="strip__arrow strip__arrow--next" data-strip-next aria-label="Scroll right">&rsaquo;</button>
                </div>
            </section>
        `).join('');

        // Fetch each strip's data in parallel.
        await Promise.all(categories.map(async (c) => {
            const section = host.querySelector(`[data-strip="${c.id}"]`);
            if (!section) return;
            const row = section.querySelector('[data-strip-row]');
            try {
                const res = await Api.get('/products', { query: { category_id: c.id, limit: STRIP_LIMIT, page: 1 } });
                const items = (res && Array.isArray(res.items)) ? res.items : [];
                if (!items.length) {
                    row.innerHTML = `<div class="no-results" style="flex: 1;">No products yet in ${esc(c.name)}.</div>`;
                } else {
                    row.innerHTML = items.map(productCard).join('');
                }
            } catch (e) {
                row.innerHTML = `<div class="no-results" style="flex: 1;">Failed to load ${esc(c.name)}.</div>`;
            }
            wireStripArrows(section);
        }));
    }

    function wireStripArrows(section) {
        const row  = section.querySelector('[data-strip-row]');
        const prev = section.querySelector('[data-strip-prev]');
        const next = section.querySelector('[data-strip-next]');
        if (!row || !prev || !next) return;

        const update = () => {
            const atStart = row.scrollLeft <= 2;
            const atEnd = row.scrollLeft + row.clientWidth >= row.scrollWidth - 2;
            prev.disabled = atStart;
            next.disabled = atEnd || row.scrollWidth <= row.clientWidth;
        };

        prev.addEventListener('click', () => row.scrollBy({ left: -row.clientWidth * 0.9, behavior: 'smooth' }));
        next.addEventListener('click', () => row.scrollBy({ left:  row.clientWidth * 0.9, behavior: 'smooth' }));
        row.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        requestAnimationFrame(update);
    }

    /* ----------------------- Init ----------------------- */

    async function init() {
        renderHero();
        const cats = await loadCategories();
        await renderStrips(cats);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
