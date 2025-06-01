<?php
// Include the database settings
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch student and associated user data based on the student_id from the GET request
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$query = "
    SELECT s.*, u.email 
    FROM students s 
    LEFT JOIN users u ON s.student_id = u.student_id 
    WHERE s.student_id = $student_id
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    die("Student not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $conn->real_escape_string($_POST['Last_Name']);
    $first_name = $conn->real_escape_string($_POST['First_Name']);
    $gender = $conn->real_escape_string($_POST['Gender']);
    $dob = $conn->real_escape_string($_POST['DOB']);
    $school_syllabus = $conn->real_escape_string($_POST['School_Syllabus']);
    $school_intake = $conn->real_escape_string($_POST['School_Intake']);
    $how_heard = $conn->real_escape_string($_POST['How_Heard_About_Us']);
    $current_grade = $conn->real_escape_string($_POST['Current_School_Grade']);
    $school = $conn->real_escape_string($_POST['School']);
    $mathology_level = $conn->real_escape_string($_POST['Mathology_Level']);
    $email = $conn->real_escape_string($_POST['Email']);

    // Update student table
    $updateStudentQuery = "
        UPDATE students SET 
            Last_Name = '$last_name', 
            First_Name = '$first_name', 
            Gender = '$gender', 
            DOB = '$dob', 
            School_Syllabus = '$school_syllabus', 
            School_Intake = '$school_intake', 
            How_Heard_About_Us = '$how_heard',
            Current_School_Grade = '$current_grade', 
            School = '$school', 
            Mathology_Level = '$mathology_level'
        WHERE student_id = $student_id
    ";

    // Update users table
    $updateUserQuery = "
        UPDATE users SET 
            email = '$email' 
        WHERE student_id = $student_id
    ";

    // Execute both queries and redirect
    if ($conn->query($updateStudentQuery) && $conn->query($updateUserQuery)) {
        echo "Student updated successfully!";
        header("Location: ../Pages/admin/users.php?active_tab=students");
        exit();
    } else {
        echo "Error updating student: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/forms.css">
    <title>Edit Student</title>
</head>
<body>
    <h1>Edit Student</h1>
    <form method="POST">
        <label for="Last_Name">Last Name:</label>
        <input type="text" id="Last_Name" name="Last_Name" value="<?php echo htmlspecialchars($student['Last_Name']); ?>" required><br>

        <label for="First_Name">First Name:</label>
        <input type="text" id="First_Name" name="First_Name" value="<?php echo htmlspecialchars($student['First_Name']); ?>" required><br>

        <label for="Gender">Gender:</label>
        <select id="Gender" name="Gender" required>
            <option value="1" <?php if ($student['Gender']) echo 'selected'; ?>>Male</option>
            <option value="0" <?php if (!$student['Gender']) echo 'selected'; ?>>Female</option>
        </select><br>

        <label for="DOB">Date of Birth:</label>
        <input type="date" id="DOB" name="DOB" value="<?php echo htmlspecialchars($student['DOB']); ?>" required><br>

        <label for="School_Syllabus">School Syllabus:</label>
        <input type="text" id="School_Syllabus" name="School_Syllabus" value="<?php echo htmlspecialchars($student['School_Syllabus']); ?>" required><br>

        <!-- New: School Intake (Month) -->
        <label for="School_Intake">School Intake (Month):</label>
        <select id="School_Intake" name="School_Intake" required>
            <option value="">Select Month</option>
            <?php
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            foreach ($months as $month) {
                $selected = ($student['School_Intake'] === $month) ? 'selected' : '';
                echo "<option value=\"$month\" $selected>$month</option>";
            }
            ?>
        </select><br>


        <label for="Current_School_Grade">Current School Grade:</label>
        <input type="text" id="Current_School_Grade" name="Current_School_Grade" value="<?php echo htmlspecialchars($student['Current_School_Grade']); ?>" required><br>

        <label for="School">School:</label>
        <input type="text" id="School" name="School" value="<?php echo htmlspecialchars($student['School']); ?>" required><br>

        <label for="Mathology_Level">Mathology Level:</label>
        <input type="text" id="Mathology_Level" name="Mathology_Level" value="<?php echo htmlspecialchars($student['Mathology_Level']); ?>" required><br>

        <label for="Email">Email:</label>
        <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br>

        <button type="submit">Update</button>
        <a href="../Pages/admin/users.php?active_tab=students">Cancel</a>
    </form>
</body>
</html>