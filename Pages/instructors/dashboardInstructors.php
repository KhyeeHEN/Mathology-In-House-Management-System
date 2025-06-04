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
            st.id, 
            st.day, 
            st.start_time, 
            st.end_time,
            st.course AS course_name,
            c.course_name AS fallback_course_name,
            GROUP_CONCAT(DISTINCT CONCAT(s.First_Name, ' ', s.Last_Name)) AS students,
            COUNT(s.student_id) AS student_count
          FROM student_timetable st
          JOIN student_courses sc ON st.student_course_id = sc.student_course_id
          JOIN students s ON sc.student_id = s.student_id
          JOIN courses c ON sc.course_id = c.course_id
          WHERE st.instructor_id = ? AND st.status = 'active'
          GROUP BY st.id, st.day, st.start_time, st.end_time, st.course, c.course_name";

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

// Format for calendar.js - Generate events for multiple months
$calendarEvents = [];
$currentYear = date('Y');

// Generate events for current month and next 2 months
for ($monthOffset = 0; $monthOffset < 3; $monthOffset++) {
    $targetMonth = date('n', strtotime("+$monthOffset months"));
    $targetYear = date('Y', strtotime("+$monthOffset months"));
    
    foreach ($events as $event) {
        $courseName = !empty($event['course_name']) ? $event['course_name'] : $event['fallback_course_name'];
        $studentsList = $event['students'] ? $event['students'] : 'No students enrolled';
        
        // Get all dates for this day of week in target month
        $dayOfWeek = $event['day'];
        $daysOfWeek = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $targetDayIndex = array_search(ucfirst($dayOfWeek), $daysOfWeek);
        
        if ($targetDayIndex !== false) {
            // Find all dates in target month that match this day of week
            $date = new DateTime("first $dayOfWeek of $targetYear-$targetMonth");
            $month = $date->format('n');
            
            while ($month == $targetMonth) {
                $calendarEvents[] = [
                    'title' => $courseName . ' (' . $event['student_count'] . ' students)',
                    'date' => $date->format('Y-m-d'),
                    'type' => '1',
                    'time' => date('h:i A', strtotime($event['start_time'])),
                    'duration' => calculateDuration($event['start_time'], $event['end_time']),
                    'venue' => 'TBD',
                    'description' => '',
                    'students' => $studentsList,
                    'dayOfWeek' => $dayOfWeek,
                    'start_time' => $event['start_time'],
                    'end_time' => $event['end_time']
                ];
                
                $date->modify('next ' . $dayOfWeek);
                $month = $date->format('n');
            }
        }
    }
}

// Debug output
echo '<script>console.log("PHP Generated Events Count:", ' . count($calendarEvents) . ');</script>';
echo '<script>console.log("PHP Generated Events:", ' . json_encode($calendarEvents) . ');</script>';
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

            <div class="calendar-wrapper">
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
                    
                    <div class="calendar-scroll-container">
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
                </div>
            </div>
        </main>
    </div>
    
    <!-- Pass PHP events to JavaScript -->
    <script>
        // Set calendar events globally BEFORE loading other scripts
        window.calendarEvents = <?php echo json_encode($calendarEvents); ?>;
        console.log('Events passed to JavaScript:', window.calendarEvents);
    </script>
    
    <script src="../../Scripts/common.js"></script>
    <script src="../../Scripts/dashboardInstructors.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Window calendarEvents:', window.calendarEvents);
            console.log('Type of calendarEvents:', typeof window.calendarEvents);
            console.log('Events count:', window.calendarEvents ? window.calendarEvents.length : 0);
            window.instructorCalendar = new InstructorCalendar();
        });
    </script>
</body>
</html>