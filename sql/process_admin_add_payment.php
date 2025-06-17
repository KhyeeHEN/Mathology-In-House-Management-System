<?php
require_once '../Pages/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $method = $_POST['payment_method'];
    $mode = $_POST['payment_mode'];
    $amount = $_POST['payment_amount'];
    $deposit_status = $_POST['deposit_status'];
    $status = $_POST['payment_status'];

    $stmt = $conn->prepare("INSERT INTO payment (student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdss", $student_id, $method, $mode, $amount, $deposit_status, $status);

    if ($stmt->execute()) {
        header("Location: ../Pages/admin/payment.php?message=Payment added successfully");
    } else {
        header("Location: ../Pages/admin/add_payment.php?error=Failed to add payment.");
    }

    $stmt->close();
    $conn->close();
}
?>
