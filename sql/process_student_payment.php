<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

$student_id = $_POST['student_id'];
$course_id = $_POST['course_id'];
$mode = $_POST['payment_mode'];
$method = $_POST['payment_method'];
$amount = $_POST['payment_amount'];

// Check if student is new
$check = $conn->prepare("SELECT is_new_student FROM students WHERE student_id = ?");
$check->bind_param("i", $student_id);
$check->execute();
$check->bind_result($is_new);
$check->fetch();
$check->close();

// Set deposit status
$deposit_status = $is_new ? 'yes' : 'no';

// Insert new payment
$stmt = $conn->prepare("INSERT INTO payment (
    student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status
) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("issds", $student_id, $method, $mode, $amount, $deposit_status);
$stmt->execute();
$stmt->close();

// ✅ Mark student as not new
if ($is_new) {
    $conn->query("UPDATE students SET is_new_student = 0 WHERE student_id = $student_id");
}

header("Location: ../Pages/client/student_payment.php?message=Payment submitted successfully");
exit;
?>
