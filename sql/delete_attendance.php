<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    header("Location: ../Pages/admin/attendance.php?error=Attendance ID missing");
    exit();
}

$attendance_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM attendance_records WHERE attendance_id = ?");
$stmt->bind_param("i", $attendance_id);

if ($stmt->execute()) {
    header("Location: ../Pages/admin/attendance.php?message=Attendance record deleted successfully");
} else {
    header("Location: ../Pages/admin/attendance.php?error=Failed to delete attendance record");
}
exit();
