<?php
$pageTitle = 'Checkout';
require_once 'includes/db.php';

// Require login
if (!$current_user) {
    setFlash('warning', 'Please sign in to complete your purchase');
    redirect('login.php?redirect=checkout.php');
}

// Get cart items
$cartItems = fetchAll("
    SELECT c.*, p.name, p.slug, p.price, p.image, p.stock_quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
", "i", [$_SESSION['user_id']]);

if (empty($cartItems)) {
    setFlash('warning', 'Your cart is empty');
    redirect('products.php');
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$discount = 0;
if (isReturningCustomer($current_user['id'])) {
    $discount = $subtotal * (getDiscountPercent() / 100);
}

$total = $subtotal - $discount;

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'full';
    $cardHolder = trim($_POST['card_holder'] ?? '');
    $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expiry = $_POST['expiry'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $shippingCity = trim($_POST['shipping_city'] ?? '');
    $shippingPhone = trim($_POST['shipping_phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    $errors = [];
    if (empty($cardHolder)) $errors[] = 'Card holder name is required';
    if (empty($cardNumber) || strlen($cardNumber) < 16) $errors[] = 'Valid card number is required';
    if (empty($expiry)) $errors[] = 'Expiry date is required';
    if (empty($cvv) || strlen($cvv) < 3) $errors[] = 'CVV is required';
    if (empty($shippingAddress)) $errors[] = 'Shipping address is required';
    if (empty($shippingCity)) $errors[] = 'City is required';
    if (empty($shippingPhone)) $errors[] = 'Phone number is required';
    
    if (empty($errors)) {
        // Generate order number
        $orderNumber = 'NTR-' . strtoupper(uniqid());
        
        // Create order
        $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, payment_method, shipping_address, shipping_city, shipping_phone, notes) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $orderStmt = executeQuery($orderSql, "isdddsssss", [
            $_SESSION['user_id'], $orderNumber, $subtotal, $discount, $total, 
            $paymentMethod, $shippingAddress, $shippingCity, $shippingPhone, $notes
        ]);
        
        $orderId = $orderStmt->insert_id;
        
        // Add order items and update stock
        foreach ($cartItems as $item) {
            executeQuery("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)",
                "iiidd", [$orderId, $item['product_id'], $item['quantity'], $item['price'], $item['price'] * $item['quantity']]);
            
            executeQuery("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?", "ii", [$item['quantity'], $item['product_id']]);
        }
        
        // Process payment
        $paymentSql = "INSERT INTO payments (order_id, user_id, amount, payment_type, card_holder_name, card_last_four, transaction_id, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, 'completed')";
        executeQuery($paymentSql, "iidssss", [
            $orderId, $_SESSION['user_id'], $total, 
            $paymentMethod === 'installment' ? 'down_payment' : 'full',
            $cardHolder, substr($cardNumber, -4), 'TXN-' . strtoupper(uniqid())
        ]);
        
        // Handle installments
        if ($paymentMethod === 'installment') {
            $installmentAmount = ($total * 0.75) / 3; // 25% down, rest in 3 installments
            $dueDate = date('Y-m-d', strtotime('+1 month'));
            
            for ($i = 1; $i <= 3; $i++) {
                executeQuery("INSERT INTO installments (order_id, installment_number, amount, due_date) VALUES (?, ?, ?, ?)",
                    "iids", [$orderId, $i, $installmentAmount, $dueDate]);
                $dueDate = date('Y-m-d', strtotime($dueDate . ' +1 month'));
            }
        }
        
        // Clear cart
        executeQuery("DELETE FROM cart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
        
        // Send notification to admin
        executeQuery("INSERT INTO notifications (type, title, message, related_id) VALUES (?, ?, ?, ?)",
            "sssi", ['system', 'New Order', "Order $orderNumber placed for " . formatPrice($total), $orderId]);
        
        // Simulate email
        @mail($current_user['email'], 'Order Confirmation - ' . $orderNumber, 
            "Thank you for your order! Your order number is $orderNumber. Total: " . formatPrice($total));
        
        setFlash('success', 'Order placed successfully! Order #: ' . $orderNumber);
        redirect('orders.php');
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="section-header">
        <span class="section-subtitle">Almost There</span>
        <h2 class="section-title">Checkout</h2>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="flash-message flash-error" style="position: static; margin-bottom: 24px; animation: none;">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" data-validate>
        <div class="cart-container">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 24px;">
                    <i class="fas fa-truck" style="color: var(--accent);"></i> Shipping Information
                </h3>
                
                <div class="form-group">
                    <label class="form-label">Full Address *</label>
                    <textarea name="shipping_address" class="form-control" placeholder="Street address, apartment, suite, etc." required><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">City *</label>
                        <input type="text" name="shipping_city" class="form-control" placeholder="City" required value="<?php echo htmlspecialchars($current_user['city'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="shipping_phone" class="form-control" placeholder="+1 (555) 123-4567" required value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Order Notes (Optional)</label>
                    <textarea name="notes" class="form-control" placeholder="Special instructions for delivery..."></textarea>
                </div>
                
                <h3 style="font-family: var(--font-display); margin: 40px 0 24px; font-size: 24px;">
                    <i class="fas fa-credit-card" style="color: var(--accent);"></i> Payment Method
                </h3>
                
                <div class="payment-options">
                    <div class="payment-option selected" data-value="full">
                        <input type="radio" name="payment_method" value="full" checked>
                        <i class="fas fa-money-bill-wave"></i>
                        <h4>Full Payment</h4>
                        <p style="font-size: 12px; color: var(--gray-600);">Pay <?php echo formatPrice($total); ?> now</p>
                    </div>
                    <div class="payment-option" data-value="installment">
                        <input type="radio" name="payment_method" value="installment">
                        <i class="fas fa-calendar-alt"></i>
                        <h4>Installments</h4>
                        <p style="font-size: 12px; color: var(--gray-600);">25% now, 3 monthly payments</p>
                    </div>
                </div>
                
                <div id="installmentDetails" style="display: none; padding: 20px; background: var(--gray-100); border-radius: var(--radius-md); margin-bottom: 24px;">
                    <h4 style="margin-bottom: 12px;">Installment Plan</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 14px;">
                        <div><strong>Down Payment (25%):</strong> <?php echo formatPrice($total * 0.25); ?></div>
                        <div><strong>Monthly Payment:</strong> <?php echo formatPrice(($total * 0.75) / 3); ?></div>
                        <div><strong>Number of Payments:</strong> 3 months</div>
                        <div><strong>First Due Date:</strong> <?php echo date('M d, Y', strtotime('+1 month')); ?></div>
                    </div>
                </div>
                
                <h3 style="font-family: var(--font-display); margin: 40px 0 24px; font-size: 24px;">
                    <i class="fas fa-lock" style="color: var(--accent);"></i> Card Details
                </h3>
                
                <div class="form-group">
                    <label class="form-label">Card Holder Name *</label>
                    <input type="text" name="card_holder" class="form-control" placeholder="Name on card" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Card Number *</label>
                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required maxlength="19" oninput="formatCardNumber(this)">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Expiry Date *</label>
                        <input type="text" name="expiry" class="form-control" placeholder="MM/YY" required maxlength="5" oninput="formatExpiry(this)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV *</label>
                        <input type="text" name="cvv" class="form-control" placeholder="123" required maxlength="4" pattern="\d{3,4}">
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="cart-summary">
                <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 20px;">Order Summary</h3>
                
                <?php foreach ($cartItems as $item): ?>
                <div style="display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-200);">
                    <img src="<?php echo $item['image'] ? 'images/products/' . $item['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=100'; ?>" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 14px;"><?php echo $item['name']; ?></div>
                        <div style="font-size: 13px; color: var(--gray-600);">Qty: <?php echo $item['quantity']; ?></div>
                    </div>
                    <div style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($subtotal); ?></span>
                </div>
                
                <?php if ($discount > 0): ?>
                <div class="summary-row" style="color: var(--success);">
                    <span>Discount</span>
                    <span>-<?php echo formatPrice($discount); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
                    <i class="fas fa-lock"></i> Complete Purchase
                </button>
                
                <p style="text-align: center; margin-top: 16px; font-size: 12px; color: var(--gray-500);">
                    <i class="fas fa-shield-alt"></i> Secure SSL Encrypted Transaction
                </p>
            </div>
        </div>
    </form>
</section>

<script>
function formatCardNumber(input) {
    let value = input.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    const parts = value.match(/.{1,4}/g);
    input.value = parts ? parts.join(' ') : '';
}

function formatExpiry(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    input.value = value;
}
</script>

<?php require_once 'includes/footer.php'; ?>