<?php
require_once '../setting.php';
session_start();

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    $_SESSION['error'] = "Student ID is required";
    header("Location: timetable_approve.php");
    exit();
}

$student_id = (int)$_GET['student_id'];

// Fetch student details
$student_result = $conn->query("SELECT * FROM students WHERE student_id = $student_id");
if (!$student_result || $student_result->num_rows === 0) {
    $_SESSION['error'] = "Student not found";
    header("Location: timetable_approve.php");
    exit();
}
$student = $student_result->fetch_assoc();

// Fetch available courses for this student with level
$courses = $conn->query("
    SELECT course_id, course_name, level 
    FROM courses 
    ORDER BY course_name
");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_timetable'])) {
    $course_id = (int)$_POST['course_id'];
    $day = $conn->real_escape_string($_POST['day']);
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $end_time = $conn->real_escape_string($_POST['end_time']);
    
    try {
        // Check if enrolled, if not - enroll them
        $enrollment = $conn->query("
            SELECT student_course_id FROM student_courses 
            WHERE student_id = $student_id AND course_id = $course_id
        ");
        
        if ($enrollment->num_rows === 0) {
            // Auto-enroll the student
            $conn->query("
                INSERT INTO student_courses 
                (student_id, course_id, enrollment_date, status)
                VALUES ($student_id, $course_id, CURDATE(), 'active')
            ");
            $student_course_id = $conn->insert_id;
        } else {
            $student_course_id = $enrollment->fetch_assoc()['student_course_id'];
        }
        
        // Get course name
        $course_info = $conn->query("
            SELECT course_name, level FROM courses WHERE course_id = $course_id
        ")->fetch_assoc();
        $course_display = $course_info['course_name'] . ', ' . $course_info['level'];
        
        // Insert timetable
        $stmt = $conn->prepare("
            INSERT INTO student_timetable 
            (student_course_id, course, day, start_time, end_time, approved_at, status)
            VALUES (?, ?, ?, ?, ?, NOW(), 'active')
        ");
        
        $stmt->bind_param("issss", $student_course_id, $course_display, $day, $start_time, $end_time);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Timetable added successfully!";
            header("Location: timetable_approve.php?student_id=$student_id");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Timetable - <?= htmlspecialchars($student['First_Name'] . ' ' . $student['Last_Name']) ?></title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .add-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        select.form-control {
            height: 42px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="add-container">
                <div class="header-actions">
                    <h2>Add New Timetable for <?= htmlspecialchars($student['First_Name'] . ' ' . $student['Last_Name']) ?></h2>
                    <a href="timetable_approve.php?student_id=<?= $student_id ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Timetable
                    </a>
                </div>

                <form method="POST" action="timetable_add.php?student_id=<?= $student_id ?>">
                    <div class="form-group">
                        <label>Course</label>
                        <select name="course_id" class="form-control" required>
                            <option value="">Select Course</option>
                            <?php 
                            $courses->data_seek(0); // Reset pointer to reuse result set
                            while ($course = $courses->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($course['course_id']) ?>">
                                    <?= htmlspecialchars($course['course_name'] . ' - ' . $course['level']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Day</label>
                        <select name="day" class="form-control" required>
                            <option value="">Select Day</option>
                            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    
                    <button type="submit" name="add_timetable" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Timetable
                    </button>
                </form>
            </div>
        </main>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startTime = document.querySelector('input[name="start_time"]');
        const endTime = document.querySelector('input[name="end_time"]');
        
        function validateTimes() {
            if (startTime.value && endTime.value && startTime.value >= endTime.value) {
                endTime.setCustomValidity('End time must be after start time');
            } else {
                endTime.setCustomValidity('');
            }
        }
        
        startTime.addEventListener('change', validateTimes);
        endTime.addEventListener('change', validateTimes);
    });
    </script>
</body>
</html>