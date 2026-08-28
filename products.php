<?php
$pageTitle = 'Products';
require_once 'includes/header.php';

// Get filter parameters
$categorySlug = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Build query
$params = [];
$types = "";
$where = "WHERE p.status = 'active'";

if ($categorySlug) {
    $where .= " AND c.slug = ?";
    $params[] = $categorySlug;
    $types .= "s";
}

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($minPrice !== '') {
    $where .= " AND p.price >= ?";
    $params[] = $minPrice;
    $types .= "d";
}

if ($maxPrice !== '') {
    $where .= " AND p.price <= ?";
    $params[] = $maxPrice;
    $types .= "d";
}

// Sorting
$orderBy = "ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_low': $orderBy = "ORDER BY p.price ASC"; break;
    case 'price_high': $orderBy = "ORDER BY p.price DESC"; break;
    case 'name': $orderBy = "ORDER BY p.name ASC"; break;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count
$countSql = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.id $where";
$countResult = !empty($types) ? fetchOne($countSql, $types, $params) : fetchOne($countSql);
$totalProducts = $countResult['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where 
        $orderBy 
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= "ii";

$products = fetchAll($sql, $types, $params);

// Get all categories for filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name");

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="section-header">
        <span class="section-subtitle">Our Collection</span>
        <h2 class="section-title">All Products</h2>
        <p class="section-description">Discover our complete range of premium furniture</p>
    </div>
    
    <!-- Filters -->
    <div style="background: white; padding: 24px; border-radius: var(--radius-lg); margin-bottom: 40px; box-shadow: var(--shadow-sm);">
        <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['slug']; ?>" <?php echo $categorySlug === $cat['slug'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div>
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-control">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A-Z</option>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <div>
                    <label class="form-label">Min Price</label>
                    <input type="number" name="min_price" class="form-control" placeholder="Rs. 0" value="<?php echo $minPrice; ?>">
                </div>
                <div>
                    <label class="form-label">Max Price</label>
                    <input type="number" name="max_price" class="form-control" placeholder="Rs. Max" value="<?php echo $maxPrice; ?>">
                </div>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
            </div>
            
            <?php if ($categorySlug || $search || $sort !== 'newest' || $minPrice || $maxPrice): ?>
            <div>
                <a href="products.php" class="btn btn-outline" style="width: 100%;">Clear Filters</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Results Count -->
    <p style="margin-bottom: 24px; color: var(--gray-600);">
        Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
    </p>
    
    <!-- Products Grid -->
    <?php if (empty($products)): ?>
    <div style="text-align: center; padding: 80px 24px;">
        <i class="fas fa-search" style="font-size: 48px; color: var(--gray-400); margin-bottom: 16px;"></i>
        <h3 style="font-family: var(--font-display); color: var(--gray-700); margin-bottom: 8px;">No Products Found</h3>
        <p style="color: var(--gray-600);">Try adjusting your filters or search criteria</p>
    </div>
    <?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): 
            $isAvailable = $product['stock_quantity'] > 0;
        ?>
        <div class="product-card">
            <div class="product-image-wrapper">
                 <img src="image/<?php echo htmlspecialchars($product['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.src='https://placehold.co/300x200/e2e8f0/64748b?text=No+Image'"
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image w-full h-48 object-cover rounded-lg">
                <span class="product-badge <?php echo $isAvailable ? 'badge-available' : 'badge-outofstock'; ?>">
                    <?php echo $isAvailable ? 'Available' : 'Out of Stock'; ?>
                </span>
                <div class="product-actions">
                    <a href="product_details.php?slug=<?php echo $product['slug']; ?>" class="action-btn" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <?php if ($isAvailable): ?>
                    <button class="action-btn add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)" title="Add to Cart">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="product-info">
                <div class="product-category"><?php echo $product['category_name']; ?></div>
                <h3 class="product-name">
                    <a href="product_details.php?slug=<?php echo $product['slug']; ?>"><?php echo $product['name']; ?></a>
                </h3>
                <div class="product-price"><?php echo 'Rs. ' . number_format((float)$product['price'], 2); ?></div>
                <div class="product-footer">
                    <span class="product-stock">
                        <i class="fas <?php echo $isAvailable ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                        <?php echo $isAvailable ? $product['stock_quantity'] . ' in stock' : 'Out of Stock'; ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 60px;">
        <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-chevron-left"></i> Previous
        </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?>
            <span class="btn btn-primary btn-sm" style="cursor: default;"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="btn btn-outline btn-sm"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline btn-sm">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</section>

<script>
function addToCart(productId) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', 1);
    
    fetch('cart.php?action=add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showNotification('Product added to cart!', 'success');
        } else {
            showNotification(data.message || 'Error adding to cart', 'error');
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>