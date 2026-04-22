<?php
$basePath = '/mostafawosama';
$apiJsVersion  = file_exists(__DIR__ . '/../../assets/js/api.js')  ? (string) filemtime(__DIR__ . '/../../assets/js/api.js')  : '1';
$authJsVersion = file_exists(__DIR__ . '/../../assets/js/auth.js') ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js') : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Ecommerce</title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/login.css">
</head>
<body>
    <main class="login-shell">
        <section class="login-card">
            <header class="login-card__header">
                <div class="login-brand">EC</div>
                <h1>Welcome back</h1>
                <p>Sign in to manage your store</p>
            </header>

            <form id="loginForm" class="login-form" novalidate>
                <label class="field">
                    <span class="field__label">Email</span>
                    <input type="email" name="email" id="email" required
                           placeholder="admin@ecommerce.local" autocomplete="email">
                </label>

                <label class="field">
                    <span class="field__label">Password</span>
                    <input type="password" name="password" id="password" required
                           placeholder="••••••••" autocomplete="current-password">
                </label>

                <div id="formError" class="form-error" hidden></div>

                <button type="submit" id="submitBtn" class="btn btn--primary btn--block">
                    <span class="btn__text">Sign in</span>
                </button>
            </form>

            <footer class="login-card__footer">
                <p class="hint">Default: <strong>admin@ecommerce.local</strong> / <strong>admin123</strong></p>
            </footer>
        </section>
    </main>

    <script>
        window.APP_CONFIG = {
            basePath: '<?= $basePath ?>',
            // The Laravel API runs on its own dev server (php artisan serve) because
            // Laravel 13 requires PHP 8.3+ while XAMPP here ships PHP 8.2.x.
            // Override by setting window.APP_CONFIG.apiBase before this script loads.
            apiBase:  'http://localhost:8000/api/v1'
        };
    </script>
    <script src="<?= $basePath ?>/assets/js/api.js?v=<?= $apiJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/auth.js?v=<?= $authJsVersion ?>"></script>
</body>
</html>
