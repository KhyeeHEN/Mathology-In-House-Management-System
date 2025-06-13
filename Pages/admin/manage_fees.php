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

// Fetch courses and fees
$sql = "
    SELECT c.course_id, c.course_name, c.level, f.fee_amount
    FROM courses c
    LEFT JOIN course_fees f ON c.course_id = f.course_id
    ORDER BY c.course_name
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Course Fees</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/payment.css?v=<?php echo time(); ?>">
    <!-- <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            padding: 30px;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        form {
            margin: 0;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        input[type="number"] {
            width: 100px;
            padding: 5px;
        }

        button {
            padding: 6px 12px;
            background: #1f2937;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #374151;
        }

        .message {
            color: green;
            text-align: center;
            font-weight: bold;
        }
    </style> -->
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
                    <form method="GET" action="payment.php">
                        <input type="text" name="search" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">

                        <label for="sort">Sort by:</label>
                        <select id="sort" name="sort">
                            <option value="course_name'" <?php if ($sort === 'course_name') echo 'selected'; ?>>Course Name</option>
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

                <!-- Payment Table -->
                <div class="table-container">
                    <table class = "payment-table">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Level</th>
                                <th>Current Fee (RM)</th>
                                <th>Update Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                                    <td><?= htmlspecialchars($row['level']) ?></td>
                                    <td><?= number_format($row['fee_amount'] ?? 0, 2) ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="course_id" value="<?= $row['course_id'] ?>">
                                            <input type="number" name="fee_amount" step="0.01" min="0" required
                                                value="<?= htmlspecialchars($row['fee_amount'] ?? '') ?>">
                                            <button type="submit">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
