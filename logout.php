<?php
require_once 'includes/db.php';

// Clear session
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Set flash message and redirect
session_start();
setFlash('success', 'You have been logged out successfully');
redirect('index.php');
?>