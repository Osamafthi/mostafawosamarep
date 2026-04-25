<?php
$basePath = '/mostafawosama';
$active   = 'delivery';
$adminCssVersion          = file_exists(__DIR__ . '/../../assets/css/admin.css')           ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css')           : '1';
$apiJsVersion             = file_exists(__DIR__ . '/../../assets/js/api.js')               ? (string) filemtime(__DIR__ . '/../../assets/js/api.js')               : '1';
$authJsVersion            = file_exists(__DIR__ . '/../../assets/js/auth.js')              ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js')              : '1';
$deliveryPersonsJsVersion = file_exists(__DIR__ . '/../../assets/js/delivery-persons.js')  ? (string) filemtime(__DIR__ . '/../../assets/js/delivery-persons.js')  : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Delivery Persons</title>
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
                <a class="nav-link" href="<?= $basePath ?>/views/admin/categories.php">
                    <span class="nav-link__icon">C</span> Categories
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/orders.php">
                    <span class="nav-link__icon">O</span> Orders
                </a>
                <a class="nav-link <?= $active === 'delivery' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/delivery-persons.php">
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
                    <h1>Delivery persons</h1>
                    <p class="topbar__sub">Couriers who can sign in and manage their assigned deliveries</p>
                </div>
                <button id="btnNewDelivery" class="btn btn--primary">+ New courier</button>
            </header>

            <section class="toolbar">
                <div class="toolbar__left">
                    <div class="search">
                        <input id="searchInput" type="search" placeholder="Search by name, email, or phone…">
                    </div>
                    <select id="filterActive" class="select">
                        <option value="">All statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
                            <th style="width:64px;">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Last assigned</th>
                            <th style="width:240px;"></th>
                        </tr>
                    </thead>
                    <tbody id="deliveryRows">
                        <tr><td colspan="7" class="muted">Loading…</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Create / edit modal -->
    <div id="deliveryModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">New courier</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="deliveryForm" class="modal__body form-grid">
                <input type="hidden" id="fId">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Name</span>
                        <input type="text" id="fName" required minlength="2" maxlength="150" autocomplete="off">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Email</span>
                        <input type="email" id="fEmail" required maxlength="190" autocomplete="off">
                    </label>
                    <label class="field">
                        <span class="field__label">Phone</span>
                        <input type="tel" id="fPhone" maxlength="40" autocomplete="off">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Password <span id="fPwHint" class="field__hint" style="font-weight:500;color:var(--text-muted);"></span></span>
                        <input type="password" id="fPassword" minlength="8" autocomplete="new-password">
                    </label>
                    <label class="field">
                        <span class="field__label">Confirm password</span>
                        <input type="password" id="fPasswordConfirm" minlength="8" autocomplete="new-password">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field" style="flex-direction: row; align-items: center; gap: 10px;">
                        <input type="checkbox" id="fActive" checked>
                        <span class="field__label" style="margin: 0;">Active (can sign in &amp; receive auto-assigned orders)</span>
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
            apiBase:  'http://localhost:8000/api/v1'
        };
    </script>
    <script src="<?= $basePath ?>/assets/js/api.js?v=<?= $apiJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/auth.js?v=<?= $authJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/delivery-persons.js?v=<?= $deliveryPersonsJsVersion ?>"></script>
</body>
</html>
