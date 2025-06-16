<?php
require_once('../../Pages/setting.php');

$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

$stmt = $conn->prepare("SELECT invoice_path FROM payment WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$stmt->bind_result($invoice_path);
$stmt->fetch();
$stmt->close();

$download_link = !empty($invoice_path) && file_exists("../../$invoice_path") ? "../../$invoice_path" : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice Generated</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f3f4f6;
        }

        .box {
            background: white;
            padding: 30px;
            display: inline-block;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #4f46e5;
        }

        a.button {
            display: inline-block;
            margin: 15px;
            padding: 10px 20px;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        a.button:hover {
            background-color: #3730a3;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ Payment Recorded</h1>
        <p>Your invoice has been generated.</p>

        <?php if ($download_link): ?>
            <a href="<?php echo htmlspecialchars($download_link); ?>" target="_blank" class="button">📄 Download Invoice</a>
        <?php else: ?>
            <p><strong>⚠️ Invoice file not found.</strong></p>
        <?php endif; ?>

        <br>
        <a href="../client/student_payment.php" class="button">← Back to Payment Page</a>
    </div>
</body>
</html>
