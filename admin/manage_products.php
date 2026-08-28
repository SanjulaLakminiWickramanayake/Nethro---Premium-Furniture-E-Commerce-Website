<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Manage Products';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $product = fetchOne("SELECT image FROM products WHERE id = ?", "i", [$id]);
    if ($product && $product['image']) {
        @unlink('../images/products/' . $product['image']);
    }
    executeQuery("DELETE FROM products WHERE id = ?", "i", [$id]);
    setFlash('success', 'Product deleted successfully');
    redirect('manage_products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = intval($_POST['product_id']);
    $newStock = intval($_POST['new_stock']);
    executeQuery("UPDATE products SET stock_quantity = ? WHERE id = ?", "ii", [$newStock, $productId]);
    setFlash('success', 'Stock updated successfully');
    redirect('manage_products.php');
}

$products = fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Manage Products</h1>
            <p class="mt-1 text-sm text-slate-500">View, update, and manage your inventory.</p>
        </div>
        <a href="add_product.php" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add New Product
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Image</th>
                        <th class="px-5 py-3 font-semibold">Product</th>
                        <th class="px-5 py-3 font-semibold">Category</th>
                        <th class="px-5 py-3 font-semibold">Price</th>
                        <th class="px-5 py-3 font-semibold">Stock</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 align-middle">
                                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg bg-gray-100">
                                    <img src="../image/<?php echo htmlspecialchars($product['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" onerror="this.src='https://placehold.co/64x64/e2e8f0/64748b?text=No+Img'" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-16 w-16 rounded-lg border border-gray-200 object-cover">
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                <?php if (!empty($product['featured'])): ?>
                                    <span class="mt-2 inline-flex rounded-full bg-amber-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Featured</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-slate-600"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                            <td class="px-5 py-4 font-medium text-slate-900"><?php echo formatPrice($product['price']); ?></td>
                            <td class="px-5 py-4 align-middle">
                                <form method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="new_stock" value="<?php echo intval($product['stock_quantity']); ?>" min="0" class="w-20 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                                    <button type="submit" name="update_stock" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-green-500 p-2 text-white shadow-sm transition hover:bg-green-600" title="Update Stock" aria-label="Update Stock">
                                        <i data-lucide="check" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-4">
                                <?php if (($product['status'] ?? 'active') === 'active'): ?>
                                    <span class="status-pill success">ACTIVE</span>
                                <?php else: ?>
                                    <span class="status-pill warning">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="window.location.href='edit_product.php?id=<?php echo (int)$product['id']; ?>'" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500 p-2 text-white shadow-sm transition hover:bg-blue-600" title="Edit Product" aria-label="Edit Product">
                                        <i data-lucide="pencil" class="h-4 w-4"></i>
                                    </button>
                                    <form method="GET" class="inline-flex" onsubmit="return confirm('Delete this product?');">
                                        <input type="hidden" name="delete" value="<?php echo (int)$product['id']; ?>">
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-500 p-2 text-white shadow-sm transition hover:bg-red-600" title="Delete Product" aria-label="Delete Product">
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
