<?php
include '../setting.php';

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

$response = ['fee' => 0];

if ($course_id && $mode) {
    $stmt = $conn->prepare("SELECT fee_amount FROM course_fees WHERE course_id = ? AND time = ?");
    $stmt->bind_param("is", $course_id, $mode);
    $stmt->execute();
    $stmt->bind_result($fee);
    if ($stmt->fetch()) {
        $response['fee'] = number_format($fee, 2);
    }
    $stmt->close();
}

echo json_encode($response);
