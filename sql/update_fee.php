<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id']);
    $fee_amount = floatval($_POST['fee_amount']);
    $package_hours = intval($_POST['package_hours']);
    $time = trim($_POST['time']);

    $stmt = $conn->prepare("
        INSERT INTO course_fees (course_id, fee_amount, package_hours, time)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            fee_amount = VALUES(fee_amount),
            package_hours = VALUES(package_hours),
            time = VALUES(time)
    ");
    $stmt->bind_param("iids", $course_id, $fee_amount, $package_hours, $time);

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
