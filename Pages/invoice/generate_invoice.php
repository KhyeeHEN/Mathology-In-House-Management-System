<?php
ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');
include '../setting.php';

if (isset($_GET['generate_invoice']) && isset($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);

    // Step 1: Fetch payment and student info
    $sql = "SELECT
            p.payment_id,
            p.student_id,
            p.payment_date,
            p.payment_amount,
            p.payment_mode,
            CONCAT(s.First_Name, ' ', s.Last_Name) AS student_name,
            IFNULL(s.address, '-') AS student_address,
            CONCAT(IFNULL(pc.First_Name, ''), ' ', IFNULL(pc.Last_Name, '')) AS guardian_name,
            c.course_name,
            c.level,
            sc.program_start,
            sc.program_end,
            sc.hours_per_week,
            cf.fee_amount,
            cf.package_hours,
            cf.time
        FROM payment p
        JOIN students s ON p.student_id = s.student_id
        LEFT JOIN primary_contact_number pc ON s.student_id = pc.student_id
        LEFT JOIN student_courses sc ON s.student_id = sc.student_id
        LEFT JOIN courses c ON sc.course_id = c.course_id
        LEFT JOIN course_fees cf ON c.course_id = cf.course_id AND cf.time = p.payment_mode
        WHERE p.payment_id = ?
        LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('Invoice data not found.');
    }

    $data = $result->fetch_assoc();
    $student_id = $data['student_id'];

    // Step 2: Check if first payment
    $check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
    $check->bind_param("i", $student_id);
    $check->execute();
    $check->bind_result($total_payments);
    $check->fetch();
    $check->close();

    $is_first_payment = $total_payments === 1;
    $one_time_fee = 0;
    $one_time_rows = [];

    if ($is_first_payment) {
        $res = $conn->query("SELECT name, amount FROM one_time_fees");
        while ($row = $res->fetch_assoc()) {
            $one_time_fee += $row['amount'];
            $one_time_rows[] = $row;
        }
    }

    // Step 3: Set values for invoice
    $date = date('d/m/Y (l)', strtotime($data['payment_date']));
    $time = date('h:i A', strtotime($data['payment_date']));
    $invoiceNo = 'INV-' . date('Ymd') . '-' . $data['payment_id'];
    $fileName = 'Invoice_' . $invoiceNo . '.pdf';
    $filePath = __DIR__ . '/' . $fileName;
    $relPath = 'Pages/invoice/' . $fileName;

    // Step 4: TCPDF setup
    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetTitle('Official Invoice');
    $pdf->setPrintHeader(false);
    $pdf->AddPage();

    // Header section
    $pdf->Image('../../Pages/invoice/LogoTransparent.png', 10, 10, 40);
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Official Invoice', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(100, 6, 'Invoice No: ' . $invoiceNo, 0, 0);
    $pdf->Cell(0, 6, 'Date: ' . $date, 0, 1);
    $pdf->Cell(100, 6, 'Student: ' . $data['student_name'], 0, 0);
    $pdf->Cell(0, 6, 'Time: ' . $time, 0, 1);
    $pdf->Cell(0, 6, 'Guardian: ' . trim($data['guardian_name']), 0, 1);
    $pdf->MultiCell(0, 6, $data['student_address'], 0, 'L');
    $pdf->Ln(3);

    // Course Fee Section
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 7, 'Particulars:', 0, 1);

    $details = "Course: {$data['course_name']} ({$data['level']})\n" .
               "Duration: {$data['program_start']} - {$data['program_end']}\n" .
               "Hours per Week: {$data['hours_per_week']}\n" .
               "Package: {$data['package_hours']} Hours - {$data['time']}";

    $fee = number_format((float)$data['fee_amount'], 2);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(140, 24, $details, 1, 'L', false, 0);
    $pdf->Cell(0, 24, 'RM ' . $fee, 1, 1, 'R');

    // One-time fees (if any)
    if ($is_first_payment && !empty($one_time_rows)) {
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, '2. One-Time Fees:', 0, 1);

        $pdf->SetFont('helvetica', '', 10);
        $i = 1;
        foreach ($one_time_rows as $fee_row) {
            $pdf->Cell(140, 6, "2.$i. {$fee_row['name']}", 1);
            $pdf->Cell(0, 6, 'RM ' . number_format($fee_row['amount'], 2), 1, 1, 'R');
            $i++;
        }
    }

    // Total
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(140, 6, 'Total Amount:', 1);
    $grand_total = $data['payment_amount'] + ($is_first_payment ? $one_time_fee : 0);
    $pdf->Cell(0, 6, 'RM ' . number_format($grand_total, 2), 1, 1, 'R');

    // Notes
    $pdf->Ln(4);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(
        0,
        5,
        "1. All registration, diagnostic test and program fees paid are non-refundable.\n" .
        "2. Cancellation requires 1 month advance notice.\n" .
        "3. No signature required. This is a computer-generated document.",
        0,
        'L'
    );

    $pdf->Ln(6);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->MultiCell(0, 5, "Mathology Kuchai Lama (LLP0023441)\n2-4, Jalan 3/114, Kuchai Business Centre, 58200 KL", 0, 'C');

    // Step 5: Save file
    ob_end_clean();
    $pdf->Output($filePath, 'F');

    // Step 6: Update invoice_path in DB
    $update = $conn->prepare("UPDATE payment SET invoice_path = ? WHERE payment_id = ?");
    $update->bind_param("si", $relPath, $payment_id);
    $update->execute();
    $update->close();

    // Step 7: Redirect
    header("Location: invoice_success.php?payment_id=$payment_id");
    exit;
}
