<?php
session_start();
require_once '../setting.php';

$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'], $_POST['fee_amount'])) {
    $course_id = intval($_POST['course_id']);
    $fee_amount = floatval($_POST['fee_amount']);

    // Insert or update
    $stmt = $conn->prepare("
        INSERT INTO course_fees (course_id, fee_amount)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE fee_amount = VALUES(fee_amount)
    ");
    $stmt->bind_param("id", $course_id, $fee_amount);
    $stmt->execute();
    $message = "Fee updated successfully.";
}

// Sanitize and fetch search & sort values
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'course_name';
$sort_direction = isset($_GET['direction']) ? strtoupper($_GET['direction']) : 'ASC';

// Whitelist of sortable columns
$allowed_columns = ['course_name', 'level', 'fee_amount'];

// Validate sort column and direction
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'course_name';
}
if (!in_array($sort_direction, ['ASC', 'DESC'])) {
    $sort_direction = 'ASC';
}

// Build SQL query
$sql = "
    SELECT c.course_id, c.course_name, c.level, f.fee_amount, f.package_hours, f.time
    FROM courses c
    LEFT JOIN course_fees f ON c.course_id = f.course_id
";

// Add search filtering
if (!empty($search)) {
    $sql .= " WHERE c.course_name LIKE '%$search%' OR c.level LIKE '%$search%'";
}

// Add sorting
$sql .= " ORDER BY $sort_column $sort_direction";

// Execute
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Course Fees</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/payment.css?v=<?php echo time(); ?>">
</head>

<body>

    <body>
        <div class="dashboard-container">
            <?php require("../includes/Aside_Nav.php"); ?>

            <main class="main-content">
                <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

                <?php if ($message): ?>
                    <div style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <br>
                <br>
                <br>
                <br>
                <!-- Search + Sort -->
                <div class="search-bar">
                    <form method="GET" action="manage_fees.php">
                        <input type="text" name="search" placeholder="Search Course..." value="<?php echo htmlspecialchars($search); ?>">

                        <label for="sort">Sort by:</label>
                        <select id="sort" name="sort">
                            <option value="course_name" <?php if ($sort === 'course_name') echo 'selected'; ?>>Course Name</option>
                            <option value="level" <?php if ($sort === 'level') echo 'selected'; ?>>Level</option>
                            <option value="fee_amount" <?php if ($sort === 'fee_amount') echo 'selected'; ?>>Fee Amount</option>
                            <option value="package_hours" <?php if ($sort === 'package_hours') echo 'selected'; ?>>Package Hours</option>
                            <option value="time" <?php if ($sort === 'time') echo 'selected'; ?>>Time</option>
                        </select>

                        <select id="direction" name="direction">
                            <option value="ASC" <?php if ($direction === 'ASC') echo 'selected'; ?>>Ascending</option>
                            <option value="DESC" <?php if ($direction === 'DESC') echo 'selected'; ?>>Descending</option>
                        </select>

                        <button type="submit">Search/Sort</button>
                        <button type="button" onclick="window.location='manage_fees.php'">Reset</button>
                        <button type="button" onclick="window.location='payment.php'">Back</button>
                    </form>
                </div>

                <div class="table-container">
                    <?php include '../../sql/course_data.php'; ?>
                </div>
            </main>
        </div>

        <script src="/Scripts/payment.js?v=<?php echo time(); ?>"></script>
        <script type="module" src="/Scripts/common.js"></script>
        <script>
            function toggleDetails(id) {
                const row = document.getElementById(id);
                if (row.style.display === 'none') {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            }
        </script>


    </body>

</html>
