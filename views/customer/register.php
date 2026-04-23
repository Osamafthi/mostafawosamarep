<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Create account — Mostafa & Osama';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/customer.css') ?>">
    <link rel="stylesheet" href="<?= customerAsset($basePath, '/assets/css/auth.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="container page">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="<?= htmlspecialchars($basePath) ?>/">Home</a>
            <span class="sep">&rsaquo;</span>
            <span>Create account</span>
        </nav>

        <div data-auth-root="register">
            <!-- Rendered by auth.js -->
        </div>
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
    <script src="<?= customerAsset($basePath, '/assets/js/auth.js') ?>"></script>
</body>
</html>
