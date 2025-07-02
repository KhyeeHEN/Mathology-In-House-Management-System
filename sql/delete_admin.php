<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['admin_id'])) {
    header("Location: ../Pages/admin/users.php?active_tab=admins&error=Invalid+admin+ID");
    exit();
}

$admin_id = intval($_GET['admin_id']);
session_start();

// Prevent deleting your own account
if ($admin_id == $_SESSION['user_id']) {
    header("Location: ../Pages/admin/users.php?active_tab=admins&error=You+cannot+delete+your+own+admin+account+while+logged+in");
    exit();
}

$conn->begin_transaction();

try {
    // 1. Delete admin user record only (no cascading needed)
    $deleteUserQuery = "DELETE FROM users WHERE user_id = $admin_id AND role = 'admin'";
    if (!$conn->query($deleteUserQuery)) {
        throw new Exception("Failed to delete admin user: " . $conn->error);
    }

    $conn->commit();

    header("Location: ../Pages/admin/users.php?active_tab=admins&message=Admin+deleted+successfully");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../Pages/admin/users.php?active_tab=admins&error=" . urlencode($e->getMessage()));
    exit();
}
?>