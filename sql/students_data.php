<?php
// Include the database settings
include 'settings.php';

// Get parameters from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10; // Default limit per page
$page = isset($_GET['students_page']) ? intval($_GET['students_page']) : 1; // Default page
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM students";
if (!empty($search)) {
    $countQuery .= " WHERE student_id LIKE '%$search%' OR Last_Name LIKE '%$search%' OR First_Name LIKE '%$search%'";
}
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Cap the page number to the total pages
if ($page > $totalPages) {
    $page = $totalPages;
}
if ($page < 1) {
    $page = 1;
}

// Base query with JOIN for credentials and GROUP_CONCAT for timetable
$sql = "
    SELECT 
        s.student_id, 
        s.Last_Name, 
        s.First_Name, 
        s.Gender, 
        s.DOB, 
        s.School_Syllabus, 
        s.School_Intake, 
        s.Current_School_Grade, 
        s.School, 
        s.Mathology_Level,
        u.email AS user_email, 
        u.role AS user_role,
        c.course_name,
        GROUP_CONCAT(CONCAT(st.day, ' (', st.start_time, ' - ', st.end_time, ')') SEPARATOR '<br>') AS timetable
    FROM 
        students s
    LEFT JOIN 
        users u ON s.student_id = u.related_id AND u.role = 'student'
    LEFT JOIN 
        student_courses sc ON s.student_id = sc.student_id
    LEFT JOIN 
        courses c ON sc.course_id = c.course_id
    LEFT JOIN 
        student_timetable st ON sc.student_course_id = st.student_course_id
";

// If a search term is provided, prioritize exact matches, case-insensitive matches, and partial matches
if (!empty($search)) {
    $sql .= " WHERE 
        s.student_id = '$search' OR
        s.Last_Name = '$search' OR
        s.First_Name = '$search'";
}

// Group by student to avoid duplicate rows
$sql .= " GROUP BY s.student_id";

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Student Data</h1>";
    echo "<table border='1'>
            <tr>
                <th>Student ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>School Syllabus</th>
                <th>School Intake</th>
                <th>Current School Grade</th>
                <th>School</th>
                <th>Mathology Level</th>
                <th>User Email</th>
                <th>User Role</th>
                <th>Course</th>
                <th>Timetable</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['student_id'] . "</td>
                <td>" . $row['Last_Name'] . "</td>
                <td>" . $row['First_Name'] . "</td>
                <td>" . ($row['Gender'] ? 'Male' : 'Female') . "</td>
                <td>" . $row['DOB'] . "</td>
                <td>" . $row['School_Syllabus'] . "</td>
                <td>" . ($row['School_Intake'] ? 'Yes' : 'No') . "</td>
                <td>" . $row['Current_School_Grade'] . "</td>
                <td>" . $row['School'] . "</td>
                <td>" . $row['Mathology_Level'] . "</td>
                <td>" . $row['user_email'] . "</td>
                <td>" . $row['user_role'] . "</td>
                <td>" . $row['course_name'] . "</td>
                <td>" . (!empty($row['timetable']) ? $row['timetable'] : 'No timetable') . "</td>
                <td>
                    <a href='../../sql/edit_student.php?student_id={$row['student_id']}'>Edit</a>
                    <a href='../../sql/delete_student.php?student_id={$row['student_id']}' onclick=\"return confirm('Are you sure you want to delete this student?');\">Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?students_page=" . ($page - 1) . "&active_tab=students&search=$search'>Previous</a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?students_page=$i&active_tab=students&search=$search' class='$activeClass'>$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='?students_page=" . ($page + 1) . "&active_tab=students&search=$search'>Next</a>";
    } else {
        echo "<a class='disabled'>Next</a>";
    }
    echo "</div>";
} else {
    echo "No data found in the table.";
}

// Close the database connection
$conn->close();
?>