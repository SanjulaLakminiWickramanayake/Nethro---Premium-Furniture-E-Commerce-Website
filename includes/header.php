<?php
require_once __DIR__ . '/db.php';

// Generate session ID for guests
if (!isset($_SESSION['user_id']) && !isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id();
}

$flash = getFlash();

$script = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($script, '/admin/') !== false) {
    $base = substr($script, 0, strpos($script, '/admin/'));
} else {
    $base = rtrim(dirname($script), '/');
}
$assetPathPrefix = ($base === '') ? '/' : $base . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Nethro Furniture</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $assetPathPrefix; ?>css/style.css">
    <?php if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false): ?>
    <link rel="stylesheet" href="<?php echo $assetPathPrefix; ?>css/admin.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Flash Messages -->
    <?php if ($flash): ?>
    <div class="flash-message flash-<?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo $assetPathPrefix; ?>index.php" class="logo">
                <span class="logo-icon"><i class="fas fa-couch"></i></span>
                <span class="logo-text">Nethro</span>
            </a>
            
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $assetPathPrefix; ?>index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo $assetPathPrefix; ?>products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Products</a></li>
                <li><a href="<?php echo $assetPathPrefix; ?>about.php">About</a></li>
                <li><a href="<?php echo $assetPathPrefix; ?>contact.php">Contact</a></li>
            </ul>

            <div class="nav-actions">
                <a href="<?php echo $assetPathPrefix; ?>cart.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount"><?php echo getCartCount(); ?></span>
                </a>
                
                <?php if ($current_user): ?>
                <div class="user-dropdown">
                    <button class="user-btn">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($current_user['first_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?php echo $assetPathPrefix; ?>profile.php"><i class="fas fa-id-card"></i> Profile</a>
                        <a href="<?php echo $assetPathPrefix; ?>orders.php"><i class="fas fa-box"></i> My Orders</a>
                        <a href="<?php echo $assetPathPrefix; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?php echo $assetPathPrefix; ?>login.php" class="btn btn-outline">Login</a>
                    <a href="<?php echo $assetPathPrefix; ?>register.php" class="btn btn-primary">Register</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="nav-spacer"></div>