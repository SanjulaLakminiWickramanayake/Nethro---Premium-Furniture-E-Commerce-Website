# Nethro Furniture Shop

A PHP and MySQL furniture shop with customer accounts, cart and checkout features, orders, and an admin dashboard.

## Requirements

- XAMPP with Apache, MySQL, and PHP
- A browser

## Setup

1. Copy the project into `C:\xampp\htdocs\xa\shop`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin and import `database.SQL`.
4. Open the shop at `http://localhost/xa/shop/`.
5. Open the admin dashboard at `http://localhost/xa/shop/admin/dashboard.php`.

The SQL file creates the `nethro_furniture` database and its tables with sample data.

## Admin Login

- Username: `admin@example.com`
- Password: `admin123`

The admin login accepts the username above. The seeded admin email is `admin@nethro.com`.

## Project Structure

- `index.php` - Shop home page
- `products.php` - Product catalogue
- `cart.php` - Shopping cart
- `checkout.php` - Checkout
- `orders.php` - Customer orders
- `admin/` - Admin dashboard and management pages
- `includes/` - Database, header, and footer files
- `css/` - Site and admin stylesheets
- `js/` - Front-end JavaScript
