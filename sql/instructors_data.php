<?php
// Include the database settings
include 'settings.php';

// Example query to retrieve data from the instructor table
$sql = "SELECT * FROM instructor";  
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Instructor Data</h1>";
    echo "<table border='1'>
            <tr>
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