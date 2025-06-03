<?php
include '../setting.php'; // Database connection
date_default_timezone_set('Asia/Kuala_Lumpur');
// Get the date from URL or default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch attendance records for the given date
$sql = "SELECT ar.*,
       CONCAT(s.Last_Name, ' ', s.First_Name) AS student_name,
       CONCAT(i.Last_Name, ' ', i.First_Name) AS instructor_name
FROM attendance_records ar
LEFT JOIN students s ON ar.student_id = s.student_id
LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
WHERE DATE(ar.attendance_datetime) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Daily Attendance Report</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button,
        a button {
            padding: 8px 14px;
            background-color: #0051a8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.2s ease-in-out;
        }

        button:hover {
            background-color: #003e82;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }

        thead {
            background-color: #0051a8;
            color: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .no-record {
            text-align: center;
            padding: 20px;
            font-size: 16px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>
            <div class="container">
                <h2>Daily Attendance Report for <?php echo htmlspecialchars($date); ?></h2>

                <table>
                    <thead>
                        <tr>
                            <th>Record ID</th>
                            <th>Student Name</th>
                            <th>Instructor Name</th>
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
                                    <td><?= $row['student_name'] ?></td>
                                    <td><?= $row['instructor_name'] ?? '-' ?></td>
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
                <a href="../utils/export_excel.php?date=<?php echo $_GET['date'] ?? date('Y-m-d'); ?>&download_excel=1"><button type="button">Download Excel</button></a>
                <a href="../utils/export_pdf.php?date=<?php echo $_GET['date'] ?? date('Y-m-d'); ?>&download_pdf=1">
                    <button type="button">Download PDF</button>
                </a>
            </form>
        </main>
    </div>

</body>

</html>
