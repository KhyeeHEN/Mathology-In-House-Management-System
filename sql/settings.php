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
}
?>