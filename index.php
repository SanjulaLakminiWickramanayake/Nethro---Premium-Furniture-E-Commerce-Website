<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get featured products
$featuredProducts = fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.featured = 1 AND p.status = 'active' 
    LIMIT 6
");

// Get categories
$categories = fetchAll("SELECT * FROM categories LIMIT 4");
?>

<!-- Hero Section -->
<section class="hero">
    <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1200" alt="Modern Furniture" class="hero-image">
    <div class="hero-content">
        <div class="hero-text">
            <span class="hero-subtitle">Premium Collection 2024</span>
            <h1 class="hero-title">Crafting Comfort for Your Home</h1>
            <p class="hero-description">Discover our curated collection of handcrafted furniture designed to transform your living spaces into sanctuaries of style and comfort.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-accent btn-lg">Shop Now</a>
                <a href="#categories" class="btn btn-outline btn-lg" style="border-color: white; color: white;">Explore Categories</a>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="section" id="categories">
    <div class="section-header">
        <span class="section-subtitle">Browse by Category</span>
        <h2 class="section-title">Find Your Style</h2>
        <p class="section-description">Explore our wide range of furniture categories designed for every room in your home.</p>
    </div>
    
    <div class="products-grid">
        <div class="product-card flex flex-col">
            <div class="w-full aspect-[4/3] overflow-hidden rounded-t-lg">
                <img src="image/living.jpg" class="w-full h-full object-cover" alt="Living Room">
            </div>
            <div class="product-info flex flex-1 flex-col">
                <h3 class="product-name"><a href="products.php?category=living-room">Living Room</a></h3>
                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">Create a comfortable and welcoming living space.</p>
                <div class="product-footer mt-auto"><a href="products.php?category=living-room" class="btn btn-sm btn-outline">View Products</a></div>
            </div>
        </div>

        <div class="product-card flex flex-col">
            <div class="w-full aspect-[4/3] overflow-hidden rounded-t-lg">
                <img src="image/bedroom.jpg" class="w-full h-full object-cover" alt="Bedroom">
            </div>
            <div class="product-info flex flex-1 flex-col">
                <h3 class="product-name"><a href="products.php?category=bedroom">Bedroom</a></h3>
                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">Bring warmth and calm to your personal retreat.</p>
                <div class="product-footer mt-auto"><a href="products.php?category=bedroom" class="btn btn-sm btn-outline">View Products</a></div>
            </div>
        </div>

        <div class="product-card flex flex-col">
            <div class="w-full aspect-[4/3] overflow-hidden rounded-t-lg">
                <img src="image/dining.jpg" class="w-full h-full object-cover" alt="Dining Room">
            </div>
            <div class="product-info flex flex-1 flex-col">
                <h3 class="product-name"><a href="products.php?category=dining-room">Dining Room</a></h3>
                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">Gather around beautifully crafted dining furniture.</p>
                <div class="product-footer mt-auto"><a href="products.php?category=dining-room" class="btn btn-sm btn-outline">View Products</a></div>
            </div>
        </div>

        <div class="product-card flex flex-col">
            <div class="w-full aspect-[4/3] overflow-hidden rounded-t-lg">
                <img src="image/office.jpg" class="w-full h-full object-cover" alt="Office">
            </div>
            <div class="product-info flex flex-1 flex-col">
                <h3 class="product-name"><a href="products.php?category=office">Office</a></h3>
                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">Design a productive office with practical style.</p>
                <div class="product-footer mt-auto"><a href="products.php?category=office" class="btn btn-sm btn-outline">View Products</a></div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="section" style="background: white;">
    <div class="section-header">
        <span class="section-subtitle">Handpicked Selection</span>
        <h2 class="section-title">Featured Products</h2>
        <p class="section-description">Our most popular pieces loved by customers worldwide.</p>
    </div>
    
    <div class="products-grid">
        <?php foreach ($featuredProducts as $product): 
            $isAvailable = $product['stock_quantity'] > 0;
        ?>
        <div class="product-card">
            <div class="product-image-wrapper">
                 <img src="image/<?php echo htmlspecialchars($product['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                     onerror="this.src='https://placehold.co/300x200/e2e8f0/64748b?text=No+Image'"
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image w-full h-48 object-cover rounded-lg">
                <?php if ($product['featured']): ?>
                <span class="product-badge badge-featured">Featured</span>
                <?php endif; ?>
                <span class="product-badge <?php echo $isAvailable ? 'badge-available' : 'badge-outofstock'; ?>" style="left: auto; right: 16px;">
                    <?php echo $isAvailable ? 'Available' : 'Out of Stock'; ?>
                </span>
                <div class="product-actions">
                    <a href="product_details.php?slug=<?php echo $product['slug']; ?>" class="action-btn" title="Quick View">
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
    
    <div style="text-align: center; margin-top: 48px;">
        <a href="products.php" class="btn btn-primary btn-lg">View All Products</a>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="section-header">
        <span class="section-subtitle">Why Choose Us</span>
        <h2 class="section-title">The Nethro Difference</h2>
    </div>
    
    <div class="products-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
        <div style="text-align: center; padding: 40px 24px;">
            <div style="width: 80px; height: 80px; background: rgba(201, 169, 110, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; color: var(--accent);">
                <i class="fas fa-truck"></i>
            </div>
            <h3 style="font-family: var(--font-display); font-size: 20px; margin-bottom: 12px;">Free Shipping</h3>
            <p style="color: var(--gray-600);">Complimentary delivery on all orders over $500 within the continental US.</p>
        </div>
        
        <div style="text-align: center; padding: 40px 24px;">
            <div style="width: 80px; height: 80px; background: rgba(201, 169, 110, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; color: var(--accent);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3 style="font-family: var(--font-display); font-size: 20px; margin-bottom: 12px;">10-Year Warranty</h3>
            <p style="color: var(--gray-600);">Every piece comes with our comprehensive decade-long warranty.</p>
        </div>
        
        <div style="text-align: center; padding: 40px 24px;">
            <div style="width: 80px; height: 80px; background: rgba(201, 169, 110, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; color: var(--accent);">
                <i class="fas fa-undo"></i>
            </div>
            <h3 style="font-family: var(--font-display); font-size: 20px; margin-bottom: 12px;">30-Day Returns</h3>
            <p style="color: var(--gray-600);">Not satisfied? Return within 30 days for a full refund.</p>
        </div>
        
        <div style="text-align: center; padding: 40px 24px;">
            <div style="width: 80px; height: 80px; background: rgba(201, 169, 110, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 32px; color: var(--accent);">
                <i class="fas fa-leaf"></i>
            </div>
            <h3 style="font-family: var(--font-display); font-size: 20px; margin-bottom: 12px;">Eco-Friendly</h3>
            <p style="color: var(--gray-600);">Sustainably sourced materials and environmentally conscious manufacturing.</p>
        </div>
    </div>
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
    })
    .catch(error => {
        showNotification('Error adding to cart', 'error');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>