<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: /Pages/login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

// Get instructor_id from users table
$result = $conn->query("SELECT instructor_id FROM users WHERE user_id = $user_id AND role = 'instructor'");
if (!$result || $result->num_rows === 0) {
    header("Location: ../Pages/instructors/attendance_instructors.php?error=Unauthorized+access");
    exit;
}
$instructor_id = intval($result->fetch_assoc()['instructor_id']);

// Validate record ID
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($record_id <= 0) {
    header("Location: ../Pages/instructors/attendance_instructors.php?error=Invalid+record+ID");
    exit;
}

// Perform the deletion
$stmt = $conn->prepare("DELETE FROM attendance_records WHERE record_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $record_id, $instructor_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: ../Pages/instructors/attendance_instructors.php?message=Record+deleted+successfully");
} else {
    header("Location: ../Pages/instructors/attendance_instructors.php?error=Failed+to+delete+record+or+not+found");
}
exit;
?>
