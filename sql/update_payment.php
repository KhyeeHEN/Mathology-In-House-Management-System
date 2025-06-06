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
        echo "<script>
        alert('Payment updated successfully!');
        window.location.href = '../Pages/admin/payment.php';
    </script>";
    } else {
        echo "<script>
        alert('Update failed. Please try again.');
        window.location.href = '../Pages/admin/payment.php';
    </script>";
    }

    $stmt->close();
} else {
    header("Location: ../Pages/admin/payment.php?error=Invalid+request");
}
$conn->close();
