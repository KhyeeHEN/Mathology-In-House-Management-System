<?php
// Include the database settings
include 'settings.php';

// Get the search term (if any) from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// If a search term is provided, prioritize exact matches, case-insensitive matches, and partial matches
$sql = "SELECT * FROM instructor";
if (!empty($search)) {
    $sql = "
        SELECT * FROM instructor
        WHERE
            instructor_id = '$search' OR
            Last_Name = '$search' OR
            First_Name = '$search'
        UNION
        SELECT * FROM instructor
        WHERE 
            instructor_id LIKE BINARY '%$search%' OR
            Last_Name LIKE BINARY '%$search%' OR
            First_Name LIKE BINARY '%$search%'
        UNION
        SELECT * FROM instructor
        WHERE 
            instructor_id LIKE '%$search%' OR
            Last_Name LIKE '%$search%' OR
            First_Name LIKE '%$search%'
    ";
}
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
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found in the table.";
}

// Close the database connection
$conn->close();
?>