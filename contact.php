<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';
?>

<section class="section" style="padding: 80px 24px; background: #f8f8f8;">
    <div class="section-header" style="max-width: 960px; margin: 0 auto; text-align: center;">
        <span class="section-subtitle">Get in Touch</span>
        <h1 class="section-title">We’d Love to Hear From You</h1>
        <p class="section-description" style="max-width: 760px; margin: 24px auto 0; color: var(--gray-700);">
            Have a question about an order, a product, or our services? Reach out and our team will respond as soon as possible.
        </p>
    </div>
</section>

<section class="section" style="padding: 64px 24px;">
    <div class="section-content" style="max-width: 1100px; margin: 0 auto; display: grid; gap: 40px; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow);">
            <h2 class="section-title">Contact Information</h2>
            <p style="color: var(--gray-700); line-height: 1.8; margin-bottom: 24px;">
                Our customer care team is ready to help with product questions, order updates, returns, and more.
            </p>
            <div style="display: grid; gap: 16px; color: var(--gray-700);">
                <p><strong>Address:</strong><br>123 Furniture Lane, Design District</p>
                <p><strong>Phone:</strong><br>+1 (555) 123-4567</p>
                <p><strong>Email:</strong><br><a href="mailto:hello@nethro.com">hello@nethro.com</a></p>
                <p><strong>Hours:</strong><br>Mon - Sat: 9:00 AM - 6:00 PM</p>
            </div>
        </div>

        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: var(--shadow);">
            <h2 class="section-title">Send Us a Message</h2>
            <form action="#" method="post" style="display: grid; gap: 18px;">
                <label>
                    <span style="display:block; margin-bottom: 8px; font-weight: 600;">Your Name</span>
                    <input type="text" name="name" placeholder="Enter your name" style="width:100%; padding:14px 16px; border:1px solid #ddd; border-radius:10px;" required>
                </label>
                <label>
                    <span style="display:block; margin-bottom: 8px; font-weight: 600;">Email Address</span>
                    <input type="email" name="email" placeholder="Enter your email" style="width:100%; padding:14px 16px; border:1px solid #ddd; border-radius:10px;" required>
                </label>
                <label>
                    <span style="display:block; margin-bottom: 8px; font-weight: 600;">Message</span>
                    <textarea name="message" rows="6" placeholder="How can we help you?" style="width:100%; padding:14px 16px; border:1px solid #ddd; border-radius:10px;" required></textarea>
                </label>
                <button type="submit" class="btn btn-primary">Submit Message</button>
            </form>
        </div>
    </div>
</section>

<section class="section" style="padding: 64px 24px; background: #f8f8f8;">
    <div class="section-header" style="max-width: 960px; margin: 0 auto; text-align: center;">
        <h2 class="section-title">Visit Our Showroom</h2>
        <p class="section-description" style="max-width: 760px; margin: 24px auto 0; color: var(--gray-700);">
            Drop by to see our collection in person, get design advice, and experience our furniture firsthand.
        </p>
    </div>
    <div style="max-width: 1100px; margin: 48px auto 0;">
        <div style="width: 100%; height: 420px; border-radius: 24px; overflow: hidden; box-shadow: var(--shadow);">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.019311180313!2d-122.42177818468177!3d37.77492977975947!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808b0d5aecd7%3A0x4e0d0bbf20fe70f7!2sSan%20Francisco!5e0!3m2!1sen!2sus!4v1700000000000!5m2!1sen!2sus" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>