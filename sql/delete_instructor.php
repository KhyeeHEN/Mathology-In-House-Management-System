<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Get the instructor ID from the query string
if (isset($_GET['instructor_id'])) {
    $instructor_id = intval($_GET['instructor_id']);

    // Start a transaction to ensure all deletions happen together
    $conn->begin_transaction();

    try {
        // Delete dependent rows from instructor_courses
        $deleteCoursesQuery = "DELETE FROM instructor_courses WHERE instructor_id = $instructor_id";
        if (!$conn->query($deleteCoursesQuery)) {
            throw new Exception("Failed to delete instructor courses: " . $conn->error);
        }

        // Delete the associated user from the users table
        $deleteUserQuery = "DELETE FROM users WHERE instructor_id = $instructor_id";
        if (!$conn->query($deleteUserQuery)) {
            throw new Exception("Failed to delete associated user: " . $conn->error);
        }

        $deleteAttendanceQuery = "DELETE FROM attendance_records WHERE instructor_id = $instructor_id";
        if (!$conn->query($deleteAttendanceQuery)) {
            throw new Exception("Failed to delete associated attendance data: " . $conn->error);
        }

        // Delete the instructor from the database
        $deleteInstructorQuery = "DELETE FROM instructor WHERE instructor_id = $instructor_id";
        if (!$conn->query($deleteInstructorQuery)) {
            throw new Exception("Failed to delete instructor: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();

        // Redirect back to the instructors tab with a success message
        header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+and+associated+data+deleted+successfully");
        exit();
    } catch (Exception $e) {
        // Roll back the transaction on failure
        $conn->rollback();

        // Redirect back with an error message
        header("Location: ../Pages/admin/users.php?active_tab=instructors&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect back if no instructor ID is provided
    header("Location: ../Pages/admin/users.php?active_tab=instructors&error=Invalid+instructor+ID");
    exit();
}
?>