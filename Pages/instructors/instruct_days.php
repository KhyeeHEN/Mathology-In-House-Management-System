<?php
require_once '../setting.php';
session_start();

// Ensure instructor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit();
}

// Verify database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch the actual instructor_id from the users table using the session's user_id
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT instructor_id FROM users WHERE user_id = ? AND role = 'instructor'";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    echo "Error preparing user query: " . $conn->error;
    exit();
}
$user_stmt->bind_param("i", $user_id);
if (!$user_stmt->execute()) {
    echo "Error executing user query: " . $user_stmt->error;
    exit();
}
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();

if (!$user_info || !isset($user_info['instructor_id'])) {
    echo "<p>Error: Instructor ID not found for user ID $user_id. Session data: " . print_r($_SESSION, true) . "</p>";
    exit();
}

$instructor_id = $user_info['instructor_id'];

// Fetch instructor details
$sql = "SELECT First_Name, Last_Name, Highest_Education, Training_Status, Employment_Type, Working_Days, Worked_Days 
        FROM instructor WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing instructor query: " . $conn->error;
    exit();
}
$stmt->bind_param("i", $instructor_id);
if (!$stmt->execute()) {
    echo "Error executing instructor query: " . $stmt->error;
    exit();
}
$result = $stmt->get_result();
$instructor = $result->fetch_assoc();

// Store the result in a variable to ensure it's not lost
$instructor_data = $instructor ? $instructor : [];

$stmt->close();
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
    <style>
        .instructor-details {
            margin: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .instructor-details h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .detail-item {
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .detail-item strong {
            display: block;
            color: #555;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav_Instructor.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Instructor.php"); ?>

            <div class="instructor-details">
                <?php if (!empty($instructor_data)): ?>
                    <h2>
                        <i class="fas fa-user-tie"></i> 
                        Instructor Details - <?= htmlspecialchars($instructor_data['First_Name'] . ' ' . $instructor_data['Last_Name']) ?>
                    </h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <strong>Highest Education</strong>
                            <?= htmlspecialchars($instructor_data['Highest_Education'] ?? 'N/A') ?>
                        </div>
                        <div class="detail-item">
                            <strong>Training Status</strong>
                            <?= htmlspecialchars($instructor_data['Training_Status'] ?? 'N/A') ?>
                        </div>
                        <div class="detail-item">
                            <strong>Employment Type</strong>
                            <?= htmlspecialchars($instructor_data['Employment_Type'] ?? 'N/A') ?>
                        </div>
                        <div class="detail-item">
                            <strong>Working Days</strong>
                            <?= htmlspecialchars($instructor_data['Working_Days'] ?? 'N/A') ?>
                        </div>
                        <div class="detail-item">
                            <strong>Worked Days</strong>
                            <?= htmlspecialchars($instructor_data['Worked_Days'] ?? 'N/A') ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; background-color: #f8f9fa; border-radius: 5px;">
                        <i class="fas fa-info-circle" style="font-size: 24px; color: #6c757d;"></i>
                        <p style="margin-top: 10px;">No instructor details found for ID <?= $instructor_id ?>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="/Scripts/common.js"></script>
</body>
</html>