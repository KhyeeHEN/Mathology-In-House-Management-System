<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    header("Location: ../Pages/admin/payment.php?error=Payment ID missing");
    exit();
}

$payment_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM payment WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);

if ($stmt->execute()) {
    header("Location: ../Pages/admin/payment.php?message=Payment deleted successfully");
} else {
    header("Location: ../Pages/admin/payment.php?error=Failed to delete payment");
}
exit();
