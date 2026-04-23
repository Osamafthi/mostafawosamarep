<?php
require_once __DIR__ . '/partials/bootstrap.php';
$pageTitle = 'Verify email — Mostafa & Osama';

// Only three states are produced by CustomerAuthController@verify.
$state = $_GET['state'] ?? '';
if (!in_array($state, ['success', 'expired', 'invalid'], true)) {
    $state = 'pending';
}
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
        <div class="auth-card" data-verify-state="<?= htmlspecialchars($state) ?>">
            <?php if ($state === 'success'): ?>
                <h1 class="auth-card__title">Email verified</h1>
                <p class="auth-card__lead">
                    Thanks — your email has been confirmed. You can now continue shopping.
                </p>
                <div class="auth-card__actions">
                    <a class="btn btn--primary btn--lg btn--block" href="<?= htmlspecialchars($basePath) ?>/">Go to storefront</a>
                </div>
            <?php elseif ($state === 'expired'): ?>
                <h1 class="auth-card__title">Link expired</h1>
                <p class="auth-card__lead">
                    This verification link has expired. Sign in and request a new one.
                </p>
                <div class="auth-card__actions">
                    <a class="btn btn--primary" href="<?= htmlspecialchars($basePath) ?>/views/customer/login.php">Sign in</a>
                    <a class="btn btn--ghost" href="<?= htmlspecialchars($basePath) ?>/">Continue to storefront</a>
                </div>
            <?php elseif ($state === 'invalid'): ?>
                <h1 class="auth-card__title">Link not valid</h1>
                <p class="auth-card__lead">
                    We couldn't verify that link. Sign in and request a new verification email.
                </p>
                <div class="auth-card__actions">
                    <a class="btn btn--primary" href="<?= htmlspecialchars($basePath) ?>/views/customer/login.php">Sign in</a>
                </div>
            <?php else: ?>
                <h1 class="auth-card__title">Verify your email</h1>
                <p class="auth-card__lead">
                    Check your inbox for a confirmation link to finish securing your account.
                </p>
                <div class="auth-card__actions">
                    <a class="btn btn--primary" href="<?= htmlspecialchars($basePath) ?>/">Back to storefront</a>
                </div>
            <?php endif; ?>
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
</body>
</html>
