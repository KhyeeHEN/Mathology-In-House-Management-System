<?php
include '../setting.php'; // adjust path as needed

// Fetch timetable data
$sql = "SELECT * FROM student_timetable ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), start_time";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("includes/Top_Nav_Bar.php"); ?>

            <!-- Page Content -->
            <section class="timetable-section">
                <table class="timetable">
                    <tr class="table_header">
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Approved At</th>
                    </tr>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                            $course = htmlspecialchars($row['course']);
                            $day = htmlspecialchars($row['day']);
                            $start = htmlspecialchars($row['start_time']);
                            $end = htmlspecialchars($row['end_time']);
                            $approved = htmlspecialchars($row['approved_at']);

                            echo "<tr>
                                <td>$name</td>
                                <td>$course</td>
                                <td>$day</td>
                                <td>$start</td>
                                <td>$end</td>
                                <td>$approved</td>
                              </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No timetable entries found.</td></tr>";
                    }
                    ?>
                </table>

                <div style="text-align: left; margin: 20px;">
                    <a href="timetable_reschedule.php">
                        <button style="
                            background-color: #4CAF50;
                            color: white;
                            padding: 10px 20px;
                            border: none;
                            border-radius: 5px;
                            font-size: 16px;
                            cursor: pointer;
                        ">
                            Reschedule Timetable
                        </button>
                    </a>
                </div>
                
            </section>

        </main>
    </div>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>
