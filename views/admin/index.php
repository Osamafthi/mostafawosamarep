<?php
$basePath = '/mostafawosama';
$active   = 'products';
$adminCssVersion = file_exists(__DIR__ . '/../../assets/css/admin.css') ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css') : '1';
$apiJsVersion    = file_exists(__DIR__ . '/../../assets/js/api.js') ? (string) filemtime(__DIR__ . '/../../assets/js/api.js') : '1';
$authJsVersion   = file_exists(__DIR__ . '/../../assets/js/auth.js') ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js') : '1';
$adminJsVersion  = file_exists(__DIR__ . '/../../assets/js/admin.js') ? (string) filemtime(__DIR__ . '/../../assets/js/admin.js') : '1';
$paginationJsVersion        = file_exists(__DIR__ . '/../../assets/js/pagination.js')          ? (string) filemtime(__DIR__ . '/../../assets/js/pagination.js')          : '1';
$paginationCompactJsVersion = file_exists(__DIR__ . '/../../assets/js/pagination-compact.js')  ? (string) filemtime(__DIR__ . '/../../assets/js/pagination-compact.js')  : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Products</title>
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
                <a class="nav-link <?= $active === 'products' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/index.php">
                    <span class="nav-link__icon">P</span>
                    Products
                </a>
                <a class="nav-link <?= $active === 'categories' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/categories.php">
                    <span class="nav-link__icon">C</span>
                    Categories
                </a>
                <a class="nav-link <?= $active === 'orders' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/orders.php">
                    <span class="nav-link__icon">O</span>
                    Orders
                </a>
                <a class="nav-link <?= $active === 'delivery' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/delivery-persons.php">
                    <span class="nav-link__icon">D</span>
                    Delivery
                </a>
                <a class="nav-link <?= $active === 'admins' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/admins.php">
                    <span class="nav-link__icon">A</span>
                    Admins
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
                    <h1>Products</h1>
                    <p class="topbar__sub">Manage catalog, pricing, and stock</p>
                </div>
                <button id="btnNewProduct" class="btn btn--primary">+ New Product</button>
            </header>

            <section class="stats">
                <div class="stat-card">
                    <div class="stat-card__label">Total Products</div>
                    <div class="stat-card__value" id="statTotal">—</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__label">Active</div>
                    <div class="stat-card__value" id="statActive">—</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__label">Categories</div>
                    <div class="stat-card__value" id="statCats">—</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__label">Low Stock (&le;5)</div>
                    <div class="stat-card__value" id="statLow">—</div>
                </div>
            </section>

            <section class="toolbar">
                <div class="toolbar__left">
                    <div class="search">
                        <input id="searchInput" type="search" placeholder="Search by name or description…">
                    </div>
                    <select id="filterCategory" class="select">
                        <option value="">All categories</option>
                    </select>
                    <select id="filterStatus" class="select">
                        <option value="">All status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="toolbar__right">
                    <button id="btnRefresh" class="btn btn--ghost">Refresh</button>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:72px;">Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th style="text-align:right;">Price</th>
                            <th style="text-align:right;">Stock</th>
                            <th>Status</th>
                            <th style="width:140px;"></th>
                        </tr>
                    </thead>
                    <tbody id="productRows">
                        <tr><td colspan="7" class="muted">Loading…</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="pagination"></div>
            </section>
        </main>
    </div>

    <!-- Product modal -->
    <div id="productModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">New Product</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="productForm" class="modal__body form-grid">
                <input type="hidden" id="fId">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Name</span>
                        <input type="text" id="fName" required maxlength="200">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Description</span>
                        <textarea id="fDescription" rows="3"></textarea>
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Price</span>
                        <input type="number" id="fPrice" required min="0" step="0.01">
                    </label>
                    <label class="field">
                        <span class="field__label">Discount price</span>
                        <input type="number" id="fDiscount" min="0" step="0.01">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Stock</span>
                        <input type="number" id="fStock" required min="0" step="1">
                    </label>
                    <label class="field">
                        <span class="field__label">Category</span>
                        <select id="fCategory" required></select>
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Status</span>
                        <select id="fStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </label>
                    <div class="field">
                        <span class="field__label">Product image</span>
                        <div class="upload-row">
                            <input type="file" id="fImage" accept="image/*">
                            <div id="imgPreview" class="img-preview"></div>
                        </div>
                    </div>
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
    <script src="<?= $basePath ?>/assets/js/pagination.js?v=<?= $paginationJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/pagination-compact.js?v=<?= $paginationCompactJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/admin.js?v=<?= $adminJsVersion ?>"></script>
</body>
</html>
