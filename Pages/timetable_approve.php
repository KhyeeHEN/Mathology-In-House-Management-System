<?php
require_once 'setting.php';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['id'];
        $type = $_POST['type']; // 'student' or 'instructor'
        
        $conn->begin_transaction();
        try {
            // Verify the request exists and is pending
            $check = $conn->query("SELECT id FROM {$type}_timetable_requests WHERE id = $id AND status = 'pending'")->num_rows;
            if ($check == 0) {
                throw new Exception("Invalid or already processed request");
            }

            // Get the request details with course information
            $request = $conn->query("
                SELECT r.*, c.course_name 
                FROM {$type}_timetable_requests r
                JOIN {$type}_courses sc ON r.{$type}_course_id = sc.{$type}_course_id
                JOIN courses c ON sc.course_id = c.course_id
                WHERE r.id = $id
            ")->fetch_assoc();
            
            if (!$request) {
                throw new Exception("Request details not found");
            }

            // Insert into main timetable
            $insertQuery = $conn->prepare("
                INSERT INTO {$type}_timetable 
                ({$type}_course_id, day, start_time, end_time, approved_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $insertQuery->bind_param(
                "isss",
                $request["{$type}_course_id"],
                $request['day'],
                $request['start_time'],
                $request['end_time']
            );
            $insertQuery->execute();
            
            if ($insertQuery->affected_rows === 0) {
                throw new Exception("Failed to create timetable entry");
            }

            // Update request status
            $updateQuery = $conn->prepare("
                UPDATE {$type}_timetable_requests 
                SET status = 'approved', rejection_reason = NULL 
                WHERE id = ?
            ");
            $updateQuery->bind_param("i", $id);
            $updateQuery->execute();
            
            if ($updateQuery->affected_rows === 0) {
                throw new Exception("Failed to update request status");
            }

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
        
        try {
            $stmt = $conn->prepare("
                UPDATE {$type}_timetable_requests 
                SET status = 'rejected', rejection_reason = ? 
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->bind_param("si", $reason, $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Timetable entry rejected.";
            } else {
                $_SESSION['error'] = "No pending request found with that ID";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Rejection failed: " . $e->getMessage();
        }
    }
}

// Check if we're viewing a specific student/instructor
$student_id = $_GET['student_id'] ?? null;
$instructor_id = $_GET['instructor_id'] ?? null;
$viewing_id = $student_id ?? $instructor_id;
$viewing_type = $student_id ? 'student' : 'instructor';
$details = null;
$timetable = null;

if ($viewing_id) {
    // Get details with enrolled courses
    $table = $viewing_type === 'student' ? 'Students' : 'instructor';
    $id_field = $viewing_type . '_id';
    
    $details = $conn->query("
        SELECT t.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as enrolled_courses
        FROM $table t
        LEFT JOIN {$viewing_type}_courses sc ON t.{$id_field} = sc.{$id_field}
        LEFT JOIN courses c ON sc.course_id = c.course_id
        WHERE t.{$id_field} = $viewing_id
        GROUP BY t.{$id_field}
    ")->fetch_assoc();
    
    // Get current timetable with course names
    $timetable = $conn->query("
        SELECT tt.*, c.course_name as course
        FROM {$viewing_type}_timetable tt
        JOIN {$viewing_type}_courses sc ON tt.{$viewing_type}_course_id = sc.{$viewing_type}_course_id
        JOIN courses c ON sc.course_id = c.course_id
        JOIN $table t ON sc.{$id_field} = t.{$id_field}
        WHERE t.{$id_field} = $viewing_id
        ORDER BY FIELD(tt.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), tt.start_time
    ");
}

// Get current tab selection (only if not viewing specific student/instructor)
$current_tab = !$viewing_id ? ($_GET['tab'] ?? 'students') : null;

// Handle search functionality
$search_query = trim($_GET['search'] ?? '');
$search_results = null;
$show_search_results = !empty($search_query);

if ($show_search_results) {
    $search_term = "%" . $conn->real_escape_string($search_query) . "%";
    
    if ($current_tab === 'students') {
        $search_results = $conn->query("
            (SELECT 
                s.student_id as id, 
                CONCAT(s.First_Name, ' ', s.Last_Name) as name,
                'Pending Request' as type,
                r.day,
                r.start_time,
                r.end_time,
                c.course_name as course,
                p.payment_status,
                NULL as timetable_count
            FROM student_timetable_requests r
            JOIN student_courses sc ON r.student_course_id = sc.student_course_id
            JOIN Students s ON sc.student_id = s.student_id
            JOIN courses c ON sc.course_id = c.course_id
            LEFT JOIN payment p ON s.student_id = p.student_id
            WHERE r.status = 'pending' 
            AND (s.student_id LIKE '$search_query' 
                OR s.First_Name LIKE '$search_term' 
                OR s.Last_Name LIKE '$search_term'))
            
            UNION
            
            (SELECT 
                s.student_id as id, 
                CONCAT(s.First_Name, ' ', s.Last_Name) as name,
                'Current Timetable' as type,
                t.day,
                t.start_time,
                t.end_time,
                c.course_name as course,
                p.payment_status,
                COUNT(t.id) as timetable_count
            FROM Students s
            LEFT JOIN student_courses sc ON s.student_id = sc.student_id
            LEFT JOIN student_timetable t ON t.student_course_id = sc.student_course_id
            LEFT JOIN courses c ON sc.course_id = c.course_id
            LEFT JOIN payment p ON s.student_id = p.student_id
            WHERE (s.student_id LIKE '$search_query' 
                OR s.First_Name LIKE '$search_term' 
                OR s.Last_Name LIKE '$search_term')
            GROUP BY s.student_id, t.day, t.start_time, t.end_time, c.course_name, p.payment_status)
            
            ORDER BY name, type
        ");
    } else {
        $search_results = $conn->query("
            (SELECT 
                i.instructor_id as id, 
                CONCAT(i.First_Name, ' ', i.Last_Name) as name,
                'Pending Request' as type,
                r.day,
                r.start_time,
                r.end_time,
                c.course_name as course,
                NULL as payment_status,
                NULL as timetable_count
            FROM instructor_timetable_requests r
            JOIN instructor_courses ic ON r.instructor_course_id = ic.instructor_course_id
            JOIN instructor i ON ic.instructor_id = i.instructor_id
            JOIN courses c ON ic.course_id = c.course_id
            WHERE r.status = 'pending' 
            AND (i.instructor_id LIKE '$search_query' 
                OR i.First_Name LIKE '$search_term' 
                OR i.Last_Name LIKE '$search_term'))
            
            UNION
            
            (SELECT 
                i.instructor_id as id, 
                CONCAT(i.First_Name, ' ', i.Last_Name) as name,
                'Current Timetable' as type,
                t.day,
                t.start_time,
                t.end_time,
                c.course_name as course,
                NULL as payment_status,
                COUNT(t.id) as timetable_count
            FROM instructor i
            LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
            LEFT JOIN instructor_timetable t ON t.instructor_course_id = ic.instructor_course_id
            LEFT JOIN courses c ON ic.course_id = c.course_id
            WHERE (i.instructor_id LIKE '$search_query' 
                OR i.First_Name LIKE '$search_term' 
                OR i.Last_Name LIKE '$search_term')
            GROUP BY i.instructor_id, t.day, t.start_time, t.end_time, c.course_name)
            
            ORDER BY name, type
        ");
    }
}

// Fetch data based on selected tab if not viewing specific student/instructor and not searching
if (!$viewing_id && !$show_search_results) {
    if ($current_tab === 'students') {
        // Pending requests with all original fields
        $pending = $conn->query("
            SELECT r.*, s.student_id, s.Last_Name, s.First_Name, 
                   s.Current_School_Grade, s.School, s.Mathology_Level,
                   c.course_name as course
            FROM student_timetable_requests r
            JOIN student_courses sc ON r.student_course_id = sc.student_course_id
            JOIN Students s ON sc.student_id = s.student_id
            JOIN courses c ON sc.course_id = c.course_id
            WHERE r.status = 'pending'
        ");
        
        // Current student list with timetable count
        $current = $conn->query("
            SELECT s.student_id, s.Last_Name, s.First_Name, 
                   s.School, s.Current_School_Grade,
                   COUNT(t.id) as timetable_count
            FROM Students s
            LEFT JOIN student_courses sc ON s.student_id = sc.student_id
            LEFT JOIN student_timetable t ON t.student_course_id = sc.student_course_id
            GROUP BY s.student_id, s.Last_Name, s.First_Name, s.School, s.Current_School_Grade
        ");
    } else {
        // Instructor section
        if ($current_tab === 'instructors') {
            // Pending requests with instructor and course info
            $pending = $conn->query("
                SELECT r.*, i.instructor_id, i.Last_Name, i.First_Name, 
                    i.Highest_Education, i.Remark, i.Training_Status,
                    c.course_name as course
                FROM instructor_timetable_requests r
                JOIN instructor_courses ic ON r.instructor_course_id = ic.instructor_course_id
                JOIN instructor i ON ic.instructor_id = i.instructor_id
                JOIN courses c ON ic.course_id = c.course_id
                WHERE r.status = 'pending'
            ");
            
            // Current instructor list with course count
            $current = $conn->query("
                SELECT i.instructor_id, i.Last_Name, i.First_Name, 
                    i.Highest_Education, i.Remark, i.Training_Status,
                    COUNT(DISTINCT ic.course_id) as course_count,
                    COUNT(t.id) as timetable_count
                FROM instructor i
                LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
                LEFT JOIN instructor_timetable t ON t.instructor_course_id = ic.instructor_course_id
                GROUP BY i.instructor_id, i.Last_Name, i.First_Name, 
                        i.Highest_Education, i.Remark, i.Training_Status
            ");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Approval</title>
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
            transition: all 0.3s;
            overflow: hidden;
        }
        .pending-entry {
            border-left: 4px solid #ffc107;
            background-color: #fffaf0;
        }
        .current-entry {
            border-left: 4px solid #4CAF50;
            background-color: #f8fff8;
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
        .info-row { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .info-label { 
            font-weight: bold; 
            min-width: 150px;
            color: #333;
        }
        .info-row div:last-child {
            flex: 1;
            min-width: 0;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-link i {
            margin-right: 5px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
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
        .btn-warning {
            background-color: #ffc107;
            color: black;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        a.btn {
            text-decoration: none;
        }
        .course-badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #1976d2;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .search-container {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .search-container input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-container button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
        .payment-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .payment-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("Top_Nav_Bar.php"); ?>

            <div class="content-header">
                <h1><i class="fas fa-clipboard-check"></i> Timetable Approval</h1>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if ($viewing_id): ?>
                <!-- Student/Instructor Details View -->
                <a href="timetable_approve.php?tab=<?= $viewing_type ?>s" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to <?= ucfirst($viewing_type) ?>s
                </a>

                <div class="student-info">
                    <h2><?= htmlspecialchars($details['First_Name'] ?? '') ?> <?= htmlspecialchars($details['Last_Name'] ?? '') ?></h2>
                    <?php if ($viewing_type === 'student'): ?>
                        <div class="info-row">
                            <div class="info-label">School:</div>
                            <div><?= htmlspecialchars($details['School'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Grade:</div>
                            <div><?= htmlspecialchars($details['Current_School_Grade'] ?? 'Not specified') ?></div>
                        </div>
                    <?php else: ?>
                        <div class="info-row">
                            <div class="info-label">Education:</div>
                            <div><?= htmlspecialchars($details['Highest_Education'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Training Status:</div>
                            <div><?= htmlspecialchars($details['Training_Status'] ?? 'Not specified') ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Enrolled Courses:</div>
                        <div><?= htmlspecialchars($details['enrolled_courses'] ?? 'None') ?></div>
                    </div>
                </div>

                <h3>Current Timetable</h3>
                <?php if ($timetable->num_rows > 0): ?>
                    <?php while ($entry = $timetable->fetch_assoc()): ?>
                        <div class="entry-card current-entry">
                            <div class="info-row">
                                <div class="info-label">Course:</div>
                                <div><?= htmlspecialchars($entry['course'] ?? '') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Day:</div>
                                <div><?= htmlspecialchars($entry['day'] ?? '') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Time:</div>
                                <div><?= date('h:i A', strtotime($entry['start_time'] ?? '')) ?> - <?= date('h:i A', strtotime($entry['end_time'] ?? '')) ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Approved At:</div>
                                <div><?= date('M j, Y', strtotime($entry['approved_at'] ?? '')) ?></div>
                            </div>
                            <div class="actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                                    <input type="hidden" name="type" value="<?= $viewing_type ?>">
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
                        <p>No timetable entries found for this <?= $viewing_type ?></p>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <a href="timetable_reschedule.php?<?= $viewing_type ?>_id=<?= $viewing_id ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-alt"></i> Reschedule Timetable
                    </a>
                    <?php if ($viewing_type === 'student'): ?>
                        <a href="payment.php?student_id=<?= $viewing_id ?>" class="btn btn-warning">
                            <i class="fas fa-money-bill-wave"></i> Payment Status
                        </a>
                    <?php endif; ?>
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

                <!-- Search Section -->
                <div class="search-container">
                    <form method="GET" action="">
                        <input type="hidden" name="tab" value="<?= $current_tab ?>">
                        <input type="text" name="search" placeholder="Search by ID or name..." 
                               value="<?= htmlspecialchars($search_query) ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <?php if ($show_search_results): ?>
                    <!-- Search Results Section -->
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-search"></i> Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
                        
                        <?php if ($search_results && $search_results->num_rows > 0): ?>
                            <?php while ($result = $search_results->fetch_assoc()): ?>
                                <div class="entry-card <?= $result['type'] === 'Pending Request' ? 'pending-entry' : 'current-entry' ?>">
                                    <div class="info-row">
                                        <div class="info-label"><?= $current_tab === 'students' ? 'Student' : 'Instructor' ?>:</div>
                                        <div>
                                            <?= htmlspecialchars($result['name']) ?> 
                                            (ID: <?= htmlspecialchars($result['id']) ?>)
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Type:</div>
                                        <div><?= htmlspecialchars($result['type']) ?></div>
                                    </div>

                                    <?php if ($result['type'] === 'Pending Request' || $result['timetable_count'] > 0): ?>
                                        <div class="info-row">
                                            <div class="info-label">Course:</div>
                                            <div><?= htmlspecialchars($result['course'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Schedule:</div>
                                            <div>
                                                <?= htmlspecialchars($result['day'] ?? '') ?> 
                                                <?= $result['start_time'] ? date('h:i A', strtotime($result['start_time'])) : '' ?> - 
                                                <?= $result['end_time'] ? date('h:i A', strtotime($result['end_time'])) : '' ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($current_tab === 'students' && isset($result['payment_status'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Payment Status:</div>
                                            <div>
                                                <span class="payment-status payment-<?= htmlspecialchars($result['payment_status'] ?? 'unpaid') ?>">
                                                    <?= ucfirst(htmlspecialchars($result['payment_status'] ?? 'unpaid')) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($result['type'] === 'Current Timetable'): ?>
                                        <div class="info-row">
                                            <div class="info-label">Sessions:</div>
                                            <div><?= $result['timetable_count'] ?? 0 ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="actions">
                                        <?php if ($result['type'] === 'Pending Request'): ?>
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?= $result['id'] ?>">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($current_tab) ?>">
                                                <button type="submit" name="approve" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>

                                            <form method="POST" class="reject-form">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($result['id']) ?>">
                                                <input type="hidden" name="type" value="<?= htmlspecialchars($current_tab) ?>">
                                                <input type="text" name="reason" placeholder="Rejection reason (optional)">
                                                <button type="submit" name="reject" class="btn btn-danger">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="timetable_approve.php?<?= $current_tab ?>_id=<?= htmlspecialchars($result['id']) ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="no-entries">
                                <i class="fas fa-check-circle"></i>
                                <p>No results found for your search</p>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Default Display (Pending + Current) -->

                    <!-- Pending Requests Section -->
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-clock"></i> Pending Requests</h2>
                        <?php if (isset($pending) && $pending->num_rows > 0): ?>
                            <?php while ($request = $pending->fetch_assoc()): ?>
                                <div class="entry-card pending-entry">
                                    <div class="info-row">
                                        <div class="info-label">Name:</div>
                                        <div><?= htmlspecialchars($request['Last_Name'] ?? '') ?>, <?= htmlspecialchars($request['First_Name'] ?? '') ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Course:</div>
                                        <div><?= htmlspecialchars($request['course'] ?? '') ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Day:</div>
                                        <div><?= htmlspecialchars($request['day'] ?? '') ?></div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Time:</div>
                                        <div><?= date('h:i A', strtotime($request['start_time'] ?? '')) ?> - <?= date('h:i A', strtotime($request['end_time'] ?? '')) ?></div>
                                    </div>
                                    <?php if ($current_tab === 'students'): ?>
                                        <div class="info-row">
                                            <div class="info-label">School:</div>
                                            <div><?= htmlspecialchars($request['School'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Grade:</div>
                                            <div><?= htmlspecialchars($request['Current_School_Grade'] ?? 'Not specified') ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="info-row">
                                            <div class="info-label">Education:</div>
                                            <div><?= htmlspecialchars($request['Highest_Education'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Training Status:</div>
                                            <div><?= htmlspecialchars($request['Training_Status'] ?? 'Not specified') ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="actions">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                            <input type="hidden" name="type" value="<?= $current_tab ?>">
                                            <button type="submit" name="approve" class="btn btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="reject-form">
                                            <input type="hidden" name="id" value="<?= $request['id'] ?>">
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
                                <p>No pending requests for <?= htmlspecialchars($current_tab) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Current Timetable Section -->
                    <div class="section">
                        <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Current Timetable</h2>
                        
                        <?php if ($current_tab === 'students'): ?>
                            <!-- Student List View -->
                            <?php if ($current && $current->num_rows > 0): ?>
                                <?php while ($student = $current->fetch_assoc()): ?>
                                    <div class="entry-card current-entry student-card" 
                                        onclick="window.location='timetable_approve.php?student_id=<?= htmlspecialchars($student['student_id']) ?>'">
                                        <div class="info-row">
                                            <div class="info-label">Name:</div>
                                            <div><?= htmlspecialchars($student['Last_Name']) ?>, <?= htmlspecialchars($student['First_Name']) ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">School:</div>
                                            <div><?= htmlspecialchars($student['School'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Grade:</div>
                                            <div><?= htmlspecialchars($student['Current_School_Grade'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Scheduled Sessions:</div>
                                            <div><?= htmlspecialchars($student['timetable_count'] ?? 0) ?></div>
                                        </div>
                                        <div class="actions">
                                            <a href="timetable_approve.php?student_id=<?= htmlspecialchars($student['student_id']) ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Timetable
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-entries">
                                    <i class="fas fa-info-circle"></i>
                                    <p>No students found</p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Instructor Timetable View -->
                            <?php if ($current && $current->num_rows > 0): ?>
                                <?php while ($instructor = $current->fetch_assoc()): ?>
                                    <div class="entry-card current-entry" 
                                        onclick="window.location='timetable_approve.php?instructor_id=<?= htmlspecialchars($instructor['instructor_id']) ?>'">
                                        <div class="info-row">
                                            <div class="info-label">Name:</div>
                                            <div><?= htmlspecialchars($instructor['Last_Name']) ?>, <?= htmlspecialchars($instructor['First_Name']) ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Education:</div>
                                            <div><?= htmlspecialchars($instructor['Highest_Education'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Courses Assigned:</div>
                                            <div><?= htmlspecialchars($instructor['course_count'] ?? 0) ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Scheduled Sessions:</div>
                                            <div><?= htmlspecialchars($instructor['timetable_count'] ?? 0) ?></div>
                                        </div>
                                        <div class="actions">
                                            <a href="timetable_approve.php?instructor_id=<?= htmlspecialchars($instructor['instructor_id']) ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Timetable
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-entries">
                                    <i class="fas fa-info-circle"></i>
                                    <p>No timetable entries found for <?= htmlspecialchars($current_tab) ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>