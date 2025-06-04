<?php
include '../Pages/setting.php';
session_start();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../Pages/login.php');
    exit();
}

$where ="";
if ($role == 'student') {
    $user_sql = "SELECT student_id FROM users WHERE user_id = ? AND role = 'student'";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_row = $user_result->fetch_assoc();
    $student_id = $user_row['student_id'];
    $where = " WHERE ar.student_id = '$student_id'";
} elseif($role == 'admin'){
    $where = "WHERE 1";
} elseif($role == 'instructor'){
    $user_sql = "SELECT instructor_id FROM users WHERE user_id = ? AND role = 'instructor'";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_row = $user_result->fetch_assoc();
    $instructor_id = $user_row['instructor_id'];
    $where = " WHERE ar.instructor_id = '$instructor_id'";
}else{
    header('Location: ../Pages/login.php    ');
    exit();
}

//Handle Search and Sorting
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

$sql = "
    SELECT
        ar.*,
        s.First_Name AS student_first_name, s.Last_Name AS student_last_name,
        i.First_Name AS instructor_first_name, i.Last_Name AS instructor_last_name
    FROM attendance_records ar
    LEFT JOIN students s ON ar.student_id = s.student_id
    LEFT JOIN instructor i ON ar.instructor_id = i.instructor_id
";

if (!empty($search)) {
    $sql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        i.First_Name LIKE '%$search%' OR
        i.Last_Name LIKE '%$search%' OR
        ar.course LIKE '%$search%' OR
        ar.status LIKE '%$search%' ";
}

$sql .= " ORDER BY $sort_column $sort_direction";

$result = $conn->query($sql);


echo "<table id='attendanceTable' class='attendence'>
        <thead>
            <tr class='attendance_title'>";
echo "<th>Student ID</th>";
echo "<th>Student Name</th>";
echo "<th>Attendance</th>";
echo "<th>Hours Attended</th>";
echo "<th>Course</th>";
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
        echo "<td>" . htmlspecialchars($row['course']) . "</td>";

        // Edit button + show/hide details
        echo "<td>
        <button onclick=\"toggleDetails('details-{$row['record_id']}')\">Show More</button>
                <a href='../../sql/edit_attendance.php?record_id={$row['record_id']}'>Edit</a>

              </td>";

        echo "</tr>";

        // Hidden details row
        echo "<tr id='details-{$row['record_id']}' class='details-row' style='display: none;'>";
        echo "<td colspan='6'>
        <div class='details-box'>
            <p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>
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


$conn->close();
