<?php
require_once __DIR__ . '/partials/bootstrap.php';
$rawQ = isset($_GET['q']) ? (string) $_GET['q'] : '';
$pageTitle = ($rawQ !== '' ? ('Search: ' . $rawQ) : 'All Products') . ' — Mostafa & Osama';
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
            <span data-breadcrumb-current>Search</span>
        </nav>

        <h1 class="page__title" data-results-title>Products</h1>
        <p class="page__sub" data-results-sub>&nbsp;</p>

        <div class="grid" data-results-grid>
            <!-- populated by search.js -->
        </div>

        <div id="noResults" class="no-results" hidden>
            No products match your search.
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
    <script src="<?= customerAsset($basePath, '/assets/js/search.js') ?>"></script>
</body>
</html>
