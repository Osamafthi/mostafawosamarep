<footer class="site-footer">
    <div class="container">
        <div class="site-footer__grid">
            <div>
                <h4>Mostafa &amp; Osama</h4>
                <p style="margin: 0; line-height: 1.6;">A modern storefront experience — curated products, fast checkout, and zero friction shopping.</p>
            </div>
            <div>
                <h4>Shop</h4>
                <ul>
                    <li><a href="<?= htmlspecialchars($basePath) ?>/">Home</a></li>
                    <li><a href="<?= htmlspecialchars($basePath) ?>/views/customer/search.php">All Products</a></li>
                    <li><a href="<?= htmlspecialchars($basePath) ?>/views/customer/cart.php">Cart</a></li>
                </ul>
            </div>
            <div>
                <h4>Help</h4>
                <ul>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Shipping</a></li>
                    <li><a href="#">Returns</a></li>
                </ul>
            </div>
            <div>
                <h4>About</h4>
                <ul>
                    <li><a href="#">Our Story</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Privacy</a></li>
                </ul>
            </div>
        </div>
        <div class="site-footer__copy">
            &copy; <?= date('Y') ?> Mostafa &amp; Osama. All rights reserved.
        </div>
    </div>
</footer>
