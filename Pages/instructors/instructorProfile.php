<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Profile</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/instructorProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
    // Start session and check if user is logged in as instructor
    session_start();
    require_once("../includes/db_connection.php");
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
        header("Location: /login.php");
        exit();
    }
    
    // Fetch instructor data
    $user_id = $_SESSION['user_id'];
    
    // Get user account info
    $query = "SELECT u.*, i.* 
              FROM users u
              JOIN instructor i ON u.instructor_id = i.instructor_id
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();
    
    // Calculate age from DOB
    $age = '';
    if (!empty($instructor['DOB'])) {
        $dob = new DateTime($instructor['DOB']);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
    }
    
    // Format gender
    $gender = isset($instructor['Gender']) ? ($instructor['Gender'] == 1 ? 'Male' : 'Female') : 'Not specified';
    ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Instructor.php"); ?>

            <!-- Profile Content -->
            <div class="profile-container">
                <div class="profile-header">
                    <h1>Instructor Profile</h1>
                    <p>View your professional information and account details</p>
                </div>

                <div class="profile-content">
                    <div class="profile-image-section">
                        <div class="image-container">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($instructor['First_Name'] . '+' . $instructor['Last_Name']); ?>&background=4e73df&color=ffffff" alt="Profile" class="profile-image">
                        </div>
                    </div>

                    <div class="profile-details">
                        <h2 class="instructor-name"><?php echo htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']); ?></h2>
                        <p class="instructor-title">Mathematics Instructor</p>
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($instructor['email']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Gender:</span>
                                <span class="detail-value"><?php echo $gender; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Date of Birth:</span>
                                <span class="detail-value"><?php echo !empty($instructor['DOB']) ? htmlspecialchars(date('F j, Y', strtotime($instructor['DOB']))) . " ($age years)" : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Highest Education:</span>
                                <span class="detail-value"><?php echo !empty($instructor['Highest_Education']) ? htmlspecialchars($instructor['Highest_Education']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Training Status:</span>
                                <span class="detail-value"><?php echo !empty($instructor['Training_Status']) ? htmlspecialchars($instructor['Training_Status']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Account Created:</span>
                                <span class="detail-value"><?php echo htmlspecialchars(date('F j, Y', strtotime($instructor['created_at']))); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($instructor['Remark'])): ?>
                        <div class="remarks-section">
                            <h3>Remarks</h3>
                            <p><?php echo htmlspecialchars($instructor['Remark']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>