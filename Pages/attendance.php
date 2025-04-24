<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/attendence.css">
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="attendance.php" class="nav-item active">
                    <i class="fas fa-user-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="timetable.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timetable</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="payment.html" class="nav-item">
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
                    <h1>Attendance</h1>
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

   <!-- Attendance Table -->
   <div style="padding: 1rem 2rem;">
    <a href="daily_report.php" class="back-btn">
        <i class="fas fa-chart-line" style="margin-right: 8px;"></i> Daily Report
    </a>
</div>

   <?php include 'setting.php'; ?>

   <table class="attendence">
       <tr class="attendance_title">
           <th>Record ID</th>
           <th>Student ID</th>
           <th>Instructor ID</th>
           <th>Scheduled Date/Time</th>
           <th>Attendance Date/Time</th>
           <th>Hours Attended</th>
           <th>Replacement Hours</th>
           <th>Remaining Hours</th>
           <th>Status</th>
           <th>Created At</th>
           <th>Updated At</th>
           <th>Course</th>
       </tr>

       <?php
       $sql = "SELECT * FROM attendance_records ORDER BY timetable_datetime DESC";
       $result = $conn->query($sql);

       if ($result && $result->num_rows > 0) {
           while ($row = $result->fetch_assoc()) {
               echo "<tr>";
               echo "<td>" . $row['record_id'] . "</td>";
               echo "<td>" . $row['student_id'] . "</td>";
               echo "<td>" . ($row['instructor_id'] ?? '-') . "</td>";
               echo "<td>" . date("Y-m-d H:i", strtotime($row['timetable_datetime'])) . "</td>";
               echo "<td>" . ($row['attendance_datetime'] ? date("Y-m-d H:i", strtotime($row['attendance_datetime'])) : '-') . "</td>";
               echo "<td>" . ($row['hours_attended'] > 0 ? $row['hours_attended'] . " hrs" : '-') . "</td>";
               echo "<td>" . ($row['hours_replacement'] > 0 ? $row['hours_replacement'] . " hrs" : '-') . "</td>";
               echo "<td>" . $row['hours_remaining'] . " hrs</td>";
               echo "<td>" . ucfirst($row['status']) . "</td>";
               echo "<td>" . date("Y-m-d H:i", strtotime($row['created_at'])) . "</td>";
               echo "<td>" . date("Y-m-d H:i", strtotime($row['updated_at'])) . "</td>";
               echo "<td>" . htmlspecialchars($row['course']) . "</td>";
               echo "</tr>";
           }
       } else {
           echo "<tr><td colspan='12' style='text-align:center;'>No attendance records found.</td></tr>";
       }

       $conn->close(); // Close the DB connection
       ?>
   </table>

        </main>
    </div>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>
