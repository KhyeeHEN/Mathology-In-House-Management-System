<?php
ob_start();

// require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');
include '../setting.php';

if (isset($_GET['download_pdf']) && $_GET['download_pdf'] == '1' && isset($_GET['date'])) {
    $selectedDate = $_GET['date'];

    if (!DateTime::createFromFormat('Y-m-d', $selectedDate)) {
        die('Invalid date format.');
    }

    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->SetTitle('Attendance Report for ' . $selectedDate);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Attendance Report for ' . $selectedDate, 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(20, 7, 'Record ID', 1, 0, 'L');
    $pdf->Cell(20, 7, 'Student ID', 1, 0, 'L');
    $pdf->Cell(25, 7, 'Instructor ID', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Scheduled Time', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Attendance Time', 1, 0, 'L');
    $pdf->Cell(28, 7, 'Hours Attended', 1, 0, 'L');
    $pdf->Cell(35, 7, 'Replacement Hours', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Hours Remaining', 1, 0, 'L');
    $pdf->Cell(20, 7, 'Status', 1, 0, 'L');
    $pdf->Cell(30, 7, 'Course', 1, 1, 'L');

    $stmt = $conn->prepare("SELECT record_id, student_id, instructor_id, timetable_datetime, attendance_datetime, hours_attended, hours_replacement, hours_remaining, status, course FROM attendance_records WHERE DATE(attendance_datetime) = ?");
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die('No records found for this date.');
    }

    $pdf->SetFont('helvetica', '', 9);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 6, $row['record_id'], 1, 0, 'L');
        $pdf->Cell(20, 6, $row['student_id'], 1, 0, 'L');
        $pdf->Cell(25, 6, $row['instructor_id'] ?? '-', 1, 0, 'L');
        $pdf->Cell(32, 6, $row['timetable_datetime'], 1, 0, 'L');
        $pdf->Cell(32, 6, $row['attendance_datetime'] ?? '-', 1, 0, 'L');
        $pdf->Cell(28, 6, $row['hours_attended'], 1, 0, 'L');
        $pdf->Cell(35, 6, $row['hours_replacement'], 1, 0, 'L');
        $pdf->Cell(32, 6, $row['hours_remaining'], 1, 0, 'L');
        $pdf->Cell(20, 6, ucfirst($row['status']), 1, 0, 'L');
        $pdf->Cell(30, 6, $row['course'], 1, 1, 'L');
    }

    ob_end_clean(); // Clear any buffered output
    $pdf->Output('Attendance_Report_' . $selectedDate . '.pdf', 'D');
    exit;
}
?>
