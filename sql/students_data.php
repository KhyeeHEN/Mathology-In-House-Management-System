<?php
// Get parameters from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10; // Default limit per page
$page = isset($_GET['students_page']) ? intval($_GET['students_page']) : 1; // Default page
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM students s LEFT JOIN users u ON s.student_id = u.student_id";
if (!empty($search)) {
    $countQuery .= " WHERE s.student_id LIKE '%$search%' OR s.Last_Name LIKE '%$search%' OR s.First_Name LIKE '%$search%' OR u.email LIKE '%$search%'";
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

// Base query for students
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
        s.How_Did_You_Heard_About_Us,
        u.email, 
        c.course_name,
        c.level,
        pc.phone AS primary_contact,
        pc.Last_Name AS primary_owner_last_name,
        pc.First_Name AS primary_owner_first_name,
        pc.Relationship_with_Student AS primary_relationship,
        pc.Email AS primary_email,
        pc.Address AS primary_address,
        pc.Postcode AS primary_postcode,
        sc.phone AS secondary_contact,
        sc.Last_Name AS secondary_owner_last_name,
        sc.First_Name AS secondary_owner_first_name,
        sc.Relationship_with_Student AS secondary_relationship,
        GROUP_CONCAT(CONCAT(st.day, ' (', st.start_time, ' - ', st.end_time, ')') SEPARATOR '<br>') AS timetable
    FROM 
        students s
    LEFT JOIN 
        users u ON s.student_id = u.student_id
    LEFT JOIN 
        student_courses scs ON s.student_id = scs.student_id
    LEFT JOIN 
        courses c ON scs.course_id = c.course_id
    LEFT JOIN 
        student_timetable st ON scs.student_course_id = st.student_course_id
    LEFT JOIN 
        primary_contact_number pc ON s.student_id = pc.student_id
    LEFT JOIN 
        secondary_contact_number sc ON s.student_id = sc.student_id
";

// If a search term is provided
if (!empty($search)) {
    $sql .= " WHERE 
        s.student_id LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        s.First_Name LIKE '%$search%' OR
        u.email LIKE '%$search%'";
}

// Group by student to avoid duplicate rows
$sql .= " GROUP BY s.student_id";

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Student Overview</h1>";
    echo "<table border='1'>
            <tr>
                <th>Student ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        // Make the ID unique by prefixing with "student"
        $detailsId = "details_student_" . $row['student_id'];
        echo "<tr>
            <td>" . $row['student_id'] . "</td>
            <td>" . $row['Last_Name'] . "</td>
            <td>" . $row['First_Name'] . "</td>
            <td>" . ($row['Gender'] ? 'Male' : 'Female') . "</td>
            <td>" . $row['email'] . "</td>
            <td class='actions-cell'> 
                <button class='action-btn view' title='Show more details' onclick=\"toggleDetails('{$detailsId}')\">
                    <i class='fa fa-eye'></i>
                </button>
                <form method='get' action='../../sql/edit_student.php' title='Edit'>
                    <input type='hidden' name='student_id' value='{$row['student_id']}'>
                    <button type='submit' class='action-btn edit'>
                        <i class='fas fa-edit'></i> 
                    </button>
                </form>
                <form method='get' action='../../sql/delete_student.php' title='Delete' onsubmit=\"return confirm('Are you sure you want to delete this student?');\">
                    <input type='hidden' name='student_id' value='{$row['student_id']}'>
                    <button type='submit' class='action-btn delete'>
                        <i class='fas fa-trash'></i> 
                    </button>
                </form>
            </td>
          </tr>";

        // Hidden detailed information row
        echo "<tr id='$detailsId' class='details-row' style='display: none;'>
            <td colspan='6'>
            <div class='details-box'>
                <strong>Date of Birth:</strong> " . $row['DOB'] . "<br>
                <strong>School Syllabus:</strong> " . $row['School_Syllabus'] . "<br>
                <strong>School Intake:</strong> " . $row['School_Intake'] . "<br>
                <strong>Current School Grade:</strong> " . $row['Current_School_Grade'] . "<br>
                <strong>School:</strong> " . $row['School'] . "<br>
                <strong>Mathology Level:</strong> " . $row['Mathology_Level'] . "<br>
                 <strong>Course Taken:</strong> " . $row['course_name'] .
            (!empty($row['level']) ? ' (' . $row['level'] . ')' : '') . "<br>
                <strong>Timetable:</strong> " . (!empty($row['timetable']) ? $row['timetable'] : 'No timetable') . "<br>
                        <strong>Primary Contact:</strong> " . (
            $row['primary_contact']
            ? "<span class='contact-tooltip' tabindex='0'>
                {$row['primary_contact']}
                <span class='contact-tooltip-popup'>
                    <strong>Name:</strong> {$row['primary_owner_first_name']} {$row['primary_owner_last_name']}<br>
                    <strong>Relationship:</strong> {$row['primary_relationship']}<br>
                    <strong>Email:</strong> {$row['primary_email']}<br>
                    <strong>Address:</strong> {$row['primary_address']} {$row['primary_postcode']}
                </span>
              </span>"
            : 'N/A'
        ) . "<br>
                 <strong>Secondary Contact:</strong> " . (
            $row['secondary_contact']
            ? "<span class='contact-tooltip' tabindex='0'>
                {$row['secondary_contact']}
                <span class='contact-tooltip-popup'>
                    <strong>Name:</strong> {$row['secondary_owner_first_name']} {$row['secondary_owner_last_name']}<br>
                    <strong>Relationship:</strong> {$row['secondary_relationship']}
                </span>
              </span>"
            : 'N/A'
        ) . "<br>
                <strong>How did you hear about us:</strong> " . $row['How_Did_You_Heard_About_Us'] . "<br>
            </div>
            </td>
          </tr>";
    }
    echo "</table>";

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?students_page=" . ($page - 1) . "&active_tab=students&search=$search'><i class='fa-solid fa-arrow-left'></i></a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?students_page=$i&active_tab=students&search=$search' class='$activeClass'>$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='?students_page=" . ($page + 1) . "&active_tab=students&search=$search'><i class='fa-solid fa-arrow-right'></i></a>";
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