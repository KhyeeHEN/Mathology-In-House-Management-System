<?php
require_once '../setting.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = $_GET['sort'] ?? 'course_name';
$sort_direction = strtoupper($_GET['direction'] ?? 'ASC');

// Validate input
$allowed_columns = ['course_name', 'level', 'fee_amount', 'package_hours', 'time'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'course_name';
}
if (!in_array($sort_direction, ['ASC', 'DESC'])) {
    $sort_direction = 'ASC';
}

// SQL query
$sql = "
    SELECT f.fee_id, c.course_id, c.course_name, c.level, f.fee_amount, f.package_hours, f.time
    FROM course_fees f
    JOIN courses c ON c.course_id = f.course_id
";

if (!empty($search)) {
    $sql .= " WHERE c.course_name LIKE '%$search%' OR c.level LIKE '%$search%'";
}

$sql .= " ORDER BY $sort_column $sort_direction";

$result = $conn->query($sql);

echo "<table class='payment-table'>
        <thead>
            <tr>
                <th>Course Name</th>
                <th>Level</th>
                <th>Current Fee (RM)</th>
                <th>Package Hours</th>
                <th>Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['fee_id'];
        $name = htmlspecialchars($row['course_name']);
        $level = htmlspecialchars($row['level']);
        $fee = number_format($row['fee_amount'] ?? 0, 2);
        $hours = number_format($row['package_hours'] ?? 0);
        $time = htmlspecialchars($row['time']);

        echo "<tr>
                <td>$name</td>
                <td>$level</td>
                <td>RM $fee</td>
                <td>$hours</td>
                <td>$time</td>
                <td><a href='../../sql/edit_fee.php?id=$id'>Edit</a>
                <br>
                <br>
                <br>
                    <a href='../../sql/delete_fee.php?fee_id=$id' onclick=\"return confirm('Are you sure you want to delete this fee?');\">Delete</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No course fee records found.</td></tr>";
}

echo "</tbody></table>";

$conn->close();
