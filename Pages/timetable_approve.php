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

// Get current tab selection
$current_tab = $_GET['tab'] ?? 'students';

// Fetch data based on selected tab
if ($current_tab === 'students') {
    $pending = $conn->query("SELECT r.*, s.Current_School_Grade, s.School, s.Mathology_Level 
                            FROM student_timetable_requests r
                            JOIN Students s ON r.last_name = s.Last_Name AND r.first_name = s.First_Name
                            WHERE r.status = 'pending'");
    
    $current = $conn->query("SELECT t.*, s.Current_School_Grade, s.School, s.Mathology_Level 
                            FROM student_timetable t
                            JOIN Students s ON t.last_name = s.Last_Name AND t.first_name = s.First_Name");
} else {
    $pending = $conn->query("SELECT r.*, i.Highest_Education, i.Remark, i.Training_Status 
                            FROM instructor_timetable_requests r
                            JOIN Instructor i ON r.last_name = i.Last_Name AND r.first_name = i.First_Name
                            WHERE r.status = 'pending'");
    
    $current = $conn->query("SELECT t.*, i.Highest_Education, i.Remark, i.Training_Status 
                            FROM instructor_timetable t
                            JOIN Instructor i ON t.last_name = i.Last_Name AND t.first_name = i.First_Name");
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
        .entry-card { 
            background: white; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #ffc107;
        }
        .approved-entry { border-left-color: #4CAF50; }
        .actions { display: flex; gap: 10px; margin-top: 10px; }
        .reject-form { display: flex; gap: 10px; align-items: center; }
        .reject-form input { flex-grow: 1; padding: 5px; }
        .no-entries { padding: 20px; text-align: center; color: #666; }
        .user-info { font-weight: bold; margin-bottom: 5px; }
        .info-row { display: flex; gap: 15px; margin-bottom: 5px; }
        .info-label { font-weight: bold; min-width: 120px; }
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
                        <div class="entry-card">
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
                
                <?php if ($current->num_rows > 0): ?>
                    <?php while ($entry = $current->fetch_assoc()): ?>
                        <div class="entry-card approved-entry">
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
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-entries">
                        <i class="fas fa-info-circle"></i>
                        <p>No timetable entries found for <?= $current_tab ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>