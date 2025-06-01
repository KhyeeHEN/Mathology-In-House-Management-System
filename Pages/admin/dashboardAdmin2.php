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
            <div class="schedule-container">
                <div class="schedule-header">
                    <div class="schedule-title">
                        <h1><i class="fas fa-calendar-week"></i> Class Schedule</h1>
                        <div class="schedule-subtitle">Weekly timetable view</div>
                    </div>
                    <div class="schedule-controls">
                        <div class="week-nav">
                            <button class="nav-btn" id="prevWeek">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="current-week" id="currentWeek">
                                Dec 1 - Dec 7, 2024
                            </div>
                            <button class="nav-btn" id="nextWeek">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="view-toggle">
                            <button class="toggle-btn active" id="weekView">Week</button>
                            <button class="toggle-btn" id="monthView">Month</button>
                        </div>
                    </div>
                </div>

                <div class="schedule-grid" id="scheduleGrid">
                    <!-- Grid will be populated by JavaScript -->
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
    </script>
    
    <script type="module" src="/Scripts/dashboard.js"></script>
    <script type="module" src="/Scripts/common.js"></script>
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