<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';


if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: /Pages/login.php');
    exit;
}
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
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <!-- Messages -->
            <?php if ($message): ?>
                <div style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <br>
            <br>


            <br><br>

            <!-- Search -->
            <div class="search-bar">
                <form method="GET" action="attendance.php">
                    <input type="text" name="search" placeholder="Search attendance..." value="<?php echo htmlspecialchars($search); ?>">

                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort">
                        <option value="student_id" <?php if ($sort === 'student_id') echo 'selected'; ?>>Student ID</option>
                        <option value="student_name" <?php if ($sort === 'student_name') echo 'selected'; ?>>Student Name</option>
                        <option value="attendance_datetime" <?php if ($sort === 'attendance_datetime') echo 'selected'; ?>>Attendance</option>
                        <option value="hours_attended" <?php if ($sort === 'hours_attended') echo 'selected'; ?>>Hours Attended</option>
                        <option value="course" <?php if ($sort === 'course') echo 'selected'; ?>>Course</option>
                    </select>

                    <select id="direction" name="direction">
                        <option value="ASC" <?php if ($direction === 'ASC') echo 'selected'; ?>>Ascending</option>
                        <option value="DESC" <?php if ($direction === 'DESC') echo 'selected'; ?>>Descending</option>
                    </select>

                    <button type="submit"><i class="fas fa-search"></i></button>
                    <button type="button" id="reset-button" onclick="window.location='attendance.php'"><i class="fas fa-undo"></i></button>
                    <button type="button" onclick="window.location='../../sql/add_attendance.php'"><i class="fas fa-user-plus"></i></button>
                    <button type="button" onclick="window.location='daily_report.php'">
                        <i class="fas fa-chart-line" style="margin-right: 8px;"></i>
                    </button>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="table-container">
                <?php include '../../sql/attendance_data.php'; ?>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
    <script src="/Scripts/dashboardInstructors.js"></script>
    <script src="/Scripts/attendance.js?v=<?php echo time(); ?>"></script>
    <script>
        function toggleDetails(id) {
            const row = document.getElementById(id);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>



</body>

</html>
