<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: /Pages/login.php');
    exit;
}

// Get all students
$students = $conn->query("SELECT student_id, CONCAT(First_Name, ' ', Last_Name) AS full_name FROM students ORDER BY First_Name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Payment</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 40px;
        }

        form {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
            color: #1f2937;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #1f2937;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: rgb(71, 82, 95);
        }

        a {
            margin-left: 10px;
            text-decoration: none;
            font-weight: bold;
            color: #1f2937;
        }

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>
<body>
    <form action="../../sql/process_admin_add_payment.php" method="POST"  enctype="multipart/form-data">
        <h2>Add New Payment Record</h2>

        <label for="student_id">Student Name:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
            <?php while ($row = $students->fetch_assoc()): ?>
                <option value="<?php echo $row['student_id']; ?>">
                    <?php echo htmlspecialchars($row['full_name']); ?>
                </option>
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
        <input type="number" name="payment_amount" step="0.01" min="0" required>

        <label for="deposit_status">Deposit Status:</label>
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

        <label for="invoice_file">Upload Invoice (PDF):</label>
        <input type="file" name="invoice_file" id="invoice_file" accept=".pdf">

        <button type="submit">Add Payment</button>
        <a href="payment.php">Cancel</a>
    </form>
</body>
</html>
