<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'payment_date';
$sort_direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

// Allowed columns to prevent SQL injection
$allowed_columns = [
    'payment_id', 'payment_method', 'payment_mode', 'payment_amount',
    'deposit_status', 'payment_status', 'payment_date', 'student_name'
];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'payment_date';
}

$sort_sql = $sort_column === 'student_name' ? "s.Last_Name" : "p.$sort_column";

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total records for pagination
$countSql = "
    SELECT COUNT(*) AS total
    FROM payment p
    LEFT JOIN students s ON p.student_id = s.student_id
";

if (!empty($search)) {
    $countSql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        p.payment_method LIKE '%$search%' OR
        p.payment_status LIKE '%$search%' OR
        p.payment_mode LIKE '%$search%'";
}

$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Main query with limit
$sql = "
    SELECT
        p.payment_id,
        CONCAT(s.Last_Name, ' ', s.First_Name) AS student_name,
        p.payment_method,
        p.payment_mode,
        p.payment_amount,
        p.deposit_status,
        p.payment_status,
        p.payment_date,
        p.invoice_path
    FROM payment p
    LEFT JOIN students s ON p.student_id = s.student_id
";

if (!empty($search)) {
    $sql .= " WHERE
        s.First_Name LIKE '%$search%' OR
        s.Last_Name LIKE '%$search%' OR
        p.payment_method LIKE '%$search%' OR
        p.payment_status LIKE '%$search%' OR
        p.payment_mode LIKE '%$search%'";
}

$sql .= " ORDER BY $sort_sql $sort_direction LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// Begin table output
echo "<table class='payment-table'>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Deposit</th>
                <th>Status</th>
                <th>Invoice</th>
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
        $invoice = (!empty($row['invoice_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . str_replace('../../', '', $row['invoice_path'])))
            ? "<a href='../../{$row['invoice_path']}' target='_blank' class='download-btn'>View Invoice</a>"
            : "<span style='color:gray;'>Not available</span>";

        echo "<tr>
                <td>$id</td>
                <td>$student</td>
                <td>RM $amount</td>
                <td>$deposit</td>
                <td>$status</td>
                <td>$invoice</td>
                <td>
                    <div class='action-buttons'>
                        <button onclick=\"toggleDetails('details-$id')\">Show More</button><br><br>
                        <a href='../../sql/edit_payment.php?id=$id'>Edit</a><br><br>
                        <a href='../../sql/delete_payment.php?id=$id' onclick=\"return confirm('Are you sure you want to delete this payment?');\">Delete</a>
                    </div>
                </td>
              </tr>";

        // Details row
        echo "<tr id='details-$id' class='details-row' style='display: none; background-color: #f9f9f9;'>
                <td colspan='7'>
                    <strong>Payment Method:</strong> $method<br>
                    <strong>Payment Mode:</strong> $mode<br>
                    <strong>Payment Date:</strong> $date<br>
                </td>
              </tr>";
    }
    echo "</tbody></table>";

    // Pagination controls
    $encodedSearch = urlencode($search);
    $encodedSort = urlencode($sort_column);
    $encodedDir = urlencode($sort_direction);

    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?page=" . ($page - 1) . "&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir'>Previous</a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?page=$i&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir' class='$activeClass'>$i</a>";
    }

    if ($page < $totalPages) {
        echo "<a href='?page=" . ($page + 1) . "&search=$encodedSearch&sort=$encodedSort&direction=$encodedDir'>Next</a>";
    } else {
        echo "<a class='disabled'>Next</a>";
    }
    echo "</div>";
} else {
    echo "<tr><td colspan='7'>No payment records found.</td></tr></tbody></table>";
}

$conn->close();
?>
