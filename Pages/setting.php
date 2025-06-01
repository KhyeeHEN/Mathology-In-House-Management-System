<?php
define('DB_HOST', '127.0.0.1:3306'); 
define('DB_USER', 'u656820910_admin'); 
define('DB_PASS', 'Thisislife23');    
define('DB_NAME', 'u656820910_mathology');

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
