<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Get record_id from URL
$record_id = isset($_GET['record_id']) ? intval($_GET['record_id']) : 0;

// Fetch attendance record
$query = "
    SELECT ar.*, s.First_Name AS student_first, s.Last_Name AS student_last,
           i.First_Name AS instructor_first, i.Last_Name AS instructor_last
    FROM attendance_records ar
    LEFT JOIN students s ON ar.student_id = s.student_id
    LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
    WHERE ar.record_id = $record_id
";
$result = $conn->query($query);

if ($result->num_rows === 0) {
    die("Attendance record not found.");
}

$record = $result->fetch_assoc();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendance_datetime = $conn->real_escape_string($_POST['attendance_datetime']);
    $hours_attended = floatval($_POST['hours_attended']);
    $hours_replacement = floatval($_POST['hours_replacement']);
    $hours_remaining = floatval($_POST['hours_remaining']);
    $status = $conn->real_escape_string($_POST['status']);
    $course = $conn->real_escape_string($_POST['course']);

    $updateSql = "
        UPDATE attendance_records SET
            attendance_datetime = '$attendance_datetime',
            hours_attended = $hours_attended,
            hours_replacement = $hours_replacement,
            hours_remaining = $hours_remaining,
            status = '$status',
            course = '$course',
            updated_at = NOW()
        WHERE record_id = $record_id
    ";

    if ($conn->query($updateSql)) {
        echo "Attendance updated successfully!";
        header("Location: ../Pages/admin/attendance.php");
        exit();
    } else {
        echo "Error updating: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #1f2937;
        }

        form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1f2937;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: box-shadow 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1f2937;
            box-shadow: 0 0 5px rgba(31, 41, 55, 0.5);
        }

        p {
            margin-bottom: 10px;
        }

        button {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(71, 82, 95);
        }

        a {
            margin-left: 15px;
            text-decoration: none;
            color: #1f2937;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>

<body>
    <h1>Edit Attendance Record</h1>
    <form method="POST">
        <p><strong>Student:</strong> <?php echo htmlspecialchars($record['student_last'] . ' ' . $record['student_first']); ?></p>
        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($record['instructor_last'] . ' ' . $record['instructor_first']); ?></p>
        <p><strong>Timetable DateTime:</strong> <?php echo htmlspecialchars($record['timetable_datetime']); ?></p>

        <label for="attendance_datetime">Attendance DateTime:</label>
        <?php
        // Use the record value if available, otherwise use current datetime
        $attendance_value = !empty($record['attendance_datetime'])
            ? date('Y-m-d\TH:i', strtotime($record['attendance_datetime']))
            : date('Y-m-d\TH:i');
        ?>
        <input type="datetime-local" id="attendance_datetime" name="attendance_datetime" value="<?php echo $attendance_value; ?>" required>


        <label for="hours_attended">Hours Attended:</label>
        <input type="number" step="0.1" name="hours_attended" value="<?php echo $record['hours_attended']; ?>" required><br>

        <label for="hours_replacement">Hours Replacement:</label>
        <input type="number" step="0.1" name="hours_replacement" value="<?php echo $record['hours_replacement']; ?>"><br>

        <label for="hours_remaining">Hours Remaining:</label>
        <input type="number" step="0.1" name="hours_remaining" value="<?php echo $record['hours_remaining']; ?>"><br>

        <label for="status">Status:</label>
        <select name="status" required>
            <option value="Present" <?php if ($record['status'] == 'Present') echo 'selected'; ?>>Present</option>
            <option value="Absent" <?php if ($record['status'] == 'Absent') echo 'selected'; ?>>Absent</option>
            <option value="Excused" <?php if ($record['status'] == 'Excused') echo 'selected'; ?>>Excused</option>
        </select><br>

        <label for="course">Course:</label>
        <input type="text" name="course" value="<?php echo htmlspecialchars($record['course']); ?>" required><br>

        <button type="submit">Update</button>
        <a href="../Pages/admin/attendance.php">Cancel</a>
    </form>
</body>

</html>
