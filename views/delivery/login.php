<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Courier sign in — Mostafa & Osama';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= deliveryAsset($basePath, '/assets/css/delivery.css') ?>">
</head>
<body class="delivery-shell">
    <main class="delivery-auth">
        <div class="delivery-brand">
            <div class="delivery-brand__logo">M&amp;O</div>
            <div class="delivery-brand__text">
                <strong>Courier</strong>
                <span>Delivery dashboard</span>
            </div>
        </div>

        <div data-auth-root="delivery-login">
            <!-- Rendered by auth.js -->
        </div>
    </main>

    <script>
        window.APP_CONFIG = {
            basePath: <?= json_encode($basePath) ?>,
            apiBase:  <?= json_encode($apiBase) ?>
        };
    </script>
    <script src="<?= deliveryAsset($basePath, '/assets/js/delivery-api.js') ?>"></script>
    <script src="<?= deliveryAsset($basePath, '/assets/js/auth.js') ?>"></script>
</body>
</html>
