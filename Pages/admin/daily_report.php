<?php
include '../setting.php'; // Database connection
date_default_timezone_set('Asia/Kuala_Lumpur');
// Get the date from URL or default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch attendance records for the given date
$sql = "SELECT * FROM attendance_records WHERE DATE(attendance_datetime) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daily Attendance Report</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/attendence.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        h2 {
            margin-top: 20px;
        }
        .container {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Daily Attendance Report for <?php echo htmlspecialchars($date); ?></h2>
    <table>
        <thead>
        <tr>
            <th>Record ID</th>
            <th>Student ID</th>
            <th>Instructor ID</th>
            <th>Scheduled Time</th>
            <th>Attendance Time</th>
            <th>Hours Attended</th>
            <th>Replacement Hours</th>
            <th>Hours Remaining</th>
            <th>Status</th>
            <th>Course</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['record_id'] ?></td>
                    <td><?= $row['student_id'] ?></td>
                    <td><?= $row['instructor_id'] ?? '-' ?></td>
                    <td><?= $row['timetable_datetime'] ?></td>
                    <td><?= $row['attendance_datetime'] ?? '-' ?></td>
                    <td><?= $row['hours_attended'] ?></td>
                    <td><?= $row['hours_replacement'] ?></td>
                    <td><?= $row['hours_remaining'] ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['course'] ?></td>
                </tr>
            <?php endwhile;
        else:
            ?>
            <tr>
                <td colspan="10">No attendance records found for this date.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<form method="GET" action="">
    <label for="report_date">Select Date:</label>
    <input type="date" id="report_date" name="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>">
    <button type="submit">Generate Report</button>
    <a href="export_excel.php?date=<?php echo $_GET['date'] ?? date('Y-m-d'); ?>&download_excel=1">
        <button type="button">Download Excel</button>
    </a>
    <a href="export_pdf.php?date=<?php echo $_GET['date'] ?? date('Y-m-d'); ?>&download_pdf=1">
        <button type="button">Download PDF</button>
    </a>
</form>
</body>
</html>
