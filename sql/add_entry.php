<?php
// Include the database settings
include 'settings.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'];

    if ($user_type === 'student') {
        // Student form submission
        $last_name = $conn->real_escape_string($_POST['Last_Name']);
        $first_name = $conn->real_escape_string($_POST['First_Name']);
        $gender = $conn->real_escape_string($_POST['Gender']);
        $dob = $conn->real_escape_string($_POST['DOB']);
        $school_syllabus = $conn->real_escape_string($_POST['School_Syllabus']);
        $current_grade = $conn->real_escape_string($_POST['Current_School_Grade']);
        $school = $conn->real_escape_string($_POST['School']);
        $mathology_level = $conn->real_escape_string($_POST['Mathology_Level']);

        $insertQuery = "INSERT INTO students (Last_Name, First_Name, Gender, DOB, School_Syllabus, Current_School_Grade, School, Mathology_Level)
                        VALUES ('$last_name', '$first_name', '$gender', '$dob', '$school_syllabus', '$current_grade', '$school', '$mathology_level')";

        if ($conn->query($insertQuery)) {
            header("Location: ../Pages/admin/users.php?active_tab=students&message=Student+added+successfully");
        } else {
            $error = "Error adding student: " . $conn->error;
        }
    } elseif ($user_type === 'instructor') {
        // Instructor form submission
        $last_name = $conn->real_escape_string($_POST['Last_Name']);
        $first_name = $conn->real_escape_string($_POST['First_Name']);
        $gender = $conn->real_escape_string($_POST['Gender']);
        $dob = $conn->real_escape_string($_POST['DOB']);
        $highest_education = $conn->real_escape_string($_POST['Highest_Education']);
        $remark = $conn->real_escape_string($_POST['Remark']);
        $training_status = $conn->real_escape_string($_POST['Training_Status']);

        $insertQuery = "INSERT INTO instructor (Last_Name, First_Name, Gender, DOB, Highest_Education, Remark, Training_Status)
                        VALUES ('$last_name', '$first_name', '$gender', '$dob', '$highest_education', '$remark', '$training_status')";

        if ($conn->query($insertQuery)) {
            header("Location: ../Pages/admin/users.php?active_tab=instructors&message=Instructor+added+successfully");
        } else {
            $error = "Error adding instructor: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Entry</title>
</head>
<body>
    <h1>Add New Entry</h1>
    <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>

    <form method="POST">
        <label for="user_type">User Type:</label>
        <select id="user_type" name="user_type" required onchange="toggleForm()">
            <option value="">Select User Type</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
        </select><br><br>

        <div id="student-form" style="display: none;">
            <h2>Student Details</h2>
            <label for="Last_Name">Last Name:</label>
            <input type="text" id="Last_Name" name="Last_Name"><br>
            <label for="First_Name">First Name:</label>
            <input type="text" id="First_Name" name="First_Name"><br>
            <label for="Gender">Gender:</label>
            <select id="Gender" name="Gender">
                <option value="1">Male</option>
                <option value="0">Female</option>
            </select><br>
            <label for="DOB">Date of Birth:</label>
            <input type="date" id="DOB" name="DOB"><br>
            <label for="School_Syllabus">School Syllabus:</label>
            <input type="text" id="School_Syllabus" name="School_Syllabus"><br>
            <label for="Current_School_Grade">Current School Grade:</label>
            <input type="text" id="Current_School_Grade" name="Current_School_Grade"><br>
            <label for="School">School:</label>
            <input type="text" id="School" name="School"><br>
            <label for="Mathology_Level">Mathology Level:</label>
            <input type="text" id="Mathology_Level" name="Mathology_Level"><br><br>
        </div>

        <div id="instructor-form" style="display: none;">
            <h2>Instructor Details</h2>
            <label for="Last_Name">Last Name:</label>
            <input type="text" id="Last_Name" name="Last_Name"><br>
            <label for="First_Name">First Name:</label>
            <input type="text" id="First_Name" name="First_Name"><br>
            <label for="Gender">Gender:</label>
            <select id="Gender" name="Gender">
                <option value="1">Male</option>
                <option value="0">Female</option>
            </select><br>
            <label for="DOB">Date of Birth:</label>
            <input type="date" id="DOB" name="DOB"><br>
            <label for="Highest_Education">Highest Education:</label>
            <input type="text" id="Highest_Education" name="Highest_Education"><br>
            <label for="Remark">Remark:</label>
            <textarea id="Remark" name="Remark"></textarea><br>
            <label for="Training_Status">Training Status:</label>
            <input type="text" id="Training_Status" name="Training_Status"><br><br>
        </div>

        <button type="submit">Add Entry</button>
        <a href="../Pages/admin/users.php">Cancel</a>
    </form>

    <script>
        function toggleForm() {
            const userType = document.getElementById('user_type').value;
            document.getElementById('student-form').style.display = userType === 'student' ? 'block' : 'none';
            document.getElementById('instructor-form').style.display = userType === 'instructor' ? 'block' : 'none';
        }
    </script>
</body>
</html>