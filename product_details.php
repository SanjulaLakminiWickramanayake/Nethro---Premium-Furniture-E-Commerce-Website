<?php
require_once 'includes/db.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    redirect('products.php');
}

$product = fetchOne("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.slug = ? AND p.status = 'active'
", "s", [$slug]);

if (!$product) {
    setFlash('error', 'Product not found');
    redirect('products.php');
}

$pageTitle = $product['name'];

// Get related products
$relatedProducts = fetchAll("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? AND status = 'active' 
    LIMIT 4
", "ii", [$product['category_id'], $product['id']]);

$isAvailable = $product['stock_quantity'] > 0;

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    
    if ($quantity > $product['stock_quantity']) {
        setFlash('error', 'Not enough stock available');
    } else {
        if (isset($_SESSION['user_id'])) {
            // Check if already in cart
            $existing = fetchOne("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", "ii", [$_SESSION['user_id'], $product['id']]);
            if ($existing) {
                $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", "ii", [$newQty, $existing['id']]);
            } else {
                executeQuery("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", "iii", [$_SESSION['user_id'], $product['id'], $quantity]);
            }
        } else {
            $sessionId = $_SESSION['cart_session'] ?? session_id();
            $existing = fetchOne("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?", "si", [$sessionId, $product['id']]);
            if ($existing) {
                $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                executeQuery("UPDATE cart SET quantity = ? WHERE id = ?", "ii", [$newQty, $existing['id']]);
            } else {
                executeQuery("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)", "sii", [$sessionId, $product['id'], $quantity]);
            }
        }
        setFlash('success', 'Product added to cart');
    }
    redirect('product_details.php?slug=' . $slug);
}

require_once 'includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
    <div class="product-details">
        <!-- Gallery -->
        <div class="product-gallery">
            <div class="gallery-main">
                <img src="<?php echo $product['image'] ? 'images/products/' . $product['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800'; ?>" 
                     alt="<?php echo $product['name']; ?>">
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="product-meta">
            <div style="margin-bottom: 16px;">
                <a href="products.php?category=<?php echo $product['category_slug']; ?>" style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 14px;">
                    <?php echo $product['category_name']; ?>
                </a>
            </div>
            
            <h1><?php echo $product['name']; ?></h1>
            
            <div class="price">
                <?php echo formatPrice($product['price']); ?>
            </div>
            
            <div style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
                <span class="status-badge <?php echo $isAvailable ? 'status-delivered' : 'status-cancelled'; ?>">
                    <?php echo $isAvailable ? 'In Stock' : 'Out of Stock'; ?>
                </span>
                <?php if ($product['featured']): ?>
                <span class="status-badge status-pending">Featured</span>
                <?php endif; ?>
            </div>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <?php if ($product['short_description']): ?>
            <p style="color: var(--gray-600); margin-bottom: 24px; font-style: italic;">
                <?php echo $product['short_description']; ?>
            </p>
            <?php endif; ?>
            
            <!-- Specs -->
            <div class="product-specs">
                <?php if ($product['dimensions']): ?>
                <div class="spec-item">
                    <span class="spec-label">Dimensions</span>
                    <span class="spec-value"><?php echo $product['dimensions']; ?></span>
                </div>
                <?php endif; ?>
                <?php if ($product['material']): ?>
                <div class="spec-item">
                    <span class="spec-label">Material</span>
                    <span class="spec-value"><?php echo $product['material']; ?></span>
                </div>
                <?php endif; ?>
                <?php if ($product['weight']): ?>
                <div class="spec-item">
                    <span class="spec-label">Weight</span>
                    <span class="spec-value"><?php echo $product['weight']; ?> kg</span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Add to Cart -->
            <?php if ($isAvailable): ?>
            <form method="POST" action="">
                <div class="quantity-selector">
                    <button type="button" class="qty-btn" onclick="decrementQty()">-</button>
                    <input type="number" name="quantity" id="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" readonly>
                    <button type="button" class="qty-btn" onclick="incrementQty()">+</button>
                </div>
                
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg" style="width: 100%;">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </form>
            <?php else: ?>
            <button class="btn btn-outline btn-lg" style="width: 100%; opacity: 0.6; cursor: not-allowed;" disabled>
                <i class="fas fa-bell"></i> Notify When Available
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <div style="margin-top: 80px;">
        <div class="section-header">
            <span class="section-subtitle">You May Also Like</span>
            <h2 class="section-title">Related Products</h2>
        </div>
        
        <div class="products-grid">
            <?php foreach ($relatedProducts as $rel): 
                $relAvailable = $rel['stock_quantity'] > 0;
            ?>
            <div class="product-card">
                <div class="product-image-wrapper">
                    <img src="<?php echo $rel['image'] ? 'images/products/' . $rel['image'] : 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600'; ?>" 
                         alt="<?php echo $rel['name']; ?>" class="product-image">
                    <span class="product-badge <?php echo $relAvailable ? 'badge-available' : 'badge-outofstock'; ?>">
                        <?php echo $relAvailable ? 'Available' : 'Out of Stock'; ?>
                    </span>
                    <div class="product-actions">
                        <a href="product_details.php?slug=<?php echo $rel['slug']; ?>" class="action-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-name">
                        <a href="product_details.php?slug=<?php echo $rel['slug']; ?>"><?php echo $rel['name']; ?></a>
                    </h3>
                    <div class="product-price"><?php echo formatPrice($rel['price']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
function incrementQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>