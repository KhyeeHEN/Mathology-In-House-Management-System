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


$has_paid = false;
$paid_check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
$paid_check->bind_param("i", $student_id);
$paid_check->execute();
$paid_check->bind_result($payment_count);
$paid_check->fetch();
$paid_check->close();

$has_paid = $payment_count > 0;

// Only include one-time fees if it's their first payment
$one_time_fee = 0;
if (!$has_paid) {
    $fee_q = $conn->query("SELECT SUM(amount) AS total FROM one_time_fees");
    $one_time_fee = $fee_q->fetch_assoc()['total'];
}

header("Location: ../Pages/client/student_payment.php?message=Payment submitted successfully");
exit;
?>
