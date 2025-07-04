<?php
require_once '../setting.php';
session_start();

// Check if student is logged in using user_id and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Fetch the actual student_id from the users table using the session's user_id
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT student_id FROM users WHERE user_id = ? AND role = 'student'";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    echo "Error preparing user query: " . $conn->error;
    exit();
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();

if (!$user_info || !isset($user_info['student_id'])) {
    echo "<p>Error: Student ID not found for user ID $user_id.</p>";
    exit();
}

$student_id = $user_info['student_id'];

// Fetch student details
$student = $conn->query("SELECT * FROM students WHERE student_id = $student_id")->fetch_assoc();

// Fetch student's current timetable
$current_timetable = $conn->query("
    SELECT t.*, c.course_name 
    FROM student_timetable t
    JOIN student_courses sc ON t.student_course_id = sc.student_course_id
    JOIN courses c ON sc.course_id = c.course_id
    WHERE sc.student_id = $student_id
    ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.start_time
");

// Fetch all available courses for dropdown with level
$enrolled_courses = $conn->query("
    SELECT course_id, course_name, level 
    FROM courses 
    ORDER BY course_name
");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reschedule'])) {
    $course_id = (int)$_POST['course_id'];
    $new_day = $conn->real_escape_string($_POST['new_day']);
    $new_start = $conn->real_escape_string($_POST['new_start_time']);
    $new_end = $conn->real_escape_string($_POST['new_end_time']);
    $reason = $conn->real_escape_string($_POST['reason']);

    // Get student_course_id (will enroll if not exists)
    $sc_result = $conn->query("
        SELECT student_course_id 
        FROM student_courses 
        WHERE student_id = $student_id AND course_id = $course_id
    ");
    
    if ($sc_result->num_rows > 0) {
        $sc_id = $sc_result->fetch_assoc()['student_course_id'];
    } else {
        // Auto-enroll the student if not enrolled
        $conn->query("
            INSERT INTO student_courses 
            (student_id, course_id, enrollment_date, status)
            VALUES ($student_id, $course_id, CURDATE(), 'active')
        ");
        $sc_id = $conn->insert_id;
    }
    
    // Insert reschedule request
    $stmt = $conn->prepare("
        INSERT INTO student_timetable_requests 
        (student_course_id, day, start_time, end_time, status, requested_at, course)
        VALUES (?, ?, ?, ?, 'pending', NOW(), ?)
    ");
    
    $course_info = $conn->query("SELECT course_name, level FROM courses WHERE course_id = $course_id")->fetch_assoc();
    $course_display = $course_info['course_name'] . ' - ' . $course_info['level'];
    
    $stmt->bind_param(
        "issss", 
        $sc_id, 
        $new_day, 
        $new_start, 
        $new_end,
        $course_display
    );
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Reschedule request submitted successfully!";
    } else {
        $_SESSION['error'] = "Failed to submit request: " . $stmt->error;
    }
    
    header("Location: student_reschedule.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Timetable Change</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/attendence.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .current-timetable, .request-form {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .timetable-entry {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .timetable-entry:last-child {
            border-bottom: none;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        select.form-control {
            height: 40px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .time-inputs {
            display: flex;
            gap: 15px;
        }
        
        .time-inputs .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
         <?php require("../includes/Aside_Nav_Student.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Student.php"); ?>

            <div class="content-container">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="current-timetable">
                    <h2>Your Current Timetable</h2>
                    
                    <?php if ($current_timetable->num_rows > 0): ?>
                        <?php while ($entry = $current_timetable->fetch_assoc()): ?>
                            <div class="timetable-entry">
                                <div>
                                    <strong><?= htmlspecialchars($entry['course_name']) ?></strong>
                                    <p><?= htmlspecialchars($entry['day']) ?>: 
                                    <?= date('h:i A', strtotime($entry['start_time'])) ?> - <?= date('h:i A', strtotime($entry['end_time'])) ?></p>
                                </div>
                                <div>
                                    <small>Last updated: <?= date('M j, Y', strtotime($entry['approved_at'])) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No timetable entries found.</p>
                    <?php endif; ?>
                </div>

                <div class="request-form">
                    <h2>Request Timetable Change</h2>
                    <form method="POST" action="student_reschedule.php">
                        <div class="form-group">
                            <label for="course_id">Select Course</label>
                            <select name="course_id" id="course_id" class="form-control" required>
                                <option value="">-- Select Course --</option>
                                <?php 
                                $enrolled_courses->data_seek(0); // Reset pointer to reuse result set
                                while ($course = $enrolled_courses->fetch_assoc()): ?>
                                    <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name'] . ' - ' . $course['level']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_day">New Day</label>
                            <select name="new_day" id="new_day" class="form-control" required>
                                <option value="">-- Select Day --</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                            </select>
                        </div>
                        
                        <div class="time-inputs">
                            <div class="form-group">
                                <label for="new_start_time">New Start Time</label>
                                <input type="time" name="new_start_time" id="new_start_time" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_end_time">New End Time</label>
                                <input type="time" name="new_end_time" id="new_end_time" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reason">Reason for Reschedule</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" name="request_reschedule" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // Auto-fill current day/time when course is selected
    document.getElementById('course_id').addEventListener('change', function() {
        const courseId = this.value;
        if (!courseId) return;
        
        // In a real implementation, you would fetch the current schedule via AJAX
        // For this example, we'll just demonstrate the concept
        console.log("Fetching schedule for course", courseId);
    });
    
    // Validate end time is after start time
    document.querySelector('form').addEventListener('submit', function(e) {
        const start = document.getElementById('new_start_time').value;
        const end = document.getElementById('new_end_time').value;
        
        if (start && end && start >= end) {
            alert('End time must be after start time');
            e.preventDefault();
        }
    });
    </script>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>