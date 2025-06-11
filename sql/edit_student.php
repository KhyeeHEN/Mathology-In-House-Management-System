<?php
// Include the database settings
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch student and associated user data based on the student_id from the GET request
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Query to fetch student details including course and contact information
$query = "
    SELECT 
        s.*, 
        u.email, 
        c.course_id, 
        c.course_name, 
        c.level, 
        pc.Last_Name AS primary_owner_last_name,
        pc.First_Name AS primary_owner_first_name,
        pc.Relationship_with_Student AS primary_relationship,
        pc.phone AS primary_phone,
        pc.email AS primary_email,
        pc.address AS primary_address,
        pc.postcode AS primary_postcode,
        sc.Last_Name AS secondary_owner_last_name,
        sc.First_Name AS secondary_owner_first_name,
        sc.Relationship_with_Student AS secondary_relationship,
        sc.phone AS secondary_phone
    FROM students s
    LEFT JOIN users u ON s.student_id = u.student_id
    LEFT JOIN student_courses scs ON s.student_id = scs.student_id
    LEFT JOIN courses c ON scs.course_id = c.course_id
    LEFT JOIN primary_contact_number pc ON s.student_id = pc.student_id
    LEFT JOIN secondary_contact_number sc ON s.student_id = sc.student_id
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
    // Student details
    $last_name = $conn->real_escape_string($_POST['Last_Name']);
    $first_name = $conn->real_escape_string($_POST['First_Name']);
    $gender = $conn->real_escape_string($_POST['Gender']);
    $dob = $conn->real_escape_string($_POST['DOB']);
    $school_syllabus = $conn->real_escape_string($_POST['School_Syllabus']);
    $school_intake = $conn->real_escape_string($_POST['School_Intake']);
    $current_grade = $conn->real_escape_string($_POST['Current_School_Grade']);
    $school = $conn->real_escape_string($_POST['School']);
    $mathology_level = $conn->real_escape_string($_POST['Mathology_Level']);
    $email = $conn->real_escape_string($_POST['Email']);
    $how_heard = $conn->real_escape_string($_POST['How_Heard_About_Us']);

    // Primary contact details
    $primary_owner_last_name = $conn->real_escape_string($_POST['primary_owner_last_name']);
    $primary_owner_first_name = $conn->real_escape_string($_POST['primary_owner_first_name']);
    $primary_relationship = $conn->real_escape_string($_POST['primary_relationship']);
    $primary_phone = $conn->real_escape_string($_POST['primary_phone']);
    $primary_email = $conn->real_escape_string($_POST['primary_email']);
    $primary_address = $conn->real_escape_string($_POST['primary_address']);
    $primary_postcode = $conn->real_escape_string($_POST['primary_postcode']);

    // Secondary contact details
    $secondary_owner_last_name = $conn->real_escape_string($_POST['secondary_owner_last_name']);
    $secondary_owner_first_name = $conn->real_escape_string($_POST['secondary_owner_first_name']);
    $secondary_relationship = $conn->real_escape_string($_POST['secondary_relationship']);
    $secondary_phone = $conn->real_escape_string($_POST['secondary_phone']);

    // Update student table
    $updateStudentQuery = "
        UPDATE students SET 
            Last_Name = '$last_name', 
            First_Name = '$first_name', 
            Gender = '$gender', 
            DOB = '$dob', 
            School_Syllabus = '$school_syllabus', 
            School_Intake = '$school_intake', 
            How_Did_You_Heard_About_Us = '$how_heard',
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

    // Update primary contact
    $updatePrimaryContactQuery = "
        UPDATE primary_contact_number SET 
            Last_Name = '$primary_owner_last_name', 
            First_Name = '$primary_owner_first_name',
            Relationship_with_Student = '$primary_relationship',
            phone = '$primary_phone',
            email = '$primary_email',
            address = '$primary_address',
            postcode = '$primary_postcode'
        WHERE student_id = $student_id
    ";

    // Update secondary contact
    $updateSecondaryContactQuery = "
        UPDATE secondary_contact_number SET 
            Last_Name = '$secondary_owner_last_name', 
            First_Name = '$secondary_owner_first_name',
            Relationship_with_Student = '$secondary_relationship',
            phone = '$secondary_phone'
        WHERE student_id = $student_id
    ";

    // Execute queries and redirect
    if (
        $conn->query($updateStudentQuery) &&
        $conn->query($updateUserQuery) &&
        $conn->query($updatePrimaryContactQuery) &&
        $conn->query($updateSecondaryContactQuery)
    ) {
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
        <h2>Student Details</h2>
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
        <select id="Mathology_Level" name="Mathology_Level" required>
            <?php for ($i = 1; $i <= 9; $i++) {
                $selected = ($student['Mathology_Level'] == $i) ? 'selected' : '';
                echo "<option value='$i' $selected>Level $i</option>";
            } ?>
        </select><br>
        <label for="Email">Email:</label>
        <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br>
        <label for="How_Heard_About_Us">How did you hear about us?</label>
        <input type="text" id="How_Heard_About_Us" name="How_Heard_About_Us" value="<?php echo htmlspecialchars($student['How_Did_You_Heard_About_Us']); ?>" required><br>

        <fieldset>
            <legend>Primary Contact</legend>
            <label for="primary_owner_last_name">Last Name:</label>
            <input type="text" id="primary_owner_last_name" name="primary_owner_last_name" value="<?php echo htmlspecialchars($student['primary_owner_last_name']); ?>" required><br>
            <label for="primary_owner_first_name">First Name:</label>
            <input type="text" id="primary_owner_first_name" name="primary_owner_first_name" value="<?php echo htmlspecialchars($student['primary_owner_first_name']); ?>" required><br>
            <label for="primary_relationship">Relationship:</label>
            <input type="text" id="primary_relationship" name="primary_relationship" value="<?php echo htmlspecialchars($student['primary_relationship']); ?>" required><br>
            <label for="primary_phone">Phone:</label>
            <input type="text" id="primary_phone" name="primary_phone" value="<?php echo htmlspecialchars($student['primary_phone']); ?>" required><br>
            <label for="primary_email">Email:</label>
            <input type="text" id="primary_email" name="primary_email" value="<?php echo htmlspecialchars($student['primary_email']); ?>" required><br>
            <label for="primary_address">Address:</label>
            <input type="text" id="primary_address" name="primary_address" value="<?php echo htmlspecialchars($student['primary_address']); ?>" required><br>
            <label for="primary_postcode">Postcode:</label>
            <input type="text" id="primary_postcode" name="primary_postcode" value="<?php echo htmlspecialchars($student['primary_postcode']); ?>" required><br>
        </fieldset>

        <fieldset>
            <legend>Secondary Contact</legend>
            <label for="secondary_owner_last_name">Last Name:</label>
            <input type="text" id="secondary_owner_last_name" name="secondary_owner_last_name" value="<?php echo htmlspecialchars($student['secondary_owner_last_name']); ?>"><br>
            <label for="secondary_owner_first_name">First Name:</label>
            <input type="text" id="secondary_owner_first_name" name="secondary_owner_first_name" value="<?php echo htmlspecialchars($student['secondary_owner_first_name']); ?>"><br>
            <label for="secondary_relationship">Relationship:</label>
            <input type="text" id="secondary_relationship" name="secondary_relationship" value="<?php echo htmlspecialchars($student['secondary_relationship']); ?>"><br>
            <label for="secondary_phone">Phone:</label>
            <input type="text" id="secondary_phone" name="secondary_phone" value="<?php echo htmlspecialchars($student['secondary_phone']); ?>"><br>
        </fieldset>

        <button type="submit">Update</button>
        <a href="../Pages/admin/users.php?active_tab=students">Cancel</a>
    </form>
</body>
</html>