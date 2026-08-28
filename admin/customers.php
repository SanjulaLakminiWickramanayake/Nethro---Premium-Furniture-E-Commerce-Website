<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Customers';

$search = trim($_GET['search'] ?? '');

$where = '';
$params = [];
$types = '';

if ($search !== '') {
    $where = ' WHERE (LOWER(CONCAT(first_name, " ", last_name)) LIKE ? OR LOWER(email) LIKE ?) ';
    $searchTerm = '%' . strtolower($search) . '%';
    $params = [$searchTerm, $searchTerm];
    $types = 'ss';
}

$customers = fetchAll(
    "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name, u.email, u.phone, u.city,
            COUNT(DISTINCT o.id) AS order_count,
            COALESCE(SUM(o.final_amount), 0) AS total_spent,
            u.created_at
     FROM users u
     LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
     $where
     GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone, u.city, u.created_at
     ORDER BY u.created_at DESC",
    $types,
    $params
);

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">People</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Customers</h1>
        </div>

        <form method="GET" class="w-full max-w-sm">
            <label for="customerSearch" class="sr-only">Search customers</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                </div>
                <input
                    id="customerSearch"
                    type="text"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by name or email"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                >
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
        <?php if (empty($customers)): ?>
            <div class="flex min-h-[280px] items-center justify-center">
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                        <i data-lucide="users" class="h-5 w-5"></i>
                    </div>
                    <p class="text-lg font-semibold text-slate-700">No customers found</p>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Email</th>
                            <th class="px-4 py-3 font-semibold">Phone</th>
                            <th class="px-4 py-3 font-semibold">Location</th>
                            <th class="px-4 py-3 font-semibold">Orders</th>
                            <th class="px-4 py-3 font-semibold">Total Spent</th>
                            <th class="px-4 py-3 font-semibold">Joined</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php foreach ($customers as $customer): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 font-semibold text-slate-900">
                                    <?php echo htmlspecialchars($customer['name'] ?? 'Unknown Customer'); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-700">
                                    <?php echo (int)($customer['order_count'] ?? 0); ?>
                                </td>
                                <td class="px-4 py-4 font-medium text-slate-900">
                                    <?php echo formatPrice((float)($customer['total_spent'] ?? 0)); ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600">
                                    <?php echo date('M d, Y', strtotime($customer['created_at'])); ?>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="customer_orders.php?id=<?php echo (int)($customer['id'] ?? 0); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition hover:border-blue-200 hover:text-blue-600">
                                            <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                            View
                                        </a>
                                        <form action="customer_orders.php" method="POST" onsubmit="return confirm('Delete this customer?');">
                                            <input type="hidden" name="delete_customer" value="<?php echo (int)($customer['id'] ?? 0); ?>">
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
