<?php
// Include the database settings
include 'settings.php';

// Get the student ID from the query string
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Delete the student from the database
    $deleteQuery = "DELETE FROM students WHERE student_id = $student_id";
    if ($conn->query($deleteQuery)) {
        // Redirect back to the students tab with a success message
        header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+deleted+successfully");
        exit();
    } else {
        // Redirect back with an error message
        header("Location: ../Pages/admin/users.php?active_tab=students&error=Failed+to+delete+student");
        exit();
    }
} else {
    // Redirect back if no student ID is provided
    header("Location: ../Pages/admin/users.php?active_tab=students&error=Invalid+student+ID");
    exit();
}
?>