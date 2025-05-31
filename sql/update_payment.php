<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $payment_method = $_POST['payment_method'];
    $payment_mode = $_POST['payment_mode'];
    $deposit_status = $_POST['deposit_status'];
    $payment_status = $_POST['payment_status'];

    $sql = "
        UPDATE payment SET
            payment_method = ?,
            payment_mode = ?,
            deposit_status = ?,
            payment_status = ?
        WHERE payment_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $payment_method, $payment_mode, $deposit_status, $payment_status, $payment_id);

    if ($stmt->execute()) {
        header("Location: ../Pages/admin/payment.php?message=Payment+updated+successfully");
    } else {
        header("Location: ../Pages/admin/payment.php?error=Update+failed");
    }

    $stmt->close();
} else {
    header("Location: ../Pages/admin/payment.php?error=Invalid+request");
}
$conn->close();
