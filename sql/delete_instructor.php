<?php
// Include the database settings
include 'settings.php';

// Get the instructor ID from the query string
if (isset($_GET['instructor_id'])) {
    $instructor_id = intval($_GET['instructor_id']);

    // Delete the instructor from the database
    $deleteQuery = "DELETE FROM instructor WHERE instructor_id = $instructor_id";
    if ($conn->query($deleteQuery)) {
        // Redirect back to the instructors tab with a success message
        header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+deleted+successfully");
        exit();
    } else {
        // Redirect back with an error message
        header("Location:  ../Pages/admin/users.php?active_tab=instructors&error=Failed+to+delete+instructor");
        exit();
    }
} else {
    // Redirect back if no instructor ID is provided
    header("Location: ../Pages/admin/users.php?active_tab=instructors&error=Invalid+instructor+ID");
    exit();
}
?>