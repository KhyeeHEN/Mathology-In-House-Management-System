<?php
require_once 'setting.php';

// Check if user is admin
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: unauthorized.php");
//     exit();
// }

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['id'];
        $type = $_POST['type']; // 'student' or 'instructor'
        
        $conn->begin_transaction();
        try {
            // Get the request details
            $request = $conn->query("SELECT * FROM {$type}_timetable_requests WHERE id = $id")->fetch_assoc();
            
            // Insert into main timetable
            $conn->query("INSERT INTO {$type}_timetable 
                         (last_name, first_name, course, day, start_time, end_time) 
                         VALUES 
                         ('{$request['last_name']}', '{$request['first_name']}', 
                         '{$request['course']}', '{$request['day']}', 
                         '{$request['start_time']}', '{$request['end_time']}')");
            
            // Update request status
            $conn->query("UPDATE {$type}_timetable_requests SET status = 'approved' WHERE id = $id");
            
            $conn->commit();
            $_SESSION['message'] = "Timetable entry approved successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Approval failed: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['reject'])) {
        $id = (int)$_POST['id'];
        $type = $_POST['type'];
        $reason = $conn->real_escape_string($_POST['reason'] ?? '');
        
        $conn->query("UPDATE {$type}_timetable_requests 
                     SET status = 'rejected', rejection_reason = '$reason' 
                     WHERE id = $id");
        $_SESSION['message'] = "Timetable entry rejected.";
    }
}

// Check if we're viewing a specific student
$student_id = $_GET['student_id'] ?? null;
$student_details = null;
$student_timetable = null;

if ($student_id) {
    // Get student details
    $student_details = $conn->query("SELECT * FROM Students WHERE student_id = $student_id")->fetch_assoc();
    
    // Get student's current timetable
    $student_timetable = $conn->query("SELECT * FROM student_timetable 
                                     WHERE last_name = '{$student_details['Last_Name']}' 
                                     AND first_name = '{$student_details['First_Name']}'");
}

// Get current tab selection (only if not viewing specific student)
$current_tab = !$student_id ? ($_GET['tab'] ?? 'students') : null;

// Fetch data based on selected tab if not viewing specific student
if (!$student_id) {
    if ($current_tab === 'students') {
        $pending = $conn->query("SELECT r.*, s.student_id, s.Current_School_Grade, s.School, s.Mathology_Level 
                                FROM student_timetable_requests r
                                JOIN Students s ON r.last_name = s.Last_Name AND r.first_name = s.First_Name
                                WHERE r.status = 'pending'");
        
        $current = $conn->query("SELECT s.student_id, s.Last_Name, s.First_Name, 
                                COUNT(t.id) as timetable_count
                                FROM Students s
                                LEFT JOIN student_timetable t ON t.last_name = s.Last_Name AND t.first_name = s.First_Name
                                GROUP BY s.student_id, s.Last_Name, s.First_Name");
    } else {
        $pending = $conn->query("SELECT r.*, i.Highest_Education, i.Remark, i.Training_Status 
                                FROM instructor_timetable_requests r
                                JOIN Instructor i ON r.last_name = i.Last_Name AND r.first_name = i.First_Name
                                WHERE r.status = 'pending'");
        
        $current = $conn->query("SELECT t.*, i.Highest_Education, i.Remark, i.Training_Status 
                                FROM instructor_timetable t
                                JOIN Instructor i ON t.last_name = i.Last_Name AND t.first_name = i.First_Name");
    }
}
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
    <style>
        .tab-container { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-color: #4CAF50; font-weight: bold; }
        .section { margin-bottom: 40px; }
        .section-title { font-size: 1.2em; margin-bottom: 15px; color: #333; }
        
        /* Updated Entry Card Styles */
        .entry-card { 
            background: white; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #ffc107;
            transition: all 0.3s;
        }
        .pending-entry {
            border-left: 4px solid #ffc107; /* Yellow for pending */
            background-color: #fffaf0; /* Light yellow background */
        }
        .current-entry {
            border-left: 4px solid #4CAF50; /* Green for approved */
            background-color: #f8fff8; /* Light green background */
        }
        .student-card {
            cursor: pointer;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .actions { display: flex; gap: 10px; margin-top: 10px; }
        .reject-form { display: flex; gap: 10px; align-items: center; }
        .reject-form input { flex-grow: 1; padding: 5px; }
        .no-entries { padding: 20px; text-align: center; color: #666; }
        .user-info { font-weight: bold; margin-bottom: 5px; }
        .info-row { display: flex; gap: 15px; margin-bottom: 5px; }
        .info-label { font-weight: bold; min-width: 120px; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-link i {
            margin-right: 5px;
        }
        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .timetable-table th, .timetable-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .timetable-table th {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: #4CAF50;
            color: white;
        }
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        .btn-primary {
            background-color: #2196F3;
            color: white;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="attendance.php" class="nav-item">
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
                        <a href="dashboard.php" class="nav-link">Home</a>
                        <a href="#" class="nav-link">Courses</a>
                        <a href="#" class="nav-link">Resources</a>
                        <a href="#" class="nav-link">Help</a>
                    </div>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser); ?>" alt="Profile" class="profile-img">
                        <div class="profile-dropdown">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>View Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="content-header">
                <h1><i class="fas fa-clipboard-check"></i> Timetable Approval</h1>
            </div>

            <?php if ($student_id): ?>
                <!-- Student Details View -->
                <a href="timetable_approve.php?tab=students" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Students
                </a>

                <div class="student-info">
                    <h2><?= htmlspecialchars($student_details['First_Name'] . ' ' . $student_details['Last_Name']) ?></h2>
                    <div class="info-row">
                        <div class="info-label">School:</div>
                        <div><?= htmlspecialchars($student_details['School']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Grade:</div>
                        <div><?= htmlspecialchars($student_details['Current_School_Grade']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mathology Level:</div>
                        <div><?= htmlspecialchars($student_details['Mathology_Level']) ?></div>
                    </div>
                </div>

                <h3>Current Timetable</h3>
                <?php if ($student_timetable->num_rows > 0): ?>
                    <?php while ($entry = $student_timetable->fetch_assoc()): ?>
                        <div class="entry-card current-entry">
                            <div class="info-row">
                                <div class="info-label">Course:</div>
                                <div><?= htmlspecialchars($entry['course']) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Day:</div>
                                <div><?= htmlspecialchars($entry['day']) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Time:</div>
                                <div><?= date('h:i A', strtotime($entry['start_time'])) ?> - <?= date('h:i A', strtotime($entry['end_time'])) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Approved At:</div>
                                <div><?= date('M j, Y', strtotime($entry['approved_at'])) ?></div>
                            </div>
                            <div class="actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                                    <input type="hidden" name="type" value="student">
                                    <button type="submit" name="remove" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-entries">
                        <i class="fas fa-info-circle"></i>
                        <p>No timetable entries found for this student</p>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <a href="timetable_reschedule.php?student_id=<?= $student_id ?>">
                        <button class="btn btn-primary">
                            <i class="fas fa-calendar-alt"></i> Reschedule Timetable
                        </button>
                    </a>
                </div>

            <?php else: ?>
                <!-- Main Approval Interface -->
                <!-- Tab Navigation -->
                <div class="tab-container">
                    <a href="?tab=students" class="tab <?= $current_tab === 'students' ? 'active' : '' ?>">
                        <i class="fas fa-user-graduate"></i> Students
                    </a>
                    <a href="?tab=instructors" class="tab <?= $current_tab === 'instructors' ? 'active' : '' ?>">
                        <i class="fas fa-chalkboard-teacher"></i> Instructors
                    </a>
                </div>

                <!-- Pending Requests Section -->
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-clock"></i> Pending Requests</h2>
                    
                    <?php if ($pending->num_rows > 0): ?>
                        <?php while ($entry = $pending->fetch_assoc()): ?>
                            <div class="entry-card pending-entry">
                                <div class="info-row">
                                    <div class="info-label">Name:</div>
                                    <div><?= htmlspecialchars($entry['last_name']) ?>, <?= htmlspecialchars($entry['first_name']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Course:</div>
                                    <div><?= htmlspecialchars($entry['course']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Day:</div>
                                    <div><?= htmlspecialchars($entry['day']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Time:</div>
                                    <div><?= date('h:i A', strtotime($entry['start_time'])) ?> - <?= date('h:i A', strtotime($entry['end_time'])) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><?= $current_tab === 'students' ? 'Mathology Level' : 'Training Status' ?>:</div>
                                    <div><?= htmlspecialchars($entry[$current_tab === 'students' ? 'Mathology_Level' : 'Training_Status']) ?></div>
                                </div>
                                
                                <div class="actions">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                                        <input type="hidden" name="type" value="<?= $current_tab ?>">
                                        <button type="submit" name="approve" class="btn btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    
                                    <form method="POST" class="reject-form">
                                        <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                                        <input type="hidden" name="type" value="<?= $current_tab ?>">
                                        <input type="text" name="reason" placeholder="Rejection reason (optional)">
                                        <button type="submit" name="reject" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-entries">
                            <i class="fas fa-check-circle"></i>
                            <p>No pending requests for <?= $current_tab ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Current Timetable Section -->
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Current Timetable</h2>
                    
                    <?php if ($current_tab === 'students'): ?>
                        <!-- Student List View -->
                        <?php if ($current->num_rows > 0): ?>
                            <?php while ($student = $current->fetch_assoc()): ?>
                                <a href="timetable_approve.php?student_id=<?= $student['student_id'] ?>" class="entry-card student-card current-entry">
                                    <div class="info-row">
                                        <div class="info-label">Name:</div>
                                        <div><?= htmlspecialchars($student['Last_Name']) ?>, <?= htmlspecialchars($student['First_Name']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">School:</div>
                                        <div><?= htmlspecialchars($student['School']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Grade:</div>
                                        <div><?= htmlspecialchars($student['Current_School_Grade']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Scheduled Sessions:</div>
                                        <div><?= $student['timetable_count'] ?></div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-entries">
                                <i class="fas fa-info-circle"></i>
                                <p>No students found</p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Instructor Timetable View -->
                        <?php if ($current->num_rows > 0): ?>
                            <?php while ($entry = $current->fetch_assoc()): ?>
                                <div class="entry-card current-entry">
                                    <div class="info-row">
                                        <div class="info-label">Name:</div>
                                        <div><?= htmlspecialchars($entry['last_name']) ?>, <?= htmlspecialchars($entry['first_name']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Course:</div>
                                        <div><?= htmlspecialchars($entry['course']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Day:</div>
                                        <div><?= htmlspecialchars($entry['day']) ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Time:</div>
                                        <div><?= date('h:i A', strtotime($entry['start_time'])) ?> - <?= date('h:i A', strtotime($entry['end_time'])) ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-entries">
                                <i class="fas fa-info-circle"></i>
                                <p>No timetable entries found for <?= $current_tab ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>