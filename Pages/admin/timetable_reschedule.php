<?php
require_once 'setting.php';

// Check if student_id is provided
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;
if (!$student_id) {
    die("Student ID is required");
}

// Fetch student details
$student = $conn->query("SELECT * FROM Students WHERE student_id = $student_id")->fetch_assoc();
if (!$student) {
    die("Student not found");
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule'])) {
    $timetable_id = (int)$_POST['timetable_id'];
    $new_day = $conn->real_escape_string($_POST['day']);
    $new_start = $conn->real_escape_string($_POST['start_time']);
    $new_end = $conn->real_escape_string($_POST['end_time']);
    
    $conn->begin_transaction();
    try {
        // 1. Create reschedule request
        $stmt = $conn->prepare("
            INSERT INTO student_timetable_requests 
            (student_course_id, day, start_time, end_time, status, requested_at)
            SELECT t.student_course_id, ?, ?, ?, 'pending', NOW()
            FROM student_timetable t
            WHERE t.id = ?
        ");
        $stmt->bind_param("sssi", $new_day, $new_start, $new_end, $timetable_id);
        $stmt->execute();
        
        // 2. Optionally mark old timetable as cancelled
        $conn->query("UPDATE student_timetable SET status = 'cancelled' WHERE id = $timetable_id");
        
        $conn->commit();
        $_SESSION['message'] = "Reschedule request submitted successfully!";
        header("Location: timetable_approve.php?student_id=$student_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Reschedule failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Timetable - <?= htmlspecialchars($student['First_Name'] . ' ' . htmlspecialchars($student['Last_Name']) )?></title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reschedule-container {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .student-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .timetable-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .timetable-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .reschedule-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("Top_Nav_Bar.php"); ?>

            <div class="reschedule-container">
                <a href="timetable_approve.php?student_id=<?= $student_id ?>" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Student Timetable
                </a>

                <div class="student-info">
                    <h2><?= htmlspecialchars($student['First_Name'] . ' ' . htmlspecialchars($student['Last_Name'])) ?></h2>
                    <p><strong>School:</strong> <?= htmlspecialchars($student['School']) ?></p>
                    <p><strong>Grade:</strong> <?= htmlspecialchars($student['Current_School_Grade']) ?></p>
                </div>

                <h2>Current Timetable</h2>
                
                <?php if ($current_timetable->num_rows > 0): ?>
                    <?php while ($entry = $current_timetable->fetch_assoc()): ?>
                        <div class="timetable-card">
                            <h3><?= htmlspecialchars($entry['course_name']) ?></h3>
                            <p><strong>Day:</strong> <?= htmlspecialchars($entry['day']) ?></p>
                            <p><strong>Time:</strong> 
                                <?= date('h:i A', strtotime($entry['start_time'])) ?> - 
                                <?= date('h:i A', strtotime($entry['end_time'])) ?>
                            </p>
                            
                            <div class="reschedule-form">
                                <form method="POST" action="timetable_reschedule.php?student_id=<?= $student_id ?>">
                                    <input type="hidden" name="timetable_id" value="<?= $entry['id'] ?>">
                                    
                                    <div class="form-group">
                                        <label for="day">New Day:</label>
                                        <select name="day" id="day" class="form-control" required>
                                            <option value="">Select Day</option>
                                            <option value="Monday">Monday</option>
                                            <option value="Tuesday">Tuesday</option>
                                            <option value="Wednesday">Wednesday</option>
                                            <option value="Thursday">Thursday</option>
                                            <option value="Friday">Friday</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="start_time">New Start Time:</label>
                                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="end_time">New End Time:</label>
                                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                                    </div>
                                    
                                    <button type="submit" name="reschedule" class="btn btn-primary">
                                        <i class="fas fa-calendar-alt"></i> Submit Reschedule
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
    // Set default time values to match current schedule when changing day
    document.querySelectorAll('select[name="day"]').forEach(select => {
        select.addEventListener('change', function() {
            const card = this.closest('.timetable-card');
            const currentStart = card.querySelector('p:nth-of-type(2)').textContent.split('-')[0].trim();
            const currentEnd = card.querySelector('p:nth-of-type(2)').textContent.split('-')[1].trim();
            
            // Convert 12-hour format to 24-hour for time inputs
            function convertTo24Hour(time12h) {
                const [time, modifier] = time12h.split(' ');
                let [hours, minutes] = time.split(':');
                if (hours === '12') hours = '00';
                if (modifier === 'PM') hours = parseInt(hours, 10) + 12;
                return `${hours}:${minutes}`;
            }
            
            card.querySelector('input[name="start_time"]').value = convertTo24Hour(currentStart);
            card.querySelector('input[name="end_time"]').value = convertTo24Hour(currentEnd);
        });
    });
    </script>
</body>
</html>