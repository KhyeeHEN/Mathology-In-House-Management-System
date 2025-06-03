<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (!isset($_GET['id'])) {
    die("Payment ID is required.");
}

$payment_id = $_GET['id'];

$sql = "
    SELECT p.*, CONCAT(s.Last_Name, ' ', s.First_Name) AS student_name
    FROM payment p
    LEFT JOIN students s ON p.student_id = s.student_id
    WHERE p.payment_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Payment not found.");
}

$payment = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Payment</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            margin: 0;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #1f2937;
        }

        form {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1f2937;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: box-shadow 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1f2937;
            box-shadow: 0 0 5px rgba(31, 41, 55, 0.5);
        }

        p {
            margin-bottom: 10px;
        }

        button {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(71, 82, 95);
        }

        a {
            margin-left: 15px;
            text-decoration: none;
            color: #1f2937;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>

<body>
    <div class="dashboard-container">

        <main class="main-content">
            <h2>Edit Payment - ID #<?php echo $payment['payment_id']; ?></h2>

            <form action="../sql/update_payment.php" method="POST">
                <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">

                <p><strong>Student:</strong> <?php echo htmlspecialchars($payment['student_name']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($payment['payment_date']); ?></p>

                <label>Amount (RM):
                    <input type="number" name="payment_amount" value="<?php echo htmlspecialchars($payment['payment_amount']); ?>" step="0.01" min="0" required>
                </label>

                <label>Payment Method:
                    <select name="payment_method" required>
                        <option value="credit_card" <?php if ($payment['payment_method'] === 'credit_card') echo 'selected'; ?>>Credit Card</option>
                        <option value="cash" <?php if ($payment['payment_method'] === 'cash') echo 'selected'; ?>>Cash</option>
                        <option value="bank_transfer" <?php if ($payment['payment_method'] === 'bank_transfer') echo 'selected'; ?>>Bank Transfer</option>
                    </select>
                </label><br><br>

                <label>Payment Mode:
                    <select name="payment_mode" required>
                        <option value="monthly" <?php if ($payment['payment_mode'] === 'monthly') echo 'selected'; ?>>Monthly</option>
                        <option value="quarterly" <?php if ($payment['payment_mode'] === 'quarterly') echo 'selected'; ?>>Quarterly</option>
                        <option value="semi_annually" <?php if ($payment['payment_mode'] === 'semi_annually') echo 'selected'; ?>>Semi-Annually</option>
                    </select>
                </label><br><br>


                <label>Deposit Status:
                    <select name="deposit_status" required>
                        <option value="yes" <?php if ($payment['deposit_status'] === 'yes') echo 'selected'; ?>>Yes</option>
                        <option value="no" <?php if ($payment['deposit_status'] === 'no') echo 'selected'; ?>>No</option>
                    </select>
                </label><br><br>

                <label>Payment Status:
                    <select name="payment_status" required>
                        <option value="paid" <?php if ($payment['payment_status'] === 'paid') echo 'selected'; ?>>Paid</option>
                        <option value="unpaid" <?php if ($payment['payment_status'] === 'unpaid') echo 'selected'; ?>>Unpaid</option>
                        <option value="pending" <?php if ($payment['payment_status'] === 'pending') echo 'selected'; ?>>Pending</option>
                    </select>
                </label><br><br>


                <button type="submit">Update Payment</button>
                <a href="../Pages/admin/payment.php">Cancel</a>
            </form>
        </main>
    </div>
</body>

</html>
