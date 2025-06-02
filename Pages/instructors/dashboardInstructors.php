<?php
require_once '../setting.php';
session_start();

// Ensure instructor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if user is an instructor
$query = "SELECT role, instructor_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if (!$userData || $userData['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit();
}

$instructorId = $userData['instructor_id'];

// Fetch instructor's timetable with student information
$query = "SELECT 
            it.id, 
            it.day, 
            it.start_time, 
            it.end_time,
            it.course AS course_name,
            c.course_name AS fallback_course_name,
            GROUP_CONCAT(DISTINCT CONCAT(s.First_Name, ' ', s.Last_Name)) AS students
          FROM instructor_timetable it
          LEFT JOIN instructor_courses ic ON it.instructor_course_id = ic.instructor_course_id
          LEFT JOIN courses c ON (ic.course_id = c.course_id OR it.course = c.course_name)
          LEFT JOIN student_courses sc ON sc.course_id = c.course_id
          LEFT JOIN students s ON sc.student_id = s.student_id
          WHERE (ic.instructor_id = ? OR it.instructor_course_id IS NULL) AND it.status = 'active'
          GROUP BY it.id, it.day, it.start_time, it.end_time, it.course, c.course_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructorId);

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
    $courseName = !empty($event['course_name']) ? $event['course_name'] : $event['fallback_course_name'];
    $calendarEvents[] = [
        'title' => $courseName,
        'date' => getNextDateForDay($event['day']),
        'type' => '1',
        'time' => date('h:i A', strtotime($event['start_time'])),
        'duration' => calculateDuration($event['start_time'], $event['end_time']),
        'venue' => 'TBD',
        'description' => '',
        'students' => $event['students'] ? $event['students'] : 'No students enrolled'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/dashboardInstructors.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

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
        const calendarEvents = <?php echo json_encode($calendarEvents); ?>;
    </script>

    <script src="../../Scripts/common.js"></script>
    <script src="../../Scripts/dashboardInstructors.js"></script>
</body>
</html>