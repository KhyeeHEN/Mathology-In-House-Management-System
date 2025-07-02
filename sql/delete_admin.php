<?php
require_once '../../Pages/setting.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Only allow deleting admins (for extra safety)
if (!isset($_GET['user_id'])) {
    header("Location: users.php?active_tab=admins&error=Missing+user_id");
    exit;
}

$admin_id = intval($_GET['user_id']);
if ($admin_id == $_SESSION['user_id']) {
    // Prevent self-delete
    header("Location: users.php?active_tab=admins&error=You+cannot+delete+yourself");
    exit;
}

// Check if the admin exists
$res = $conn->query("SELECT * FROM users WHERE user_id = $admin_id AND role = 'admin'");
if (!$res || $res->num_rows === 0) {
    header("Location: users.php?active_tab=admins&error=Admin+not+found");
    exit;
}

// Proceed to delete
if ($conn->query("DELETE FROM users WHERE user_id = $admin_id AND role = 'admin'")) {
    header("Location: users.php?active_tab=admins&message=Admin+deleted+successfully");
    exit;
} else {
    header("Location: users.php?active_tab=admins&error=Failed+to+delete+admin");
    exit;
}