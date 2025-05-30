<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Get the student ID from the query string
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Start a transaction to ensure all deletions happen together
    $conn->begin_transaction();

    try {
        // Get all student_course_ids for this student
        $student_courses_result = $conn->query("SELECT student_course_id FROM student_courses WHERE student_id = $student_id");
        $student_course_ids = [];
        while ($row = $student_courses_result->fetch_assoc()) {
            $student_course_ids[] = $row['student_course_id'];
        }

        // Delete all entries in student_timetable linked to these student_course_ids
        if (!empty($student_course_ids)) {
            $ids_str = implode(',', $student_course_ids);
            $deleteTimetableQuery = "DELETE FROM student_timetable WHERE student_course_id IN ($ids_str)";
            if (!$conn->query($deleteTimetableQuery)) {
                throw new Exception("Failed to delete student timetable: " . $conn->error);
            }
        }

        // Delete dependent rows from student_courses
        $deleteCoursesQuery = "DELETE FROM student_courses WHERE student_id = $student_id";
        if (!$conn->query($deleteCoursesQuery)) {
            throw new Exception("Failed to delete student courses: " . $conn->error);
        }

        // Delete the associated user from the users table
        $deleteUserQuery = "DELETE FROM users WHERE student_id = $student_id";
        if (!$conn->query($deleteUserQuery)) {
            throw new Exception("Failed to delete associated user: " . $conn->error);
        }

        // Delete the student from the database
        $deleteStudentQuery = "DELETE FROM students WHERE student_id = $student_id";
        if (!$conn->query($deleteStudentQuery)) {
            throw new Exception("Failed to delete student: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();

        // Redirect back to the students tab with a success message
        header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+and+associated+data+deleted+successfully");
        exit();
    } catch (Exception $e) {
        // Roll back the transaction on failure
        $conn->rollback();

        // Redirect back with an error message
        header("Location: ../Pages/admin/users.php?active_tab=students&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect back if no student ID is provided
    header("Location: ../Pages/admin/users.php?active_tab=students&error=Invalid+student+ID");
    exit();
}
?>