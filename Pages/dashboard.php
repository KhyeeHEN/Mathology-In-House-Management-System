<?php
require_once 'setting.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verify database connection exists
if (!isset($conn) || $conn->connect_error) {
    die("Database connection not established");
}

// Get user info from session
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$user_name = $_SESSION['name'];

// Fetch events based on user role
if ($user_role === 'admin') {
    // Admin sees all events from all tables
    $query = "SELECT 
                'student' AS type, 
                course AS title, 
                CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', DAY(CURDATE())) AS class_date,
                start_time, 
                end_time, 
                'Classroom' AS venue, 
                'Instructor' AS lecturer, 
                'Scheduled class' AS description
              FROM student_timetable
              UNION ALL
              SELECT 
                'instructor' AS type, 
                course AS title, 
                CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', DAY(CURDATE())) AS class_date,
                start_time, 
                end_time, 
                'Classroom' AS venue, 
                'Teaching' AS lecturer, 
                'Teaching session' AS description
              FROM instructor_timetable";
    $result = $conn->query($query);
    
} elseif ($user_role === 'instructor') {
    // Instructor sees their timetable and their classes
    $stmt = $conn->prepare("SELECT 
                            course AS title, 
                            CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', DAY(CURDATE())) AS class_date,
                            start_time, 
                            end_time, 
                            'Classroom' AS venue, 
                            'You' AS lecturer, 
                            'Teaching session' AS description
                          FROM instructor_timetable
                          WHERE id IN (
                              SELECT instructor_timetable_id 
                              FROM instructor_courses 
                              WHERE instructor_id = (
                                  SELECT instructor_id 
                                  FROM instructor 
                                  WHERE CONCAT(First_Name, ' ', Last_Name) = ?
                              )
                          )");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
} else {
    // Student sees their own timetable
    $stmt = $conn->prepare("SELECT 
                            course AS subject_name, 
                            CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', DAY(CURDATE())) AS class_date,
                            start_time, 
                            end_time, 
                            'Classroom' AS venue, 
                            (SELECT CONCAT(First_Name, ' ', Last_Name) 
                             FROM instructor 
                             WHERE instructor_id = (
                                 SELECT instructor_id 
                                 FROM instructor_courses 
                                 WHERE course_id = (
                                     SELECT course_id 
                                     FROM student_courses 
                                     WHERE student_course_id = st.student_course_id
                                 )
                             )
                            ) AS lecturer,
                            'Scheduled class' AS description
                          FROM student_timetable st
                          WHERE student_course_id IN (
                              SELECT student_course_id 
                              FROM student_courses 
                              WHERE student_id = (
                                  SELECT student_id 
                                  FROM students 
                                  WHERE CONCAT(First_Name, ' ', Last_Name) = ?
                              )
                          )");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
}

$events = $result->fetch_all(MYSQLI_ASSOC);

function calculateDuration($start, $end) {
    $startTime = new DateTime($start);
    $endTime = new DateTime($end);
    $interval = $startTime->diff($endTime);
    
    $hours = $interval->h;
    $minutes = $interval->i;
    
    if ($hours > 0 && $minutes > 0) {
        return "$hours hours $minutes minutes";
    } elseif ($hours > 0) {
        return "$hours hours";
    } else {
        return "$minutes minutes";
    }
}

// Convert database events to calendar format
$calendarEvents = [];
foreach ($events as $event) {
    $calendarEvents[] = [
        'title' => $event['subject_name'] ?? $event['title'],
        'date' => $event['class_date'],
        'type' => $event['type'] ?? 'class',
        'time' => date('h:i A', strtotime($event['start_time'])),
        'duration' => calculateDuration($event['start_time'], $event['end_time']),
        'venue' => $event['venue'],
        'lecturer' => $event['lecturer'],
        'description' => $event['description']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($user_role); ?> Dashboard</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require("includes/Aside_Nav.php"); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php require("includes/Top_Nav_Bar.php"); ?>

            <!-- Welcome Message -->
            <div class="welcome-message">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
                <p>You are logged in as: <?php echo htmlspecialchars(ucfirst($user_role)); ?></p>
                <?php if ($user_role === 'instructor'): ?>
                    <p class="instructor-badge"><i class="fas fa-chalkboard-teacher"></i> Instructor View</p>
                <?php elseif ($user_role === 'admin'): ?>
                    <p class="admin-badge"><i class="fas fa-shield-alt"></i> Administrator View</p>
                <?php endif; ?>
            </div>

            <!-- Calendar Section -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <div class="calendar-navigation">
                        <button class="nav-btn" id="prevMonth">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h2 id="currentMonth"><?php echo date('F Y'); ?></h2>
                        <button class="nav-btn" id="nextMonth">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <?php if ($user_role === 'admin'): ?>
                        <div class="admin-controls">
                            <button id="addEvent" class="btn btn-small">
                                <i class="fas fa-plus"></i> Add Event
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-days" id="calendarDays">
                        <!-- Days will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Event Details Section -->
            <div class="event-details-container">
                <h3><?php echo $user_role === 'instructor' ? 'Your Teaching Schedule' : 'Your Upcoming Classes'; ?></h3>
                <div class="events-list">
                    <?php if (empty($calendarEvents)): ?>
                        <div class="no-events">
                            <i class="fas fa-calendar-times"></i>
                            <p>No scheduled <?php echo $user_role === 'instructor' ? 'teaching sessions' : 'classes'; ?> found</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($calendarEvents as $event): ?>
                            <div class="event-card">
                                <div class="event-header">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <?php if ($user_role === 'admin'): ?>
                                        <span class="event-type-badge"><?php echo ucfirst($event['type']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="event-details">
                                    <p><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($event['date']); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['time']); ?> (<?php echo $event['duration']; ?>)</p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue']); ?></p>
                                    <?php if ($user_role === 'student' && isset($event['lecturer'])): ?>
                                        <p><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($event['lecturer']); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($event['description'])): ?>
                                        <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const calendarEvents = <?php echo json_encode($calendarEvents); ?>;
        const currentUserRole = '<?php echo $user_role; ?>';
        console.log('Loaded events for ' + currentUserRole + ':', calendarEvents);
    </script>
    
    <script type="module" src="../scripts/dashboard.js"></script>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>