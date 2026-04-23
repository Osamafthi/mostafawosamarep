<?php
/**
 * Site-wide header used by every customer page.
 *
 * Expects these to be already defined by the caller:
 *   - string $basePath   app base path (e.g. "/mostafawosama")
 *   - string $pageTitle  <title> content
 *
 * All dynamic widgets (cart count, account menu, search submit,
 * mobile drawer) are wired up client-side by customer-ui.js + cart.js.
 */
?>
<header class="site-header">
    <div class="container site-header__bar">
        <div class="site-header__left">
            <button type="button" class="menu-toggle" data-menu-toggle aria-label="Open menu">&#9776;</button>
            <a class="site-header__logo" href="<?= htmlspecialchars($basePath) ?>/" aria-label="Home">
                MOSTAFA<span class="dot" aria-hidden="true"></span>
            </a>
        </div>

        <form class="site-search" data-search-form action="<?= htmlspecialchars($basePath) ?>/views/customer/search.php" method="get" role="search">
            <input type="search" name="q" placeholder="Search products and categories" autocomplete="off" aria-label="Search">
            <button type="submit">Search</button>
        </form>

        <div class="site-header__right">
            <div class="account-wrap">
                <button type="button" class="hdr-btn hdr-account" data-account-toggle aria-haspopup="true">
                    <span class="ic" data-account-icon aria-hidden="true">&#128100;</span>
                    <span class="label" data-account-label>Account</span>
                </button>
                <div class="account-menu" data-account-menu role="menu">
                    <div class="account-menu__title" data-account-title>Welcome, guest (please sign in)</div>
                    <a href="<?= htmlspecialchars($basePath) ?>/views/customer/cart.php">My Cart</a>
                    <a href="<?= htmlspecialchars($basePath) ?>/views/customer/orders.php" data-when-auth="in" hidden>My Orders</a>
                    <a href="<?= htmlspecialchars($basePath) ?>/views/customer/login.php" data-when-auth="out">Sign in</a>
                    <a href="<?= htmlspecialchars($basePath) ?>/views/customer/register.php" data-when-auth="out">Create account</a>
                    <button type="button" data-sign-out data-when-auth="in" hidden>Sign out</button>
                </div>
            </div>
            <a class="hdr-btn hdr-cart" href="<?= htmlspecialchars($basePath) ?>/views/customer/cart.php" aria-label="Cart">
                <span class="ic" aria-hidden="true">&#128722;</span>
                <span class="label">Cart</span>
                <span class="cart-badge" data-cart-count hidden>0</span>
            </a>
        </div>
    </div>
</header>

<div class="drawer-backdrop" data-drawer-backdrop></div>
<aside class="drawer" data-drawer aria-label="Categories">
    <div class="drawer__head">
        <div class="drawer__title">Categories</div>
        <button type="button" class="drawer__close" data-drawer-close aria-label="Close">&times;</button>
    </div>
    <div class="drawer__body" data-drawer-cats>
        <div class="cats-sidebar__title">Loading…</div>
    </div>
</aside>
