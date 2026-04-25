<?php
$basePath = '/mostafawosama';
$active   = 'categories';
$adminCssVersion      = file_exists(__DIR__ . '/../../assets/css/admin.css')      ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css')      : '1';
$apiJsVersion         = file_exists(__DIR__ . '/../../assets/js/api.js')          ? (string) filemtime(__DIR__ . '/../../assets/js/api.js')          : '1';
$authJsVersion        = file_exists(__DIR__ . '/../../assets/js/auth.js')         ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js')         : '1';
$categoriesJsVersion  = file_exists(__DIR__ . '/../../assets/js/categories.js')   ? (string) filemtime(__DIR__ . '/../../assets/js/categories.js')   : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Categories</title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/admin.css?v=<?= $adminCssVersion ?>">
<style>body[data-guarded]:not(.auth-ready){visibility:hidden}</style>
</head>
<body data-guarded>
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
                <a class="nav-link is-active" href="<?= $basePath ?>/views/admin/categories.php">
                    <span class="nav-link__icon">C</span> Categories
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/orders.php">
                    <span class="nav-link__icon">O</span> Orders
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/delivery-persons.php">
                    <span class="nav-link__icon">D</span> Delivery
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/admins.php">
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
                    <h1>Categories</h1>
                    <p class="topbar__sub">Organize your catalog with categories</p>
                </div>
                <button id="btnNewCategory" class="btn btn--primary">+ New Category</button>
            </header>

            <section class="toolbar">
                <div class="toolbar__left">
                    <div class="search">
                        <input id="searchInput" type="search" placeholder="Search categories…">
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
                            <th>Slug</th>
                            <th>Description</th>
                            <th style="width:160px;"></th>
                        </tr>
                    </thead>
                    <tbody id="categoryRows">
                        <tr><td colspan="5" class="muted">Loading…</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Category modal -->
    <div id="categoryModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">New Category</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="categoryForm" class="modal__body form-grid">
                <input type="hidden" id="fId">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Name</span>
                        <input type="text" id="fName" required minlength="2" maxlength="150">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Description</span>
                        <textarea id="fDescription" rows="4" maxlength="1000"></textarea>
                    </label>
                </div>
                <div id="modalError" class="form-error" hidden></div>
            </form>
            <footer class="modal__footer">
                <button type="button" class="btn btn--ghost" data-close>Cancel</button>
                <button type="button" id="btnSave" class="btn btn--primary">Save</button>
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
    <script src="<?= $basePath ?>/assets/js/categories.js?v=<?= $categoriesJsVersion ?>"></script>
</body>
</html>
