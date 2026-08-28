<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$orderId = intval($_GET['id'] ?? 0);
$order = fetchOne("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone as user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
", "i", [$orderId]);

if (!$order) {
    setFlash('error', 'Order not found');
    redirect('dashboard.php');
}

$orderItems = fetchAll("
    SELECT oi.*, p.name, p.image, p.slug
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
", "i", [$orderId]);

$payments = fetchAll("SELECT * FROM payments WHERE order_id = ? ORDER BY paid_at DESC", "i", [$orderId]);
$installments = fetchAll("SELECT * FROM installments WHERE order_id = ? ORDER BY installment_number", "i", [$orderId]);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === POST && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        executeQuery("UPDATE orders SET status = ? WHERE id = ?", "si", [$newStatus, $orderId]);
        setFlash('success', 'Order status updated');
        redirect('order_details.php?id=' . $orderId);
    }
}

$pageTitle = 'Order ' . $order['order_number'];

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h2><i class="fas fa-couch"></i> Nethro Admin</h2>
        </div>
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
            <li><a href="manage_products.php"><i class="fas fa-box"></i> Manage Products</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <div style="margin-bottom: 40px;">
            <a href="dashboard.php" class="btn btn-sm btn-outline" style="margin-bottom: 16px;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <h1 style="font-family: var(--font-display); font-size: 32px;">Order <?php echo $order['order_number']; ?></h1>
            <p style="color: var(--gray-600);">Placed on <?php echo date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- Order Items -->
            <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
                <h3 style="font-family: var(--font-display); margin-bottom: 24px;">Order Items</h3>
                
                <?php foreach ($orderItems as $item): ?>
                <div style="display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--gray-200);">
                    <img src="<?php echo $item['image'] ? '../images/products/' . $item['image'] : 'https://via.placeholder.com/80'; ?>" 
                         style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md);">
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 4px;"><?php echo $item['name']; ?></h4>
                        <p style="color: var(--gray-600); font-size: 14px;">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['unit_price']); ?></p>
                    </div>
                    <div style="font-weight: 700;"><?php echo formatPrice($item['total_price']); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Subtotal</span>
                        <span><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--success);">
                        <span>Discount</span>
                        <span>-<?php echo formatPrice($order['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; margin-top: 16px;">
                        <span>Total</span>
                        <span><?php echo formatPrice($order['final_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Order Info -->
            <div>
                <!-- Status Card -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <h4 style="margin-bottom: 16px;">Order Status</h4>
                    <div style="margin-bottom: 16px;">
                        <span class="status-badge status-<?php echo $order['status']; ?>" style="font-size: 14px; padding: 8px 16px;">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                    
                    <form method="POST" action="">
                        <select name="status" class="form-control" style="margin-bottom: 12px;">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary btn-sm" style="width: 100%;">Update Status</button>
                    </form>
                </div>
                
                <!-- Customer Info -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <h4 style="margin-bottom: 16px;">Customer</h4>
                    <p style="margin-bottom: 8px;"><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                    <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;"><i class="fas fa-envelope"></i> <?php echo $order['email']; ?></p>
                    <p style="color: var(--gray-600); font-size: 14px;"><i class="fas fa-phone"></i> <?php echo $order['shipping_phone']; ?></p>
                </div>
                
                <!-- Shipping Info -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <h4 style="margin-bottom: 16px;">Shipping Address</h4>
                    <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?><br>
                        <?php echo htmlspecialchars($order['shipping_city']); ?>
                    </p>
                </div>
                
                <!-- Payment Info -->
                <?php if (!empty($payments)): ?>
                <div style="background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
                    <h4 style="margin-bottom: 16px;">Payment</h4>
                    <?php foreach ($payments as $payment): ?>
                    <div style="font-size: 14px; margin-bottom: 8px;">
                        <span class="status-badge status-delivered" style="font-size: 11px;"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_type'])); ?></span><br>
                        <strong><?php echo formatPrice($payment['amount']); ?></strong> on <?php echo date('M d, Y', strtotime($payment['paid_at'])); ?><br>
                        <small style="color: var(--gray-600);">Card: **** <?php echo $payment['card_last_four']; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Installments -->
                <?php if (!empty($installments)): ?>
                <div style="background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
                    <h4 style="margin-bottom: 16px;">Installment Plan</h4>
                    <?php foreach ($installments as $inst): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--gray-200); font-size: 14px;">
                        <div>
                            <div>#<?php echo $inst['installment_number']; ?></div>
                            <small style="color: var(--gray-600);">Due: <?php echo date('M d, Y', strtotime($inst['due_date'])); ?></small>
                        </div>
                        <div style="text-align: right;">
                            <div><strong><?php echo formatPrice($inst['amount']); ?></strong></div>
                            <span class="status-badge status-<?php echo $inst['status']; ?>" style="font-size: 10px;"><?php echo ucfirst($inst['status']); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>