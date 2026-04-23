<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'My Orders — Mostafa & Osama';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/customer.css') ?>">
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/auth.css') ?>">
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/orders.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="container page">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($basePath) ?>/">Home</a>
            <span class="sep">&rsaquo;</span>
            <span>My Orders</span>
        </nav>

        <h1 class="page__title">My Orders</h1>
        <p class="page__sub" data-orders-sub>Your recent orders appear below.</p>

        <div class="orders-toolbar">
            <div class="orders-tabs" data-orders-tabs role="tablist">
                <button type="button" class="tab is-active" data-status="">All</button>
                <button type="button" class="tab" data-status="pending">Pending</button>
                <button type="button" class="tab" data-status="processing">Processing</button>
                <button type="button" class="tab" data-status="delivered">Delivered</button>
                <button type="button" class="tab" data-status="cancelled">Cancelled</button>
            </div>
            <label class="orders-window">
                <span class="field__label">Show</span>
                <select data-orders-window>
                    <option value="6m" selected>Last 6 months</option>
                    <option value="1y">Last year</option>
                    <option value="all">All time</option>
                </select>
            </label>
        </div>

        <div data-orders-root>
            <!-- Rendered by my-orders.js -->
        </div>

        <nav class="pagination" id="pagination" aria-label="Pagination"></nav>
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
    <script src="<?= customerAsset($basePath, '/assets/js/pagination.js') ?>"></script>
    <script src="<?= customerAsset($basePath, '/assets/js/my-orders.js') ?>"></script>
</body>
</html>
