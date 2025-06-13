<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fee_id = intval($_POST['fee_id']);
    $fee_amount = floatval($_POST['fee_amount']);
    $package_hours = intval($_POST['package_hours']);
    $time = trim($_POST['time']);

    $stmt = $conn->prepare("
        UPDATE course_fees
        SET fee_amount = ?, package_hours = ?, time = ?
        WHERE fee_id = ?
    ");
    $stmt->bind_param("disi", $fee_amount, $package_hours, $time, $fee_id);

    if ($stmt->execute()) {
        header("Location: ../Pages/admin/manage_fees.php?message=Fee updated successfully");
        exit();
    } else {
        header("Location: ../Pages/admin/manage_fees.php?error=Failed to update fee");
        exit();
    }
} else {
    header("Location: ../Pages/admin/manage_fees.php?error=Invalid request");
    exit();
}
