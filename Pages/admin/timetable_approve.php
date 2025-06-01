<?php
require_once '../setting.php';
session_start();
$per_page = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $per_page;

error_log("Script accessed: " . date('Y-m-d H:i:s'));
error_log("POST data: " . print_r($_POST, true));

// Initialize search query
$search_query = trim($_GET['search'] ?? '');
$show_search_results = !empty($search_query);

// Handle all POST actions first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle approval
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['id'];
        $type = 'student'; // Only student now
        
        $tables = [
            'student' => [
                'request_table' => 'student_timetable_requests',
                'timetable_table' => 'student_timetable',
                'courses_table' => 'student_courses',
                'request_course_id' => 'student_course_id',
                'courses_pk' => 'student_course_id'
            ]
        ];
        
        $cfg = $tables[$type];
        $conn->begin_transaction();
        
        try {
            // Verify request exists
            $stmt = $conn->prepare("SELECT * FROM {$cfg['request_table']} WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            
            if (!$request) {
                throw new Exception("Request not found or already processed");
            }
    
            // Get full request details
            $stmt = $conn->prepare("
                SELECT r.*, c.course_name, sc.course_id
                FROM {$cfg['request_table']} r
                JOIN {$cfg['courses_table']} sc ON r.{$cfg['request_course_id']} = sc.{$cfg['courses_pk']}
                JOIN courses c ON sc.course_id = c.course_id
                WHERE r.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $fullRequest = $stmt->get_result()->fetch_assoc();
            
            if (!$fullRequest) {
                throw new Exception("Course details not found for this request");
            }
    
            // Insert into timetable
            $stmt = $conn->prepare("
                INSERT INTO {$cfg['timetable_table']} 
                ({$cfg['request_course_id']}, day, start_time, end_time, approved_at, course)
                VALUES (?, ?, ?, ?, NOW(), ?)
            ");
            $stmt->bind_param(
                "issss", 
                $fullRequest[$cfg['request_course_id']],
                $fullRequest['day'],
                $fullRequest['start_time'],
                $fullRequest['end_time'],
                $fullRequest['course_name']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            // Update request status
            $stmt = $conn->prepare("
                UPDATE {$cfg['request_table']} 
                SET status = 'approved', rejection_reason = NULL 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "Timetable entry approved successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Approval failed: " . $e->getMessage();
            error_log("Approval Error: " . $e->getMessage());
        }
    }
    // Handle rejection
    elseif (isset($_POST['reject'])) {
        $id = (int)$_POST['id'];
        $type = 'student';
        $reason = $conn->real_escape_string($_POST['reason'] ?? '');
        
        try {
            $stmt = $conn->prepare("
                UPDATE student_timetable_requests 
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
    // Handle removal
    elseif (isset($_POST['remove'])) {
        $id = (int)$_POST['id'];
        $type = 'student';
        
        try {
            $stmt = $conn->prepare("DELETE FROM student_timetable WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Timetable entry removed successfully!";
            } else {
                $_SESSION['error'] = "No timetable entry found with that ID";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Removal failed: " . $e->getMessage();
        }
        
        // Instead of redirecting, just refresh the current page
        header("Refresh:0");
        exit();
    }
}

// Check if we're viewing a specific student
$student_id = $_GET['student_id'] ?? null;
$viewing_id = $student_id;
$viewing_type = 'student';

// Preserve search query from GET parameters
$search_query = trim($_GET['search'] ?? '');
$show_search_results = !empty($search_query);

// Get current tab selection (now preserves tab when viewing specific student)
$current_tab = 'students'; // Only students now

if ($viewing_id) {
    // Get details with enrolled courses
    $table = 'students';
    $id_field = 'student_id';
    
    $details = $conn->query("
        SELECT t.*, GROUP_CONCAT(c.course_name SEPARATOR ', ') as enrolled_courses
        FROM $table t
        LEFT JOIN student_courses sc ON t.{$id_field} = sc.{$id_field}
        LEFT JOIN courses c ON sc.course_id = c.course_id
        WHERE t.{$id_field} = $viewing_id
        GROUP BY t.{$id_field}
    ")->fetch_assoc();
    
    // Get current timetable with course names
    $timetable = $conn->query("
        SELECT tt.*, c.course_name as course
        FROM student_timetable tt
        JOIN student_courses sc ON tt.student_course_id = sc.student_course_id
        JOIN courses c ON sc.course_id = c.course_id
        JOIN $table t ON sc.{$id_field} = t.{$id_field}
        WHERE t.{$id_field} = $viewing_id
        ORDER BY FIELD(tt.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), tt.start_time
        LIMIT $per_page OFFSET $offset
    ");
    
    // Add total count query for pagination
    $total_query = $conn->query("
        SELECT COUNT(*) as total 
        FROM student_timetable tt
        JOIN student_courses sc ON tt.student_course_id = sc.student_course_id
        JOIN $table t ON sc.{$id_field} = t.{$id_field}
        WHERE t.{$id_field} = $viewing_id
    ");
    $total = $total_query->fetch_assoc()['total'];
    $total_pages = ceil($total / $per_page);
}

// Handle search functionality
$search_results = null;
if ($show_search_results) {
    $search_term = "%" . $conn->real_escape_string($search_query) . "%";
    
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
        JOIN students s ON sc.student_id = s.student_id
        JOIN courses c ON sc.course_id = c.course_id
        LEFT JOIN payment p ON s.student_id = p.student_id
        WHERE r.status = 'pending' 
        AND (s.student_id LIKE '$search_query' 
            OR s.First_Name LIKE '$search_term' 
            OR s.Last_Name LIKE '$search_term')
        LIMIT $per_page OFFSET $offset)
        
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
        FROM students s
        LEFT JOIN student_courses sc ON s.student_id = sc.student_id
        LEFT JOIN student_timetable t ON t.student_course_id = sc.student_course_id
        LEFT JOIN courses c ON sc.course_id = c.course_id
        LEFT JOIN payment p ON s.student_id = p.student_id
        WHERE (s.student_id LIKE '$search_query' 
            OR s.First_Name LIKE '$search_term' 
            OR s.Last_Name LIKE '$search_term')
        GROUP BY s.student_id, t.day, t.start_time, t.end_time, c.course_name, p.payment_status
        LIMIT $per_page OFFSET $offset)
        
        ORDER BY name, type
    ");
    
    // Get total count for search results
    $total_search = $conn->query("
        SELECT COUNT(*) as total FROM (
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
            JOIN students s ON sc.student_id = s.student_id
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
            FROM students s
            LEFT JOIN student_courses sc ON s.student_id = sc.student_id
            LEFT JOIN student_timetable t ON t.student_course_id = sc.student_course_id
            LEFT JOIN courses c ON sc.course_id = c.course_id
            LEFT JOIN payment p ON s.student_id = p.student_id
            WHERE (s.student_id LIKE '$search_query' 
                OR s.First_Name LIKE '$search_term' 
                OR s.Last_Name LIKE '$search_term')
            GROUP BY s.student_id, t.day, t.start_time, t.end_time, c.course_name, p.payment_status)
        ) as combined_results
    ")->fetch_assoc()['total'];
    $total_search_pages = ceil($total_search / $per_page);
}

// Fetch data if not viewing specific student and not searching
if (!$viewing_id && !$show_search_results) {
    // Pending requests
    $pending = $conn->query("
        SELECT r.*, s.student_id, s.Last_Name, s.First_Name, 
               s.Current_School_Grade, s.School, s.Mathology_Level,
               c.course_name as course
        FROM student_timetable_requests r
        JOIN student_courses sc ON r.student_course_id = sc.student_course_id
        JOIN students s ON sc.student_id = s.student_id
        JOIN courses c ON sc.course_id = c.course_id
        WHERE r.status = 'pending'
    ");
    
    // Current student list
    $current = $conn->query("
        SELECT s.student_id, s.Last_Name, s.First_Name, 
            s.School, s.Current_School_Grade,
            COUNT(t.id) as timetable_count
        FROM students s
        LEFT JOIN student_courses sc ON s.student_id = sc.student_id
        LEFT JOIN student_timetable t ON t.student_course_id = sc.student_course_id
        GROUP BY s.student_id, s.Last_Name, s.First_Name, s.School, s.Current_School_Grade
        LIMIT $per_page OFFSET $offset
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Approval</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tab-container { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-color: #4CAF50; font-weight: bold; }
        .section { margin-bottom: 40px; }
        .section-title { font-size: 1.2em; margin-bottom: 15px; color: #333; }
        
        .contents{
            margin: 20px;
        }

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
        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #3e8e41;
        }
        .pagination span {
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <div class = contents>
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
                    <!-- Student Details View -->
                    <a href="timetable_approve.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Back to Timetable Approval
                    </a>

                    <div class="student-info">
                        <h2><?= htmlspecialchars($details['First_Name'] ?? '') ?> <?= htmlspecialchars($details['Last_Name'] ?? '') ?></h2>
                        <div class="info-row">
                            <div class="info-label">School:</div>
                            <div><?= htmlspecialchars($details['School'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Grade:</div>
                            <div><?= htmlspecialchars($details['Current_School_Grade'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Enrolled Courses:</div>
                            <div><?= htmlspecialchars($details['enrolled_courses'] ?? 'None') ?></div>
                        </div>
                    </div>

                    <h3>Current Timetable</h3>
                    <?php if ($timetable->num_rows > 0): ?>
                        <!-- Tab Navigation for View Options -->
                        <div class="tab-container" style="margin-bottom: 20px;">
                            <button class="tab view-tab active" data-view="list">List View</button>
                            <button class="tab view-tab" data-view="table">Timetable View</button>
                        </div>

                        <!-- List View (Original Style) -->
                        <div id="list-view" class="view-content">
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
                                        <form method="POST" action="timetable_approve.php" onsubmit="return confirm('Are you sure you want to remove this entry?');">
                                            <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                                            <input type="hidden" name="type" value="student">
                                            <?php if (!empty($search_query)): ?>
                                                <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                                            <?php endif; ?>
                                            <button type="submit" name="remove" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Timetable View (New Table Format) -->
                        <div id="table-view" class="view-content" style="display: none;">
                            <?php
                            // Reset pointer to reuse the result set
                            $timetable->data_seek(0);
                            
                            // Organize timetable data by time and day
                            $timetableData = [];
                            $allTimes = [];
                            $daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                            
                            while ($entry = $timetable->fetch_assoc()) {
                                $day = $entry['day'];
                                $startTime = date('H:i', strtotime($entry['start_time']));
                                $endTime = date('H:i', strtotime($entry['end_time']));
                                $timeSlot = "$startTime-$endTime";
                                $displayTime = date('h:i A', strtotime($entry['start_time'])) . ' - ' . date('h:i A', strtotime($entry['end_time']));
                                
                                $timetableData[$timeSlot][$day] = [
                                    'course' => $entry['course'],
                                    'display_time' => $displayTime,
                                    'entry_id' => $entry['id']
                                ];
                                
                                if (!in_array($timeSlot, $allTimes)) {
                                    $allTimes[] = $timeSlot;
                                }
                            }
                            
                            // Sort times chronologically
                            usort($allTimes, function($a, $b) {
                                $timeA = explode('-', $a)[0];
                                $timeB = explode('-', $b)[0];
                                return strtotime($timeA) - strtotime($timeB);
                            });
                            ?>
                            
                            <div class="timetable-container" style="overflow-x: auto; margin-bottom: 20px;">
                                <table class="timetable" style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Day/Time</th>
                                            <?php foreach ($allTimes as $timeSlot): 
                                                $times = explode('-', $timeSlot);
                                                $displayTime = date('h:i A', strtotime($times[0])) . ' - ' . date('h:i A', strtotime($times[1]));
                                            ?>
                                                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;"><?= $displayTime ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($daysOrder as $day): ?>
                                            <tr>
                                                <td style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; white-space: nowrap;">
                                                    <?= $day ?>
                                                </td>
                                                <?php foreach ($allTimes as $timeSlot): ?>
                                                    <td style="border: 1px solid #ddd; padding: 8px; min-width: 120px; height: 60px; vertical-align: top;">
                                                        <?php if (isset($timetableData[$timeSlot][$day])): ?>
                                                            <div class="course-slot" style="background-color: #e3f2fd; padding: 5px; border-radius: 4px; height: 100%;">
                                                                <strong><?= htmlspecialchars($timetableData[$timeSlot][$day]['course']) ?></strong>
                                                                <div class="actions" style="margin-top: 5px;">
                                                                    <form method="POST" action="timetable_approve.php" onsubmit="return confirm('Are you sure you want to remove this entry?');" style="display: inline;">
                                                                        <input type="hidden" name="id" value="<?= $timetableData[$timeSlot][$day]['entry_id'] ?>">
                                                                        <input type="hidden" name="type" value="student">
                                                                        <?php if (!empty($search_query)): ?>
                                                                            <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                                                                        <?php endif; ?>
                                                                        <button type="submit" name="remove" class="btn btn-danger btn-sm" style="padding: 3px 6px; font-size: 12px;">
                                                                            <i class="fas fa-trash"></i> Remove
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- JavaScript to toggle views -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const viewTabs = document.querySelectorAll('.view-tab');
                                
                                viewTabs.forEach(tab => {
                                    tab.addEventListener('click', function() {
                                        // Remove active class from all tabs
                                        viewTabs.forEach(t => t.classList.remove('active'));
                                        
                                        // Add active class to clicked tab
                                        this.classList.add('active');
                                        
                                        // Hide all view contents
                                        document.querySelectorAll('.view-content').forEach(view => {
                                            view.style.display = 'none';
                                        });
                                        
                                        // Show selected view
                                        const viewToShow = this.getAttribute('data-view');
                                        document.getElementById(viewToShow + '-view').style.display = 'block';
                                    });
                                });
                            });
                        </script>   
                            <?php if (isset($timetable) && $timetable->num_rows > 0 && isset($total_pages) && $total_pages > 1): ?>
                                <div class="pagination">
                                    <?php if ($page > 1): ?>
                                        <a href="?student_id=<?= $viewing_id ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>&page=<?= ($page - 1) ?>" class="btn btn-primary">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <span>
                                        Page <?= $page ?> of <?= $total_pages ?>
                                    </span>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?student_id=<?= $viewing_id ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>&page=<?= ($page + 1) ?>" class="btn btn-primary">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 20px;">
                    <?php else: ?>
                        <div class="no-entries">
                            <i class="fas fa-info-circle"></i>
                            <p>No timetable entries found for this student</p>
                        </div>
                    <?php endif; ?>
                    <div style="margin-top: 20px;">
                        <a href="timetable_reschedule.php?student_id=<?= $viewing_id ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-alt"></i> Reschedule Timetable
                        </a>
                        <a href="timetable_add.php?student_id=<?= $viewing_id ?>" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Timetable
                        </a>
                        <a href="payment.php?student_id=<?= $viewing_id ?>" class="btn btn-warning">
                            <i class="fas fa-money-bill-wave"></i> Payment Status
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Main Approval Interface -->
                    <!-- Search Section -->
                    <div class="search-container">
                        <form method="GET" action="">
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
                                            <div class="info-label">Student:</div>
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

                                        <?php if (isset($result['payment_status'])): ?>
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
                                                <form method="POST" action="timetable_approve.php" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="id" value="<?= $result['id'] ?>">
                                                    <input type="hidden" name="type" value="student">
                                                    <?php if (!empty($search_query)): ?>
                                                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                                                    <?php endif; ?>
                                                    <button type="submit" name="approve" class="btn btn-success">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>

                                                <form method="POST" action="timetable_approve.php">
                                                    <input type="hidden" name="id" value="<?= $result['id'] ?>">
                                                    <input type="hidden" name="type" value="student">
                                                    <?php if (!empty($search_query)): ?>
                                                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
                                                    <?php endif; ?>
                                                    <input type="text" name="reason" placeholder="Rejection reason">
                                                    <button type="submit" name="reject" class="btn btn-danger">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <a href="timetable_approve.php?student_id=<?= htmlspecialchars($result['id']) ?>&search=<?= urlencode($search_query) ?>" 
                                                class="btn btn-primary">
                                                    <i class="fas fa-eye"></i> View Timetable
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
                                        <div class="info-row">
                                            <div class="info-label">School:</div>
                                            <div><?= htmlspecialchars($request['School'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Grade:</div>
                                            <div><?= htmlspecialchars($request['Current_School_Grade'] ?? 'Not specified') ?></div>
                                        </div>
                                        <div class="actions">
                                            <form method="POST" action="timetable_approve.php" onsubmit="return confirm('Are you sure?');">
                                                <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="type" value="student">
                                                <button type="submit" name="approve" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>

                                            <form method="POST" action="timetable_approve.php">
                                                <input type="hidden" name="id" value="<?= $request['id'] ?>">
                                                <input type="hidden" name="type" value="student">
                                                <input type="text" name="reason" placeholder="Rejection reason">
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
                                    <p>No pending requests for students</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Current Timetable Section -->
                        <div class="section">
                            <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Current Timetable</h2>
                            
                            <!-- Student List View -->
                            <?php if ($current && $current->num_rows > 0): ?>
                                <?php while ($student = $current->fetch_assoc()): ?>
                                    <div class="entry-card current-entry student-card" 
                                        onclick="window.location='timetable_approve.php?student_id=<?= htmlspecialchars($student['student_id']) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>'">
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
                                            <a href="timetable_approve.php?student_id=<?= htmlspecialchars($student['student_id']) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Timetable
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>

                                <?php if ($show_search_results && isset($total_search_pages) && $total_search_pages > 1): ?>
                                    <div class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?search=<?= urlencode($search_query) ?>&page=<?= $page - 1 ?>" class="btn btn-primary">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span>
                                            Page <?= $page ?> of <?= $total_search_pages ?>
                                        </span>
                                        
                                        <?php if ($page < $total_search_pages): ?>
                                            <a href="?search=<?= urlencode($search_query) ?>&page=<?= $page + 1 ?>" class="btn btn-primary">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-entries">
                                    <i class="fas fa-info-circle"></i>
                                    <p>No students found</p>
                                </div>
                            <?php endif; ?>

                            <?php 
                                // Get total count for main lists
                                $total = $conn->query("SELECT COUNT(*) as total FROM Students")->fetch_assoc()['total'];
                                $total_pages = ceil($total / $per_page);

                                if ($total_pages > 1): ?>
                                    <div class="pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span>
                                            Page <?= $page ?> of <?= $total_pages ?>
                                        </span>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script type="module" src="../../Scripts/common.js"></script>
</body>
</html>