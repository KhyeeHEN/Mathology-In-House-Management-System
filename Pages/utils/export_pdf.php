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
    $pdf->Cell(27, 7, 'Student Name', 1, 0, 'L');
    $pdf->Cell(27, 7, 'Instructor Name', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Scheduled Time', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Attendance Time', 1, 0, 'L');
    $pdf->Cell(28, 7, 'Hours Attended', 1, 0, 'L');
    $pdf->Cell(35, 7, 'Replacement Hours', 1, 0, 'L');
    $pdf->Cell(32, 7, 'Hours Remaining', 1, 0, 'L');
    $pdf->Cell(20, 7, 'Status', 1, 0, 'L');
    $pdf->Cell(30, 7, 'Course', 1, 1, 'L');

    $sql = "SELECT
            ar.record_id,
            CONCAT(s.Last_Name, ' ', s.First_Name) AS student_name,
            CONCAT(i.Last_Name, ' ', i.First_Name) AS instructor_name,
            ar.timetable_datetime,
            ar.attendance_datetime,
            ar.hours_attended,
            ar.hours_replacement,
            ar.hours_remaining,
            ar.status,
            ar.course
        FROM attendance_records ar
        LEFT JOIN students s ON ar.student_id = s.student_id
        LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
        WHERE DATE(ar.attendance_datetime) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die('No records found for this date.');
    }

    $pdf->SetFont('helvetica', '', 9);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 6, $row['record_id'], 1, 0, 'L');
        $pdf->Cell(20, 6, $row['student_name'], 1, 0, 'L');
        $pdf->Cell(25, 6, $row['instructor_name'] ?? '-', 1, 0, 'L');
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
