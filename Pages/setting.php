<?php
$host="localhost:3310";
$user="root";
$pwd="";
$sql_db="mathology";

// Create connection
$conn = new mysqli($host, $user, $pwd, $sql_db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>