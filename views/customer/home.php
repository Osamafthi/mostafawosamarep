<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Home — Mostafa & Osama';
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

    <main class="container page">
        <!-- Hero row: sidebar + slider -->
        <section class="home-hero">
            <aside class="cats-sidebar" aria-label="Categories">
                <div class="cats-sidebar__title">Shop by Category</div>
                <div data-categories-list>
                    <!-- skeleton placeholders, replaced by home.js -->
                    <div class="cat-link"><span class="skeleton" style="height:14px;width:120px;display:block;"></span></div>
                    <div class="cat-link"><span class="skeleton" style="height:14px;width:100px;display:block;"></span></div>
                    <div class="cat-link"><span class="skeleton" style="height:14px;width:140px;display:block;"></span></div>
                    <div class="cat-link"><span class="skeleton" style="height:14px;width:110px;display:block;"></span></div>
                </div>
            </aside>

            <div class="hero-slider" aria-roledescription="carousel">
                <div class="hero-slides" data-hero-slides></div>
                <button type="button" class="hero-arrow hero-arrow--prev" data-hero-prev aria-label="Previous slide">&lsaquo;</button>
                <button type="button" class="hero-arrow hero-arrow--next" data-hero-next aria-label="Next slide">&rsaquo;</button>
                <div class="hero-dots" data-hero-dots></div>
            </div>
        </section>

        <!-- Promo quick-links row -->
        <section class="promos">
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#9889;</div>
                <div class="promo__title">Flash Sale</div>
                <div class="promo__sub">Hot deals</div>
            </a>
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#127873;</div>
                <div class="promo__title">Best Prices</div>
                <div class="promo__sub">Every day</div>
            </a>
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#10024;</div>
                <div class="promo__title">New Arrivals</div>
                <div class="promo__sub">Just landed</div>
            </a>
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#128176;</div>
                <div class="promo__title">Top Offers</div>
                <div class="promo__sub">This week</div>
            </a>
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#128230;</div>
                <div class="promo__title">Free Shipping</div>
                <div class="promo__sub">On orders</div>
            </a>
            <a class="promo" href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">
                <div class="promo__icon" aria-hidden="true">&#11088;</div>
                <div class="promo__title">Bestsellers</div>
                <div class="promo__sub">Most loved</div>
            </a>
        </section>

        <!-- Per-category product strips (rendered by home.js) -->
        <div data-category-strips></div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script>
        window.APP_CONFIG = {
            basePath: <?= json_encode($basePath) ?>,
            apiBase:  <?= json_encode($apiBase) ?>
        };
    </script>
    <script src="<?= customerAsset($basePath, '/assets/js/customer-api.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/cart.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/customer-ui.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/home.js') ?>"></script>
</body>
</html>
