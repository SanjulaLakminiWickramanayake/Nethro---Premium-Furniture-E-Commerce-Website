<?php
require_once 'includes/db.php';

if (!$current_user) {
    setFlash('warning', 'Please sign in to view order details');
    redirect('login.php');
}

$orderId = intval($_GET['id'] ?? 0);
$order = fetchOne("
    SELECT o.* 
    FROM orders o
    WHERE o.id = ? AND o.user_id = ?
", "ii", [$orderId, $_SESSION['user_id']]);

if (!$order) {
    setFlash('error', 'Order not found');
    redirect('orders.php');
}

$orderItems = fetchAll("
    SELECT oi.*, p.name, p.image, p.slug
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
", "i", [$orderId]);

$payments = fetchAll("SELECT * FROM payments WHERE order_id = ? ORDER BY paid_at DESC", "i", [$orderId]);
$installments = fetchAll("SELECT * FROM installments WHERE order_id = ? ORDER BY installment_number", "i", [$orderId]);

$pageTitle = 'Order ' . $order['order_number'];
require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div style="max-width: 900px; margin: 0 auto;">
        <a href="orders.php" class="btn btn-sm btn-outline" style="margin-bottom: 24px;"><i class="fas fa-arrow-left"></i> Back to Orders</a>
        
        <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 style="font-family: var(--font-display); font-size: 28px; margin-bottom: 8px;">Order <?php echo $order['order_number']; ?></h1>
                    <p style="color: var(--gray-600);">Placed on <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                </div>
                <span class="status-badge status-<?php echo $order['status']; ?>" style="font-size: 14px; padding: 8px 16px;">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
            
            <!-- Order Items -->
            <h3 style="font-family: var(--font-display); margin-bottom: 16px; font-size: 20px;">Items</h3>
            <?php foreach ($orderItems as $item): ?>
            <div style="display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--gray-200);">
                <img src="<?php echo $item['image'] ? 'images/products/' . $item['image'] : 'https://via.placeholder.com/80'; ?>" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md);">
                <div style="flex: 1;">
                    <h4 style="margin-bottom: 4px;"><a href="product_details.php?slug=<?php echo $item['slug']; ?>"><?php echo $item['name']; ?></a></h4>
                    <p style="color: var(--gray-600); font-size: 14px;">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['unit_price']); ?></p>
                </div>
                <div style="font-weight: 700;"><?php echo formatPrice($item['total_price']); ?></div>
            </div>
            <?php endforeach; ?>
            
            <!-- Totals -->
            <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid var(--primary);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal</span>
                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: var(--success);">
                    <span>Returning Customer Discount</span>
                    <span>-<?php echo formatPrice($order['discount_amount']); ?></span>
                </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; margin-top: 16px;">
                    <span>Total</span>
                    <span><?php echo formatPrice($order['final_amount']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Shipping Info -->
        <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <h3 style="font-family: var(--font-display); margin-bottom: 16px; font-size: 20px;">Shipping Details</h3>
            <div style="color: var(--gray-600); line-height: 1.8;">
                <p><strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><strong>City:</strong> <?php echo htmlspecialchars($order['shipping_city']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                <?php if ($order['notes']): ?>
                <p><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Payment Info -->
        <?php if (!empty($payments)): ?>
        <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); margin-bottom: 24px;">
            <h3 style="font-family: var(--font-display); margin-bottom: 16px; font-size: 20px;">Payment Information</h3>
            <?php foreach ($payments as $payment): ?>
            <div style="padding: 16px; background: var(--gray-100); border-radius: var(--radius-md); margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="text-transform: capitalize;"><?php echo str_replace('_', ' ', $payment['payment_type']); ?></span>
                    <strong><?php echo formatPrice($payment['amount']); ?></strong>
                </div>
                <div style="font-size: 14px; color: var(--gray-600);">
                    <p>Card: **** <?php echo $payment['card_last_four']; ?> (<?php echo htmlspecialchars($payment['card_holder_name']); ?>)</p>
                    <p>Date: <?php echo date('F d, Y H:i', strtotime($payment['paid_at'])); ?></p>
                    <p>Transaction: <?php echo $payment['transaction_id']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Installments -->
        <?php if (!empty($installments)): ?>
        <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
            <h3 style="font-family: var(--font-display); margin-bottom: 16px; font-size: 20px;">Installment Schedule</h3>
            <div style="display: grid; gap: 12px;">
                <?php foreach ($installments as $inst): 
                    $isOverdue = $inst['status'] === 'pending' && strtotime($inst['due_date']) < time();
                ?>
                <div style="padding: 20px; border-radius: var(--radius-md); border: 2px solid <?php echo $isOverdue ? 'var(--danger)' : ($inst['status'] === 'paid' ? 'var(--success)' : 'var(--gray-300)'); ?>; background: <?php echo $isOverdue ? 'rgba(220, 53, 69, 0.05)' : 'white'; ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="margin-bottom: 4px;">Installment #<?php echo $inst['installment_number']; ?></h4>
                            <p style="font-size: 14px; color: var(--gray-600);">
                                Due: <?php echo date('F d, Y', strtotime($inst['due_date'])); ?>
                                <?php if ($isOverdue): ?>
                                <span style="color: var(--danger); font-weight: 600;">(Overdue)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 20px; font-weight: 700; margin-bottom: 4px;"><?php echo formatPrice($inst['amount']); ?></div>
                            <span class="status-badge status-<?php echo $inst['status']; ?>" style="font-size: 11px;">
                                <?php echo ucfirst($inst['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>