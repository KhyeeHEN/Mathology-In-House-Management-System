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

// Fetch all courses from the courses table using prepared statement
$stmt = $conn->prepare("SELECT course_id, course_name FROM courses");
$stmt->execute();
$all_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch existing instructor courses to check for matches
$stmt = $conn->prepare("SELECT course_id, instructor_course_id FROM instructor_courses WHERE instructor_id = ? AND status = 'active'");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$instructor_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $course_id = $_POST['course_id'];

    if (strtotime($start_time) >= strtotime($end_time)) {
        $error = "Start time must be before end time.";
    } else {
        // Check if the course is already linked to the instructor
        $instructor_course_id = null;
        foreach ($instructor_courses as $ic) {
            if ($ic['course_id'] == $course_id) {
                $instructor_course_id = $ic['instructor_course_id'];
                break;
            }
        }

        // If not linked, create a new instructor_course entry
        if ($instructor_course_id === null) {
            $stmt = $conn->prepare("INSERT INTO instructor_courses (instructor_id, course_id, status) VALUES (?, ?, 'active')");
            $stmt->bind_param("ii", $instructor_id, $course_id);
            $stmt->execute();
            $instructor_course_id = $conn->insert_id;
            $stmt->close();
        }

        // Fetch course name for the selected course_id
        $stmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $course_result = $stmt->get_result()->fetch_assoc();
        $course_name = $course_result['course_name'];
        $stmt->close();

        // Insert new timetable entry using prepared statement
        $stmt = $conn->prepare("INSERT INTO instructor_timetable (day, start_time, end_time, instructor_course_id, course) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $day, $start_time, $end_time, $instructor_course_id, $course_name);
        if ($stmt->execute()) {
            header("Location: instructor_timetable.php?instructor_id=$instructor_id&message=Timetable entry added successfully");
            exit();
        } else {
            $error = "Error adding timetable entry: " . $conn->error;
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
    <title>Add Instructor Timetable</title>
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
        .form-group {
            margin-bottom: 15px;
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
    </style>
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

                <h2>Add Timetable for <?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?></h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="day">Day:</label>
                        <select name="day" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time:</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time:</label>
                        <input type="time" name="end_time" required>
                    </div>
                    <div class="form-group">
                        <label for="course_id">Course:</label>
                        <select name="course_id" required>
                            <?php foreach ($all_courses as $course): ?>
                                <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit">Add Entry</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>