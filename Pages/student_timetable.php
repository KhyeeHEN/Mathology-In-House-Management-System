<?php
include 'setting.php'; // adjust path as needed

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
        <aside class="sidebar">
            <div class="logo-container">
                <h2>Mathology</h2>
            </div>
            <nav class="side-nav">
                <a href="dashboard.html" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="attendance.html" class="nav-item">
                    <i class="fas fa-user-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="timetable.php" class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timetable</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-left">
                    <button id="menu-toggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Timetable</h1>
                </div>
                <div class="nav-right">
                    <div class="nav-links">
                        <a href="dashboard.html" class="nav-link">Home</a>
                        <a href="#" class="nav-link">Courses</a>
                        <a href="#" class="nav-link">Resources</a>
                        <a href="#" class="nav-link">Help</a>
                    </div>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=Darshann" alt="Profile" class="profile-img">
                        <div class="profile-dropdown">
                            <span class="user-name">Darrshan</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="profile.html" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>View Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

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
