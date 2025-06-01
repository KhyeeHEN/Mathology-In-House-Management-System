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
    <link rel="stylesheet" href="/Styles/dashboard.css">
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/dashboardAdmin2.css">
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

            <!-- Staff Duty Roster Section -->
            <div class="staff-duty-roster">
                <h2 class="section-title">Current Classes Roster</h2>
                
                <div class="roster-controls">
                    <div class="current-time-display">
                        <i class="fas fa-clock"></i>
                        <span id="currentDateTime"><?php echo date('l, F j, Y - g:i A'); ?></span>
                    </div>
                    <div class="view-toggle">
                        <button id="dayView" class="toggle-btn active">Day View</button>
                        <button id="weekView" class="toggle-btn">Week View</button>
                    </div>
                </div>
                
                <div class="roster-container">
                    <!-- Current Day Classes -->
                    <div class="current-day-roster">
                        <h3>Today's Classes (<?php echo date('l'); ?>)</h3>
                        
                        <?php
                        $currentDay = date('l');
                        $currentTime = date('H:i:s');
                        $todayClasses = [];
                        
                        foreach ($classes as $class) {
                            if ($class['day'] === $currentDay) {
                                $startTime = $class['start_time'];
                                $endTime = $class['end_time'];
                                
                                // Check if current time is within class time
                                $isCurrent = ($currentTime >= $startTime && $currentTime <= $endTime);
                                
                                $todayClasses[] = [
                                    'class' => $class,
                                    'is_current' => $isCurrent
                                ];
                            }
                        }
                        
                        if (empty($todayClasses)) {
                            echo '<div class="no-classes">No classes scheduled for today.</div>';
                        } else {
                            echo '<div class="class-cards-container">';
                            foreach ($todayClasses as $item) {
                                $class = $item['class'];
                                $isCurrent = $item['is_current'];
                                $duration = calculateDuration($class['start_time'], $class['end_time']);
                                
                                echo '<div class="class-card ' . ($isCurrent ? 'current-class' : '') . '">';
                                echo '<div class="class-header">';
                                echo '<span class="class-time">' . date('g:i A', strtotime($class['start_time'])) . ' - ' . date('g:i A', strtotime($class['end_time'])) . '</span>';
                                echo '<span class="class-duration">' . $duration . '</span>';
                                if ($isCurrent) {
                                    echo '<span class="current-badge">Now</span>';
                                }
                                echo '</div>';
                                echo '<h4 class="class-title">' . $class['course_name'] . '</h4>';
                                echo '<div class="class-instructor"><i class="fas fa-chalkboard-teacher"></i> ' . $class['instructor'] . '</div>';
                                echo '<div class="class-students"><i class="fas fa-users"></i> ' . (count(explode(',', $class['students'])) > 3 ? substr($class['students'], 0, 30) . '...' : $class['students']) . '</div>';
                                
                                // Expanded details (hidden by default)
                                echo '<div class="class-details">';
                                echo '<div class="detail-row"><strong>Course:</strong> ' . $class['course_name'] . '</div>';
                                echo '<div class="detail-row"><strong>Instructor:</strong> ' . $class['instructor'] . '</div>';
                                echo '<div class="detail-row"><strong>Time:</strong> ' . date('g:i A', strtotime($class['start_time'])) . ' - ' . date('g:i A', strtotime($class['end_time'])) . ' (' . $duration . ')</div>';
                                echo '<div class="detail-row"><strong>Students:</strong> ' . $class['students'] . '</div>';
                                echo '</div>';
                                
                                echo '<button class="toggle-details-btn">Show Details <i class="fas fa-chevron-down"></i></button>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <!-- Week View (hidden by default) -->
                    <div class="week-view-roster" style="display: none;">
                        <div class="schedule-grid" id="scheduleGrid"></div>
                        <div class="week-navigation">
                            <button id="prevWeek"><i class="fas fa-chevron-left"></i> Previous Week</button>
                            <span id="currentWeek"></span>
                            <button id="nextWeek">Next Week <i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Tooltip for class details -->
                <div id="classTooltip" class="class-tooltip"></div>
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

            <!--Analysis-->
            <div class="analysis-section">
                <h2 class="section-title">Student & Instructor Analysis</h2>
                
                <div class="analysis-grid">
                    <!-- Student Demographics Card -->
                    <div class="analysis-card">
                        <div class="card-header">
                            <h3>Student Demographics</h3>
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-content">
                            <?php
                            // Gender distribution
                            $query = "SELECT Gender, COUNT(*) as count FROM students GROUP BY Gender";
                            $result = $conn->query($query);
                            $genderData = [];
                            while ($row = $result->fetch_assoc()) {
                                $genderData[] = $row;
                            }
                            
                            // School syllabus distribution
                            $query = "SELECT 
                                        CASE 
                                            WHEN School_Syllabus LIKE '%IGCSE%' THEN 'IGCSE'
                                            WHEN School_Syllabus LIKE '%SPM%' THEN 'SPM'
                                            WHEN School_Syllabus LIKE '%KSSR%' THEN 'KSSR'
                                            ELSE 'Other'
                                        END as syllabus,
                                        COUNT(*) as count
                                    FROM students
                                    GROUP BY syllabus";
                            $result = $conn->query($query);
                            $syllabusData = [];
                            while ($row = $result->fetch_assoc()) {
                                $syllabusData[] = $row;
                            }
                            
                            // Mathology level distribution
                            $query = "SELECT Mathology_Level, COUNT(*) as count FROM students GROUP BY Mathology_Level";
                            $result = $conn->query($query);
                            $levelData = [];
                            while ($row = $result->fetch_assoc()) {
                                $levelData[] = $row;
                            }
                            ?>
                            
                            <div class="charts-container">
                                <div class="chart-item">
                                    <h4>Gender Distribution</h4>
                                    <div class="demographic-chart" id="genderChart"></div>
                                    <div class="chart-legend">
                                        <div class="legend-item"><span class="color-box male"></span> Male: <?php echo $genderData[1]['count']; ?></div>
                                        <div class="legend-item"><span class="color-box female"></span> Female: <?php echo $genderData[0]['count']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="chart-item">
                                    <h4>Syllabus Distribution</h4>
                                    <div class="demographic-bars" id="syllabusChart">
                                        <?php foreach ($syllabusData as $item): ?>
                                        <div class="bar-container">
                                            <div class="bar-label"><?php echo $item['syllabus']; ?></div>
                                            <div class="bar-outer">
                                                <div class="bar-inner" style="width: <?php echo ($item['count'] / 40) * 100; ?>%"></div>
                                            </div>
                                            <div class="bar-value"><?php echo $item['count']; ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="chart-item wide">
                                <h4>Mathology Level Distribution</h4>
                                <div class="level-distribution">
                                    <?php foreach ($levelData as $level): ?>
                                    <div class="level-item <?php echo strtolower($level['Mathology_Level']); ?>">
                                        <div class="level-count"><?php echo $level['count']; ?></div>
                                        <div class="level-name"><?php echo $level['Mathology_Level']; ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructor Analysis Card -->
                    <div class="analysis-card">
                        <div class="card-header">
                            <h3>Instructor Analysis</h3>
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="card-content">
                            <?php
                            // Training status distribution
                            $query = "SELECT Training_Status, COUNT(*) as count FROM instructor GROUP BY Training_Status";
                            $result = $conn->query($query);
                            $trainingData = [];
                            while ($row = $result->fetch_assoc()) {
                                $trainingData[] = $row;
                            }
                            
                            // Education level distribution
                            $query = "SELECT 
                                        CASE 
                                            WHEN Highest_Education LIKE '%PhD%' THEN 'PhD'
                                            WHEN Highest_Education LIKE '%Master%' THEN 'Master'
                                            ELSE 'Bachelor'
                                        END as education,
                                        COUNT(*) as count
                                    FROM instructor
                                    GROUP BY education";
                            $result = $conn->query($query);
                            $educationData = [];
                            while ($row = $result->fetch_assoc()) {
                                $educationData[] = $row;
                            }
                            
                            // Calculate instructor experience levels based on DOB (rough estimate)
                            $query = "SELECT 
                                        CASE 
                                            WHEN YEAR(CURDATE()) - YEAR(DOB) >= 40 THEN 'Senior (40+)'
                                            WHEN YEAR(CURDATE()) - YEAR(DOB) >= 35 THEN 'Experienced (35-40)'
                                            WHEN YEAR(CURDATE()) - YEAR(DOB) >= 30 THEN 'Mid-level (30-35)'
                                            ELSE 'Junior (<30)'
                                        END as age_group,
                                        COUNT(*) as count
                                    FROM instructor
                                    GROUP BY age_group
                                    ORDER BY MIN(YEAR(DOB))";
                            $result = $conn->query($query);
                            $experienceData = [];
                            while ($row = $result->fetch_assoc()) {
                                $experienceData[] = $row;
                            }
                            ?>
                            
                            <div class="charts-container">
                                <div class="chart-item">
                                    <h4>Training Status</h4>
                                    <div class="training-status">
                                        <?php foreach ($trainingData as $status): ?>
                                        <div class="status-item <?php echo strtolower(str_replace(' ', '-', $status['Training_Status'])); ?>">
                                            <div class="status-count"><?php echo $status['count']; ?></div>
                                            <div class="status-label"><?php echo $status['Training_Status']; ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="chart-item">
                                    <h4>Education Qualification</h4>
                                    <div class="qualification-chart" id="educationChart">
                                        <?php foreach ($educationData as $edu): ?>
                                        <div class="qualification-segment <?php echo strtolower($edu['education']); ?>" 
                                            style="height: <?php echo ($edu['count'] / 20) * 100; ?>%"
                                            title="<?php echo $edu['education']; ?>: <?php echo $edu['count']; ?>">
                                            <span class="qualification-label"><?php echo $edu['education']; ?></span>
                                            <span class="qualification-count"><?php echo $edu['count']; ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="chart-item wide">
                                <h4>Instructor Experience Levels</h4>
                                <div class="experience-chart">
                                    <?php foreach ($experienceData as $exp): ?>
                                    <div class="experience-bar">
                                        <div class="experience-label"><?php echo $exp['age_group']; ?></div>
                                        <div class="experience-bar-outer">
                                            <div class="experience-bar-inner" style="width: <?php echo ($exp['count'] / 20) * 100; ?>%"></div>
                                        </div>
                                        <div class="experience-count"><?php echo $exp['count']; ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Performance Metrics Card -->
                    <div class="analysis-card full-width">
                        <div class="card-header">
                            <h3>Education Performance Matrix</h3>
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-content">
                            <?php
                            // Calculate student distribution by syllabus and level
                            $query = "SELECT 
                                        CASE 
                                            WHEN School_Syllabus LIKE '%IGCSE%' THEN 'IGCSE'
                                            WHEN School_Syllabus LIKE '%SPM%' THEN 'SPM'
                                            WHEN School_Syllabus LIKE '%KSSR%' THEN 'KSSR'
                                            ELSE 'Other'
                                        END as syllabus,
                                        Mathology_Level,
                                        COUNT(*) as count
                                    FROM students
                                    GROUP BY syllabus, Mathology_Level
                                    ORDER BY syllabus, Mathology_Level";
                            $result = $conn->query($query);
                            $matrixData = [];
                            while ($row = $result->fetch_assoc()) {
                                $matrixData[] = $row;
                            }
                            ?>
                            
                            <div class="matrix-container">
                                <table class="performance-matrix">
                                    <thead>
                                        <tr>
                                            <th>Syllabus / Level</th>
                                            <th>Beginner</th>
                                            <th>Intermediate</th>
                                            <th>Advanced</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $syllabusTypes = ['IGCSE', 'SPM', 'KSSR'];
                                        $totalsByLevel = ['Beginner' => 0, 'Intermediate' => 0, 'Advanced' => 0];
                                        
                                        foreach ($syllabusTypes as $syllabus) {
                                            echo "<tr>";
                                            echo "<td><strong>$syllabus</strong></td>";
                                            
                                            $syllabusTotal = 0;
                                            foreach (['Beginner', 'Intermediate', 'Advanced'] as $level) {
                                                $count = 0;
                                                foreach ($matrixData as $item) {
                                                    if ($item['syllabus'] == $syllabus && $item['Mathology_Level'] == $level) {
                                                        $count = $item['count'];
                                                        $totalsByLevel[$level] += $count;
                                                        $syllabusTotal += $count;
                                                        break;
                                                    }
                                                }
                                                
                                                $cellClass = '';
                                                if ($count > 10) $cellClass = 'high-count';
                                                else if ($count > 5) $cellClass = 'medium-count';
                                                else $cellClass = 'low-count';
                                                
                                                echo "<td class='$cellClass'>$count</td>";
                                            }
                                            
                                            echo "<td class='total-column'>$syllabusTotal</td>";
                                            echo "</tr>";
                                        }
                                        
                                        // Add totals row
                                        $grandTotal = array_sum($totalsByLevel);
                                        echo "<tr class='totals-row'>";
                                        echo "<td><strong>Total</strong></td>";
                                        foreach ($totalsByLevel as $level => $total) {
                                            echo "<td>$total</td>";
                                        }
                                        echo "<td class='total-column'>$grandTotal</td>";
                                        echo "</tr>";
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="key-insights">
                                <h4><i class="fas fa-lightbulb"></i> Key Insights</h4>
                                <ul>
                                    <?php
                                    // Generate some basic insights
                                    $maxSyllabus = '';
                                    $maxCount = 0;
                                    foreach ($syllabusData as $item) {
                                        if ($item['count'] > $maxCount) {
                                            $maxCount = $item['count'];
                                            $maxSyllabus = $item['syllabus'];
                                        }
                                    }
                                    
                                    $maxLevel = '';
                                    $maxLevelCount = 0;
                                    foreach ($levelData as $item) {
                                        if ($item['count'] > $maxLevelCount) {
                                            $maxLevelCount = $item['count'];
                                            $maxLevel = $item['Mathology_Level'];
                                        }
                                    }
                                    
                                    // Find gender ratio
                                    $maleCount = $genderData[1]['count'];
                                    $femaleCount = $genderData[0]['count'];
                                    $genderRatio = round($maleCount / $femaleCount, 2);
                                    
                                    // Find completed training percentage
                                    $completedCount = 0;
                                    $totalInstructors = 0;
                                    foreach ($trainingData as $item) {
                                        $totalInstructors += $item['count'];
                                        if ($item['Training_Status'] == 'Completed') {
                                            $completedCount = $item['count'];
                                        }
                                    }
                                    $completedPercentage = round(($completedCount / $totalInstructors) * 100);
                                    ?>
                                    
                                    <li><?php echo $maxSyllabus; ?> is the most common syllabus with <?php echo $maxCount; ?> students (<?php echo round(($maxCount / 40) * 100); ?>%).</li>
                                    <li>Most students are at the <?php echo $maxLevel; ?> level (<?php echo $maxLevelCount; ?> students).</li>
                                    <li>The male to female ratio among students is <?php echo $genderRatio; ?>:1.</li>
                                    <li><?php echo $completedPercentage; ?>% of instructors have completed their training.</li>
                                    <li>There's an opportunity to balance instructor qualifications with student needs across different syllabi.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
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

        class ScheduleCalendar {
            constructor() {
                this.currentWeek = new Date();
                this.timeSlots = this.generateTimeSlots();
                this.currentTooltip = null;
                
                // Sample data - replace with your PHP data
                this.classes = [
                    {
                        course: 'Mathematics',
                        instructor: 'John Smith',
                        students: 'Alice Johnson, Bob Wilson',
                        day: 'Monday',
                        startTime: '09:00',
                        endTime: '10:30',
                        type: 'math'
                    },
                    {
                        course: 'Physics',
                        instructor: 'Sarah Davis',
                        students: 'Charlie Brown, Diana Prince',
                        day: 'Monday',
                        startTime: '11:00',
                        endTime: '12:00',
                        type: 'science'
                    },
                    {
                        course: 'English Literature',
                        instructor: 'Michael Johnson',
                        students: 'Emma Watson, Frank Miller',
                        day: 'Tuesday',
                        startTime: '10:00',
                        endTime: '11:30',
                        type: 'english'
                    },
                    {
                        course: 'Chemistry',
                        instructor: 'Lisa Anderson',
                        students: 'Grace Lee, Henry Ford',
                        day: 'Wednesday',
                        startTime: '14:00',
                        endTime: '15:30',
                        type: 'science'
                    },
                    {
                        course: 'History',
                        instructor: 'Robert Taylor',
                        students: 'Ivy Chen, Jack Robinson',
                        day: 'Thursday',
                        startTime: '09:30',
                        endTime: '11:00',
                        type: 'history'
                    },
                    {
                        course: 'Art',
                        instructor: 'Maria Garcia',
                        students: 'Kate Williams, Leo Martinez',
                        day: 'Friday',
                        startTime: '13:00',
                        endTime: '14:30',
                        type: 'art'
                    }
                ];

                this.init();
            }

            generateTimeSlots() {
                const slots = [];
                for (let hour = 8; hour <= 17; hour++) {
                    slots.push(`${hour.toString().padStart(2, '0')}:00`);
                    if (hour < 17) {
                        slots.push(`${hour.toString().padStart(2, '0')}:30`);
                    }
                }
                return slots;
            }

            init() {
                this.setupEventListeners();
                this.renderSchedule();
                this.updateCurrentWeekDisplay();
                this.showCurrentTimeIndicator();
            }

            setupEventListeners() {
                document.getElementById('prevWeek').addEventListener('click', () => {
                    this.currentWeek.setDate(this.currentWeek.getDate() - 7);
                    this.updateCurrentWeekDisplay();
                    this.renderSchedule();
                });

                document.getElementById('nextWeek').addEventListener('click', () => {
                    this.currentWeek.setDate(this.currentWeek.getDate() + 7);
                    this.updateCurrentWeekDisplay();
                    this.renderSchedule();
                });

                document.getElementById('weekView').addEventListener('click', (e) => {
                    document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                });

                document.getElementById('monthView').addEventListener('click', (e) => {
                    document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    // Implement month view if needed
                });
            }

            updateCurrentWeekDisplay() {
                const startOfWeek = new Date(this.currentWeek);
                startOfWeek.setDate(this.currentWeek.getDate() - this.currentWeek.getDay());
                
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);

                const options = { month: 'short', day: 'numeric' };
                const startStr = startOfWeek.toLocaleDateString('en-US', options);
                const endStr = endOfWeek.toLocaleDateString('en-US', options);
                const year = startOfWeek.getFullYear();

                document.getElementById('currentWeek').textContent = `${startStr} - ${endStr}, ${year}`;
            }

            renderSchedule() {
                const grid = document.getElementById('scheduleGrid');
                grid.innerHTML = '';

                // Create time column
                const timeColumn = document.createElement('div');
                timeColumn.className = 'time-column';

                // Time header
                const timeHeader = document.createElement('div');
                timeHeader.className = 'time-slot';
                timeHeader.textContent = 'Time';
                timeColumn.appendChild(timeHeader);

                // Time slots
                this.timeSlots.forEach(time => {
                    const timeSlot = document.createElement('div');
                    timeSlot.className = 'time-slot';
                    timeSlot.textContent = this.formatTime12Hour(time);
                    timeColumn.appendChild(timeSlot);
                });

                grid.appendChild(timeColumn);

                // Create day columns
                const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const startOfWeek = new Date(this.currentWeek);
                startOfWeek.setDate(this.currentWeek.getDate() - this.currentWeek.getDay());

                daysOfWeek.forEach((day, index) => {
                    const dayColumn = document.createElement('div');
                    dayColumn.className = 'day-column';

                    const currentDate = new Date(startOfWeek);
                    currentDate.setDate(startOfWeek.getDate() + index);

                    // Day header
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'day-header';
                    dayHeader.innerHTML = `
                        <div class="day-name">${day.substring(0, 3)}</div>
                        <div class="day-date">${currentDate.getDate()}</div>
                    `;
                    dayColumn.appendChild(dayHeader);

                    // Time slots for this day
                    this.timeSlots.forEach(time => {
                        const timeSlot = document.createElement('div');
                        timeSlot.className = 'time-grid-slot';
                        
                        // Find classes for this day and time
                        const dayClasses = this.getClassesForDayAndTime(day, time);
                        
                        if (dayClasses.length > 0) {
                            dayClasses.forEach(classInfo => {
                                const classBlock = this.createClassBlock(classInfo);
                                timeSlot.appendChild(classBlock);
                            });
                        }

                        dayColumn.appendChild(timeSlot);
                    });

                    grid.appendChild(dayColumn);
                });
            }

            getClassesForDayAndTime(day, time) {
                return this.classes.filter(classInfo => {
                    if (classInfo.day !== day) return false;
                    
                    const classStart = this.timeToMinutes(classInfo.startTime);
                    const classEnd = this.timeToMinutes(classInfo.endTime);
                    const slotTime = this.timeToMinutes(time);
                    
                    return slotTime >= classStart && slotTime < classEnd;
                });
            }

            createClassBlock(classInfo) {
                const block = document.createElement('div');
                block.className = `class-block ${classInfo.type}`;
                
                const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
                const height = Math.max(40, (duration / 30) * 60 - 8); // Minimum 40px height
                block.style.height = `${height}px`;
                
                block.innerHTML = `
                    <div class="class-title">${classInfo.course}</div>
                    <div class="class-instructor">${classInfo.instructor}</div>
                    <div class="class-students">${classInfo.students}</div>
                `;

                // Add tooltip functionality
                block.addEventListener('mouseenter', (e) => this.showTooltip(e, classInfo));
                block.addEventListener('mouseleave', () => this.hideTooltip());

                return block;
            }

            showTooltip(event, classInfo) {
                const tooltip = document.getElementById('classTooltip');
                const duration = this.getClassDuration(classInfo.startTime, classInfo.endTime);
                
                tooltip.innerHTML = `
                    <div class="tooltip-title">${classInfo.course}</div>
                    <div class="tooltip-info"><i class="fas fa-clock"></i> ${this.formatTime12Hour(classInfo.startTime)} - ${this.formatTime12Hour(classInfo.endTime)} (${duration} min)</div>
                    <div class="tooltip-info"><i class="fas fa-chalkboard-teacher"></i> ${classInfo.instructor}</div>
                    <div class="tooltip-info"><i class="fas fa-users"></i> ${classInfo.students}</div>
                `;

                const rect = event.target.getBoundingClientRect();
                tooltip.style.left = `${rect.right + 10}px`;
                tooltip.style.top = `${rect.top}px`;
                tooltip.classList.add('show');
            }

            hideTooltip() {
                const tooltip = document.getElementById('classTooltip');
                tooltip.classList.remove('show');
            }

            getClassDuration(startTime, endTime) {
                const start = this.timeToMinutes(startTime);
                const end = this.timeToMinutes(endTime);
                return end - start;
            }

            timeToMinutes(time) {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
            }

            formatTime12Hour(time24) {
                const [hours, minutes] = time24.split(':').map(Number);
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const hours12 = hours % 12 || 12;
                return `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
            }

            showCurrentTimeIndicator() {
                const now = new Date();
                const currentTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
                const currentMinutes = this.timeToMinutes(currentTime);
                
                // Only show if within schedule hours
                if (currentMinutes >= this.timeToMinutes('08:00') && currentMinutes <= this.timeToMinutes('17:00')) {
                    const firstTimeSlot = this.timeToMinutes(this.timeSlots[0]);
                    const position = ((currentMinutes - firstTimeSlot) / 30) * 60 + 60; // +60 for header
                    
                    const dayColumns = document.querySelectorAll('.day-column');
                    const currentDay = now.getDay();
                    
                    if (dayColumns[currentDay]) {
                        const indicator = document.createElement('div');
                        indicator.className = 'current-time-indicator';
                        indicator.style.top = `${position}px`;
                        indicator.innerHTML = '<div class="current-time-dot"></div>';
                        dayColumns[currentDay].appendChild(indicator);
                    }
                }
            }
        }

        // Initialize the schedule calendar
        document.addEventListener('DOMContentLoaded', () => {
            new ScheduleCalendar();
        });
    </script>
    
    <script type="module" src="/Scripts/dashboard.js"></script>
    <script type="module" src="/Scripts/common.js"></script>
    <script type="module" src="/Scripts/dashboardAdmin2.js"></script>
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