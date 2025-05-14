<?php
// Include the database settings
include 'settings.php';

// Get the student ID from the query string
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Start a transaction to ensure all deletions happen together
    $conn->begin_transaction();

    try {
        // Delete dependent rows from student_courses
        $deleteCoursesQuery = "DELETE FROM student_courses WHERE student_id = $student_id";
        if (!$conn->query($deleteCoursesQuery)) {
            throw new Exception("Failed to delete student courses: " . $conn->error);
        }

        // Delete the student from the database
        $deleteStudentQuery = "DELETE FROM students WHERE student_id = $student_id";
        if (!$conn->query($deleteStudentQuery)) {
            throw new Exception("Failed to delete student: " . $conn->error);
        }

        // Delete the associated user from the users table
        $deleteUserQuery = "DELETE FROM users WHERE student_id = $student_id";
        if (!$conn->query($deleteUserQuery)) {
            throw new Exception("Failed to delete associated user: " . $conn->error);
        }

        // Reset auto-increment to avoid gaps
        $conn->query("OPTIMIZE TABLE student_courses");
        $conn->query("OPTIMIZE TABLE students");
        $conn->query("OPTIMIZE TABLE users");

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