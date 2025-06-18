<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Ensure logged-in instructor
if ($_SESSION['role'] !== 'instructor') {
    header('Location: /Pages/login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$instructorQuery = $conn->query("SELECT instructor_id FROM users WHERE user_id = $user_id AND role = 'instructor'");
if (!$instructorQuery || $instructorQuery->num_rows == 0) {
    die("Unauthorized.");
}
$instructor_id = $instructorQuery->fetch_assoc()['instructor_id'];

// Fetch students and courses the instructor teaches
$studentsResult = $conn->query("
    SELECT DISTINCT s.student_id, s.First_Name, s.Last_Name
    FROM students s
    JOIN student_courses sc ON s.student_id = sc.student_id
    JOIN instructor_courses ic ON sc.course_id = ic.course_id
    WHERE ic.instructor_id = $instructor_id AND ic.status = 'active'
");
$coursesResult = $conn->query("
    SELECT c.course_id, c.course_name, c.level
    FROM instructor_courses ic
    JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = $instructor_id AND ic.status = 'active'
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $attendance_datetime = $conn->real_escape_string($_POST['attendance_datetime']);
    $hours_attended = floatval($_POST['hours_attended']);
    $hours_replacement = floatval($_POST['hours_replacement']);
    $hours_remaining = floatval($_POST['hours_remaining']);
    $status = $conn->real_escape_string($_POST['status']);

    $insertSql = "
        INSERT INTO attendance_records (
            student_id, instructor_id, course, attendance_datetime, hours_attended,
            hours_replacement, hours_remaining, status, created_at
        ) VALUES (
            $student_id, $instructor_id, $course_id, '$attendance_datetime', $hours_attended,
            $hours_replacement, $hours_remaining, '$status', NOW()
        )
    ";

    if ($conn->query($insertSql)) {
        header("Location: /Pages/instructors/attendance_instructors.php?message=Attendance+record+added+successfully");
        exit;
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add Attendance</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <style>
        form { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        button { margin-top: 20px; padding: 10px 20px; background: #1f2937; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        button:hover { background: rgb(71, 82, 95); }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Add Attendance</h2>
<?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">
    <label for="student_id">Student:</label>
    <select name="student_id" required>
        <option value="">-- Select Student --</option>
        <?php while ($s = $studentsResult->fetch_assoc()): ?>
            <option value="<?= $s['student_id'] ?>">
                <?= htmlspecialchars($s['Last_Name'] . ' ' . $s['First_Name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="course_id">Course:</label>
    <select name="course_id" required>
        <option value="">-- Select Course --</option>
        <?php while ($c = $coursesResult->fetch_assoc()): ?>
            <option value="<?= $c['course_id'] ?>">
                <?= htmlspecialchars($c['course_name'] . ' (' . $c['level'] . ')') ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="attendance_datetime">Attendance Date & Time:</label>
    <input type="datetime-local" name="attendance_datetime" required>

    <label for="hours_attended">Hours Attended:</label>
    <input type="number" step="0.1" name="hours_attended" required>

    <label for="hours_replacement">Hours Replacement:</label>
    <input type="number" step="0.1" name="hours_replacement">

    <label for="hours_remaining">Hours Remaining:</label>
    <input type="number" step="0.1" name="hours_remaining">

    <label for="status">Status:</label>
    <select name="status" required>
        <option value="attended">Attended</option>
        <option value="missed">Missed</option>
        <option value="replacement_booked">Replacement Booked</option>
    </select>

    <button type="submit">Add Attendance</button>
</form>

</body>
</html>
