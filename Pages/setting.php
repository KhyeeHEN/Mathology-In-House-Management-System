<?php
// Database configuration - Update these with your actual credentials
define('DB_HOST', 'localhost:3310');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mathology');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection and handle errors gracefully
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("We're experiencing technical difficulties. Please try again later.");
}

// Set charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// Optional: Timezone setting
date_default_timezone_set('Asia/Kuala_Lumpur');
?>