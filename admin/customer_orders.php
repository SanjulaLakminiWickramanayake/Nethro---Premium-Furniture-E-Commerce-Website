<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$customerId = intval($_GET['id'] ?? 0);
$customer = fetchOne("SELECT * FROM users WHERE id = ?", "i", [$customerId]);

if (!$customer) {
    setFlash('error', 'Customer not found');
    redirect('customers.php');
}

$pageTitle = 'Customer Orders - ' . $customer['first_name'] . ' ' . $customer['last_name'];

$orders = fetchAll("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.quantity) as total_items
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
", "i", [$customerId]);

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
            <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <div style="margin-bottom: 40px;">
            <a href="customers.php" class="btn btn-sm btn-outline" style="margin-bottom: 16px;"><i class="fas fa-arrow-left"></i> Back to Customers</a>
            <h1 style="font-family: var(--font-display); font-size: 32px;"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h1>
            <p style="color: var(--gray-600);"><?php echo htmlspecialchars($customer['email']); ?> | <?php echo htmlspecialchars($customer['phone'] ?? 'No phone'); ?></p>
        </div>
        
        <div style="background: white; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm);">
            <h3 style="font-family: var(--font-display); margin-bottom: 24px; font-size: 20px;">Order History</h3>
            
            <?php if (empty($orders)): ?>
            <p style="color: var(--gray-600); text-align: center; padding: 40px;">No orders found for this customer.</p>
            <?php else: ?>
            <div class="data-table" style="box-shadow: none;">
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
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>