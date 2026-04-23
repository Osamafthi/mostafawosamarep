<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Product — Mostafa & Osama';
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
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($basePath) ?>/">Home</a>
            <span class="sep">&rsaquo;</span>
            <a data-breadcrumb-category href="#">Category</a>
            <span class="sep">&rsaquo;</span>
            <span data-breadcrumb-current>Product</span>
        </nav>

        <section class="product-detail" data-product-detail>
            <div class="product-gallery">
                <div class="product-gallery__main">
                    <div class="skeleton" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
            <div class="product-info">
                <div class="skeleton" style="height: 14px; width: 120px;"></div>
                <div class="skeleton" style="height: 28px; width: 80%;"></div>
                <div class="skeleton" style="height: 18px; width: 160px;"></div>
                <div class="skeleton" style="height: 96px; width: 100%;"></div>
            </div>
        </section>

        <section class="strip" data-related-strip hidden style="margin-top: 28px;">
            <header class="strip__head">
                <div class="strip__title">More in this category</div>
                <a class="strip__see-all" data-related-see-all href="#">See all &rarr;</a>
            </header>
            <div class="strip__body">
                <button type="button" class="strip__arrow strip__arrow--prev" data-strip-prev disabled>&lsaquo;</button>
                <div class="strip__row" data-related-row></div>
                <button type="button" class="strip__arrow strip__arrow--next" data-strip-next>&rsaquo;</button>
            </div>
        </section>
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
    <script src="<?= customerAsset($basePath, '/assets/js/product.js') ?>"></script>
</body>
</html>
