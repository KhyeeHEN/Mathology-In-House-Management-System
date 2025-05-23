<?php
require_once '../setting.php';
session_start();

// Initialize variables
$student = null;
$current_timetable = null;
$student_id = null;

// Check if student_id is provided
if (isset($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];
    
    // Fetch student details
    $student_result = $conn->query("SELECT * FROM Students WHERE student_id = $student_id");
    if ($student_result && $student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule'])) {
            $timetable_id = (int)$_POST['timetable_id'];
            $new_day = $conn->real_escape_string($_POST['day']);
            $new_start = $conn->real_escape_string($_POST['start_time']);
            $new_end = $conn->real_escape_string($_POST['end_time']);
            
            try {
                // Update the timetable directly
                $stmt = $conn->prepare("
                    UPDATE student_timetable 
                    SET day = ?, start_time = ?, end_time = ?, approved_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt) {
                    $stmt->bind_param("sssi", $new_day, $new_start, $new_end, $timetable_id);
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "Timetable updated successfully!";
                    } else {
                        $_SESSION['error'] = "Failed to update timetable: " . $stmt->error;
                    }
                } else {
                    $_SESSION['error'] = "Database error: " . $conn->error;
                }
                
                // Redirect back to show changes
                header("Location: timetable_reschedule.php?student_id=$student_id");
                exit();
                
            } catch (Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header("Location: timetable_reschedule.php?student_id=$student_id");
                exit();
            }
        }
        
        // Fetch current timetable
        $current_timetable = $conn->query("
            SELECT t.*, c.course_name 
            FROM student_timetable t
            JOIN student_courses sc ON t.student_course_id = sc.student_course_id
            JOIN courses c ON sc.course_id = c.course_id
            WHERE sc.student_id = $student_id
            ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), t.start_time
        ");
    } else {
        $_SESSION['error'] = "Student not found";
        header("Location: timetable_approve.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Student ID is required";
    header("Location: timetable_approve.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Timetable - <?= htmlspecialchars($student['First_Name'] . ' ' . $student['Last_Name']) ?></title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reschedule-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #4CAF50;
        }
        
        .timetable-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .current-schedule {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .reschedule-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="reschedule-container">
                <a href="timetable_approve.php?student_id=<?= $student_id ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Student Timetable
                </a>

                <div class="student-info">
                    <h2><?= htmlspecialchars($student['First_Name'] . ' ' . $student['Last_Name']) ?></h2>
                    <div style="display: flex; gap: 20px;">
                        <div><strong>School:</strong> <?= htmlspecialchars($student['School']) ?></div>
                        <div><strong>Grade:</strong> <?= htmlspecialchars($student['Current_School_Grade']) ?></div>
                    </div>
                </div>

                <h2>Reschedule Timetable</h2>
                
                <?php if ($current_timetable && $current_timetable->num_rows > 0): ?>
                    <?php while ($entry = $current_timetable->fetch_assoc()): ?>
                        <div class="timetable-card">
                            <h3><?= htmlspecialchars($entry['course_name']) ?></h3>
                            
                            <div class="current-schedule">
                                <p><strong>Current Schedule:</strong></p>
                                <p><?= htmlspecialchars($entry['day']) ?> from 
                                <?= date('h:i A', strtotime($entry['start_time'])) ?> to 
                                <?= date('h:i A', strtotime($entry['end_time'])) ?></p>
                            </div>
                            
                            <div class="reschedule-form">
                                <form method="POST" action="timetable_reschedule.php?student_id=<?= $student_id ?>">
                                    <input type="hidden" name="timetable_id" value="<?= $entry['id'] ?>">
                                    
                                    <div class="form-group">
                                        <label>Day</label>
                                        <select name="day" class="form-control" required>
                                            <option value="">Select Day</option>
                                            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday'] as $day): ?>
                                                <option value="<?= $day ?>" <?= $entry['day'] == $day ? 'selected' : '' ?>>
                                                    <?= $day ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="start_time" class="form-control" 
                                               value="<?= date('H:i', strtotime($entry['start_time'])) ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="end_time" class="form-control" 
                                               value="<?= date('H:i', strtotime($entry['end_time'])) ?>" required>
                                    </div>
                                    
                                    <button type="submit" name="reschedule" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Schedule
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No timetable entries found for this student.
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
    document.querySelectorAll('form').forEach(form => {
        const startTime = form.querySelector('input[name="start_time"]');
        const endTime = form.querySelector('input[name="end_time"]');
        
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