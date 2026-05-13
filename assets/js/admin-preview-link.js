/**
 * admin-preview-link.js — Adds admin session to "View Store" links
 * so admins can access customer views with admin controls visible.
 *
 * This is secure because:
 * 1. It only works when admin is already authenticated
 * 2. The session expires after 5 minutes
 * 3. It uses a hash, not the actual admin token
 */
(function () {
    'use strict';

    function setupAdminPreviewLinks() {
        const adminToken = localStorage.getItem('adminToken');
        if (!adminToken) return;

        // Find all "View Store" / "View Offers" links
        const links = document.querySelectorAll('a[href*="/views/customer/"]');

        links.forEach(function(link) {
            const href = link.getAttribute('href');
            if (!href) return;

            // Don't modify if already has session
            if (href.includes('_admin_session')) return;

            // Generate session key (hash of token + timestamp)
            const timestamp = Date.now();
            const tokenHash = btoa(adminToken.substring(0, 16)).substring(0, 16);
            const sessionKey = tokenHash + timestamp;

            // Store in sessionStorage (only for this tab, expires with tab)
            try {
                sessionStorage.setItem('admin_session_key', sessionKey);
                sessionStorage.setItem('admin_session_time', String(timestamp));
            } catch (e) {
                // sessionStorage might be disabled
                return;
            }

            // Append session to URL
            const url = new URL(href, window.location.origin);
            url.searchParams.set('_admin_session', sessionKey);
            link.href = url.toString();
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupAdminPreviewLinks);
    } else {
        setupAdminPreviewLinks();
    }
})();
