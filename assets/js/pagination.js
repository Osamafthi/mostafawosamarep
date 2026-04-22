/**
 * Pagination — reusable base class for page-number controls.
 *
 * The backend returns a payload shaped like:
 *   { items, total, page, limit, last_page }
 * which matches `App\Http\Resources\V1\PaginatedCollection::toArray()`.
 *
 * Usage:
 *   const pager = new Pagination({
 *       root: '#pagination',
 *       onPageChange: (page) => { state.page = page; loadProducts(); },
 *   });
 *   pager.render(apiData);
 *
 * Subclassing:
 *   class MyPager extends Pagination {
 *       buildPages() { ...custom markup... }
 *   }
 *
 * All rendering helpers are intentionally small so subclasses can override
 * only the piece they care about (e.g. just `buildPages` for a different
 * window size, or `buildPrev` for a different arrow style).
 */
(function (global) {
    'use strict';

    class Pagination {
        /**
         * @param {Object} opts
         * @param {string|HTMLElement} opts.root           Container to render into.
         * @param {(page:number)=>void} opts.onPageChange  Called when user picks a page.
         * @param {number} [opts.siblings=2]               Pages to show on each side of current.
         * @param {boolean} [opts.showPrevNext=true]       Show ‹ / › buttons.
         * @param {boolean} [opts.hideWhenSinglePage=true] Render nothing when last_page <= 1.
         * @param {string} [opts.btnClass='page-btn']      CSS class for each button.
         * @param {string} [opts.activeClass='is-active']  CSS class for the current page button.
         */
        constructor(opts) {
            if (!opts || !opts.root || typeof opts.onPageChange !== 'function') {
                throw new Error('Pagination requires { root, onPageChange }');
            }

            this.root = typeof opts.root === 'string'
                ? document.querySelector(opts.root)
                : opts.root;

            if (!this.root) {
                throw new Error('Pagination: root element not found');
            }

            this.onPageChange        = opts.onPageChange;
            this.siblings            = opts.siblings            ?? 2;
            this.showPrevNext        = opts.showPrevNext        ?? true;
            this.hideWhenSinglePage  = opts.hideWhenSinglePage  ?? true;
            this.btnClass            = opts.btnClass            || 'page-btn';
            this.activeClass         = opts.activeClass         || 'is-active';

            this.page = 1;
            this.lastPage = 1;

            this._onClick = this._onClick.bind(this);
            this.root.addEventListener('click', this._onClick);
        }

        /* ---------- Public API ---------- */

        /**
         * Render from a paginated API payload.
         * @param {{ page:number, last_page:number }} data
         */
        render(data) {
            this.page     = Math.max(1, Number(data.page)      || 1);
            this.lastPage = Math.max(1, Number(data.last_page) || 1);

            if (this.hideWhenSinglePage && this.lastPage <= 1) {
                this.root.innerHTML = '';
                return;
            }

            this.root.innerHTML = this.build();
        }

        /** Remove the click listener + clear markup. */
        destroy() {
            this.root.removeEventListener('click', this._onClick);
            this.root.innerHTML = '';
        }

        /* ---------- Overridable building blocks ---------- */

        /** Compose the final HTML string. Subclasses rarely need to override this. */
        build() {
            const parts = [];
            if (this.showPrevNext) parts.push(this.buildPrev());
            parts.push(this.buildPages());
            if (this.showPrevNext) parts.push(this.buildNext());
            return parts.join('');
        }

        buildPrev() {
            return this.button({
                page: this.page - 1,
                label: '‹',
                disabled: this.page <= 1,
                ariaLabel: 'Previous page',
            });
        }

        buildNext() {
            return this.button({
                page: this.page + 1,
                label: '›',
                disabled: this.page >= this.lastPage,
                ariaLabel: 'Next page',
            });
        }

        /**
         * The middle strip of page buttons. Override this in subclasses to
         * change the windowing logic (e.g. add first/last + ellipsis).
         */
        buildPages() {
            const { from, to } = this.windowRange();
            let html = '';
            for (let i = from; i <= to; i++) {
                html += this.button({
                    page: i,
                    label: String(i),
                    active: i === this.page,
                });
            }
            return html;
        }

        /** Compute the [from, to] page range based on `siblings`. */
        windowRange() {
            const from = Math.max(1, this.page - this.siblings);
            const to   = Math.min(this.lastPage, this.page + this.siblings);
            return { from, to };
        }

        /**
         * Build a single <button>. Subclasses can override to change markup,
         * but the `data-page` attribute must be preserved so click-handling works.
         * @param {{page:number,label:string,active?:boolean,disabled?:boolean,ariaLabel?:string}} o
         */
        button(o) {
            const cls = [this.btnClass];
            if (o.active) cls.push(this.activeClass);
            const attrs = [
                `class="${cls.join(' ')}"`,
                `data-page="${o.page}"`,
                o.disabled ? 'disabled' : '',
                o.ariaLabel ? `aria-label="${o.ariaLabel}"` : '',
            ].filter(Boolean).join(' ');
            return `<button ${attrs}>${o.label}</button>`;
        }

        /* ---------- Internals ---------- */

        _onClick(e) {
            const btn = e.target.closest('[data-page]');
            if (!btn || btn.disabled) return;
            if (!this.root.contains(btn)) return;
            const page = Number(btn.dataset.page);
            if (!Number.isFinite(page) || page === this.page) return;
            if (page < 1 || page > this.lastPage) return;
            this.onPageChange(page);
        }
    }

    global.Pagination = Pagination;
})(window);
