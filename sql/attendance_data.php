<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: /Pages/login.php');
    exit;
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'timetable_datetime';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$new_sort_direction = $sort_direction === 'ASC' ? 'DESC' : 'ASC';
$visible_columns = ['student_id', 'student_name', 'attendance_datetime', 'hours_attended', 'course'];

$allowed_columns = [
    'record_id',
    'student_id',
    'instructor_id',
    'timetable_datetime',
    'attendance_datetime',
    'hours_attended',
    'hours_replacement',
    'hours_remaining',
    'status',
    'created_at',
    'updated_at',
    'course'
];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'timetable_datetime';
}
switch ($sort) {
    case 'student_name':
        $sort_column = "s.Last_Name";
        break;
    case 'student_id':
    case 'attendance_datetime':
    case 'hours_attended':
    case 'course':
        $sort_column = "ar.$sort";
        break;
    default:
        $sort_column = "ar.timetable_datetime";
}

$recordsPerPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $recordsPerPage;

$sql = "
  SELECT ar.*,
       s.First_Name AS student_first_name,
       s.Last_Name AS student_last_name,
       i.First_Name AS instructor_first_name,
       i.Last_Name AS instructor_last_name,
       c.course_name AS course_name,
       c.level AS course_level
FROM attendance_records ar
LEFT JOIN students s ON ar.student_id = s.student_id
LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
LEFT JOIN courses c ON ar.course = c.course_id
LEFT JOIN instructor_courses ic ON ic.course_id = c.course_id
";

$countSql = "
  SELECT COUNT(*) AS total
  FROM attendance_records ar
  LEFT JOIN students s ON ar.student_id = s.student_id
  LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
  LEFT JOIN courses c ON ar.course = c.course_id
";

if (!empty($search)) {
    $countSql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        i.First_Name LIKE '%$search%' OR
        i.Last_Name LIKE '%$search%' OR
        ar.course LIKE '%$search%' OR
        ar.status LIKE '%$search%' ";
}

$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $recordsPerPage);

if (!empty($search)) {
    $sql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        i.First_Name LIKE '%$search%' OR
        i.Last_Name LIKE '%$search%' OR
        ar.course LIKE '%$search%' OR
        ar.status LIKE '%$search%' ";
}

$sql .= " ORDER BY $sort_column $sort_direction LIMIT $recordsPerPage OFFSET $offset";

$result = $conn->query($sql);


echo "<table id='attendanceTable' class='attendence'>
        <thead>
            <tr class='attendance_title'>";
echo "<th>Student ID</th>";
echo "<th>Student Name</th>";
echo "<th>Attendance</th>";
echo "<th>Hours Attended</th>";
echo "<th>Course</th>";
echo "<th>Status</th>";
echo "<th>Action</th>";
echo "  </tr>
        </thead>
        <tbody>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";

        // Show student ID and student name
        echo "<td data-student-id='{$row['student_id']}'>" . htmlspecialchars($row['student_id']) . "</td>";
        $studentName = htmlspecialchars($row['student_last_name'] . ' ' . $row['student_first_name']);
        echo "<td>$studentName</td>";

        // Show attendance datetime
        echo "<td>" . htmlspecialchars($row['attendance_datetime']) . "</td>";

        // Show hours attended
        echo "<td>" . htmlspecialchars($row['hours_attended']) . "</td>";

       // Show course
        echo "<td>"
            . htmlspecialchars($row['course_name'] . ' (' . $row['course_level'] . ')')
            . "</td>";

        echo "<td>"  . htmlspecialchars($row['status']) .  "</td>";
        $record_id = (int)$row['record_id'];
        // Edit button + show/hide details
        echo "<td>
        <div class='action-buttons'>
    <button onclick=\"toggleDetails('details-$record_id')\"><i class='fa fa-eye'></i></button>
    <a href='../../sql/edit_attendance.php?record_id=$record_id'><i class='fas fa-edit'></i> </a>
    <a href='../../sql/delete_attendance.php?id=$record_id' onclick=\"return confirm('Are you sure you want to delete this attendance record?');\"><i class='fas fa-trash'><</a>
        </div>
    </td>";

        echo "</tr>";

        // Hidden details row
        echo "<tr id='details-{$row['record_id']}' class='details-row' style='display: none;'>";
        echo "<td colspan='6'>
        <div class='details-box'>
            <p><strong>Timetable:</strong> " . htmlspecialchars($row['timetable_datetime']) . "</p>
            <p><strong>Hours Replacement:</strong> " . htmlspecialchars($row['hours_replacement']) . "</p>
            <p><strong>Hours Remaining:</strong> " . htmlspecialchars($row['hours_remaining']) . "</p>
            <p><strong>Instructor:</strong> " . htmlspecialchars($row['instructor_first_name'] . ' ' . $row['instructor_last_name']) . "</p>
            <p><strong>Created At:</strong> " . htmlspecialchars($row['created_at']) . "</p>
            <p><strong>Updated At:</strong> " . htmlspecialchars($row['updated_at']) . "</p>
        </div>
      </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No attendance records found.</td></tr>";
}

echo "</tbody></table>";

// Safely encode parameters
$encodedSearch = urlencode($search);
$encodedSort = urlencode($sort_column);
$encodedDir = urlencode($sort_direction);

// Pagination controls
if ($totalPages >= 1) {
    echo "<div class='pagination'>";

    if ($page > 1) {
        echo "<a href='?page=" . ($page - 1) . "&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir'>Previous</a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        echo "<a href='?page=$i&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir' class='$activeClass'>$i</a>";
    }

    if ($page < $totalPages) {
        echo "<a href='?page=" . ($page + 1) . "&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir'>Next</a>";
    } else {
        echo "<a class='disabled'>Next</a>";
    }

    echo "</div>";
}

$conn->close();
