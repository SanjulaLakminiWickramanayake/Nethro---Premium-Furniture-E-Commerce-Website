<?php
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<section class="section about-hero" style="padding: 80px 24px; background: #f8f8f8;">
    <div class="section-header" style="max-width: 960px; margin: 0 auto; text-align: center;">
        <span class="section-subtitle">Our Story</span>
        <h1 class="section-title">Designing Homes with Heart</h1>
        <p class="section-description" style="max-width: 760px; margin: 24px auto 0; color: var(--gray-700);">
            At Nethro Furniture, we believe every piece of furniture should be a blend of comfort, durability, and beauty. Since 2010,
            we have been crafting premium home furnishings that make everyday living more elegant and welcoming.
        </p>
    </div>
</section>

<section class="section" style="padding: 64px 24px;">
    <div class="section-content" style="max-width: 1100px; margin: 0 auto; display: grid; gap: 40px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        <div>
            <h2 class="section-title">Our Mission</h2>
            <p style="color: var(--gray-700); line-height: 1.8;">
                We create furniture that elevates your home with thoughtful design, premium materials, and exceptional craftsmanship. Our mission is to help you build spaces that feel both stylish and lived-in.
            </p>
        </div>
        <div>
            <h2 class="section-title">What Sets Us Apart</h2>
            <ul style="color: var(--gray-700); line-height: 1.8; list-style: disc inside;">
                <li>Carefully selected materials for durability and comfort.</li>
                <li>Modern furniture designs with timeless appeal.</li>
                <li>Personalized customer service for every order.</li>
                <li>Fast, reliable shipping across the country.</li>
            </ul>
        </div>
    </div>
</section>

<section class="section" style="padding: 64px 24px; background: #f8f8f8;">
    <div class="section-header" style="max-width: 960px; margin: 0 auto; text-align: center;">
        <span class="section-subtitle">Our Values</span>
        <h2 class="section-title">Built on Trust and Quality</h2>
        <p class="section-description" style="max-width: 760px; margin: 24px auto 0; color: var(--gray-700);">
            From the showroom to your living room, we strive to deliver an experience that feels curated, thoughtful, and dependable.
        </p>
    </div>

    <div class="products-grid" style="max-width: 1100px; margin: 48px auto 0; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
        <div style="padding: 32px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h3 style="font-size: 20px; margin-bottom: 16px;">Quality Craftsmanship</h3>
            <p style="color: var(--gray-700); line-height: 1.8;">We partner with expert artisans and trusted suppliers to ensure every piece meets our high standards.</p>
        </div>
        <div style="padding: 32px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h3 style="font-size: 20px; margin-bottom: 16px;">Customer-First Service</h3>
            <p style="color: var(--gray-700); line-height: 1.8;">Your satisfaction is our priority. We support your journey from browsing to delivery and beyond.</p>
        </div>
        <div style="padding: 32px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h3 style="font-size: 20px; margin-bottom: 16px;">Sustainability</h3>
            <p style="color: var(--gray-700); line-height: 1.8;">We choose materials and processes that reduce waste and support responsible manufacturing.</p>
        </div>
    </div>
</section>

<section class="section" style="padding: 64px 24px;">
    <div class="section-content" style="max-width: 960px; margin: 0 auto; text-align: center;">
        <h2 class="section-title">Join the Nethro Community</h2>
        <p style="color: var(--gray-700); line-height: 1.8; margin: 24px auto 0; max-width: 720px;">
            Discover stylish furniture designed for modern living. Whether you're furnishing a first home, updating a favorite room, or shopping for a perfect statement piece,
            Nethro makes it easy to bring warmth and elegance into every space.
        </p>
        <a href="products.php" class="btn btn-primary" style="margin-top: 32px; display: inline-block;">Browse Our Collection</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>