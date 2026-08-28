<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Dashboard';

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';

$stats = [
    'sales' => fetchOne("SELECT COALESCE(SUM(final_amount), 0) AS total FROM orders WHERE status != 'cancelled'")['total'],
    'pending' => fetchOne("SELECT COUNT(*) AS count FROM orders WHERE status = 'pending'")['count'],
    'low_stock' => fetchOne("SELECT COUNT(*) AS count FROM products WHERE stock_quantity <= 5")['count'],
    'customers' => fetchOne("SELECT COUNT(*) AS count FROM users")['count'],
];

$recentOrders = fetchAll("SELECT o.id, o.order_number, o.final_amount, o.status, o.created_at,
    u.first_name, u.last_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 8");

$monthlySales = fetchAll("SELECT DATE_FORMAT(created_at, '%b') AS month,
    COALESCE(SUM(final_amount), 0) AS total
    FROM orders
    WHERE status != 'cancelled'
      AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
    ORDER BY YEAR(created_at), MONTH(created_at)");

$topProducts = fetchAll("SELECT p.name, COALESCE(SUM(oi.quantity), 0) AS quantity
    FROM order_items oi
    INNER JOIN products p ON p.id = oi.product_id
    INNER JOIN orders o ON o.id = oi.order_id
    WHERE o.status != 'cancelled'
    GROUP BY p.id, p.name
    ORDER BY quantity DESC
    LIMIT 5");

$formatStatus = static function (string $status): array {
    if (in_array($status, ['shipped', 'delivered', 'processing'], true)) {
        return ['label' => 'Completed', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'];
    }
    if ($status === 'cancelled') {
        return ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-700 ring-red-600/20'];
    }
    return ['label' => 'Pending', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'];
};

$monthlySalesJson = json_encode([
    'labels' => array_column($monthlySales, 'month'),
    'values' => array_map('floatval', array_column($monthlySales, 'total')),
]);
$topProductsJson = json_encode([
    'labels' => array_column($topProducts, 'name'),
    'values' => array_map('intval', array_column($topProducts, 'quantity')),
]);
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Overview</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Dashboard</h1>
        </div>
        <div class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm">
            <?php echo date('l, F j, Y'); ?>
        </div>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600"><i data-lucide="badge-dollar-sign" class="h-5 w-5"></i></div>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Revenue</span>
            </div>
            <p class="mt-5 text-sm text-slate-500">Total Sales</p>
            <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo formatPrice($stats['sales']); ?></p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600"><i data-lucide="clock-3" class="h-5 w-5"></i></div>
                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Action</span>
            </div>
            <p class="mt-5 text-sm text-slate-500">Pending Orders</p>
            <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo number_format($stats['pending']); ?></p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-rose-600"><i data-lucide="triangle-alert" class="h-5 w-5"></i></div>
                <span class="rounded-full bg-rose-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-rose-600">Inventory</span>
            </div>
            <p class="mt-5 text-sm text-slate-500">Low Stock</p>
            <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo number_format($stats['low_stock']); ?></p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"><i data-lucide="users" class="h-5 w-5"></i></div>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Users</span>
            </div>
            <p class="mt-5 text-sm text-slate-500">Total Customers</p>
            <p class="mt-1 text-2xl font-bold text-slate-900"><?php echo number_format($stats['customers']); ?></p>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Monthly Sales</h2>
                <p class="text-sm text-slate-500">Last 6 months</p>
            </div>
            <div class="h-64"><canvas id="monthlySalesChart"></canvas></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Top Selling Products</h2>
                <p class="text-sm text-slate-500">Best sellers by volume</p>
            </div>
            <div class="h-64"><canvas id="topProductsChart"></canvas></div>
        </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Recent Orders</h2>
                <p class="text-sm text-slate-500">Latest customer transactions</p>
            </div>
            <a href="customer_orders.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Order ID</th>
                        <th class="px-5 py-3 font-semibold">Customer</th>
                        <th class="px-5 py-3 font-semibold">Total</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-500">No orders found.</td>
                        </tr>
                    <?php else: foreach ($recentOrders as $order): $status = $formatStatus($order['status']); ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-semibold text-slate-900">#<?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars(trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''))); ?></td>
                            <td class="px-5 py-4 font-medium text-slate-900"><?php echo formatPrice($order['final_amount']); ?></td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 <?php echo $status['class']; ?>"><?php echo $status['label']; ?></span>
                            </td>
                            <td class="px-5 py-4 text-slate-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthlySales = <?php echo $monthlySalesJson ?: '{"labels":[],"values":[]}'; ?>;
    const topProducts = <?php echo $topProductsJson ?: '{"labels":[],"values":[]}'; ?>;
    const chartFont = { family: 'Inter, sans-serif' };

    if (document.getElementById('monthlySalesChart')) {
        new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: {
                labels: monthlySales.labels,
                datasets: [{
                    data: monthlySales.values,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59,130,246,0.12)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' }, ticks: { font: chartFont } },
                    x: { grid: { display: false }, ticks: { font: chartFont } }
                }
            }
        });
    }

    if (document.getElementById('topProductsChart')) {
        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProducts.labels,
                datasets: [{
                    data: topProducts.values,
                    backgroundColor: ['#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE', '#DBEAFE'],
                    borderRadius: 7,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#e2e8f0' }, ticks: { precision: 0, font: chartFont } },
                    y: { grid: { display: false }, ticks: { font: chartFont } }
                }
            }
        });
    }
</script>

</main>
