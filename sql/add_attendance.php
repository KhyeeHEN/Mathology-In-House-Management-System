<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: /Pages/login.php');
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $instructor_id = intval($_POST['instructor_id']);
    $timetable = $_POST['timetable_datetime'];
    $attendance = $_POST['attendance_datetime'];
    $hours_attended = floatval($_POST['hours_attended']);
    $hours_replacement = floatval($_POST['hours_replacement']);
    $hours_remaining = floatval($_POST['hours_remaining']);
    $status = $_POST['status'];
    $course_id = intval($_POST['course_id']);

    $stmt = $conn->prepare("
        INSERT INTO attendance_records
        (student_id, instructor_id, timetable_datetime, attendance_datetime, hours_attended, hours_replacement, hours_remaining, status, course)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissdddsi", $student_id, $instructor_id, $timetable, $attendance, $hours_attended, $hours_replacement, $hours_remaining, $status, $course_id);
    $stmt->execute();

    header("Location: /Pages/admin/attendance.php?message=Attendance added");
    exit;
}

// Load students, instructors, and courses
$students = $conn->query("SELECT student_id, First_Name, Last_Name FROM students");
$instructors = $conn->query("SELECT instructor_id, First_Name, Last_Name FROM instructor");
$courses = $conn->query("SELECT course_id, course_name, level FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Add Attendance</title>
  <style>
    body { font-family: Arial; background: #f7f7f7; padding: 30px; }
    form { background: white; padding: 25px; border-radius: 8px; max-width: 600px; margin: auto; }
    label { font-weight: bold; display: block; margin-top: 10px; }
    input, select { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; }
    button { background: #1f2937; color: white; padding: 10px 20px; border: none; border-radius: 5px; }
    button:hover { background: #374151; }
  </style>
</head>
<body>

<h2 style="text-align: center;">Add Attendance Record</h2>

<form method="POST">
  <label for="student_id">Student</label>
  <select name="student_id" required>
    <?php while ($s = $students->fetch_assoc()): ?>
      <option value="<?= $s['student_id'] ?>">
        <?= htmlspecialchars($s['Last_Name'] . ' ' . $s['First_Name']) ?>
      </option>
    <?php endwhile; ?>
  </select>

  <label for="instructor_id">Instructor</label>
  <select name="instructor_id" required>
    <?php while ($i = $instructors->fetch_assoc()): ?>
      <option value="<?= $i['instructor_id'] ?>">
        <?= htmlspecialchars($i['Last_Name'] . ' ' . $i['First_Name']) ?>
      </option>
    <?php endwhile; ?>
  </select>

  <label for="course_id">Course</label>
  <select name="course_id" required>
    <?php while ($c = $courses->fetch_assoc()): ?>
      <option value="<?= $c['course_id'] ?>">
        <?= htmlspecialchars($c['course_name'] . ' (' . $c['level'] . ')') ?>
      </option>
    <?php endwhile; ?>
  </select>

  <label for="timetable_datetime">Scheduled Time</label>
  <input type="datetime-local" name="timetable_datetime" required>

  <label for="attendance_datetime">Attendance Time</label>
  <input type="datetime-local" name="attendance_datetime" required>

  <label for="hours_attended">Hours Attended</label>
  <input type="number" step="0.1" name="hours_attended" required>

  <label for="hours_replacement">Hours Replacement</label>
  <input type="number" step="0.1" name="hours_replacement">

  <label for="hours_remaining">Hours Remaining</label>
  <input type="number" step="0.1" name="hours_remaining">

  <label for="status">Status</label>
  <select name="status" required>
    <option value="attended">Attended</option>
    <option value="missed">Missed</option>
    <option value="replacement_booked">Replacement Booked</option>
  </select>

  <button type="submit">Save Attendance</button>
</form>

</body>
</html>
