<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['instructor_id'])) {
    header("Location: ../Pages/admin/users.php?active_tab=instructors");
    exit();
}

$instructor_id = intval($_GET['instructor_id']);

// Start transaction for safety
$conn->begin_transaction();

try {
    // 1. Get all instructor_course_id for this instructor
    $courseIds = [];
    $res = $conn->query("SELECT instructor_course_id FROM instructor_courses WHERE instructor_id = $instructor_id");
    while ($row = $res->fetch_assoc()) {
        $courseIds[] = $row['instructor_course_id'];
    }

    if (!empty($courseIds)) {
        $ids = implode(',', $courseIds);
        // 2. Delete all instructor_timetable rows for those courses
        $conn->query("DELETE FROM instructor_timetable WHERE instructor_course_id IN ($ids)");
    }

    // 2.1 Delete attendance records for this instructor
    $conn->query("DELETE FROM attendance_records WHERE instructor_id = $instructor_id");

    // 3. Delete from instructor_courses
    $conn->query("DELETE FROM instructor_courses WHERE instructor_id = $instructor_id");

    // 4. Delete from users table (if you have a user record for this instructor)
    $conn->query("DELETE FROM users WHERE instructor_id = $instructor_id");

    // 5. Delete the instructor
    $conn->query("DELETE FROM instructor WHERE instructor_id = $instructor_id");

    // Commit transaction
    $conn->commit();

    header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+deleted+successfully");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Failed+to+delete+instructor". urlencode($e->getMessage()));
    exit();
}
?>