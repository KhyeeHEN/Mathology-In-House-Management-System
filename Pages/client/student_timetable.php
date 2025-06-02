<?php
include '../setting.php'; // Adjust path as needed
session_start();

// Ensure student is logged in
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

// Fetch timetable data with student and course details
$sql = "SELECT 
            st.id, 
            st.course,
            st.day,
            TIME_FORMAT(st.start_time, '%h:%i %p') as start_time,
            TIME_FORMAT(st.end_time, '%h:%i %p') as end_time,
            DATE_FORMAT(st.approved_at, '%M %d, %Y') as approved_at
        FROM student_timetable st
        JOIN student_courses sc ON st.student_course_id = sc.student_course_id
        WHERE sc.student_id = ?
        ORDER BY FIELD(st.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), st.start_time";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing timetable query: " . $conn->error;
    exit();
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Store the results in an array to ensure data is available for rendering
$timetable_data = [];
while ($row = $result->fetch_assoc()) {
    $timetable_data[] = $row;
}

// Get student info for header
$student_sql = "SELECT First_Name, Last_Name FROM students WHERE student_id = ?";
$student_stmt = $conn->prepare($student_sql);
if (!$student_stmt) {
    echo "Error preparing student query: " . $conn->error;
    exit();
}
$student_stmt->bind_param("i", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_info = $student_result ? $student_result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Timetable</title>
    <link rel="stylesheet" href="../../Styles/common.css">
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
            display: table; /* Ensure table is visible */
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
         <?php require("../includes/Aside_Nav_Student.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <!-- Page Content -->
            <div class="student-header">
                <h2>
                    <i class="fas fa-calendar-alt"></i> 
                    Timetable for <?= $student_info ? htmlspecialchars($student_info['First_Name'] . ' ' . $student_info['Last_Name']) : 'Student' ?>
                </h2>
            </div>

            <div class="timetable-container">
                <?php if (!empty($timetable_data)): ?>
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
                            <?php foreach ($timetable_data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['day'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['start_time'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['end_time'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($row['approved_at'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
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
                <?php if (!empty($timetable_data)): ?>
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