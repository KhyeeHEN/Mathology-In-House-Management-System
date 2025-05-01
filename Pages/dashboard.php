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
    // Admin sees all events
    $query = "SELECT * FROM subjects";
    $result = $conn->query($query);
} elseif ($user_role === 'instructor') {
    // Instructor sees only their classes
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE lecturer = ?");
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Student sees their own classes
    $stmt = $conn->prepare("SELECT * FROM subjects WHERE student_name = ?");
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
        'title' => $event['subject_name'],
        'date' => $event['class_date'],
        'type' => '1',
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

            <!-- Event Details Section -->
            <div class="event-details-container">
                <h3>Your Upcoming Classes</h3>
                <div class="events-list">
                    <?php if (empty($calendarEvents)): ?>
                        <p>No upcoming classes found.</p>
                    <?php else: ?>
                        <?php foreach ($calendarEvents as $event): ?>
                            <div class="event-card">
                                <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                <p><i class="fas fa-calendar-day"></i> <?php echo htmlspecialchars($event['date']); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($event['time']); ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue']); ?></p>
                                <?php if ($user_role === 'student'): ?>
                                    <p><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($event['lecturer']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        const calendarEvents = <?php echo json_encode($calendarEvents); ?>;
        console.log('Loaded events:', calendarEvents);
    </script>
    
    <script type="module" src="../scripts/dashboard.js"></script>
    <script type="module" src="../scripts/common.js"></script>
</body>
</html>