<?php
include '../setting.php';

// Messages
$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'timetable_datetime';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Attendance</title>
    <link rel="stylesheet" href="../../Styles/common.css" />
    <link rel="stylesheet" href="../../Styles/attendance.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            
        </main>
    </div>

    <script src="../../Scripts/attendance.js?v=<?php echo time(); ?>"></script>



</body>

</html>
