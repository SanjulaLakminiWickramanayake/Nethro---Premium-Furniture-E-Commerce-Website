<?php
// includes/db.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change to your password
define('DB_NAME', 'nethro_furniture');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle special characters
$conn->set_charset("utf8mb4");

// Helper function for prepared statements
function executeQuery($sql, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        trigger_error('DB prepare failed: ' . $conn->error, E_USER_WARNING);
        return false;
    }
    if ($types && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        trigger_error('DB execute failed: ' . $stmt->error, E_USER_WARNING);
    }
    return $stmt;
}

// Helper function to get single result
function fetchOne($sql, $types = "", $params = []) {
    $stmt = executeQuery($sql, $types, $params);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Helper function to get multiple results
function fetchAll($sql, $types = "", $params = []) {
    $stmt = executeQuery($sql, $types, $params);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure there is at least one admin user in the database
function ensureDefaultAdmin() {
    global $conn;

    $stmt = executeQuery("SHOW TABLES LIKE 'admins'");
    if (!$stmt) {
        return;
    }
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return;
    }

    $admin = fetchOne("SELECT id FROM admins LIMIT 1");
    if (!$admin) {
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        executeQuery(
            "INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)",
            "ssss",
            ['admin', $passwordHash, 'admin@nethro.com', 'System Administrator']
        );
    }
}

ensureDefaultAdmin();

// Get current user if logged in
$current_user = null;
if (isset($_SESSION['user_id'])) {
    $current_user = fetchOne("SELECT * FROM users WHERE id = ?", "i", [$_SESSION['user_id']]);
}

// Get cart count
function getCartCount() {
    global $conn;
    $count = 0;
    
    if (isset($_SESSION['user_id'])) {
        $result = fetchOne("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", "i", [$_SESSION['user_id']]);
        $count = $result['count'] ?? 0;
    } elseif (isset($_SESSION['cart_session'])) {
        $result = fetchOne("SELECT SUM(quantity) as count FROM cart WHERE session_id = ?", "s", [$_SESSION['cart_session']]);
        $count = $result['count'] ?? 0;
    }
    
    return $count;
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['admin_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Redirect function
function redirect($url) {
    session_write_close();
    header("Location: " . $url);
    exit();
}

// Flash message system
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format price
function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

// Check if customer is returning (has previous orders)
function isReturningCustomer($user_id) {
    $result = fetchOne("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status != 'cancelled'", "i", [$user_id]);
    return $result['count'] > 0;
}

// Get discount percentage for returning customers
function getDiscountPercent() {
    return 10; // 10% discount
}
?>