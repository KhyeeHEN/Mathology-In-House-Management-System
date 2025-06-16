<?php
session_start();
include '../setting.php';

$student_id = $_SESSION['student_id'];
$course_id = $_POST['course_id'];
$mode = $_POST['payment_mode'];
$method = $_POST['payment_method'];
$amount = $_POST['payment_amount'];
$is_new = $_POST['is_new'];

// Insert payment
$stmt = $conn->prepare("INSERT INTO payment (student_id, course_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
$deposit_status = $is_new ? 'yes' : 'no';
$stmt->bind_param("iissds", $student_id, $course_id, $method, $mode, $amount, $deposit_status);
$stmt->execute();
$stmt->close();

// Mark student as not new
if ($is_new) {
    $conn->query("UPDATE student_courses SET is_new_student = 0 WHERE student_id = $student_id AND course_id = $course_id");
}

header("Location: student_payment.php?message=Payment submitted successfully");
?>
