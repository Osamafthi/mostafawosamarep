<?php
$basePath = '/mostafawosama';
$active   = 'featured-offers';
$adminCssVersion = file_exists(__DIR__ . '/../../assets/css/admin.css') ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css') : '1';
$apiJsVersion    = file_exists(__DIR__ . '/../../assets/js/api.js') ? (string) filemtime(__DIR__ . '/../../assets/js/api.js') : '1';
$authJsVersion   = file_exists(__DIR__ . '/../../assets/js/auth.js') ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js') : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Featured Offers</title>
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
                <a class="nav-link" href="<?= $basePath ?>/views/admin/delivery-persons.php">
                    <span class="nav-link__icon">D</span> Delivery
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/admins.php">
                    <span class="nav-link__icon">A</span> Admins
                </a>
                <a class="nav-link" href="<?= $basePath ?>/views/admin/hero-slides.php">
                    <span class="nav-link__icon">H</span> Hero Slides
                </a>
                <a class="nav-link is-active" href="<?= $basePath ?>/views/admin/featured-offers.php">
                    <span class="nav-link__icon">F</span> Featured Offers
                </a>
            </nav>
            <div class="sidebar__foot">
                <a id="viewOffersLink" href="<?= $basePath ?>/views/customer/offers.php" target="_blank" style="display:block;padding:10px 14px;background:#f5f5f5;border-radius:8px;margin-bottom:12px;text-decoration:none;color:#333;font-size:13px;text-align:center;">
                    View Offers Page &#8599;
                </a>
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
                    <h1>Featured Offers</h1>
                    <p class="topbar__sub">Curate products for the Special Offers page</p>
                </div>
                <button id="btnNewOffer" class="btn btn--primary">+ Add Featured Product</button>
            </header>

            <section class="toolbar">
                <div class="toolbar__left">
                    <button id="btnRefresh" class="btn btn--ghost">Refresh</button>
                </div>
                <div class="toolbar__right">
                    <span id="offerCount" class="muted">Loading...</span>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Image</th>
                            <th>Product</th>
                            <th style="width:120px;">Price / Discount</th>
                            <th style="width:100px;">Order</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:220px;"></th>
                        </tr>
                    </thead>
                    <tbody id="offerRows">
                        <tr><td colspan="6" class="muted">Loading...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Add Offer Modal -->
    <div id="offerModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">Add Featured Product</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="offerForm" class="modal__body form-grid">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Product ID <span style="color:#b00020">*</span></span>
                        <input type="number" id="fProductId" required min="1" placeholder="Enter product ID">
                        <small style="color:#666;display:block;margin-top:4px;">Find Product ID in the Products page</small>
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Sort Order</span>
                        <input type="number" id="fSortOrder" min="0" value="0">
                    </label>
                    <label class="field">
                        <span class="field__label">Status</span>
                        <select id="fStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                </div>

                <div id="productPreview" style="padding:16px;background:#f9f9f9;border-radius:8px;display:none;">
                    <div style="display:flex;gap:12px;align-items:center;">
                        <img id="previewImg" src="" style="width:60px;height:60px;object-fit:cover;border-radius:6px;display:none;">
                        <div>
                            <div id="previewName" style="font-weight:600;"></div>
                            <div id="previewPrice" style="color:#666;font-size:13px;"></div>
                        </div>
                    </div>
                </div>

                <div id="modalError" class="form-error" hidden></div>
            </form>
            <footer class="modal__footer">
                <button type="button" class="btn btn--ghost" data-close>Cancel</button>
                <button type="button" id="btnSave" class="btn btn--primary">Add to Featured</button>
            </footer>
        </div>
    </div>

    <!-- Replace Modal -->
    <div id="replaceModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2>Replace Product</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form class="modal__body form-grid">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">New Product ID <span style="color:#b00020">*</span></span>
                        <input type="number" id="fNewProductId" required min="1" placeholder="Enter new product ID">
                    </label>
                </div>
                <div id="replaceError" class="form-error" hidden></div>
            </form>
            <footer class="modal__footer">
                <button type="button" class="btn btn--ghost" data-close>Cancel</button>
                <button type="button" id="btnConfirmReplace" class="btn btn--primary">Replace</button>
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
    <script src="<?= $basePath ?>/assets/js/featured-offers-admin.js?v=<?= time() ?>"></script>
</body>
</html>
