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

echo "<!-- Debugging: Database connection successful -->";

// Fetch the actual instructor_id from the users table using the session's user_id
$user_id = $_SESSION['user_id'];
echo "<!-- Debugging: Session user_id = $user_id, role = " . ($_SESSION['role'] ?? 'N/A') . " -->";
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
echo "<!-- Debugging: Retrieved instructor_id = $instructor_id -->";

// Verify the instructor table exists
$check_table = $conn->query("SHOW TABLES LIKE 'instructor'");
if ($check_table->num_rows == 0) {
    echo "<p>Error: Table 'instructor' does not exist in the database.</p>";
    exit();
}
echo "<!-- Debugging: Table 'instructor' exists -->";

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
echo "<!-- Debugging: Number of rows returned = " . $result->num_rows . " -->";
$instructor = $result->fetch_assoc();

echo "<!-- Debugging: Retrieved instructor data = " . print_r($instructor, true) . " -->";

if (!$instructor) {
    $check_id = $conn->query("SELECT instructor_id FROM instructor WHERE instructor_id = $instructor_id");
    echo "<!-- Debugging: Instructor ID $instructor_id exists = " . ($check_id->num_rows > 0 ? 'Yes' : 'No') . " -->";
}

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
        .instructor-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .instructor-table th, .instructor-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .instructor-table th {
            background-color: #4CAF50;
            color: white;
        }
        .instructor-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .instructor-table tr:hover {
            background-color: #e6f7e6;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav_Instructor.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Instructor.php"); ?>

            <div class="instructor-details">
                <?php if ($instructor): ?>
                    <h2>
                        <i class="fas fa-user-tie"></i> 
                        Instructor Details - <?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?>
                    </h2>
                    <table class="instructor-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Highest Education</td>
                                <td><?= htmlspecialchars($instructor['Highest_Education'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Training Status</td>
                                <td><?= htmlspecialchars($instructor['Training_Status'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Employment Type</td>
                                <td><?= htmlspecialchars($instructor['Employment_Type'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Working Days</td>
                                <td><?= htmlspecialchars($instructor['Working_Days'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td>Worked Days</td>
                                <td><?= htmlspecialchars($instructor['Worked_Days'] ?? '0') ?></td>
                            </tr>
                        </tbody>
                    </table>
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