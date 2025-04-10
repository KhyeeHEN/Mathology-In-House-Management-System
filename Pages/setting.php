<?php
// Database configuration
$host = 'localhost:3310';
$user = 'root';  // Replace with your username
$password = '';  // Replace with your password (leave empty if none)
$database = 'mathology';

// Create a connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // Connection successful - you can remove this message in production
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 10px; border-radius: 5px;'>
          Database connection successful!
          </div>";
}

// Rest of your PHP code...
?>