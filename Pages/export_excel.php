<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'setting.php';

if (isset($_GET['download_excel']) && $_GET['download_excel'] == '1' && isset($_GET['date'])) {
    $selectedDate = $_GET['date'];

    // Validate date format (example format: YYYY-MM-DD)
    if (!DateTime::createFromFormat('Y-m-d', $selectedDate)) {
        die('Invalid date format.');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header row
    $sheet->setCellValue('A1', 'Record ID');
    $sheet->setCellValue('B1', 'Student ID');
    $sheet->setCellValue('C1', 'Instructor ID');
    $sheet->setCellValue('D1', 'Scheduled Time');
    $sheet->setCellValue('E1', 'Attendance Time');
    $sheet->setCellValue('F1', 'Hours Attended');
    $sheet->setCellValue('G1', 'Replacement Hours');
    $sheet->setCellValue('H1', 'Hours Remaining');
    $sheet->setCellValue('I1', 'Status');
    $sheet->setCellValue('J1', 'Course');

    // SQL query with date filter
    $sql = "SELECT record_id, student_id, instructor_id, timetable_datetime, attendance_datetime,
            hours_attended, hours_replacement, hours_remaining, status, course
            FROM attendance_records
            WHERE DATE(attendance_datetime) = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedDate);
    $stmt->execute();
    $result = $stmt->get_result();

    // If no records are found
    if ($result->num_rows == 0) {
        die('No records found for this date.');
    }

    // Data rows
    $rowNum = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowNum, $row['record_id']);
        $sheet->setCellValue('B' . $rowNum, $row['student_id']);
        $sheet->setCellValue('C' . $rowNum, $row['instructor_id'] ?? '-');
        $sheet->setCellValue('D' . $rowNum, $row['timetable_datetime']);
        $sheet->setCellValue('E' . $rowNum, $row['attendance_datetime'] ?? '-');
        $sheet->setCellValue('F' . $rowNum, $row['hours_attended']);
        $sheet->setCellValue('G' . $rowNum, $row['hours_replacement']);
        $sheet->setCellValue('H' . $rowNum, $row['hours_remaining']);
        $sheet->setCellValue('I' . $rowNum, ucfirst($row['status']));
        $sheet->setCellValue('J' . $rowNum, $row['course']);
        $rowNum++;
    }

    // Output as file
    $filename = 'Attendance_Report_' . $selectedDate . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    ob_clean();
    flush();

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
