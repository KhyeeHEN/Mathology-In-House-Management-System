<?php
require_once '../setting.php';
session_start();

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$studentId = $_SESSION['user_id']; 

// First get the student_id from users table
$query = "SELECT student_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$actualStudentId = $userData['student_id'];

// Fetch student's timetable
$query = "SELECT st.id, c.course_name, st.day, st.start_time, st.end_time, st.status 
          FROM student_timetable st
          JOIN student_courses sc ON st.student_course_id = sc.student_course_id
          JOIN courses c ON sc.course_id = c.course_id
          WHERE sc.student_id = ? AND st.status = 'active'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $actualStudentId);

if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);

function calculateDuration($start, $end) {
    $startTime = new DateTime($start);
    $endTime = new DateTime($end);
    $interval = $startTime->diff($endTime);
    $hours = $interval->h;
    $minutes = $interval->i;
    return ($hours > 0 ? "$hours hours " : "") . ($minutes > 0 ? "$minutes minutes" : "");
}

function getNextDateForDay($dayName) {
    $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    $today = new DateTime();
    $currentDay = (int)$today->format('w');
    $targetDay = array_search(ucfirst($dayName), $daysOfWeek);
    if ($targetDay === false) return null;
    $daysToAdd = ($targetDay - $currentDay + 7) % 7;
    $today->modify("+$daysToAdd days");
    return $today->format('Y-m-d');
}

// Format for calendar.js
$calendarEvents = [];
foreach ($events as $event) {
    $calendarEvents[] = [
        'title' => $event['course_name'],
        'date' => getNextDateForDay($event['day']),
        'type' => '1',
        'time' => date('h:i A', strtotime($event['start_time'])),
        'duration' => calculateDuration($event['start_time'], $event['end_time']),
        'venue' => 'TBD', // Optional placeholder
        'lecturer' => 'TBD', // Optional placeholder
        'description' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="/Styles/dashboard.css">
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav_Student.php"); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Student.php"); ?>

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
                    <div class="date-picker-container">
                        <input type="date" id="datePicker" class="date-picker">
                    </div>
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
        </main>
    </div>
    
    <script>
        // Pass PHP events to JavaScript with proper formatting
        const calendarEvents = <?php echo json_encode($calendarEvents); ?>;
        console.log('Loaded events:', calendarEvents); // Debug output
    </script>
    
    <script type="module" src="/Scripts/dashboard.js"></script>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>
