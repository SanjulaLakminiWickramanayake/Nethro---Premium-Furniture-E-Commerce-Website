<?php
$pageTitle = 'Shopping Cart';
require_once 'includes/db.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    
    // AJAX request handling
    if ($action === 'add' && isset($_POST['product_id'])) {
        header('Content-Type: application/json');
        $productId = intval($_POST['product_id']);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        
        // Check stock
        $product = fetchOne("SELECT stock_quantity FROM products WHERE id = ?", "i", [$productId]);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        if (isset($_SESSION['user_id'])) {
            $existing = fetchOne("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$_SESSION['user_id'], $productId]);
            if ($existing) {
                $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", "ii", [$newQty, $existing['id']]);
            } else {
                executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", "iii", [$_SESSION['user_id'], $productId, $quantity]);
            }
        } else {
            $sessionId = $_SESSION['cart_session'] ?? session_id();
            $existing = fetchOne("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?", "si", [$sessionId, $productId]);
            if ($existing) {
                $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", "ii", [$newQty, $existing['id']]);
            } else {
                executeQuery("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)", "sii", [$sessionId, $productId, $quantity]);
            }
        }
        
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        exit;
    }
    
    // Update quantity
    if ($action === 'update' && isset($_POST['cart_id'], $_POST['quantity'])) {
        $cartId = intval($_POST['cart_id']);
        $quantity = max(1, intval($_POST['quantity']));
        
        if (isset($_SESSION['user_id'])) {
            executeQuery("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", "iii", [$quantity, $cartId, $_SESSION['user_id']]);
        } else {
            executeQuery("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?", "iis", [$quantity, $cartId, $_SESSION['cart_session']]);
        }
        redirect('cart.php');
    }
    
    // Remove item
    if ($action === 'remove' && isset($_POST['cart_id'])) {
        $cartId = intval($_POST['cart_id']);
        if (isset($_SESSION['user_id'])) {
            executeQuery("DELETE FROM cart WHERE id = ? AND user_id = ?", "ii", [$cartId, $_SESSION['user_id']]);
        } else {
            executeQuery("DELETE FROM cart WHERE id = ? AND session_id = ?", "is", [$cartId, $_SESSION['cart_session']]);
        }
        redirect('cart.php');
    }
}

// Get cart items
if (isset($_SESSION['user_id'])) {
    $cartItems = fetchAll("
        SELECT c.*, p.name, p.slug, p.price, p.image, p.stock_quantity, p.status 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ", "i", [$_SESSION['user_id']]);
} else {
    $sessionId = $_SESSION['cart_session'] ?? session_id();
    $cartItems = fetchAll("
        SELECT c.*, p.name, p.slug, p.price, p.image, p.stock_quantity, p.status 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.session_id = ?
    ", "s", [$sessionId]);
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$discount = 0;
if ($current_user && isReturningCustomer($current_user['id'])) {
    $discount = $subtotal * (getDiscountPercent() / 100);
}

$total = $subtotal - $discount;

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="section-header">
        <span class="section-subtitle">Your Selection</span>
        <h2 class="section-title">Shopping Cart</h2>
    </div>
    
    <?php if (empty($cartItems)): ?>
    <div style="text-align: center; padding: 80px 24px; background: white; border-radius: var(--radius-lg);">
        <i class="fas fa-shopping-cart" style="font-size: 64px; color: var(--gray-300); margin-bottom: 24px;"></i>
        <h3 style="font-family: var(--font-display); color: var(--gray-700); margin-bottom: 16px;">Your Cart is Empty</h3>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Looks like you haven't added any items yet.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="cart-container">
        <!-- Cart Items -->
        <div class="cart-items">
            <?php foreach ($cartItems as $item): 
                $itemTotal = $item['price'] * $item['quantity'];
                $isAvailable = $item['stock_quantity'] > 0 && $item['status'] === 'active';
            ?>
            <div class="cart-item" style="opacity: <?php echo $isAvailable ? '1' : '0.6'; ?>">
                <img src="<?php echo $item['image'] ? 'images/products/' . $item['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=200'; ?>" 
                     alt="<?php echo $item['name']; ?>" class="cart-item-image">
                
                <div class="cart-item-details">
                    <h3><a href="product_details.php?slug=<?php echo $item['slug']; ?>"><?php echo $item['name']; ?></a></h3>
                    <p class="cart-item-price"><?php echo formatPrice($item['price']); ?></p>
                    <?php if (!$isAvailable): ?>
                    <p style="color: var(--danger); font-size: 14px;"><i class="fas fa-exclamation-circle"></i> Currently unavailable</p>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; align-items: center; gap: 16px;">
                    <form method="POST" action="?action=update" style="display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>" class="qty-btn" <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>-</button>
                        <span style="font-weight: 600; min-width: 30px; text-align: center;"><?php echo $item['quantity']; ?></span>
                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn" <?php echo $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : ''; ?>>+</button>
                    </form>
                    
                    <div style="text-align: right; min-width: 80px;">
                        <div style="font-weight: 700;"><?php echo formatPrice($itemTotal); ?></div>
                    </div>
                    
                    <form method="POST" action="?action=remove" onsubmit="return confirmDelete('Remove this item from cart?')">
                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="action-btn" style="color: var(--danger);" title="Remove">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Cart Summary -->
        <div class="cart-summary">
            <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 20px;">Order Summary</h3>
            
            <div class="summary-row">
                <span>Subtotal</span>
                <span><?php echo formatPrice($subtotal); ?></span>
            </div>
            
            <?php if ($discount > 0): ?>
            <div class="summary-row" style="color: var(--success);">
                <span><i class="fas fa-tag"></i> Returning Customer Discount (<?php echo getDiscountPercent(); ?>%)</span>
                <span>-<?php echo formatPrice($discount); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="summary-row">
                <span>Shipping</span>
                <span style="color: var(--success);">Free</span>
            </div>
            
            <div class="summary-row total">
                <span>Total</span>
                <span><?php echo formatPrice($total); ?></span>
            </div>
            
            <?php if ($current_user): ?>
            <a href="checkout.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
                Proceed to Checkout
            </a>
            <?php else: ?>
            <div style="margin-top: 24px; padding: 20px; background: var(--gray-100); border-radius: var(--radius-md); text-align: center;">
                <p style="margin-bottom: 16px; color: var(--gray-600);">Please sign in to complete your purchase</p>
                <a href="login.php?redirect=cart.php" class="btn btn-primary" style="width: 100%;">Sign In to Checkout</a>
            </div>
            <?php endif; ?>
            
            <a href="products.php" class="btn btn-outline" style="width: 100%; margin-top: 12px;">
                Continue Shopping
            </a>
        </div>
    </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>