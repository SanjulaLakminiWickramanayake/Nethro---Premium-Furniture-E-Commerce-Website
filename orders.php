<?php
$pageTitle = 'My Orders';
require_once 'includes/db.php';

if (!$current_user) {
    setFlash('warning', 'Please sign in to view your orders');
    redirect('login.php?redirect=orders.php');
}

$orders = fetchAll("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
", "i", [$_SESSION['user_id']]);

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="section-header">
        <span class="section-subtitle">Order History</span>
        <h2 class="section-title">My Orders</h2>
    </div>
    
    <?php if (empty($orders)): ?>
    <div style="text-align: center; padding: 80px 24px; background: white; border-radius: var(--radius-lg);">
        <i class="fas fa-box-open" style="font-size: 64px; color: var(--gray-300); margin-bottom: 24px;"></i>
        <h3 style="font-family: var(--font-display); color: var(--gray-700); margin-bottom: 16px;">No Orders Yet</h3>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Start shopping to see your orders here.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                    <td><?php echo $order['total_items']; ?> items</td>
                    <td><?php echo formatPrice($order['final_amount']); ?></td>
                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    <td><span class="status-badge status-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                    <td><a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>