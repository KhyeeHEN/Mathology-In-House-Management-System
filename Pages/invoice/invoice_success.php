<?php
require_once('../setting.php');

$payment_id = isset($_GET['payment_id']) ? intval($_GET['payment_id']) : 0;

$stmt = $conn->prepare("SELECT invoice_path FROM payment WHERE payment_id = ?");
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$stmt->bind_result($invoice_path);
$stmt->fetch();
$stmt->close();

$invoice_path = str_replace('../../', '', $invoice_path); // make it relative to web root
$fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $invoice_path;

$download_link = null;
if (!empty($invoice_path) && file_exists($fullPath)) {
    $download_link = '/' . $invoice_path;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Generated</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <style>
        body {
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 60px;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4f46e5;
        }
        .button {
            display: inline-block;
            margin: 15px 10px;
            padding: 12px 25px;
            font-size: 16px;
            text-decoration: none;
            color: white;
            background-color: #4f46e5;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #3730a3;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>✅ Invoice Generated</h1>
        <p>Your payment has been successfully recorded.</p>

        <?php if ($download_link): ?>
            <a href="<?php echo $download_link; ?>" target="_blank" class="button">📄 Download Invoice</a>
        <?php else: ?>
            <p style="color: red;">⚠️ Invoice file not found.</p>
        <?php endif; ?>

        <a href="../client/paymentclient.php" class="button">← Back to Payment Page</a>
    </div>
</body>
</html>
