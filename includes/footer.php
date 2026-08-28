    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <span class="logo-icon"><i class="fas fa-couch"></i></span>
                        <span class="logo-text">Nethro</span>
                    </a>
                    <p>Crafting comfort and elegance for your home since 2010. Premium furniture for modern living.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo $assetPathPrefix; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $assetPathPrefix; ?>products.php">Products</a></li>
                        <li><a href="<?php echo $assetPathPrefix; ?>about.php">About Us</a></li>
                        <li><a href="<?php echo $assetPathPrefix; ?>contact.php">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="<?php echo $assetPathPrefix; ?>profile.php">My Account</a></li>
                        <li><a href="<?php echo $assetPathPrefix; ?>orders.php">Order Tracking</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Furniture Lane, Colombo</p>
                    <p><i class="fas fa-phone"></i> +94 123 456 7895</p>
                    <p><i class="fas fa-envelope"></i> hello@nethro.com</p>
                    <p><i class="fas fa-clock"></i> Mon - Sat: 9:00 - 18:00</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Nethro Furniture. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <?php if (!isset($assetPathPrefix)) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($script, '/admin/') !== false) {
            $base = substr($script, 0, strpos($script, '/admin/'));
        } else {
            $base = rtrim(dirname($script), '/');
        }
        $assetPathPrefix = ($base === '') ? '/' : $base . '/';
    } ?>
    <script src="<?php echo $assetPathPrefix; ?>js/script.js"></script>
</body>
</html>