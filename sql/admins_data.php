<?php
// Get parameters from the GET request
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$limit = 10; // Default limit per page
$page = isset($_GET['admins_page']) ? intval($_GET['admins_page']) : 1; // Default page
$offset = ($page - 1) * $limit;

// Get total rows for pagination
$countQuery = "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'";
if (!empty($search)) {
    $countQuery .= " AND (email LIKE '%$search%')";
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

// Base query to retrieve admin credentials
$sql = "
    SELECT 
        user_id, 
        email, 
        role, 
        created_at 
    FROM 
        users
    WHERE 
        role = 'admin'
";

// If a search term is provided, apply filtering
if (!empty($search)) {
    $sql .= " AND (email LIKE '%$search%')";
}

// Add pagination
$sql .= " LIMIT $limit OFFSET $offset";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the data as an HTML table
    echo "<h1>Admin Users</h1>";
    echo "<table border='1'>
            <tr>
                <th>User ID</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row['user_id'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . $row['created_at'] . "</td>
                <td class='actions-cell'> 
                <form method='get' action='../../sql/edit_admin.php' title='Edit'>
                    <input type='hidden' name='user_id' value='{$row['user_id']}'>
                    <button type='submit' class='action-btn edit'>
                        <i class='fas fa-edit'></i> 
                    </button>
                </form>
                <form method='get' action='../../sql/delete_admin.php' title='Delete' onsubmit=\"return confirm('Are you sure you want to delete this student?');\">
                    <input type='hidden' name='user_id' value='{$row['user_id']}'>
                    <button type='submit' class='action-btn delete'>
                        <i class='fas fa-trash'></i> 
                    </button>
                </form>
                </td>
        </tr>";
    }
    echo "</table>";

    // Pagination controls
    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?admins_page=" . ($page - 1) . "&active_tab=admins&search=$search'><i class='fa-solid fa-arrow-left'></i></a>";
    } else {
        echo "<a class='disabled'>Previous</a>";
    }
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = $i == $page ? 'active' : '';
        echo "<a href='?admins_page=$i&active_tab=admins&search=$search' class='$activeClass'>$i</a>";
    }
    if ($page < $totalPages) {
        echo "<a href='?admins_page=" . ($page + 1) . "&active_tab=admins&search=$search'><i class='fa-solid fa-arrow-right'></i></a>";
    } else {
        echo "<a class='disabled'>Next</a>";
    }
    echo "</div>";
} else {
    echo "No admin users found.";
}
?>