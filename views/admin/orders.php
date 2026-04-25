<?php
$basePath = '/mostafawosama';
$active   = 'orders';
$apiJsVersion    = file_exists(__DIR__ . '/../../assets/js/api.js')    ? (string) filemtime(__DIR__ . '/../../assets/js/api.js')    : '1';
$authJsVersion   = file_exists(__DIR__ . '/../../assets/js/auth.js')   ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js')   : '1';
$ordersJsVersion = file_exists(__DIR__ . '/../../assets/js/orders.js') ? (string) filemtime(__DIR__ . '/../../assets/js/orders.js') : '1';
$adminOrdersCssVersion = file_exists(__DIR__ . '/../../assets/css/admin-orders.css') ? (string) filemtime(__DIR__ . '/../../assets/css/admin-orders.css') : '1';
$paginationJsVersion = file_exists(__DIR__ . '/../../assets/js/pagination.js') ? (string) filemtime(__DIR__ . '/../../assets/js/pagination.js') : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Orders</title>
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?= $basePath ?>/assets/css/admin-orders.css?v=<?= $adminOrdersCssVersion ?>">
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
                <a class="nav-link is-active" href="<?= $basePath ?>/views/admin/orders.php">
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
                    <h1>Orders</h1>
                    <p class="topbar__sub">Track, update status, and inspect customer orders</p>
                </div>
            </header>

            <section class="stats">
                <div class="stat-card"><div class="stat-card__label">Total</div><div class="stat-card__value" id="statTotal">—</div></div>
                <div class="stat-card"><div class="stat-card__label">Pending</div><div class="stat-card__value" id="statPending">—</div></div>
                <div class="stat-card"><div class="stat-card__label">Processing</div><div class="stat-card__value" id="statProcessing">—</div></div>
                <div class="stat-card"><div class="stat-card__label">Delivered</div><div class="stat-card__value" id="statDelivered">—</div></div>
                <div class="stat-card"><div class="stat-card__label">Revenue</div><div class="stat-card__value" id="statRevenue">—</div></div>
            </section>

            <section class="status-tabs" id="statusTabs">
                <button class="tab is-active" data-status="">All</button>
                <button class="tab" data-status="pending">Pending</button>
                <button class="tab" data-status="processing">Processing</button>
                <button class="tab" data-status="shipped">Out for delivery</button>
                <button class="tab" data-status="delivered">Delivered</button>
                <button class="tab" data-status="cancelled">Cancelled</button>
            </section>

            <section class="toolbar">
                <div class="toolbar__left">
                    <div class="search">
                        <input id="searchInput" type="search" placeholder="Search by order #, name, or email…">
                    </div>
                    <select id="filterPayment" class="select">
                        <option value="">All payments</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
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
                            <th>Order #</th>
                            <th>Customer</th>
                            <th style="text-align:right;">Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Placed</th>
                            <th style="width:100px;"></th>
                        </tr>
                    </thead>
                    <tbody id="orderRows">
                        <tr><td colspan="7" class="muted">Loading…</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="pagination"></div>
            </section>
        </main>
    </div>

    <!-- Order detail drawer -->
    <div id="orderDrawer" class="drawer" hidden>
        <div class="drawer__backdrop" data-close></div>
        <aside class="drawer__panel">
            <header class="drawer__header">
                <div>
                    <div class="drawer__eyebrow" id="dOrderNumber">—</div>
                    <h2 id="dTitle">Order details</h2>
                </div>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <div class="drawer__body">
                <section class="card">
                    <h3>Customer</h3>
                    <dl class="defs">
                        <dt>Name</dt><dd id="dName">—</dd>
                        <dt>Email</dt><dd id="dEmail">—</dd>
                        <dt>Phone</dt><dd id="dPhone">—</dd>
                        <dt>Shipping</dt><dd id="dAddress">—</dd>
                        <dt>Location</dt><dd id="dLocation">—</dd>
                    </dl>
                </section>

                <section class="card">
                    <h3>Delivery</h3>
                    <dl class="defs">
                        <dt>Assigned to</dt><dd id="dAssignee">—</dd>
                    </dl>
                    <div class="manage-grid">
                        <label class="field">
                            <span class="field__label">Reassign courier</span>
                            <select id="dAssigneeSelect">
                                <option value="">— Unassigned —</option>
                            </select>
                        </label>
                        <div class="field" style="align-self:end;">
                            <button class="btn btn--ghost" id="btnReassign" type="button">Update assignee</button>
                        </div>
                    </div>
                    <div id="assigneeError" class="form-error" hidden></div>
                </section>

                <section class="card">
                    <h3>Items</h3>
                    <table class="table table--inner">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="text-align:right;">Qty</th>
                                <th style="text-align:right;">Unit</th>
                                <th style="text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="dItems"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align:right; font-weight:700;">Total</td>
                                <td id="dTotal" style="text-align:right; font-weight:800;">$0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </section>

                <section class="card">
                    <h3>Manage</h3>
                    <div class="manage-grid">
                        <label class="field">
                            <span class="field__label">Order status</span>
                            <select id="dStatus">
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Out for delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </label>
                        <label class="field">
                            <span class="field__label">Payment status</span>
                            <select id="dPayment">
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </label>
                    </div>
                    <div id="drawerError" class="form-error" hidden></div>
                </section>
            </div>
            <footer class="drawer__footer">
                <button class="btn btn--ghost" data-close>Close</button>
                <button class="btn btn--primary" id="btnUpdateOrder">Save changes</button>
            </footer>
        </aside>
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
    <script src="<?= $basePath ?>/assets/js/orders.js?v=<?= $ordersJsVersion ?>"></script>
</body>
</html>
