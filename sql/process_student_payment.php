<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Get student_id using session user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT s.student_id FROM users u JOIN students s ON u.student_id = s.student_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

//  Collect POST data
$course_id = $_POST['course_id'];
$mode = $_POST['payment_mode'];
$method = $_POST['payment_method'];
$amount = $_POST['payment_amount'];

// Check if this is the student's first payment
$paid_check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
$paid_check->bind_param("i", $student_id);
$paid_check->execute();
$paid_check->bind_result($payment_count);
$paid_check->fetch();
$paid_check->close();

$is_first_payment = $payment_count === 0;
$deposit_status = $is_first_payment ? 'yes' : 'no';

// Insert the payment record
$stmt = $conn->prepare("INSERT INTO payment (
    student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status
) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("issds", $student_id, $method, $mode, $amount, $deposit_status);
$stmt->execute();
$stmt->close();

// Redirect back to payment page with success message
header("Location: ../Pages/client/student_payment.php?message=Payment submitted successfully");
exit;
