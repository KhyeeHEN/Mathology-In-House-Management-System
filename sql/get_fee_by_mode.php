<?php
include '../setting.php';

$course_id = intval($_GET['course_id']);
$mode = $_GET['mode'];

$stmt = $conn->prepare("SELECT fee_amount FROM course_fees WHERE course_id = ? AND time = ?");
$stmt->bind_param("is", $course_id, $mode);
$stmt->execute();
$stmt->bind_result($fee);
$stmt->fetch();
$stmt->close();

echo json_encode(['fee' => $fee ?? 0]);
?>
