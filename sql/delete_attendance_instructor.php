<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';
session_start();

if ($_SESSION['role'] !== 'instructor') {
    header('Location: /Pages/login.php');
    exit;
}

$instructor_id = intval($_SESSION['instructor_id']);
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($record_id > 0) {
    $stmt = $conn->prepare("DELETE FROM attendance_records WHERE record_id = ? AND instructor_id = ?");
    $stmt->bind_param("ii", $record_id, $instructor_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../instructors/attendance_instructors.php?message=Record+deleted");
        exit;
    } else {
        header("Location: ../instructors/attendance_instructors.php?error=Unauthorized+or+record+not+found");
        exit;
    }
} else {
    header("Location: ../instructors/attendance_instructors.php?error=Invalid+ID");
    exit;
}
