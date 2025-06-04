<?php
include '../setting.php';
session_start();

// Protect route
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$message = isset($_GET['message']) ? $_GET['message'] : null; // Retrieve message from URL query parameter
$error = isset($_GET['error']) ? $_GET['error'] : null; // Retrieve error from URL query parameter
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Attendance</title>
    <link rel="stylesheet" href="/Styles/common.css" />
    <link rel="stylesheet" href="/Styles/attendance.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll(".attendence tbody tr");
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(filter) ? "" : "none";
            });
        }
    </script>
</head>
<body>
<div class="dashboard-container">
    <?php include("../includes/Aside_Nav_Student.php"); ?>
    <main class="main-content">
        <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

        <h2 style="padding: 1rem 2rem;">My Attendance</h2>
        <div style="padding: 0 2rem;">
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search attendance..." />
        </div>

        <div class = "table-container">
            <?php include '../../sql/attendance_data.php'; ?>
        </div>
    </main>
</div>

<script type="module" src="/Scripts/common.js"></script>
</body>
</html>
