/**
 * CompactPagination — a Pagination variant that:
 *   - Always shows the first and last page buttons
 *   - Inserts ellipses when there's a gap between the siblings window and
 *     the edges.
 *
 * Requires `pagination.js` to be loaded first (defines `window.Pagination`).
 *
 * Usage:
 *   const pager = new CompactPagination({
 *       root: '#pagination',
 *       onPageChange: (p) => { state.page = p; load(); },
 *       siblings: 1,            // show just ±1 around current
 *   });
 *   pager.render(apiData);
 *
 * This class only overrides `buildPages()`; every other piece of behavior
 * (prev/next arrows, click handling, `destroy()`, etc.) is inherited as-is.
 */
(function (global) {
    'use strict';

    if (typeof global.Pagination !== 'function') {
        throw new Error('CompactPagination requires pagination.js to be loaded first.');
    }

    class CompactPagination extends global.Pagination {
        constructor(opts) {
            super(Object.assign({ siblings: 1 }, opts || {}));
        }

        buildPages() {
            const { from, to } = this.windowRange();
            let html = '';

            // First page + leading ellipsis
            if (from > 1) {
                html += this.button({
                    page: 1,
                    label: '1',
                    active: this.page === 1,
                });
                if (from > 2) html += this.ellipsis();
            }

            // Sibling window
            for (let i = from; i <= to; i++) {
                // Skip first/last since we render them separately.
                if (i === 1 || i === this.lastPage) continue;
                html += this.button({
                    page: i,
                    label: String(i),
                    active: i === this.page,
                });
            }

            // Trailing ellipsis + last page
            if (to < this.lastPage) {
                if (to < this.lastPage - 1) html += this.ellipsis();
                html += this.button({
                    page: this.lastPage,
                    label: String(this.lastPage),
                    active: this.page === this.lastPage,
                });
            }

            return html;
        }

        /** Override this if you want different ellipsis markup. */
        ellipsis() {
            return `<span class="${this.btnClass} ${this.btnClass}--ellipsis" aria-hidden="true">…</span>`;
        }
    }

    global.CompactPagination = CompactPagination;
})(window);
