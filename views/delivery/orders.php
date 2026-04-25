<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'My deliveries — Mostafa & Osama';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= deliveryAsset($basePath, '/assets/css/delivery.css') ?>">
    <style>body[data-guarded]:not(.auth-ready){visibility:hidden}</style>
</head>
<body class="delivery-shell" data-guarded>
    <header class="delivery-topbar">
        <div class="delivery-topbar__inner">
            <div class="delivery-brand delivery-brand--compact">
                <div class="delivery-brand__logo">M&amp;O</div>
                <div class="delivery-brand__text">
                    <strong data-courier-name>Courier</strong>
                    <span data-courier-email></span>
                </div>
            </div>
            <button id="deliveryLogoutBtn" type="button" class="btn btn--ghost btn--sm">Sign out</button>
        </div>

        <nav class="delivery-tabs" data-tabs role="tablist" aria-label="Deliveries filter">
            <button class="delivery-tab is-active" role="tab" data-filter="active" aria-selected="true">
                <span class="delivery-tab__label">Active</span>
                <span class="delivery-tab__count" data-count-active>—</span>
            </button>
            <button class="delivery-tab" role="tab" data-filter="delivered" aria-selected="false">
                <span class="delivery-tab__label">Delivered</span>
                <span class="delivery-tab__count" data-count-delivered>—</span>
            </button>
            <button class="delivery-tab" role="tab" data-filter="all" aria-selected="false">
                <span class="delivery-tab__label">All</span>
            </button>
        </nav>
    </header>

    <main class="delivery-main" id="deliveryMain">
        <section class="delivery-list" data-orders-list>
            <div class="delivery-empty" data-empty hidden>
                <strong>No active deliveries</strong>
                <span>You're all caught up. New orders will appear here automatically.</span>
            </div>
            <div class="delivery-loading" data-loading>Loading deliveries…</div>
            <div data-orders-cards></div>
        </section>
    </main>

    <!-- Slide-up details sheet shown when a card is tapped. -->
    <div class="delivery-sheet" id="orderSheet" hidden aria-hidden="true">
        <div class="delivery-sheet__backdrop" data-sheet-close></div>
        <aside class="delivery-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="sheetTitle">
            <header class="delivery-sheet__header">
                <div>
                    <div class="delivery-sheet__eyebrow" data-sheet-number>Order</div>
                    <h2 id="sheetTitle" class="delivery-sheet__title">Order details</h2>
                </div>
                <button type="button" class="icon-btn" data-sheet-close aria-label="Close">&times;</button>
            </header>

            <div class="delivery-sheet__body" data-sheet-body>
                <!-- Filled in by delivery.js -->
            </div>

            <footer class="delivery-sheet__footer" data-sheet-footer>
                <!-- Action buttons rendered per status -->
            </footer>
        </aside>
    </div>

    <div id="toast" class="delivery-toast" hidden></div>

    <script>
        window.APP_CONFIG = {
            basePath: <?= json_encode($basePath) ?>,
            apiBase:  <?= json_encode($apiBase) ?>
        };
    </script>
    <script src="<?= deliveryAsset($basePath, '/assets/js/delivery-api.js') ?>"></script>
    <script src="<?= deliveryAsset($basePath, '/assets/js/auth.js') ?>"></script>
    <script src="<?= deliveryAsset($basePath, '/assets/js/delivery.js') ?>"></script>
</body>
</html>
