<?php
// Include the database settings
include 'settings.php';

// Get parameters from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10; // Default limit per page
$page = isset($_GET['instructors_page']) ? intval($_GET['instructors_page']) : 1; // Default page
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM instructor";
if (!empty($search)) {
    $countQuery .= " WHERE instructor_id LIKE '%$search%' OR Last_Name LIKE '%$search%' OR First_Name LIKE '%$search%'";
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
        i.instructor_id, 
        i.Last_Name, 
        i.First_Name, 
        i.Gender, 
        i.DOB, 
        i.Highest_Education, 
        i.Remark, 
        i.Training_Status,
        u.email AS user_email, 
        u.role AS user_role,
        GROUP_CONCAT(DISTINCT c.course_name SEPARATOR ', ') AS courses,
        GROUP_CONCAT(
            DISTINCT CONCAT(it.day, ' (', it.start_time, ' - ', it.end_time, ')') SEPARATOR '<br>'
        ) AS timetable
    FROM 
        instructor i
    LEFT JOIN 
        users u ON i.instructor_id = u.related_id AND u.role = 'instructor'
    LEFT JOIN 
        instructor_courses ic ON i.instructor_id = ic.instructor_id
    LEFT JOIN 
        courses c ON ic.course_id = c.course_id
    LEFT JOIN 
        instructor_timetable it ON ic.instructor_course_id = it.instructor_course_id
";

// If a search term is provided, prioritize exact matches, case-insensitive matches, and partial matches
if (!empty($search)) {
    $sql .= " WHERE 
        i.instructor_id = '$search' OR
        i.Last_Name = '$search' OR
        i.First_Name = '$search'";
}

// Group by instructor to avoid duplicate rows
$sql .= " GROUP BY i.instructor_id";

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Instructor Data</h1>";
    echo "<table border='1'>
            <tr>
                <th>Instructor ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Highest Education</th>
                <th>Remark</th>
                <th>Training Status</th>
                <th>User Email</th>
                <th>User Role</th>
                <th>Courses</th>
                <th>Timetable</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['instructor_id'] . "</td>
                <td>" . $row['Last_Name'] . "</td>
                <td>" . $row['First_Name'] . "</td>
                <td>" . ($row['Gender'] ? 'Male' : 'Female') . "</td>
                <td>" . $row['DOB'] . "</td>
                <td>" . $row['Highest_Education'] . "</td>
                <td>" . $row['Remark'] . "</td>
                <td>" . $row['Training_Status'] . "</td>
                <td>" . $row['user_email'] . "</td>
                <td>" . $row['user_role'] . "</td>
                <td>" . (!empty($row['courses']) ? $row['courses'] : 'No courses assigned') . "</td>
                <td>" . (!empty($row['timetable']) ? $row['timetable'] : 'No timetable') . "</td>
                <td>
                    <a href='../../sql/edit_instructor.php?instructor_id={$row['instructor_id']}'>Edit</a> 
                    <a href='../../sql/delete_instructor.php?instructor_id={$row['instructor_id']}' onclick=\"return confirm('Are you sure you want to delete this instructor?');\">Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?instructors_page=" . ($page - 1) . "&active_tab=instructors&search=$search'>Previous</a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?instructors_page=$i&active_tab=instructors&search=$search' class='$activeClass'>$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='?instructors_page=" . ($page + 1) . "&active_tab=instructors&search=$search'>Next</a>";
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