<?php
// Include the database settings
include 'settings.php';

// Example query to retrieve data from a table (e.g., 'students')
$sql = "SELECT * FROM test";  
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Student Data</h1>";
    echo "<table border='1'>
            <tr>
                <th>Name</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['Name'] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found in the table.";
}

// Example query to insert data (uncomment the next lines to use)
// $name = "Darrshan";
// $class = "10-A";
// $insertSql = "INSERT INTO students (name, class) VALUES ('$name', '$class')";
// if ($conn->query($insertSql) === TRUE) {
//     echo "New record created successfully.";
// } else {
//     echo "Error: " . $insertSql . "<br>" . $conn->error;
// }

// Close the database connection
$conn->close();
?>