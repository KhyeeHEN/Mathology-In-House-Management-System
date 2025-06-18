<?php
ob_start(); // Prevent output issues before header()
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// ✅ Get student_id using session user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT s.student_id FROM users u JOIN students s ON u.student_id = s.student_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($student_id);
$stmt->fetch();
$stmt->close();

// ✅ Collect POST data
$course_id = $_POST['course_id'];
$mode = $_POST['payment_mode'];
$method = $_POST['payment_method'];
$amount = $_POST['payment_amount'];

// ✅ Check if this is the student's first payment
$paid_check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
$paid_check->bind_param("i", $student_id);
$paid_check->execute();
$paid_check->bind_result($payment_count);
$paid_check->fetch();
$paid_check->close();

$is_first_payment = $payment_count === 0;
$deposit_status = $is_first_payment ? 'yes' : 'no';

// ✅ Insert the payment record
$stmt = $conn->prepare("INSERT INTO payment (
    student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status
) VALUES (?, ?, ?, ?, ?, 'paid')");
$stmt->bind_param("issds", $student_id, $method, $mode, $amount, $deposit_status);
$stmt->execute();
$new_payment_id = $conn->insert_id;
$stmt->close();

// ✅ Get package hours for the selected course and payment mode
$hours_stmt = $conn->prepare("SELECT package_hours FROM course_fees WHERE course_id = ? AND time = ?");
$hours_stmt->bind_param("is", $course_id, $mode);
$hours_stmt->execute();
$hours_stmt->bind_result($package_hours);
$hasResult = $hours_stmt->fetch();
$hours_stmt->close();

// ✅ Update attendance_records if package_hours is valid
if ($hasResult && $package_hours > 0) {
    $update = $conn->prepare("UPDATE attendance_records SET hours_remaining = hours_remaining + ? WHERE student_id = ?");
    $update->bind_param("ii", $package_hours, $student_id);
    $update->execute();
    $update->close();
}

// ✅ Redirect to generate invoice (ONLY if everything above succeeds)
header("Location: ../Pages/invoice/generate_invoice.php?generate_invoice=1&payment_id=$new_payment_id");
exit;
