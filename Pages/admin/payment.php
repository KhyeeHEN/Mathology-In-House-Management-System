<?php
include '../setting.php';

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
    <link rel="stylesheet" href="../../Styles/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../../Scripts/payment.js"></script>

    <style>

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
        <input type="text" id="searchInput" placeholder="Search payments..." onkeyup="searchTable()" style="margin-top: 20px; padding: 10px; width: 300px;">

            <table class="payment-table">
                <thead>
                    <tr>
                        <?php
                        $headers = [
                            'payment_id' => 'Payment ID',
                            'student_name' => 'Student',
                            'payment_method' => 'Payment Method',
                            'payment_mode' => 'Payment Mode',
                            'payment_amount' => 'Payment Amount',
                            'deposit_status' => 'Deposit',
                            'payment_status' => 'Status',
                            'payment_date' => 'Date',
                        ];

                        foreach ($headers as $column => $label) {
                            $arrow_class = $sort_column == $column ? strtolower($sort_direction) : '';
                            echo "<th class='$arrow_class' onclick=\"window.location='?sort=$column&direction=$new_sort_direction'\">$label<span class='sort-arrow'></span></th>";
                        }
                        ?>
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
    <script type="module" src="../../Scripts/common.js"></script>

</body>
</html>
