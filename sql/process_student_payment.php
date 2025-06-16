<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

$student_id = $_POST['student_id'];
$course_id = $_POST['course_id']; // Used for update
$mode = $_POST['payment_mode'];
$method = $_POST['payment_method'];
$amount = $_POST['payment_amount'];
$is_new = $_POST['is_new'];

// Insert payment
$stmt = $conn->prepare("INSERT INTO payment (
    student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status
) VALUES (?, ?, ?, ?, ?, 'pending')");
$deposit_status = $is_new ? 'yes' : 'no';


$stmt->bind_param("issds", $student_id, $method, $mode, $amount, $deposit_status);
$stmt->execute();
$stmt->close();

// Update is_new_student
if ($is_new) {
    $conn->query("UPDATE student_courses SET is_new_student = 0 WHERE student_id = $student_id AND course_id = $course_id");
}

header("Location: ../Pages/client/student_payment.php?message=Payment submitted successfully");
exit;
