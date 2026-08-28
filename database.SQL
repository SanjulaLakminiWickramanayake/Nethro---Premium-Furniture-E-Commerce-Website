-- nethro_furniture.sql
CREATE DATABASE IF NOT EXISTS nethro_furniture CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nethro_furniture;

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample users
INSERT INTO users (first_name, last_name, email, password, phone, address, city) VALUES
('Amina', 'Khan', 'amina.khan@example.com', '$2y$10$u9jz1E4xK7wY2DvG2BzG5uP8jO1A6n5TYKQW8h6eFUDP6Vbq2uYNG', '+1 (555) 234-7890', '23 Maple Street, Apt 4', 'Lagos'),
('David', 'Mensah', 'david.mensah@example.com', '$2y$10$u9jz1E4xK7wY2DvG2BzG5uP8jO1A6n5TYKQW8h6eFUDP6Vbq2uYNG', '+1 (555) 987-6543', '18 Ocean View Drive', 'Accra'),
('Sara', 'Owusu', 'sara.owusu@example.com', '$2y$10$u9jz1E4xK7wY2DvG2BzG5uP8jO1A6n5TYKQW8h6eFUDP6Vbq2uYNG', '+1 (555) 321-4567', '77 Garden Lane', 'Kumasi');

-- 2. Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(255),
    gallery TEXT,
    dimensions VARCHAR(100),
    material VARCHAR(100),
    weight DECIMAL(8,2),
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 5. Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'partial', 'failed') DEFAULT 'pending',
    payment_method ENUM('full', 'installment') NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 7. Payments Table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('full', 'installment', 'down_payment') NOT NULL,
    card_holder_name VARCHAR(100),
    card_last_four VARCHAR(4),
    transaction_id VARCHAR(100),
    status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. Installments Table
CREATE TABLE installments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    installment_number INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    status ENUM('pending', 'paid', 'overdue', 'late') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- 9. Cart Table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    UNIQUE KEY unique_cart_session (session_id, product_id)
);

-- 10. Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('installment_alert', 'message', 'system') NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    related_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    admin_id INT,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    sender_type ENUM('customer', 'admin') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password, email, full_name) VALUES 
('admin@example.com', '$2y$10$K1tx.TQYbvMtTVQa4WwA4.NqYJaZpjH7I5hSfqGgw7pUhlpu8Dueq', 'admin@nethro.com', 'System Administrator');

-- Insert sample categories
INSERT INTO categories (name, slug, description) VALUES
('Living Room', 'living-room', 'Comfortable and stylish living room furniture'),
('Bedroom', 'bedroom', 'Cozy bedroom furniture for better sleep'),
('Dining Room', 'dining-room', 'Elegant dining sets and tables'),
('Office', 'office', 'Professional office furniture'),
('Outdoor', 'outdoor', 'Durable outdoor furniture');

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, short_description, price, stock_quantity, image, dimensions, material, featured) VALUES
(1, 'Modern Velvet Sofa', 'modern-velvet-sofa', 'Luxurious velvet sofa with gold legs...', 'Premium velvet sofa', 180000.00, 15, 'sofa.jpg', '84"W x 35"D x 32"H', 'Velvet, Metal', 1),
(2, 'King Size Platform Bed', 'king-platform-bed', 'Minimalist platform bed...', 'Modern platform bed', 250000.00, 8, 'bed.jpg', '80"W x 84"D x 12"H', 'Wood', 1),
(3, 'Marble Dining Table', 'marble-dining-table', 'Real marble top dining table...', 'Elegant marble table', 300000.00, 5, 'table.jpg', '72"W x 36"D x 30"H', 'Marble, Steel', 1),
(4, 'Ergonomic Office Chair', 'ergonomic-office-chair', 'Fully adjustable ergonomic chair...', 'Premium office chair', 75000.00, 20, 'chair.jpg', '26"W x 26"D x 48"H', 'Mesh, Plastic', 0),
(5, 'Outdoor Patio Set', 'outdoor-patio-set', 'Weather-resistant patio furniture...', '5-piece patio set', 220000.00, 12, 'patio.jpg', 'Varies', 'Wicker, Metal', 0);

-- Insert sample orders
INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, status, payment_status, payment_method, shipping_address, shipping_city, shipping_phone, notes) VALUES
(1, 'ORD-1001', 1349.98, 0.00, 1349.98, 'shipped', 'paid', 'full', '23 Maple Street, Apt 4', 'Lagos', '+1 (555) 234-7890', 'Please leave package at front door'),
(2, 'ORD-1002', 2399.98, 300.00, 2099.98, 'processing', 'pending', 'installment', '18 Ocean View Drive', 'Accra', '+1 (555) 987-6543', 'Deliver between 9am and 5pm');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 1, 899.99, 899.99),
(1, 4, 1, 449.99, 449.99),
(2, 3, 1, 1599.99, 1599.99),
(2, 5, 1, 799.99, 799.99);

-- Insert sample payments
INSERT INTO payments (order_id, user_id, amount, payment_type, card_holder_name, card_last_four, transaction_id, status) VALUES
(1, 1, 1349.98, 'full', 'Amina Khan', '1234', 'TXN1001', 'completed'),
(2, 2, 1049.99, 'installment', 'David Mensah', '5678', 'TXN1002', 'pending');

-- Insert sample installments
INSERT INTO installments (order_id, installment_number, amount, due_date, paid_date, status) VALUES
(2, 1, 1049.99, '2026-07-15', NULL, 'pending'),
(2, 2, 1049.99, '2026-08-15', NULL, 'pending');

-- Insert sample cart items
INSERT INTO cart (user_id, product_id, quantity) VALUES
(1, 2, 2),
(3, 1, 1);

-- Insert sample notifications
INSERT INTO notifications (type, title, message, related_id, is_read) VALUES
('installment_alert', 'Installment due soon', 'Your next installment payment for order ORD-1002 is due soon.', 2, 0),
('system', 'Welcome to Nethro', 'Thank you for creating an account with Nethro Furniture.', NULL, 0);

-- Insert sample messages
INSERT INTO messages (user_id, admin_id, subject, message, sender_type, is_read) VALUES
(1, 1, 'Order question', 'When will my order be delivered?', 'customer', 0),
(1, 1, 'Re: Order question', 'Your order is scheduled to arrive in 2 days.', 'admin', 1);