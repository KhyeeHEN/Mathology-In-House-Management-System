<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $amount = $_POST['payment_amount'];
    $method = $_POST['payment_method'];
    $mode = $_POST['payment_mode'];
    $deposit_status = $_POST['deposit_status'];
    $status = $_POST['payment_status'];
    $remove_invoice = isset($_POST['remove_invoice']) ? true : false;

    // Get current invoice path
    $stmt = $conn->prepare("SELECT invoice_path FROM payment WHERE payment_id = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $stmt->bind_result($existing_invoice);
    $stmt->fetch();
    $stmt->close();

    $invoice_path = $existing_invoice;

    // Handle invoice removal
    if ($remove_invoice && $existing_invoice && file_exists("../../" . $existing_invoice)) {
        unlink("../../" . $existing_invoice);  // Delete file
        $invoice_path = null;
    }

    // Handle invoice upload
    if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Pages/invoice/';
        $fileName = 'manual_invoice_' . time() . '_' . basename($_FILES['invoice_file']['name']);
        $targetPath = $uploadDir . $fileName;

        // Ensure upload folder exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['invoice_file']['tmp_name'], $targetPath)) {
            // Delete old file if exists
            if ($existing_invoice && file_exists("../../" . $existing_invoice)) {
                unlink("../../" . $existing_invoice);
            }
            $invoice_path = 'Pages/invoice/' . $fileName;
        }
    }

    // Update payment
    $stmt = $conn->prepare("UPDATE payment
        SET payment_amount = ?, payment_method = ?, payment_mode = ?, deposit_status = ?, payment_status = ?, invoice_path = ?
        WHERE payment_id = ?");
    $stmt->bind_param("dsssssi", $amount, $method, $mode, $deposit_status, $status, $invoice_path, $payment_id);

    if ($stmt->execute()) {
        header("Location: ../Pages/admin/payment.php?message=Payment updated successfully");
    } else {
        header("Location: ../Pages/admin/edit_payment.php?id=$payment_id&error=Update failed");
    }

    $stmt->close();
    $conn->close();
}
?>
