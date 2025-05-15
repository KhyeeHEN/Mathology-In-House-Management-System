<?php
include '../setting.php';

// Handle sorting
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'timetable_datetime';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$new_sort_direction = $sort_direction === 'ASC' ? 'DESC' : 'ASC';

// Validate allowed columns to prevent SQL injection
$allowed_columns = [
    'record_id', 'student_id', 'instructor_id', 'timetable_datetime',
    'attendance_datetime', 'hours_attended', 'hours_replacement',
    'hours_remaining', 'status', 'created_at', 'updated_at', 'course'
];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'timetable_datetime';
}

// SQL with LEFT JOIN to students and instructors to get names
$sql = "
    SELECT
        ar.*,
        s.First_Name AS student_first_name,
        s.Last_Name AS student_last_name,
        i.First_Name AS instructor_first_name,
        i.Last_Name AS instructor_last_name
    FROM attendance_records ar
    LEFT JOIN students s ON ar.student_id = s.student_id
    LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
    ORDER BY $sort_column $sort_direction
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Attendance</title>
    <link rel="stylesheet" href="../../Styles/common.css" />
    <link rel="stylesheet" href="../../Styles/attendance.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="../../Scripts/attendance.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <div style="padding: 1rem 2rem;">
                <a href="daily_report.php" class="back-btn">
                    <i class="fas fa-chart-line" style="margin-right: 8px;"></i> Daily Report
                </a>
            </div>

            <!-- Search Field -->
            <div style="padding: 0 2rem;">
                <input type="text" id="searchInput" placeholder="Search attendance..." onkeyup="searchTable()" />
            </div>

            <!-- Attendance Table -->
            <table class="attendence">
                <thead>
                    <tr class="attendance_title">
                        <?php
                        $headers = [
                            'record_id' => 'Record ID',
                            'student_id' => 'Student',
                            'instructor_id' => 'Instructor',
                            'timetable_datetime' => 'Scheduled Date/Time',
                            'attendance_datetime' => 'Attendance Date/Time',
                            'hours_attended' => 'Hours Attended',
                            'hours_replacement' => 'Replacement Hours',
                            'hours_remaining' => 'Remaining Hours',
                            'status' => 'Status',
                            'created_at' => 'Created At',
                            'updated_at' => 'Updated At',
                            'course' => 'Course'
                        ];

                        foreach ($headers as $column => $label) {
                            $arrow_class = $sort_column == $column ? strtolower($sort_direction) : '';
                            echo "<th class='$arrow_class' onclick=\"window.location='?sort=$column&direction=$new_sort_direction'\">$label<span class='sort-arrow'></span></th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            // Record ID
                            echo "<td>" . $row['record_id'] . "</td>";

                            // Student full name, data attribute for student_id
                            $studentName = htmlspecialchars(trim($row['student_first_name'] . ' ' . $row['student_last_name']));
                            $studentId = htmlspecialchars($row['student_id']);
                            echo "<td data-student-id='$studentId'>$studentName</td>";

                            // Instructor full name, data attribute for instructor_id (or '-')
                            if ($row['instructor_first_name']) {
                                $instructorName = htmlspecialchars(trim($row['instructor_first_name'] . ' ' . $row['instructor_last_name']));
                                $instructorId = htmlspecialchars($row['instructor_id']);
                            } else {
                                $instructorName = '-';
                                $instructorId = '';
                            }
                            echo "<td data-instructor-id='$instructorId'>$instructorName</td>";

                            // Other columns
                            echo "<td>" . date("Y-m-d H:i", strtotime($row['timetable_datetime'])) . "</td>";
                            echo "<td>" . ($row['attendance_datetime'] ? date("Y-m-d H:i", strtotime($row['attendance_datetime'])) : '-') . "</td>";
                            echo "<td>" . ($row['hours_attended'] > 0 ? $row['hours_attended'] . " hrs" : '-') . "</td>";
                            echo "<td>" . ($row['hours_replacement'] > 0 ? $row['hours_replacement'] . " hrs" : '-') . "</td>";
                            echo "<td>" . $row['hours_remaining'] . " hrs</td>";
                            echo "<td>" . ucfirst($row['status']) . "</td>";
                            echo "<td>" . date("Y-m-d H:i", strtotime($row['created_at'])) . "</td>";
                            echo "<td>" . date("Y-m-d H:i", strtotime($row['updated_at'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['course']) . "</td>";
                        }
                    } else {
                        echo "<tr><td colspan='13' style='text-align:center;'>No attendance records found.</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </main>
    </div>

    <script type="module" src="../../Scripts/common.js"></script>
</body>
</html>
