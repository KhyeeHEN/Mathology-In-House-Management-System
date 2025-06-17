<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    header("Location: ../Pages/admin/payment.php?error=Payment+ID+missing");
    exit();
}

$payment_id = intval($_GET['id']);

// First, get the invoice path before deleting the record
$stmt = $conn->prepare("SELECT invoice_path FROM payment WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$stmt->bind_result($invoice_path);
$stmt->fetch();
$stmt->close();

// Delete the invoice file if it exists
if (!empty($invoice_path)) {
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $invoice_path;
    if (file_exists($absolutePath)) {
        unlink($absolutePath);  // Delete the file
    }
}

// Now delete the payment record
$stmt = $conn->prepare("DELETE FROM payment WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);

if ($stmt->execute()) {
    header("Location: ../Pages/admin/payment.php?message=Payment+and+invoice+deleted+successfully");
} else {
    header("Location: ../Pages/admin/payment.php?error=Failed+to+delete+payment");
}
$stmt->close();
$conn->close();
exit();
?>
