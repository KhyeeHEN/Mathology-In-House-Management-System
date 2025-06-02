<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../setting.php';
session_start();

// Protect route
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$studentId = $_SESSION['user_id'];

// Handle sorting
$allowed_columns = ['timetable_datetime', 'attendance_datetime', 'hours_attended', 'hours_replacement', 'hours_remaining', 'status', 'course', 'updated_at'];
$sort_column = in_array($_GET['sort'] ?? '', $allowed_columns) ? $_GET['sort'] : 'timetable_datetime';
$sort_direction = ($_GET['direction'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$new_sort_direction = $sort_direction === 'ASC' ? 'DESC' : 'ASC';

// Query
$sql = "
    SELECT * FROM attendance_records
    WHERE student_id = ?
    ORDER BY $sort_column $sort_direction
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Attendance</title>
    <link rel="stylesheet" href="/Styles/common.css" />
    <link rel="stylesheet" href="/Styles/attendance.css" />
    <script>
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll(".attendence tbody tr");
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(filter) ? "" : "none";
            });
        }
    </script>
</head>
<body>
<div class="dashboard-container">
    <?php include("../includes/Aside_Nav_Student.php"); ?>
    <main class="main-content">
        <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

        <h2 style="padding: 1rem 2rem;">My Attendance</h2>
        <div style="padding: 0 2rem;">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search attendance..." />
        </div>

        <table class="attendence">
            <thead>
                <tr class="attendance_title">
                    <?php
                    $headers = [
                        'timetable_datetime' => 'Scheduled',
                        'attendance_datetime' => 'Attended',
                        'hours_attended' => 'Attended (hrs)',
                        'hours_replacement' => 'Replacement (hrs)',
                        'hours_remaining' => 'Remaining (hrs)',
                        'status' => 'Status',
                        'course' => 'Course',
                        'updated_at' => 'Updated At'
                    ];
                    foreach ($headers as $col => $label) {
                        $arrow = ($sort_column === $col) ? strtolower($sort_direction) : '';
                        echo "<th class='$arrow' onclick=\"window.location='?sort=$col&direction=$new_sort_direction'\">$label</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(date("Y-m-d H:i", strtotime($row['timetable_datetime']))) ?></td>
                        <td><?= $row['attendance_datetime'] ? date("Y-m-d H:i", strtotime($row['attendance_datetime'])) : '-' ?></td>
                        <td><?= $row['hours_attended'] > 0 ? $row['hours_attended'] . " hrs" : '-' ?></td>
                        <td><?= $row['hours_replacement'] > 0 ? $row['hours_replacement'] . " hrs" : '-' ?></td>
                        <td><?= $row['hours_remaining'] . " hrs" ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['course']) ?></td>
                        <td><?= date("Y-m-d H:i", strtotime($row['updated_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No attendance records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>

<script type="module" src="/Scripts/common.js"></script>
</body>
</html>
