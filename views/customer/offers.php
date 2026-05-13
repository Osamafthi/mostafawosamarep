<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Special Offers — Mostafa & Osama';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/customer.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="container page offers-page">
        <!-- Hero Section -->
        <section class="offers-hero">
            <h1 class="offers-hero__title">Special Offers & Deals</h1>
            <p class="offers-hero__subtitle">Discover our best discounts and save big on your favorite products</p>
        </section>

        <!-- Best Offers Section -->
        <section class="offers-section">
            <h2 class="offers-section__title">
                Best Deals
                <span class="badge">Up to 50% off</span>
            </h2>
            <div class="offers-grid" data-offers-best>
                <div class="no-results">Loading best offers...</div>
            </div>
        </section>

        <!-- Featured Offers Section -->
        <section class="offers-section">
            <h2 class="offers-section__title">Featured Offers</h2>
            <div class="offers-grid" data-offers-featured>
                <div class="no-results">Loading featured offers...</div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <!-- Admin Replace Modal (hidden by default) -->
    <div id="replaceModal" data-replace-modal style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);">
        <div style="background:#fff;border-radius:10px;padding:24px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 style="margin:0 0 16px;font-size:18px;">Replace Product</h3>
            <p style="margin:0 0 16px;color:#666;font-size:14px;">Enter the ID of the new product to replace the current one in featured offers.</p>
            <input type="number" id="replaceProductId" placeholder="New Product ID" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-bottom:16px;font-size:14px;">
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" data-close-replace style="padding:8px 16px;border:1px solid #ddd;background:#fff;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="button" id="confirmReplace" style="padding:8px 16px;border:0;background:#f68b1e;color:#fff;border-radius:6px;cursor:pointer;font-weight:600;">Replace</button>
            </div>
        </div>
    </div>

    <script>
        window.APP_CONFIG = {
            basePath: <?= json_encode($basePath) ?>,
            apiBase:  <?= json_encode($apiBase) ?>
        };
    </script>
    <script src="<?= customerAsset($basePath, '/assets/js/customer-api.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/cart.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/customer-ui.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/offers.js') ?>"></script>
</body>
</html>
