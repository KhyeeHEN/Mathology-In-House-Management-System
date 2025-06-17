<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: /Pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $method = $_POST['payment_method'];
    $mode = $_POST['payment_mode'];
    $amount = $_POST['payment_amount'];
    $deposit_status = $_POST['deposit_status'];
    $status = $_POST['payment_status'];

    $invoice_path = null;

    // Handle invoice file upload
    if (isset($_FILES['invoice_file']) && $_FILES['invoice_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Pages/invoice/';
        $fileName = 'manual_invoice_' . time() . '_' . basename($_FILES['invoice_file']['name']);
        $targetPath = $uploadDir . $fileName;

        // Ensure the directory exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['invoice_file']['tmp_name'], $targetPath)) {
            // Save relative path to DB
            $invoice_path = 'Pages/invoice/' . $fileName;
        } else {
            header("Location: ../Pages/admin/add_payment.php?error=Failed+to+upload+invoice");
            exit;
        }
    }

    // Insert payment record
    $stmt = $conn->prepare("
        INSERT INTO payment (
            student_id, payment_method, payment_mode, payment_amount, deposit_status, payment_status, invoice_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issdsss", $student_id, $method, $mode, $amount, $deposit_status, $status, $invoice_path);

    if ($stmt->execute()) {
        header("Location: ../Pages/admin/payment.php?message=Payment+and+invoice+added+successfully");
    } else {
        header("Location: ../Pages/admin/add_payment.php?error=Failed+to+save+payment");
    }

    $stmt->close();
    $conn->close();
}
