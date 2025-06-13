<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

$allowed_columns = [
    'payment_id', 'payment_method', 'payment_mode', 'payment_amount',
    'deposit_status', 'payment_status', 'payment_date', 'student_name'
];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'payment_date';
}

$sort_sql = match ($sort_column) {
    'student_name' => "s.Last_Name",
    default => "p.$sort_column"
};

$sql = "
    SELECT
        p.payment_id,
        CONCAT(s.Last_Name, ' ', s.First_Name) AS student_name,
        p.payment_method,
        p.payment_mode,
        p.payment_amount,
        p.deposit_status,
        p.payment_status,
        p.payment_date
    FROM payment p
    LEFT JOIN students s ON p.student_id = s.student_id
";

if (!empty($search)) {
    $sql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        p.payment_method LIKE '%$search%' OR
        p.payment_status LIKE '%$search%' OR
        p.payment_mode LIKE '%$search%'
    ";
}

$sql .= " ORDER BY $sort_sql $sort_direction";

$result = $conn->query($sql);

echo "<table class='payment-table'>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Deposit</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = htmlspecialchars($row['payment_id']);
        $student = htmlspecialchars($row['student_name']);
        $amount = number_format($row['payment_amount'], 2);
        $deposit = ucfirst($row['deposit_status']);
        $status = ucfirst($row['payment_status']);
        $method = ucfirst($row['payment_method']);
        $mode = ucfirst($row['payment_mode']);
        $date = date('Y-m-d H:i', strtotime($row['payment_date']));

        // Main row
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$student</td>";
        echo "<td>RM $amount</td>";
        echo "<td>$deposit</td>";
        echo "<td>$status</td>";
        echo "<td><button onclick=\"toggleDetails('details-$id')\">Show More</button><br><br><br>
        <a href='../../sql/edit_payment.php?id=$id'>Edit</a><br><br><br>
          <a href='../../sql/delete_payment.php?id=$id' onclick=\"return confirm('Are you sure you want to delete this payment?');\">Delete</a> </td>";
        echo "</tr>";

        // Hidden details row
        echo "<tr id='details-$id' class='details-row' style='display: none; background-color: #f9f9f9;'>";
        echo "<td colspan='6'>
                <strong>Payment Method:</strong> $method<br>
                <strong>Payment Mode:</strong> $mode<br>
                <strong>Payment Date:</strong> $date<br>

              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No payment records found.</td></tr>";
}

echo "</tbody></table>";


$conn->close();
