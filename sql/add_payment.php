<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch all students
$students = $conn->query("SELECT student_id, CONCAT(First_Name, ' ', Last_Name) AS name FROM students ORDER BY First_Name ASC");

$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Payment Record</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/payment.css">
    <style>
        form {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"],
        select {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btns {
            text-align: center;
        }
        button, a.button {
            background: #4f46e5;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 5px;
        }
        button:hover, a.button:hover {
            background: #3730a3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include("../includes/Aside_Nav.php"); ?>
        <main class="main-content">
            <?php include("../includes/Top_Nav_Bar_Admin.php"); ?>

            <h2 style="text-align:center;">Add New Payment</h2>

            <?php if ($message): ?>
                <p style="color:green; text-align:center;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p style="color:red; text-align:center;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="../../sql/process_admin_add_payment.php">
                <label for="student_id">Student:</label>
                <select name="student_id" id="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php while($row = $students->fetch_assoc()): ?>
                        <option value="<?php echo $row['student_id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="payment_mode">Payment Mode:</label>
                <select name="payment_mode" id="payment_mode" required>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="semi_annually">Semi-Annually</option>
                </select>

                <label for="payment_method">Payment Method:</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="ewallet">E-Wallet</option>
                </select>

                <label for="payment_amount">Amount (RM):</label>
                <input type="number" name="payment_amount" id="payment_amount" step="0.01" required>

                <label for="deposit_status">Deposit Paid:</label>
                <select name="deposit_status" id="deposit_status" required>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>

                <label for="payment_status">Payment Status:</label>
                <select name="payment_status" id="payment_status" required>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending</option>
                    <option value="unpaid">Unpaid</option>
                </select>

                <div class="btns">
                    <button type="submit">Save Payment</button>
                    <a href="payment.php" class="button">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
