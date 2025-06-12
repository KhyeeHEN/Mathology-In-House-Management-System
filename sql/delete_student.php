<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['student_id'])) {
    header("Location: ../Pages/admin/users.php?active_tab=students&error=Invalid+student+ID");
    exit();
}

$student_id = intval($_GET['student_id']);
$conn->begin_transaction();

try {
    // 1. Get all student_course_ids for this student
    $student_course_ids = [];
    $result = $conn->query("SELECT student_course_id FROM student_courses WHERE student_id = $student_id");
    while ($row = $result->fetch_assoc()) {
        $student_course_ids[] = $row['student_course_id'];
    }

    // 2. Delete all student_timetable rows for those courses
    if (!empty($student_course_ids)) {
        $ids_str = implode(',', $student_course_ids);
        $deleteTimetableQuery = "DELETE FROM student_timetable WHERE student_course_id IN ($ids_str)";
        if (!$conn->query($deleteTimetableQuery)) {
            throw new Exception("Failed to delete student timetable: " . $conn->error);
        }
    }

    // 3. Delete attendance records
    $deleteAttendanceQuery = "DELETE FROM attendance_records WHERE student_id = $student_id";
    if (!$conn->query($deleteAttendanceQuery)) {
        throw new Exception("Failed to delete student from attendance records: " . $conn->error);
    }

    // 4. Delete contact numbers
    $deletePrimaryContactQuery = "DELETE FROM primary_contact_number WHERE student_id = $student_id";
    if (!$conn->query($deletePrimaryContactQuery)) {
        throw new Exception("Failed to delete student's primary contact: " . $conn->error);
    }
    $deleteSecondaryContactQuery = "DELETE FROM secondary_contact_number WHERE student_id = $student_id";
    if (!$conn->query($deleteSecondaryContactQuery)) {
        throw new Exception("Failed to delete student's secondary contact: " . $conn->error);
    }

    // 5. Delete payment records
    $deletePaymentQuery = "DELETE FROM payment WHERE student_id = $student_id";
    if (!$conn->query($deletePaymentQuery)) {
        throw new Exception("Failed to delete student's payment records: " . $conn->error);
    }

    // 6. Delete from student_courses
    $deleteCoursesQuery = "DELETE FROM student_courses WHERE student_id = $student_id";
    if (!$conn->query($deleteCoursesQuery)) {
        throw new Exception("Failed to delete student courses: " . $conn->error);
    }

    // 7. Delete user
    $deleteUserQuery = "DELETE FROM users WHERE student_id = $student_id";
    if (!$conn->query($deleteUserQuery)) {
        throw new Exception("Failed to delete associated user: " . $conn->error);
    }

    // 8. Delete student
    $deleteStudentQuery = "DELETE FROM students WHERE student_id = $student_id";
    if (!$conn->query($deleteStudentQuery)) {
        throw new Exception("Failed to delete student: " . $conn->error);
    }

    $conn->commit();

    header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+and+associated+data+deleted+successfully");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../Pages/admin/users.php?active_tab=students&error=" . urlencode($e->getMessage()));
    exit();
}
?>