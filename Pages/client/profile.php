<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
    // Start session and check if user is logged in as student
    session_start();
    require_once("../setting.php");
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
        header("Location: /login.php");
        exit();
    }
    
    // Fetch student data
    $user_id = $_SESSION['user_id'];
    
    // Get user account info
    $query = "SELECT u.*, s.* 
              FROM users u
              JOIN students s ON u.student_id = s.student_id
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    
    if (!$student) {
        die("Student data not found");
    }
    
    // Calculate age from DOB
    $age = '';
    if (!empty($student['DOB'])) {
        $dob = new DateTime($student['DOB']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
    }
    
    // Format gender
    $gender = isset($student['Gender']) ? ($student['Gender'] == 1 ? 'Male' : 'Female') : 'Not specified';
    
    // Get attendance summary
    $attendance_query = "SELECT 
                        SUM(hours_attended) as total_attended,
                        SUM(hours_replacement) as total_replacement,
                        SUM(hours_remaining) as total_remaining
                        FROM attendance_records 
                        WHERE student_id = ?";
    $attendance_stmt = $conn->prepare($attendance_query);
    $attendance_stmt->bind_param("i", $student['student_id']);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    $attendance = $attendance_result->fetch_assoc();
    
    // Set default values
    $email = $student['email'] ?? 'Not available';
    $created_at = isset($student['created_at']) ? date('F j, Y', strtotime($student['created_at'])) : 'Not available';
    $first_name = $student['First_Name'] ?? '';
    $last_name = $student['Last_Name'] ?? '';
    $full_name = trim("$first_name $last_name") ?: 'Student';
    ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav_Student.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Student.php"); ?>

            <!-- Profile Content -->
            <div class="profile-container">
                <div class="profile-header">
                    <h1>Student Profile</h1>
                    <p>View your personal information and learning progress</p>
                </div>

                <div class="profile-content">
                    <div class="profile-image-section">
                        <div class="image-container">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($full_name); ?>&background=4e73df&color=ffffff" alt="Profile" class="profile-image">
                        </div>
                    </div>

                    <div class="profile-details">
                        <h2 class="student-name"><?php echo htmlspecialchars($full_name); ?></h2>
                        <p class="student-level">Mathology Level <?php echo htmlspecialchars($student['Mathology_Level'] ?? 'Not specified'); ?></p>
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Gender:</span>
                                <span class="detail-value"><?php echo $gender; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date of Birth:</span>
                                <span class="detail-value"><?php echo !empty($student['DOB']) ? htmlspecialchars(date('F j, Y', strtotime($student['DOB']))) . " ($age years)" : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Current School:</span>
                                <span class="detail-value"><?php echo !empty($student['School']) ? htmlspecialchars($student['School']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Grade Level:</span>
                                <span class="detail-value"><?php echo !empty($student['Current_School_Grade']) ? htmlspecialchars($student['Current_School_Grade']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Account Created:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($created_at); ?></span>
                            </div>
                        </div>
                        
                        <!-- Attendance Summary -->
                        <div class="attendance-summary">
                            <h3>Attendance Summary</h3>
                            <div class="attendance-grid">
                                <div class="attendance-item">
                                    <span class="attendance-label">Hours Attended:</span>
                                    <span class="attendance-value"><?php echo htmlspecialchars($attendance['total_attended'] ?? '0.0'); ?></span>
                                </div>
                                <div class="attendance-item">
                                    <span class="attendance-label">Replacement Hours:</span>
                                    <span class="attendance-value"><?php echo htmlspecialchars($attendance['total_replacement'] ?? '0.0'); ?></span>
                                </div>
                                <div class="attendance-item">
                                    <span class="attendance-label">Hours Remaining:</span>
                                    <span class="attendance-value"><?php echo htmlspecialchars($attendance['total_remaining'] ?? '0.0'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>