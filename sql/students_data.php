<?php
// Include the database settings
include 'settings.php';

// Example query to retrieve data from the students table
$sql = "SELECT * FROM students";  
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Student Data</h1>";
    echo "<table border='1'>
            <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>School Syllabus</th>
                <th>School Intake</th>
                <th>Current School Grade</th>
                <th>School</th>
                <th>Mathology Level</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['Last_Name'] . "</td>
                <td>" . $row['First_Name'] . "</td>
                <td>" . ($row['Gender'] ? 'Male' : 'Female') . "</td>
                <td>" . $row['DOB'] . "</td>
                <td>" . $row['School_Syllabus'] . "</td>
                <td>" . ($row['School_Intake'] ? 'Yes' : 'No') . "</td>
                <td>" . $row['Current_School_Grade'] . "</td>
                <td>" . $row['School'] . "</td>
                <td>" . $row['Mathology_Level'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found in the table.";
}

// Close the database connection
$conn->close();
?>