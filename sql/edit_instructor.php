<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch instructor and associated user data based on the instructor_id from the GET request
$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;
$stmt = $conn->prepare("SELECT i.*, u.email FROM instructor i LEFT JOIN users u ON i.instructor_id = u.instructor_id WHERE i.instructor_id = ?");
if (!$stmt) {
    die("Error preparing query: " . $conn->error);
}
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $instructor = $result->fetch_assoc();
} else {
    die("Instructor not found.");
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = $conn->real_escape_string($_POST['Last_Name']);
    $first_name = $conn->real_escape_string($_POST['First_Name']);
    $gender = $conn->real_escape_string($_POST['Gender']);
    $dob = $conn->real_escape_string($_POST['DOB']);
    $highest_education = $conn->real_escape_string($_POST['Highest_Education']);
    $remark = $conn->real_escape_string($_POST['Remark']);
    $training_status = $conn->real_escape_string($_POST['Training_Status']);
    $email = $conn->real_escape_string($_POST['Email']);
    $employment_type = $conn->real_escape_string($_POST['Employment_Type']);
    $working_days = $conn->real_escape_string($_POST['Working_Days']);
    $worked_days = (int)$_POST['Worked_Days'];

    // Validate worked_days (must be non-negative)
    if ($worked_days < 0) {
        $error = "Worked days cannot be negative.";
    } else {
        // Update instructor table with prepared statement
        $stmt = $conn->prepare("UPDATE instructor SET 
            Last_Name = ?, 
            First_Name = ?, 
            Gender = ?, 
            DOB = ?, 
            Highest_Education = ?, 
            Remark = ?, 
            Training_Status = ?, 
            Employment_Type = ?, 
            Working_Days = ?, 
            Worked_Days = ? 
            WHERE instructor_id = ?");
        if (!$stmt) {
            $error = "Failed to prepare update statement: " . $conn->error;
        } else {
            $stmt->bind_param("sss/sssssi", $last_name, $first_name, $gender, $dob, $highest_education, $remark, $training_status, $employment_type, $working_days, $worked_days, $instructor_id);
            if ($stmt->execute()) {
                // Update users table
                $stmt_user = $conn->prepare("UPDATE users SET email = ? WHERE instructor_id = ?");
                if ($stmt_user) {
                    $stmt_user->bind_param("si", $email, $instructor_id);
                    $stmt_user->execute();
                    $stmt_user->close();
                }
                $message = "Instructor updated successfully!";
                header("Location: ../Pages/admin/users.php?active_tab=instructors&message=" . urlencode($message));
                exit();
            } else {
                $error = "Failed to update instructor: " . $stmt->error;
            }
            $stmt->close();
        }
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
    <title>Edit Instructor</title>
</head>
<body>
    <h1>Edit Instructor</h1>
    <?php if (isset($message)): ?>
        <div style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST">
        <label for="Last_Name">Last Name:</label>
        <input type="text" id="Last_Name" name="Last_Name" value="<?php echo htmlspecialchars($instructor['Last_Name']); ?>" required><br>

        <label for="First_Name">First Name:</label>
        <input type="text" id="First_Name" name="First_Name" value="<?php echo htmlspecialchars($instructor['First_Name']); ?>" required><br>

        <label for="Gender">Gender:</label>
        <select id="Gender" name="Gender" required>
            <option value="1" <?php if ($instructor['Gender']) echo 'selected'; ?>>Male</option>
            <option value="0" <?php if (!$instructor['Gender']) echo 'selected'; ?>>Female</option>
        </select><br>

        <label for="DOB">Date of Birth:</label>
        <input type="date" id="DOB" name="DOB" value="<?php echo htmlspecialchars($instructor['DOB']); ?>" required><br>

        <label for="Highest_Education">Highest Education:</label>
        <input type="text" id="Highest_Education" name="Highest_Education" value="<?php echo htmlspecialchars($instructor['Highest_Education']); ?>" required><br>

        <label for="Employment_Type">Employment Type:</label>
        <select id="Employment_Type" name="Employment_Type" required>
            <option value="Full-Time" <?php if ($instructor['Employment_Type'] === 'Full-Time') echo 'selected'; ?>>Full-Time</option>
            <option value="Part-Time" <?php if ($instructor['Employment_Type'] === 'Part-Time') echo 'selected'; ?>>Part-Time</option>
        </select><br>

        <label for="Working_Days">Working Days (Part-Time only):</label>
        <input type="text" id="Working_Days" name="Working_Days" value="<?php echo htmlspecialchars($instructor['Working_Days'] ?? ''); ?>" placeholder="e.g., Monday,Wednesday,Friday" <?php echo $instructor['Employment_Type'] === 'Full-Time' ? 'disabled' : ''; ?>><br>

        <label for="Worked_Days">Worked Days:</label>
        <input type="number" id="Worked_Days" name="Worked_Days" value="<?php echo htmlspecialchars($instructor['Worked_Days'] ?? 0); ?>" min="0" required><br>

        <label for="Remark">Remark:</label>
        <textarea id="Remark" name="Remark"><?php echo htmlspecialchars($instructor['Remark'] ?? ''); ?></textarea><br>

        <label for="Training_Status">Training Status:</label>
        <input type="text" id="Training_Status" name="Training_Status" value="<?php echo htmlspecialchars($instructor['Training_Status']); ?>" required><br>

        <label for="Email">Email:</label>
        <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($instructor['email']); ?>" required><br>

        <button type="submit">Update</button>
        <a href="../Pages/admin/users.php?active_tab=instructors">Cancel</a>
    </form>

    <script>
        // JavaScript to disable/enable Working_Days based on Employment_Type
        document.getElementById('Employment_Type').addEventListener('change', function() {
            const workingDaysInput = document.getElementById('Working_Days');
            workingDaysInput.disabled = this.value === 'Full-Time';
            if (this.value === 'Full-Time') {
                workingDaysInput.value = '';
            }
        });
    </script>
</body>
</html>