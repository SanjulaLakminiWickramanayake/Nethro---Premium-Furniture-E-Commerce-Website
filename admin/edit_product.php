<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Edit Product';
$error = '';

$id = intval($_GET['id'] ?? 0);
$product = fetchOne("SELECT * FROM products WHERE id = ?", "i", [$id]);

if (!$product) {
    setFlash('error', 'Product not found');
    redirect('manage_products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock_quantity'] ?? 0);
    $dimensions = trim($_POST['dimensions'] ?? '');
    $material = trim($_POST['material'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    
    // Handle image upload
    $imageName = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/products/';
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowedExts)) {
            // Delete old image
            if ($imageName && file_exists($uploadDir . $imageName)) {
                unlink($uploadDir . $imageName);
            }
            $imageName = uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }
    
    if (empty($name) || $price <= 0) {
        $error = 'Product name and valid price are required';
    } else {
        $sql = "UPDATE products SET category_id = ?, name = ?, description = ?, short_description = ?, price = ?, 
                stock_quantity = ?, image = ?, dimensions = ?, material = ?, weight = ?, featured = ?, status = ? 
                WHERE id = ?";
        executeQuery($sql, "isssdisssdisi", [
            $categoryId ?: null, $name, $description, $shortDescription, $price, 
            $stock, $imageName, $dimensions, $material, $weight, $featured, $status, $id
        ]);
        
        setFlash('success', 'Product updated successfully');
        redirect('manage_products.php');
    }
}

$categories = fetchAll("SELECT * FROM categories ORDER BY name");

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Catalog</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">Edit Product</h1>
        <p class="mt-1 text-sm text-slate-500">Update product details and inventory.</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="POST" action="edit_product.php?id=<?php echo $id; ?>" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Product Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Category</label>
                    <select name="category_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" min="0" value="<?php echo (int)$product['stock_quantity']; ?>" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Short Description</label>
                <input type="text" name="short_description" value="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Dimensions</label>
                    <input type="text" name="dimensions" value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Material</label>
                    <input type="text" name="material" value="<?php echo htmlspecialchars($product['material'] ?? ''); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" min="0" value="<?php echo htmlspecialchars($product['weight'] ?? 0); ?>" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Current Image</label>
                    <?php if ($product['image']): ?>
                        <img src="../images/products/<?php echo rawurlencode($product['image']); ?>" alt="Current product" class="h-32 w-32 rounded-xl object-cover">
                    <?php else: ?>
                        <p class="text-sm text-slate-500">No image uploaded</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Change Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-sm">
                </div>
            </div>
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                <input type="checkbox" name="featured" value="1" <?php echo $product['featured'] ? 'checked' : ''; ?> class="h-4 w-4 rounded border-slate-300 text-blue-600">
                Feature this product on homepage
            </label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 sm:w-auto">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Update Product
                </button>
                <a href="manage_products.php" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">Cancel</a>
            </div>
        </form>
    </div>
</div>

</main>