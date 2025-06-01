<?php
include '../setting.php'; // adjust path as needed
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Pages/login.php");
    exit;
}

// Get current student ID - depends on who is viewing
if ($_SESSION['role'] === 'student') {
    // If student is viewing their own timetable, use their user_id
    $student_id = $_SESSION['related_id']; // You'll need to store this in login.php
} else {
    // For admin/instructor viewing, get from URL or default to session
    $student_id = $_GET['student_id'] ?? null;
}

if (!$student_id) {
    die("Student ID not provided.");
}

// Fetch timetable data with student and course details
$sql = "SELECT 
            st.id, 
            s.First_Name, 
            s.Last_Name, 
            c.course_name as course,
            st.day,
            TIME_FORMAT(st.start_time, '%h:%i %p') as start_time,
            TIME_FORMAT(st.end_time, '%h:%i %p') as end_time,
            DATE_FORMAT(st.approved_at, '%M %d, %Y') as approved_at
        FROM student_timetable st
        JOIN student_courses sc ON st.student_course_id = sc.student_course_id
        JOIN students s ON sc.student_id = s.student_id
        JOIN courses c ON sc.course_id = c.course_id
        WHERE s.student_id = ?
        ORDER BY FIELD(st.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), st.start_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Get student info for header
$student_sql = "SELECT First_Name, Last_Name FROM students WHERE student_id = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_info = $student_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Timetable</title>
    <link rel="stylesheet" href="../../styles/common.css">
    <link rel="stylesheet" href="../../styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .timetable-container {
            margin: 20px;
            overflow-x: auto;
        }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .timetable th, .timetable td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .timetable th {
            background-color: #4CAF50;
            color: white;
        }
        .timetable tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .timetable tr:hover {
            background-color: #e6f7e6;
        }
        .student-header {
            margin: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .action-buttons {
            margin: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #2196F3;
            color: white;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-container">
                <h2>Mathology</h2>
            </div>
            <nav class="side-nav">
                <a href="dashboardclient.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="attendanceclient.php" class="nav-item">
                    <i class="fas fa-user-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="student_reschedule.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Student replacement</span>
                </a>
                <a href="student_timetable.php" class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>My Timetable</span>
                </a>
                <a href="learninghours.php" class="nav-item">
                    <i class="fas fa-clock"></i>
                    <span>Learning Hours</span>
                </a>
                <a href="leave.php" class="nav-item">
                    <i class="fas fa-check"></i>
                    <span>Apply Leave</span>
                </a>
                <a href="payment.php" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <!-- Page Content -->
            <div class="student-header">
                <h2>
                    <i class="fas fa-calendar-alt"></i> 
                    Timetable for <?= htmlspecialchars($student_info['First_Name'] . ' ' . $student_info['Last_Name']) ?>
                </h2>
            </div>

            <div class="timetable-container">
                <?php if ($result && $result->num_rows > 0): ?>
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Approved At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course']) ?></td>
                                    <td><?= htmlspecialchars($row['day']) ?></td>
                                    <td><?= htmlspecialchars($row['start_time']) ?></td>
                                    <td><?= htmlspecialchars($row['end_time']) ?></td>
                                    <td><?= htmlspecialchars($row['approved_at']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; background-color: #f8f9fa; border-radius: 5px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; color: #6c757d;"></i>
                        <p style="margin-top: 10px;">No timetable entries found for this student.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="action-buttons">
                <a href="timetable_reschedule.php?student_id=<?= $student_id ?>" class="btn btn-primary">
                    <i class="fas fa-calendar-alt"></i> Reschedule Timetable
                </a>
                <a href="timetable_add.php?student_id=<?= $student_id ?>" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add New Session
                </a>
                <?php if ($result && $result->num_rows > 0): ?>
                    <a href="print_timetable.php?student_id=<?= $student_id ?>" class="btn btn-danger" target="_blank">
                        <i class="fas fa-print"></i> Print Timetable
                    </a>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script type="module" src="../../scripts/common.js"></script>
</body>
</html>