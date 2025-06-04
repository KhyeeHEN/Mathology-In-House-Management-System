<?php
require_once '../setting.php';
session_start();

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
echo "<!-- Debugging: Session user_id = $user_id, role = " . ($_SESSION['role'] ?? 'N/A') . " -->";
$user_sql = "SELECT instructor_id FROM users WHERE user_id = ? AND role = 'instructor'";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    die("Error preparing user query: " . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_info = $user_result->fetch_assoc();

if (!$user_info || !isset($user_info['instructor_id'])) {
    echo "<p>Error: Instructor ID not found for user ID $user_id. Session data: " . print_r($_SESSION, true) . "</p>";
    exit();
}

$instructor_id = $user_info['instructor_id'];
echo "<!-- Debugging: Retrieved instructor_id = $instructor_id -->";

// Fetch instructor details (use lowercase column aliases to avoid case sensitivity issues)
$sql = "SELECT First_Name AS first_name, Last_Name AS last_name, Highest_Education AS highest_education, 
        Training_Status AS training_status, Employment_Type AS employment_type, 
        Working_Days AS working_days, Worked_Days AS worked_days 
        FROM instructor WHERE instructor_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing instructor query: " . $conn->error);
}
$stmt->bind_param("i", $instructor_id);
if (!$stmt->execute()) {
    die("Error executing instructor query: " . $stmt->error);
}
$result = $stmt->get_result();
$instructor = $result->fetch_assoc();

if (!$instructor) {
    echo "<p>Error: Instructor details not found for ID $instructor_id. Query: $sql with instructor_id = $instructor_id</p>";
    exit();
}

echo "<!-- Debugging: Retrieved instructor data = " . print_r($instructor, true) . " -->";
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
                <h2>
                    <i class="fas fa-user-tie"></i> 
                    Instructor Details - <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                </h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <strong>Highest Education</strong>
                        <?php echo htmlspecialchars($instructor['highest_education'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Training Status</strong>
                        <?php echo htmlspecialchars($instructor['training_status'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Employment Type</strong>
                        <?php echo htmlspecialchars($instructor['employment_type'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Working Days</strong>
                        <?php echo htmlspecialchars($instructor['working_days'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Worked Days</strong>
                        <?php echo htmlspecialchars($instructor['worked_days'] ?? '0'); ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="/Scripts/common.js"></script>
</body>
</html>