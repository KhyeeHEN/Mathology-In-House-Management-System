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

// Fetch instructor details (ensure exact column names from the database schema)
$sql = "SELECT First_Name, Last_Name, Highest_Education, Training_Status, Employment_Type, Working_Days, Worked_Days 
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
    // Debug: Check if the instructor_id exists in the table
    $check_sql = "SELECT instructor_id FROM instructor WHERE instructor_id = $instructor_id";
    $check_result = $conn->query($check_sql);
    echo "<p>Debug: Number of rows for instructor_id $instructor_id = " . $check_result->num_rows . "</p>";
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
                    Instructor Details - 
                    <?php 
                    // Check if instructor data exists to avoid undefined key errors
                    if (isset($instructor['First_Name']) && isset($instructor['Last_Name'])) {
                        echo htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']);
                    } else {
                        echo 'Unknown Instructor';
                    }
                    ?>
                </h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <strong>Highest Education</strong>
                        <?php echo htmlspecialchars($instructor['Highest_Education'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Training Status</strong>
                        <?php echo htmlspecialchars($instructor['Training_Status'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Employment Type</strong>
                        <?php echo htmlspecialchars($instructor['Employment_Type'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Working Days</strong>
                        <?php echo htmlspecialchars($instructor['Working_Days'] ?? 'N/A'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Worked Days</strong>
                        <?php echo htmlspecialchars($instructor['Worked_Days'] ?? '0'); ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="/Scripts/common.js"></script>
</body>
</html>