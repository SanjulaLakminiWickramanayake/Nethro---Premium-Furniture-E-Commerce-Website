<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Payments';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $installmentId = intval($_POST['installment_id']);
    executeQuery("UPDATE installments SET status = 'paid', paid_date = CURDATE() WHERE id = ?", "i", [$installmentId]);

    $installment = fetchOne("SELECT order_id FROM installments WHERE id = ?", "i", [$installmentId]);
    if ($installment) {
        $pending = fetchOne("SELECT COUNT(*) as count FROM installments WHERE order_id = ? AND status != 'paid'", "i", [$installment['order_id']]);
        if (($pending['count'] ?? 0) == 0) {
            executeQuery("UPDATE orders SET payment_status = 'paid' WHERE id = ?", "i", [$installment['order_id']]);
        }
    }

    setFlash('success', 'Payment marked as completed');
    redirect('payments.php');
}

$payments = fetchAll("
    SELECT p.*, o.order_number, u.first_name, u.last_name
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.paid_at DESC
");

$pendingInstallments = fetchAll("
    SELECT i.*, o.order_number, u.first_name, u.last_name, u.email
    FROM installments i
    JOIN orders o ON i.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE i.status = 'pending'
    ORDER BY i.due_date ASC
");

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Finance</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">Payments</h1>
    </div>

    <?php if (!empty($pendingInstallments)): ?>
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600"><i data-lucide="clock-3" class="h-5 w-5"></i></div>
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Pending Installments</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Order ID</th>
                            <th class="px-5 py-3 font-semibold">Customer</th>
                            <th class="px-5 py-3 font-semibold">Amount</th>
                            <th class="px-5 py-3 font-semibold">Due Date</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($pendingInstallments as $inst): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($inst['order_number']); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars($inst['first_name'] . ' ' . $inst['last_name']); ?></td>
                                <td class="px-5 py-4 font-medium text-slate-900"><?php echo formatPrice($inst['amount']); ?></td>
                                <td class="px-5 py-4 text-slate-600"><?php echo date('M d, Y', strtotime($inst['due_date'])); ?></td>
                                <td class="px-5 py-4"><span class="status-pill warning">Pending</span></td>
                                <td class="px-5 py-4">
                                    <form method="POST">
                                        <input type="hidden" name="installment_id" value="<?php echo (int)$inst['id']; ?>">
                                        <button type="submit" name="mark_paid" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                            <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                            Mark Paid
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-6 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
            <p class="text-lg font-semibold text-slate-700">No pending payments</p>
            <p class="mt-2 text-sm text-slate-500">Everything is up to date.</p>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600"><i data-lucide="wallet" class="h-5 w-5"></i></div>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Payment History</h2>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Transaction ID</th>
                        <th class="px-5 py-3 font-semibold">Order</th>
                        <th class="px-5 py-3 font-semibold">Customer</th>
                        <th class="px-5 py-3 font-semibold">Amount</th>
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars($payment['order_number']); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                            <td class="px-5 py-4 font-medium text-slate-900"><?php echo formatPrice($payment['amount']); ?></td>
                            <td class="px-5 py-4 text-slate-600"><?php echo date('M d, Y', strtotime($payment['paid_at'])); ?></td>
                            <td class="px-5 py-4"><span class="status-pill success">Completed</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
