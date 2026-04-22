<?php
$basePath = '/mostafawosama';
$active   = 'admins';
$adminCssVersion  = file_exists(__DIR__ . '/../../assets/css/admin.css')  ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css')  : '1';
$apiJsVersion     = file_exists(__DIR__ . '/../../assets/js/api.js')      ? (string) filemtime(__DIR__ . '/../../assets/js/api.js')      : '1';
$authJsVersion    = file_exists(__DIR__ . '/../../assets/js/auth.js')     ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js')     : '1';
$adminsJsVersion  = file_exists(__DIR__ . '/../../assets/js/admins.js')   ? (string) filemtime(__DIR__ . '/../../assets/js/admins.js')   : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Accounts</title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/admin.css?v=<?= $adminCssVersion ?>">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar__brand">
                <span class="logo">EC</span>
                <span>Ecommerce</span>
            </div>
            <nav class="sidebar__nav">
                <a class="nav-link" href="<?= $basePath ?>/views/admin/index.php">
                    <span class="nav-link__icon">P</span> Products
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/categories.php">
                    <span class="nav-link__icon">C</span> Categories
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/orders.php">
                    <span class="nav-link__icon">O</span> Orders
                </a>
                <a class="nav-link is-active" href="<?= $basePath ?>/views/admin/admins.php">
                    <span class="nav-link__icon">A</span> Admins
                </a>
            </nav>
            <div class="sidebar__foot">
                <div class="admin-chip">
                    <div class="admin-chip__avatar" id="adminAvatar">A</div>
                    <div>
                        <div class="admin-chip__name" id="adminName">Admin</div>
                        <button id="logoutBtn" class="linklike">Sign out</button>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Admin accounts</h1>
                    <p class="topbar__sub">Create and manage users who can access the admin panel</p>
                </div>
                <button id="btnNewAdmin" class="btn btn--primary">+ New Admin</button>
            </header>

            <section class="toolbar">
                <div class="toolbar__left">
                    <div class="search">
                        <input id="searchInput" type="search" placeholder="Search by name or email…">
                    </div>
                </div>
                <div class="toolbar__right">
                    <button id="btnRefresh" class="btn btn--ghost">Refresh</button>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80px;">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created</th>
                            <th style="width:140px;"></th>
                        </tr>
                    </thead>
                    <tbody id="adminRows">
                        <tr><td colspan="5" class="muted">Loading…</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Admin modal -->
    <div id="adminModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">New Admin</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="adminForm" class="modal__body form-grid">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Name</span>
                        <input type="text" id="fName" required minlength="2" maxlength="150" autocomplete="off">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Email</span>
                        <input type="email" id="fEmail" required maxlength="190" autocomplete="off">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Password</span>
                        <input type="password" id="fPassword" required minlength="8" autocomplete="new-password">
                    </label>
                    <label class="field">
                        <span class="field__label">Confirm password</span>
                        <input type="password" id="fPasswordConfirm" required minlength="8" autocomplete="new-password">
                    </label>
                </div>
                <div id="modalError" class="form-error" hidden></div>
            </form>
            <footer class="modal__footer">
                <button type="button" class="btn btn--ghost" data-close>Cancel</button>
                <button type="button" id="btnSave" class="btn btn--primary">Create admin</button>
            </footer>
        </div>
    </div>

    <div id="toast" class="toast" hidden></div>

    <script>
        window.APP_CONFIG = {
            basePath: '<?= $basePath ?>',
            // The Laravel API runs on its own dev server (php artisan serve) because
            // Laravel 13 requires PHP 8.3+ while XAMPP here ships PHP 8.2.x.
            apiBase:  'http://localhost:8000/api/v1'
        };
    </script>
    <script src="<?= $basePath ?>/assets/js/api.js?v=<?= $apiJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/auth.js?v=<?= $authJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/admins.js?v=<?= $adminsJsVersion ?>"></script>
</body>
</html>
