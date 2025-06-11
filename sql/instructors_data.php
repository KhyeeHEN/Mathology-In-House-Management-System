<?php
// Get parameters from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10; // Default limit per page
$page = isset($_GET['instructors_page']) ? intval($_GET['instructors_page']) : 1; // Default page
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM instructor i LEFT JOIN users u ON i.instructor_id = u.instructor_id";
if (!empty($search)) {
    $countQuery .= " WHERE i.instructor_id LIKE '%$search%' OR i.Last_Name LIKE '%$search%' OR i.First_Name LIKE '%$search%' OR u.email LIKE '%$search%'";
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

// Base query for instructors
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
        i.Employment_Type,
        i.Working_Days,
        i.Worked_Days,
        i.Total_Hours,
        i.contact,
        i.hiring_status,
        u.email, 
        GROUP_CONCAT(DISTINCT c.course_name SEPARATOR ', ') AS courses,
        GROUP_CONCAT(
            DISTINCT CONCAT(it.day, ' (', it.start_time, ' - ', it.end_time, ')') SEPARATOR '<br>'
        ) AS timetable
    FROM 
        instructor i
    LEFT JOIN 
        users u ON i.instructor_id = u.instructor_id
    LEFT JOIN 
        instructor_courses ic ON i.instructor_id = ic.instructor_id
    LEFT JOIN 
        courses c ON ic.course_id = c.course_id
    LEFT JOIN 
        instructor_timetable it ON ic.instructor_course_id = it.instructor_course_id
";

// If a search term is provided
if (!empty($search)) {
    $sql .= " WHERE 
        i.instructor_id LIKE '%$search%' OR
        i.Last_Name LIKE '%$search%' OR
        i.First_Name LIKE '%$search%' OR
        u.email LIKE '%$search%'";
}

// Group by instructor to avoid duplicate rows
$sql .= " GROUP BY i.instructor_id";

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Instructor Overview</h1>";
    echo "<table border='1'>
            <tr>
                <th>Instructor ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        // Make the ID unique by prefixing with "instructor"
        $detailsId = "details_instructor_" . $row['instructor_id'];
        echo "<tr>
            <td>" . $row['instructor_id'] . "</td>
            <td>" . $row['Last_Name'] . "</td>
            <td>" . $row['First_Name'] . "</td>
            <td>" . ($row['Gender'] ? 'Male' : 'Female') . "</td>
            <td>" . $row['email'] . "</td>
            <td>
                <button class='action-btn view' onclick=\"toggleDetails('$detailsId')\">View Details</button>
                <form method='get' action='../../sql/edit_instructor.php' style='display:inline; margin:0; padding:0;'>
                    <input type='hidden' name='instructor_id' value='{$row['instructor_id']}'>
                    <button type='submit' class='action-btn edit'>Edit</button>
                </form>
                <form method='get' action='../../sql/delete_instructor.php' style='display:inline; margin:0; padding:0;' onsubmit=\"return confirm('Are you sure you want to delete this instructor?');\">
                    <input type='hidden' name='instructor_id' value='{$row['instructor_id']}'>
                    <button type='submit' class='action-btn delete'>Delete</button>
                </form>
            </td>
          </tr>";

        // Hidden detailed information row
        echo "<tr id='$detailsId' class='details-row' style='display: none;'>
            <td colspan='6'>
            <div class='details-box'>
                <strong>Date of Birth:</strong> " . $row['DOB'] . "<br>
                <strong>Highest Education:</strong> " . $row['Highest_Education'] . "<br>
                <strong>Employment Type:</strong> " . ($row['Employment_Type'] ?? 'N/A') . "<br>
                <strong>Working Days:</strong> " . ($row['Working_Days'] ?? 'N/A') . "<br>
                <strong>Worked Days:</strong> " . ($row['Worked_Days'] ?? 'N/A') . "<br>
                <strong>Total Hours:</strong> " . ($row['Total_Hours'] ?? 'N/A') . "<br>
                <strong>Remark:</strong> " . ($row['Remark'] ?? 'N/A') . "<br>
                <strong>Contact Number:</strong> " . ($row['contact'] ?? 'N/A') . "<br>
                <strong>Training Status:</strong> " . $row['Training_Status'] . "<br>
                <strong>Courses:</strong> " . (!empty($row['courses']) ? $row['courses'] : 'No courses assigned') . "<br>
                <strong>Timetable:</strong> " . (!empty($row['timetable']) ? $row['timetable'] : 'No timetable') . "
            </div>
            </td>
          </tr>";
    }
    echo "</table>";

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?instructors_page=" . ($page - 1) . "&active_tab=instructors&search=" . urlencode($search) . "'>Previous</a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?instructors_page=$i&active_tab=instructors&search=" . urlencode($search) . "' class='$activeClass'>$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='?instructors_page=" . ($page + 1) . "&active_tab=instructors&search=" . urlencode($search) . "'>Next</a>";
    } else {
        echo "<a class='disabled'>Next</a>";
    }
    echo "</div>";
} else {
    echo "No data found in the table.";
}
?>

<script>
    // JavaScript to toggle detailed information
    function toggleDetails(detailsId) {
        const detailsRow = document.getElementById(detailsId);
        if (detailsRow.style.display === "none") {
            detailsRow.style.display = "table-row";
        } else {
            detailsRow.style.display = "none";
        }
    }
</script>