<?php
// Include the database settings
include 'settings.php';

// Fetch instructor data based on the instructor_id from the GET request
$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;
$query = "SELECT * FROM instructor WHERE instructor_id = $instructor_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $instructor = $result->fetch_assoc();
} else {
    die("Instructor not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $conn->real_escape_string($_POST['Last_Name']);
    $first_name = $conn->real_escape_string($_POST['First_Name']);
    $gender = $conn->real_escape_string($_POST['Gender']);
    $dob = $conn->real_escape_string($_POST['DOB']);
    $highest_education = $conn->real_escape_string($_POST['Highest_Education']);
    $remark = $conn->real_escape_string($_POST['Remark']);
    $training_status = $conn->real_escape_string($_POST['Training_Status']);

    $updateQuery = "UPDATE instructor SET 
        Last_Name = '$last_name', 
        First_Name = '$first_name', 
        Gender = '$gender', 
        DOB = '$dob', 
        Highest_Education = '$highest_education', 
        Remark = '$remark', 
        Training_Status = '$training_status'
        WHERE instructor_id = $instructor_id";

    if ($conn->query($updateQuery)) {
        echo "Instructor updated successfully!";
        header("Location: ../Pages/admin/users.php?active_tab=instructors");
    } else {
        echo "Error updating instructor: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Instructor</title>
</head>
<body>
    <h1>Edit Instructor</h1>
    <form method="POST">
        <label for="Last_Name">Last Name:</label>
        <input type="text" id="Last_Name" name="Last_Name" value="<?php echo $instructor['Last_Name']; ?>" required><br>

        <label for="First_Name">First Name:</label>
        <input type="text" id="First_Name" name="First_Name" value="<?php echo $instructor['First_Name']; ?>" required><br>

        <label for="Gender">Gender:</label>
        <select id="Gender" name="Gender" required>
            <option value="1" <?php if ($instructor['Gender']) echo 'selected'; ?>>Male</option>
            <option value="0" <?php if (!$instructor['Gender']) echo 'selected'; ?>>Female</option>
        </select><br>

        <label for="DOB">Date of Birth:</label>
        <input type="date" id="DOB" name="DOB" value="<?php echo $instructor['DOB']; ?>" required><br>

        <label for="Highest_Education">Highest Education:</label>
        <input type="text" id="Highest_Education" name="Highest_Education" value="<?php echo $instructor['Highest_Education']; ?>" required><br>

        <label for="Remark">Remark:</label>
        <textarea id="Remark" name="Remark"><?php echo $instructor['Remark']; ?></textarea><br>

        <label for="Training_Status">Training Status:</label>
        <input type="text" id="Training_Status" name="Training_Status" value="<?php echo $instructor['Training_Status']; ?>" required><br>

        <button type="submit">Update</button>
        <a href="../Pages/admin/users.php?active_tab=instructors">Cancel</a>
    </form>
</body>
</html>