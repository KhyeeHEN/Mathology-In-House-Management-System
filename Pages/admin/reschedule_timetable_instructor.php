<?php
include '../setting.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get instructor ID from GET parameter
$instructor_id = $_GET['instructor_id'] ?? null;
if (!$instructor_id) {
    header("Location: instructor_timetable.php");
    exit();
}

// Fetch instructor details using prepared statement
$stmt = $conn->prepare("SELECT First_Name, Last_Name FROM instructor WHERE instructor_id = ?");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$instructor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch timetable for the instructor using prepared statement
$stmt = $conn->prepare("SELECT it.id, it.day, TIME_FORMAT(it.start_time, '%h:%i %p') AS start_time, 
                       TIME_FORMAT(it.end_time, '%h:%i %p') AS end_time, it.course, it.instructor_course_id
                       FROM instructor_timetable it
                       JOIN instructor_courses ic ON it.instructor_course_id = ic.instructor_course_id
                       WHERE ic.instructor_id = ? AND it.status = 'active'
                       ORDER BY FIELD(it.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), it.start_time");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$timetable = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch available courses for the instructor using prepared statement
$stmt = $conn->prepare("
    SELECT ic.instructor_course_id, c.course_name
    FROM instructor_courses ic
    JOIN courses c ON ic.course_id = c.course_id
    WHERE ic.instructor_id = ? AND ic.status = 'active'
");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission for rescheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timetable_id = $_POST['timetable_id'];
    $day = $_POST['day'];
    $start_time = date('H:i:s', strtotime($_POST['start_time']));
    $end_time = date('H:i:s', strtotime($_POST['end_time']));
    $instructor_course_id = $_POST['instructor_course_id'];

    if (strtotime($start_time) >= strtotime($end_time)) {
        $error = "Start time must be before end time.";
    } else {
        // Fetch course name for the selected instructor_course_id
        $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = (SELECT course_id FROM instructor_courses WHERE instructor_course_id = ?)");
        $stmt->bind_param("i", $instructor_course_id);
        $stmt->execute();
        $course_result = $stmt->get_result()->fetch_assoc();
        $course_name = $course_result['course_name'];
        $stmt->close();

        // Update timetable entry using prepared statement
        $stmt = $conn->prepare("UPDATE instructor_timetable 
                               SET day = ?, start_time = ?, end_time = ?, instructor_course_id = ?, course = ?
                               WHERE id = ?");
        $stmt->bind_param("sssisi", $day, $start_time, $end_time, $instructor_course_id, $course_name, $timetable_id);
        if ($stmt->execute()) {
            header("Location: instructor_timetable.php?instructor_id=$instructor_id&message=Timetable entry rescheduled successfully");
            exit();
        } else {
            $error = "Error rescheduling timetable entry: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Instructor Timetable</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content {
            margin: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .timetable-table th, .timetable-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .timetable-table th {
            background-color: #4CAF50;
            color: white;
        }
        .timetable-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .timetable-table tr:hover {
            background-color: #e6f7e6;
        }
        .form-group {
            margin-bottom: 15px;
            display: none; /* Hidden by default */
        }
        .form-group.active {
            display: block;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select, .form-group input[type="time"] {
            padding: 8px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
    <script>
        function showEditForm(id) {
            document.querySelectorAll('.form-group').forEach(form => form.classList.remove('active'));
            document.getElementById('edit-form-' + id).classList.add('active');
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content">
                <a href="instructor_timetable.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Instructor Timetable
                </a>

                <h2>Reschedule Timetable for <?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?></h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
                <?php endif; ?>

                <?php if (!empty($timetable)): ?>
                    <h3>Current Timetable</h3>
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Course</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timetable as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['day']) ?></td>
                                    <td><?= htmlspecialchars($entry['start_time']) ?></td>
                                    <td><?= htmlspecialchars($entry['end_time']) ?></td>
                                    <td><?= htmlspecialchars($entry['course']) ?></td>
                                    <td>
                                        <button onclick="showEditForm(<?= $entry['id'] ?>)">Edit</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <div class="form-group" id="edit-form-<?= $entry['id'] ?>">
                                            <form method="POST">
                                                <input type="hidden" name="timetable_id" value="<?= $entry['id'] ?>">
                                                <div>
                                                    <label for="day">Day:</label>
                                                    <select name="day" required>
                                                        <option value="Monday" <?= $entry['day'] == 'Monday' ? 'selected' : '' ?>>Monday</option>
                                                        <option value="Tuesday" <?= $entry['day'] == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                                                        <option value="Wednesday" <?= $entry['day'] == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                                                        <option value="Thursday" <?= $entry['day'] == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                                                        <option value="Friday" <?= $entry['day'] == 'Friday' ? 'selected' : '' ?>>Friday</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="start_time">Start Time:</label>
                                                    <input type="time" name="start_time" value="<?= date('H:i', strtotime($entry['start_time'])) ?>" required>
                                                </div>
                                                <div>
                                                    <label for="end_time">End Time:</label>
                                                    <input type="time" name="end_time" value="<?= date('H:i', strtotime($entry['end_time'])) ?>" required>
                                                </div>
                                                <div>
                                                    <label for="instructor_course_id">Course:</label>
                                                    <select name="instructor_course_id" required>
                                                        <?php foreach ($courses as $course): ?>
                                                            <option value="<?= $course['instructor_course_id'] ?>" <?= $entry['instructor_course_id'] == $course['instructor_course_id'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($course['course_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button type="submit">Save Changes</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-entries">
                        <i class="fas fa-info-circle"></i>
                        <p>No timetable entries found for this instructor.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>