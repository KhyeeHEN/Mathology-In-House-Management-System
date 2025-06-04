<?php
require_once '../setting.php';
session_start();

// Ensure instructor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}


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
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav_Instructor.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Instructor.php"); ?>

            <div>
                
            </div>
        </main>
    </div>
    
    <script src="/Scripts/common.js"></script>

</body>
</html>