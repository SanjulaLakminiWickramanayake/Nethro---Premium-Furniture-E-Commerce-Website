<?php
require_once '../includes/db.php';

if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$pageTitle = 'Add Product';
$error = '';

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

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $existing = fetchOne("SELECT id FROM products WHERE slug = ?", "s", [$slug]);
    if ($existing) {
        $slug .= '-' . uniqid();
    }

    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowedExts)) {
            $imageName = uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP';
        }
    }

    if (empty($error)) {
        if (empty($name) || $price <= 0) {
            $error = 'Product name and valid price are required';
        } else {
            $sql = "INSERT INTO products (category_id, name, slug, description, short_description, price, stock_quantity, image, dimensions, material, weight, featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = executeQuery($sql, "issssdissddi", [
                $categoryId ?: null,
                $name,
                $slug,
                $description,
                $shortDescription,
                $price,
                $stock,
                $imageName,
                $dimensions,
                $material,
                $weight,
                $featured,
            ]);

            if ($stmt && $stmt->affected_rows > 0) {
                setFlash('success', 'Product added successfully');
                redirect('manage_products.php');
            } else {
                $error = 'Failed to add product';
                if ($stmt === false) {
                    global $conn;
                    $error .= ': ' . $conn->error;
                }
            }
        }
    }
}

$categories = fetchAll("SELECT * FROM categories ORDER BY name");

require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="ml-64 p-6 max-lg:ml-0">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-600">Catalog</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">Add Product</h1>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Product Name *</label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="Modern Velvet Sofa" required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Category</label>
                    <select name="category_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="0.00" required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" min="0" value="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" required>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Short Description</label>
                <input type="text" name="short_description" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="Brief product tagline">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                <textarea name="description" rows="5" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="Detailed description of the product..."></textarea>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Dimensions</label>
                    <input type="text" name="dimensions" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder='84"W x 35"D x 32"H'>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Material</label>
                    <input type="text" name="material" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="Velvet, Oak, Metal">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" min="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none" placeholder="0.00">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Image Upload</label>
                    <input type="file" name="image" accept="image/*" class="w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                <input type="checkbox" name="featured" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Feature this product on the homepage
            </label>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto">
                    Save Product
                </button>
                <a href="manage_products.php" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</main>
