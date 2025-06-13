<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['fee_id'])) {
    header("Location: ../Pages/admin/manage_fees.php?error=Fee ID missing");
    exit();
}

$fee_id = intval($_GET['fee_id']);

$stmt = $conn->prepare("DELETE FROM course_fees WHERE fee_id = ?");
$stmt->bind_param("i", $fee_id);

if ($stmt->execute()) {
    header("Location: ../Pages/admin/manage_fees.php?message=Fee deleted successfully");
} else {
    header("Location: ../Pages/admin/manage_fees.php?error=Failed to delete fee");
}
exit();
