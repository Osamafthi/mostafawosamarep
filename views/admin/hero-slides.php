<?php
$basePath = '/mostafawosama';
$active   = 'hero-slides';
$adminCssVersion = file_exists(__DIR__ . '/../../assets/css/admin.css') ? (string) filemtime(__DIR__ . '/../../assets/css/admin.css') : '1';
$apiJsVersion    = file_exists(__DIR__ . '/../../assets/js/api.js') ? (string) filemtime(__DIR__ . '/../../assets/js/api.js') : '1';
$authJsVersion   = file_exists(__DIR__ . '/../../assets/js/auth.js') ? (string) filemtime(__DIR__ . '/../../assets/js/auth.js') : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Hero Slides</title>
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
                <a class="nav-link <?= $active === 'hero-slides' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/hero-slides.php">
                    <span class="nav-link__icon">H</span>
                    Hero Slides
                </a>
                <a class="nav-link <?= $active === 'featured-offers' ? 'is-active' : '' ?>"
                   href="<?= $basePath ?>/views/admin/featured-offers.php">
                    <span class="nav-link__icon">F</span>
                    Featured Offers
                </a>
            </nav>
            <div class="sidebar__foot">
                <a href="<?= $basePath ?>/views/customer/home.php" target="_blank" style="display:block;padding:10px 14px;background:#f5f5f5;border-radius:8px;margin-bottom:12px;text-decoration:none;color:#333;font-size:13px;text-align:center;">
                    View Store &#8599;
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
                    <h1>Hero Slides</h1>
                    <p class="topbar__sub">Manage homepage banner slides</p>
                </div>
                <button id="btnNewSlide" class="btn btn--primary">+ New Slide</button>
            </header>

            <section class="toolbar">
                <div class="toolbar__left">
                    <button id="btnRefresh" class="btn btn--ghost">Refresh</button>
                </div>
                <div class="toolbar__right">
                    <span id="slideCount" class="muted">Loading...</span>
                </div>
            </section>

            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Preview</th>
                            <th>Title / Subtitle</th>
                            <th>Link</th>
                            <th style="width:100px;">Order</th>
                            <th style="width:100px;">Status</th>
                            <th style="width:180px;"></th>
                        </tr>
                    </thead>
                    <tbody id="slideRows">
                        <tr><td colspan="6" class="muted">Loading...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Slide modal -->
    <div id="slideModal" class="modal" hidden>
        <div class="modal__backdrop" data-close></div>
        <div class="modal__dialog">
            <header class="modal__header">
                <h2 id="modalTitle">New Hero Slide</h2>
                <button class="icon-btn" data-close aria-label="Close">&times;</button>
            </header>
            <form id="slideForm" class="modal__body form-grid">
                <input type="hidden" id="fId">
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Title <span style="color:#b00020">*</span></span>
                        <input type="text" id="fTitle" required maxlength="200" placeholder="e.g., Summer Sale">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Subtitle / Kicker</span>
                        <input type="text" id="fSubtitle" maxlength="255" placeholder="e.g., Limited Time Offer">
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Description</span>
                        <textarea id="fDescription" rows="2" placeholder="Brief description for the slide"></textarea>
                    </label>
                </div>
                <div class="form-row form-row--full">
                    <label class="field">
                        <span class="field__label">Image URL <span style="color:#b00020">*</span></span>
                        <input type="url" id="fImageUrl" required maxlength="500" placeholder="https://example.com/image.jpg">
                    </label>
                </div>
                <div class="form-row">
                    <label class="field">
                        <span class="field__label">Link URL</span>
                        <input type="text" id="fLinkUrl" maxlength="500" value="/views/customer/search.php">
                    </label>
                    <label class="field">
                        <span class="field__label">CTA Text</span>
                        <input type="text" id="fCtaText" maxlength="50" value="Shop now">
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
                <div class="form-row form-row--full">
                    <div id="imgPreview" style="width:100%;height:150px;background:#f5f5f5;border-radius:8px;background-size:cover;background-position:center;display:none;"></div>
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
    <script src="<?= $basePath ?>/assets/js/admin-preview-link.js?v=<?= $apiJsVersion ?>"></script>
    <script src="<?= $basePath ?>/assets/js/hero-slides.js?v=<?= time() ?>"></script>
</body>
</html>
