<?php
ob_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');
include '../setting.php';

if (isset($_GET['generate_invoice']) && isset($_GET['payment_id'])) {
    $payment_id = intval($_GET['payment_id']);

    $sql = "SELECT
    p.payment_id,
    p.payment_date,
    p.payment_amount,
    CONCAT(s.First_Name, ' ', s.Last_Name) AS student_name,
    s.address AS student_address,
    CONCAT(pc.First_Name, ' ', pc.Last_Name) AS guardian_name,
    c.course_name,
    c.level,
    sc.program_start,
    sc.program_end,
    sc.hours_per_week
FROM payment p
JOIN students s ON p.student_id = s.student_id
LEFT JOIN primary_contact_number pc ON s.student_id = pc.student_id
LEFT JOIN student_courses sc ON s.student_id = sc.student_id
LEFT JOIN courses c ON sc.course_id = c.course_id
LEFT JOIN course_fees cf ON cf.course_id = c.course_id
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
    // Check if this is the first payment for the student
    $student_id_check = $conn->prepare("SELECT student_id FROM payment WHERE payment_id = ?");
    $student_id_check->bind_param("i", $payment_id);
    $student_id_check->execute();
    $student_id_check->bind_result($student_id);
    $student_id_check->fetch();
    $student_id_check->close();

    $count_check = $conn->prepare("SELECT COUNT(*) FROM payment WHERE student_id = ?");
    $count_check->bind_param("i", $student_id);
    $count_check->execute();
    $count_check->bind_result($total_payments);
    $count_check->fetch();
    $count_check->close();

    $is_first_payment = $total_payments === 1;
    $one_time_fee = 0;
    $one_time_details = '';

    if ($is_first_payment) {
        $res = $conn->query("SELECT name, amount FROM one_time_fees");
        $index = 1;
        while ($fee = $res->fetch_assoc()) {
            $one_time_fee += $fee['amount'];
            $one_time_details .= "   2.$index. {$fee['name']}: RM " . number_format($fee['amount'], 2) . "\n";
            $index++;
        }
    }
    $date = date('d/m/Y (l)', strtotime($data['payment_date']));
    $time = date('h:i A', strtotime($data['payment_date']));
    $amount = number_format($data['payment_amount'], 2);
    $invoiceNo = 'INV-' . date('Ymd') . '-' . $data['payment_id'];
    $fileName = 'Invoice_' . $invoiceNo . '.pdf';

    $serverDir = __DIR__ . '/'; // Absolute server path to /Pages/invoice/
    $filePath = $serverDir . $fileName;
    $relPath = 'Pages/invoice/' . $fileName; // Relative for download

    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetTitle('Official Invoice');
    $pdf->setPrintHeader(false);
    $pdf->AddPage();

    // Header
    $pdf->Image('../../Pages/invoice/LogoTransparent.png', 10, 10, 40);
    $pdf->Ln(10);  // move cursor down after logo
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Official Invoice', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(100, 6, 'Invoice No: ' . $invoiceNo, 0, 0);
    $pdf->Cell(0, 6, 'Date: ' . $date, 0, 1);
    $pdf->Cell(100, 6, 'Student: ' . $data['student_name'], 0, 0);
    $pdf->Cell(0, 6, 'Time: ' . $time, 0, 1);
    $pdf->Cell(0, 6, 'Guardian: ' . $data['guardian_name'], 0, 1);
    $pdf->MultiCell(0, 6, $data['student_address'], 0, 'L');

    $pdf->Ln(3);

    // Course Info
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 7, 'Particulars:', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(
        0,
        6,
        "1. {$data['course_name']} ({$data['level']})\n" .
            "   Programme Duration: {$data['program_start']} - {$data['program_end']}\n" .
            "   Hours Per Week: {$data['hours_per_week']}\n" .
            "   Package Hours: {$data['package_hours']}\n" .
            "   Package Time: {$data['time']}\n" .
            "   Fee Amount: RM " . number_format($data['fee_amount'], 2) . "\n" .
            ($is_first_payment ? "   \n2. One-Time Fees:\n" . $one_time_details : ''),
        0,
        'L'
    );
    $pdf->Ln(2);

    // Total
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(140, 6, 'Total Amount:', 1);
    $grand_total = $data['payment_amount'] + $one_time_fee;
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
    // Footer
    $pdf->Ln(6);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->MultiCell(0, 5, "Mathology Kuchai Lama (LLP0023441)\n2-4, Jalan 3/114, Kuchai Business Centre, 58200 KL", 0, 'C');

    // Save file
    ob_end_clean();
    $pdf->Output($filePath, 'F');


    $relPath = '../../Pages/invoice/' . $fileName;
    $update = $conn->prepare("UPDATE payment SET invoice_path = ? WHERE payment_id = ?");
    $update->bind_param("si", $relPath, $payment_id);
    $update->execute();
    $update->close();


    header("Location: invoice_success.php?payment_id=$payment_id");
    exit;
}
