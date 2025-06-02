<?php
include '../setting.php';
session_start();

// Protect route
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Fetch the actual student_id from the users table using the session's user_id
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT student_id FROM users WHERE user_id = ? AND role = 'student'";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    echo "<p>Error: Unable to retrieve student information. Please try again later.</p>";
    exit();
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();

if (!$user_info || !isset($user_info['student_id'])) {
    echo "<p>Error: Student ID not found for this user.</p>";
    exit();
}

$student_id = $user_info['student_id'];

// Fetch attendance data
$sql = "
    SELECT course, timetable_datetime, attendance_datetime, hours_attended, hours_remaining, status
    FROM attendance_records
    WHERE student_id = ?
    ORDER BY timetable_datetime DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<p>Error: Unable to fetch attendance records. Please try again later.</p>";
    exit();
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Store results in an array
$attendance_data = [];
$total_hours = 0;
$remaining_hours = 0;

while ($row = $result->fetch_assoc()) {
    $attendance_data[] = $row;
    $total_hours += $row['hours_attended'] > 0 ? floatval($row['hours_attended']) : 0;
    // Use the last record's hours_remaining as the total remaining (assuming cumulative tracking)
    $remaining_hours = floatval($row['hours_remaining']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Hours</title>
    <link rel="stylesheet" href="/Styles/common.css" />
    <link rel="stylesheet" href="/Styles/attendance.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .attendance-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .attendance-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .attendance-table tr:hover {
            background-color: #f1f1f1;
        }

        .status-attended {
            color: green;
            font-weight: bold;
        }

        .status-missed {
            color: red;
            font-weight: bold;
        }

        .status-replacement {
            color: orange;
            font-weight: bold;
        }

        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include("../includes/Aside_Nav_Student.php"); ?>
        <main class="main-content">
            <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

            <!-- Attendance Content -->
            <div class="content-container">
                <div class="summary-card">
                    <div class="summary-title">Total Hours Attended</div>
                    <div class="summary-value"><?= number_format($total_hours, 1) ?> hours</div>
                </div>

                <div class="summary-card">
                    <div class="summary-title">Hours Remaining</div>
                    <div class="summary-value"><?= number_format($remaining_hours, 1) ?> hours</div>
                </div>

                <h2>Attendance Records</h2>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Scheduled Time</th>
                            <th>Attendance Time</th>
                            <th>Hours Attended</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($attendance_data)): ?>
                        <?php foreach ($attendance_data as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(date("Y-m-d", strtotime($row['timetable_datetime']))) ?></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= htmlspecialchars(date("H:i", strtotime($row['timetable_datetime']))) ?></td>
                                <td><?= $row['attendance_datetime'] ? htmlspecialchars(date("H:i", strtotime($row['attendance_datetime']))) : 'N/A' ?></td>
                                <td><?= $row['hours_attended'] > 0 ? htmlspecialchars(number_format($row['hours_attended'], 1)) . ' hours' : 'N/A' ?></td>
                                <td class="<?php
                                    echo $row['status'] === 'attended' ? 'status-attended' :
                                         ($row['status'] === 'missed' ? 'status-missed' :
                                         ($row['status'] === 'replacement_booked' ? 'status-replacement' : ''));
                                ?>">
                                    <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No attendance records found</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>