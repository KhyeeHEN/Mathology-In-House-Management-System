```php
<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

// Include required files
try {
    require_once('../../vendor/tecnickcom/tcpdf/tcpdf.php');
} catch (Exception $e) {
    die('Error loading TCPDF: ' . $e->getMessage());
}

try {
    include '../setting.php';
} catch (Exception $e) {
    die('Error including setting.php: ' . $e->getMessage());
}

// Check if the request is valid
if (!isset($_GET['download_pdf']) || $_GET['download_pdf'] != '1' || !isset($_GET['student_id'])) {
    die('Invalid request: Missing required parameters.');
}

$student_id = (int)$_GET['student_id'];

// Fetch student information
$student_sql = "SELECT First_Name, Last_Name FROM students WHERE student_id = ?";
$student_stmt = $conn->prepare($student_sql);
if (!$student_stmt) {
    die("Error preparing student query: " . $conn->error);
}
$student_stmt->bind_param("i", $student_id);
if (!$student_stmt->execute()) {
    die("Error executing student query: " . $student_stmt->error);
}
$student_result = $student_stmt->get_result();
$student_info = $student_result->fetch_assoc();
$student_stmt->close();

if (!$student_info) {
    die('Student not found.');
}
$student_name = htmlspecialchars($student_info['First_Name'] . ' ' . $student_info['Last_Name']);

// Fetch timetable data
$sql = "SELECT 
            st.id, 
            st.course,
            st.day,
            TIME_FORMAT(st.start_time, '%h:%i %p') as start_time,
            TIME_FORMAT(st.end_time, '%h:%i %p') as end_time,
            DATE_FORMAT(st.approved_at, '%M %d, %Y') as approved_at
        FROM student_timetable st
        JOIN student_courses sc ON st.student_course_id = sc.student_course_id
        WHERE sc.student_id = ?
        ORDER BY FIELD(st.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), st.start_time";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing timetable query: " . $conn->error);
}
$stmt->bind_param("i", $student_id);
if (!$stmt->execute()) {
    die("Error executing timetable query: " . $stmt->error);
}
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('No timetable entries found for this student.');
}

// Create PDF
try {
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->AddPage();
    $pdf->SetTitle('Timetable for ' . $student_name);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Timetable for ' . $student_name, 0, 1, 'C');
    $pdf->Ln(5);

    // Table header
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, 'Course', 1, 0, 'L');
    $pdf->Cell(30, 7, 'Day', 1, 0, 'L');
    $pdf->Cell(30, 7, 'Start Time', 1, 0, 'L');
    $pdf->Cell(30, 7, 'End Time', 1, 0, 'L');
    $pdf->Cell(40, 7, 'Approved At', 1, 1, 'L');

    // Table data
    $pdf->SetFont('helvetica', '', 9);
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 6, $row['course'] ?? 'N/A', 1, 0, 'L');
        $pdf->Cell(30, 6, $row['day'] ?? 'N/A', 1, 0, 'L');
        $pdf->Cell(30, 6, $row['start_time'] ?? 'N/A', 1, 0, 'L');
        $pdf->Cell(30, 6, $row['end_time'] ?? 'N/A', 1, 0, 'L');
        $pdf->Cell(40, 6, $row['approved_at'] ?? 'N/A', 1, 1, 'L');
    }

    $stmt->close();
} catch (Exception $e) {
    die('Error generating PDF: ' . $e->getMessage());
}

// Ensure no output before PDF
ob_end_clean();

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Timetable_' . $student_id . '_' . date('Y-m-d') . '.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output the PDF
try {
    $pdf->Output('Timetable_' . $student_id . '_' . date('Y-m-d') . '.pdf', 'D');
} catch (Exception $e) {
    die('Error outputting PDF: ' . $e->getMessage());
}

exit;
?>
```