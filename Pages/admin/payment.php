<?php
include '../setting.php'; // Include the database connection

// Get the selected column and sorting direction from GET
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date'; // Default to payment_date
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC'; // Default to descending

// Toggle sort direction for the next click
$new_sort_direction = $sort_direction == 'ASC' ? 'DESC' : 'ASC';

$sql = "SELECT
            p.payment_id,
            CONCAT(s.First_Name, ' ', s.Last_Name) AS student_name,
            p.payment_method,
            p.payment_mode,
            p.payment_amount,
            p.deposit_status,
            p.payment_status,
            p.payment_date
        FROM payment p
        LEFT JOIN students s ON p.student_id = s.student_id
        ORDER BY $sort_column $sort_direction";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="../../styles/common.css">
    <link rel="stylesheet" href="../../styles/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../../scripts/payment.js"></script>
    <style>
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .payment-table th, .payment-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .payment-table th {
            background-color: #f4f4f4;
            cursor: pointer;
        }

        .payment-table td {
            vertical-align: middle;
        }

        th {
            position: relative;
            cursor: pointer;
        }

        th .sort-arrow {
            margin-left: 5px;
            font-size: 12px;
        }

        .asc::after {
            content: '\2191'; /* Up Arrow */
        }

        .desc::after {
            content: '\2193'; /* Down Arrow */
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
           <?php require("../includes/Top_Nav_Bar.php"); ?>
        <h1>All Payments</h1>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th class="<?php echo $sort_column == 'payment_id' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_id&direction=<?php echo $new_sort_direction; ?>'">
                            Payment ID
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'student_name' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=student_name&direction=<?php echo $new_sort_direction; ?>'">
                            Student Name
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'payment_method' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_method&direction=<?php echo $new_sort_direction; ?>'">
                            Payment Method
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'payment_mode' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_mode&direction=<?php echo $new_sort_direction; ?>'">
                            Payment Mode
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'payment_amount' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_amount&direction=<?php echo $new_sort_direction; ?>'">
                            Amount (RM)
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'deposit_status' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=deposit_status&direction=<?php echo $new_sort_direction; ?>'">
                            Deposit
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'payment_status' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_status&direction=<?php echo $new_sort_direction; ?>'">
                            Status
                            <span class="sort-arrow"></span>
                        </th>
                        <th class="<?php echo $sort_column == 'payment_date' ? strtolower($sort_direction) : ''; ?>" onclick="window.location='?sort=payment_date&direction=<?php echo $new_sort_direction; ?>'">
                            Date
                            <span class="sort-arrow"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['payment_id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['student_name'] ?? '-') . "</td>";
                            echo "<td>" . ucfirst($row['payment_method']) . "</td>";
                            echo "<td>" . ucfirst($row['payment_mode']) . "</td>";
                            echo "<td>RM " . number_format($row['payment_amount'], 2) . "</td>";
                            echo "<td>" . ucfirst($row['deposit_status']) . "</td>";
                            echo "<td>" . ucfirst($row['payment_status']) . "</td>";
                            echo "<td>" . date("Y-m-d H:i", strtotime($row['payment_date'])) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;'>No payment records found.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>


        </main>
    </div>
    <script type="module" src="../../scripts/common.js"></script>

</body>
</html>
