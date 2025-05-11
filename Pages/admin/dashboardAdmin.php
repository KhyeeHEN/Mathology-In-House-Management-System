<?php
require_once '../setting.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all scheduled classes with student and instructor information
$query = "
    SELECT 
        c.course_name,
        st.day,
        st.start_time,
        st.end_time,
        GROUP_CONCAT(DISTINCT CONCAT(s.First_Name, ' ', s.Last_Name) SEPARATOR ', ') AS students,
        CONCAT(i.First_Name, ' ', i.Last_Name) AS instructor
    FROM 
        student_timetable st
    JOIN 
        student_courses sc ON st.student_course_id = sc.student_course_id
    JOIN 
        courses c ON sc.course_id = c.course_id
    JOIN 
        students s ON sc.student_id = s.student_id
    JOIN 
        instructor_courses ic ON ic.course_id = c.course_id
    JOIN 
        instructor i ON ic.instructor_id = i.instructor_id
    WHERE 
        st.status = 'active'
    GROUP BY 
        c.course_name, st.day, st.start_time, st.end_time, instructor
    ORDER BY 
        st.day, st.start_time
";

$stmt = $conn->prepare($query);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);

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
foreach ($classes as $class) {
    $calendarEvents[] = [
        'title' => $class['course_name'] . ' - ' . $class['instructor'],
        'date' => getNextDateForDay($class['day']),
        'type' => '1',
        'time' => date('h:i A', strtotime($class['start_time'])),
        'duration' => calculateDuration($class['start_time'], $class['end_time']),
        'students' => $class['students'],
        'description' => 'Students: ' . $class['students']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../styles/dashboard.css">
    <link rel="stylesheet" href="../../styles/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .event-details {
            margin-top: 5px;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

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

            <!-- Additional Admin Summary Section -->
            <div class="admin-summary">
                <div class="summary-card">
                    <h3>Total Students</h3>
                    <?php
                    $query = "SELECT COUNT(*) as total FROM students";
                    $result = $conn->query($query);
                    $totalStudents = $result->fetch_assoc()['total'];
                    ?>
                    <p><?php echo $totalStudents; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Instructors</h3>
                    <?php
                    $query = "SELECT COUNT(*) as total FROM instructor";
                    $result = $conn->query($query);
                    $totalInstructors = $result->fetch_assoc()['total'];
                    ?>
                    <p><?php echo $totalInstructors; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Active Courses</h3>
                    <?php
                    $query = "SELECT COUNT(*) as total FROM courses";
                    $result = $conn->query($query);
                    $totalCourses = $result->fetch_assoc()['total'];
                    ?>
                    <p><?php echo $totalCourses; ?></p>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Pass PHP events to JavaScript with proper formatting
        const calendarEvents = <?php echo json_encode($calendarEvents); ?>;
        console.log('Admin loaded events:', calendarEvents);
        
        // Customize event display to show instructor and students
        function formatEventDetails(event) {
            return `
                <strong>${event.title}</strong>
                <div class="event-details">
                    <div>Time: ${event.time} (${event.duration})</div>
                    <div>Students: ${event.students}</div>
                </div>
            `;
        }
    </script>
    
    <script type="module" src="../../Scripts/dashboard.js"></script>
    <script type="module" src="../../Scripts/common.js"></script>
    <script>
        // Override default event display for admin
        document.addEventListener('DOMContentLoaded', () => {
            // This assumes your dashboard.js has a way to customize event display
            if (typeof window.customizeEventDisplay === 'function') {
                window.customizeEventDisplay = formatEventDetails;
            }
        });
    </script>
</body>
</html>