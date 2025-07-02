<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';
session_start();

// Ensure logged-in instructor
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: /Pages/login.php");
    exit();
}

// Validate attendance ID
if (!isset($_GET['id'])) {
    header("Location: /Pages/instructors/attendance_instructors.php?error=Attendance+ID+missing");
    exit();
}

$attendance_id = intval($_GET['id']);

// Optional: make sure this instructor owns the record
$instructor_id = intval($_SESSION['user_id']);
$checkStmt = $conn->prepare("SELECT record_id FROM attendance_records WHERE record_id = ? AND instructor_id = ?");
$checkStmt->bind_param("ii", $attendance_id, $instructor_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    header("Location: /Pages/instructors/attendance_instructors.php?error=Unauthorized+or+record+not+found");
    exit();
}

// Proceed to delete
$stmt = $conn->prepare("DELETE FROM attendance_records WHERE record_id = ?");
$stmt->bind_param("i", $attendance_id);

if ($stmt->execute()) {
    header("Location: /Pages/instructors/attendance_instructors.php?message=Attendance+record+deleted+successfully");
} else {
    header("Location: /Pages/instructors/attendance_instructors.php?error=Failed+to+delete+attendance+record");
}
exit();
?>
