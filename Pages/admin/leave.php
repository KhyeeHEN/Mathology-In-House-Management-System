<?php
include '../setting.php';
session_start();

// Protect route: Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Paging configuration
$per_page = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $per_page;

// Messages
$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'requested_at';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

// Validate sort column
$allowed_columns = ['leave_id', 'name', 'start_date', 'end_date', 'reason', 'status', 'requested_at'];
$sort_column = in_array($sort, $allowed_columns) ? $sort : 'requested_at';
$sort_direction = $direction === 'ASC' ? 'ASC' : 'DESC';
$new_direction = $sort_direction === 'ASC' ? 'DESC' : 'ASC';

// Handle leave request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['approve']) || isset($_POST['reject']))) {
    $leave_id = (int)$_POST['leave_id'];
    $status = isset($_POST['approve']) ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE leave_requests SET status = ?, approved_by = ?, approved_at = NOW() WHERE leave_id = ?");
    if (!$stmt) {
        $error = "Failed to update leave request: " . $conn->error;
    } else {
        $stmt->bind_param("sii", $status, $_SESSION['user_id'], $leave_id);
        if ($stmt->execute()) {
            $message = "Leave request " . ($status === 'approved' ? 'approved' : 'rejected') . " successfully!";
        } else {
            $error = "Failed to update leave request: " . $stmt->error;
        }
        $stmt->close();
    }

    // Redirect to avoid form resubmission, preserving page, search, sort, and direction
    $query_params = [];
    if ($message) $query_params['message'] = urlencode($message);
    if ($error) $query_params['error'] = urlencode($error);
    if ($search) $query_params['search'] = urlencode($search);
    if ($sort) $query_params['sort'] = $sort;
    if ($direction) $query_params['direction'] = $direction;
    $query_params['page'] = $page;
    $query_string = http_build_query($query_params);
    header("Location: leave.php?" . $query_string);
    exit();
}

// Fetch total number of leave requests for pagination
$search_query = $search ? "AND (COALESCE(CONCAT(s.First_Name, ' ', s.Last_Name), CONCAT(i.First_Name, ' ', i.Last_Name), u.email) LIKE '%$search%' OR lr.reason LIKE '%$search%' OR lr.status LIKE '%$search%')" : "";
$total_sql = "
    SELECT COUNT(*) as total
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.user_id
    LEFT JOIN students s ON u.student_id = s.student_id AND lr.user_type = 'student'
    LEFT JOIN instructor i ON u.instructor_id = i.instructor_id AND lr.user_type = 'instructor'
    WHERE 1=1 $search_query
";
$total_result = $conn->query($total_sql);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Fetch leave requests with search, sort, and paging
$sql = "
    SELECT lr.*, 
           COALESCE(CONCAT(s.First_Name, ' ', s.Last_Name), CONCAT(i.First_Name, ' ', i.Last_Name), u.email) AS name,
           lr.user_type,
           CONVERT_TZ(lr.requested_at, '+00:00', '+08:00') AS requested_at
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.user_id
    LEFT JOIN students s ON u.student_id = s.student_id AND lr.user_type = 'student'
    LEFT JOIN instructor i ON u.instructor_id = i.instructor_id AND lr.user_type = 'instructor'
    WHERE 1=1 $search_query
    ORDER BY $sort_column $sort_direction
    LIMIT $per_page OFFSET $offset
";
$result = $conn->query($sql);
if (!$result) {
    $error = "Failed to fetch leave requests: " . $conn->error;
}
$leave_requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leave_requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Leave Requests</title>
    <link rel="stylesheet" href="../../Styles/common.css" />
    <link rel="stylesheet" href="../../Styles/attendance.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .content-container {
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }
        .search-bar {
            margin-bottom: 20px;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .leave-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .leave-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .leave-table th, .leave-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .leave-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            cursor: pointer;
        }
        .leave-table th.asc::after {
            content: ' ↑';
        }
        .leave-table th.desc::after {
            content: ' ↓';
        }
        .leave-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .leave-table .status-pending { color: #888; }
        .leave-table .status-approved { color: green; }
        .leave-table .status-rejected { color: red; }
        .action-buttons form {
            display: inline;
        }
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-approve {
            background-color: #4CAF50;
            color: white;
        }
        .btn-reject {
            background-color: #f44336;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #3e8e41;
        }
        .pagination span {
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <div class="content-container">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="search-bar">
                    <form method="GET" action="leave.php">
                        <input type="text" name="search" placeholder="Search by name, reason, or status..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" style="padding: 10px; border-radius: 4px; border: none; background-color: #4CAF50; color: white;">Search</button>
                        <?php if ($sort): ?>
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <?php endif; ?>
                        <?php if ($direction): ?>
                            <input type="hidden" name="direction" value="<?= htmlspecialchars($direction) ?>">
                        <?php endif; ?>
                    </form>
                </div>

                <div class="leave-table">
                    <h2>Manage Leave Requests</h2>
                    <?php if (!empty($leave_requests)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th class="<?php echo $sort_column === 'leave_id' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=leave_id&direction=<?= $sort_column === 'leave_id' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Leave ID</a>
                                    </th>
                                    <th class="<?php echo $sort_column === 'name' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=name&direction=<?= $sort_column === 'name' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">User</a>
                                    </th>
                                    <th>User Type</th>
                                    <th class="<?php echo $sort_column === 'start_date' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=start_date&direction=<?= $sort_column === 'start_date' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Start Date</a>
                                    </th>
                                    <th class="<?php echo $sort_column === 'end_date' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=end_date&direction=<?= $sort_column === 'end_date' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">End Date</a>
                                    </th>
                                    <th class="<?php echo $sort_column === 'reason' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=reason&direction=<?= $sort_column === 'reason' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Reason</a>
                                    </th>
                                    <th class="<?php echo $sort_column === 'status' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=status&direction=<?= $sort_column === 'status' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Status</a>
                                    </th>
                                    <th class="<?php echo $sort_column === 'requested_at' ? strtolower($sort_direction) : ''; ?>">
                                        <a href="?sort=requested_at&direction=<?= $sort_column === 'requested_at' ? $new_direction : 'ASC' ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>">Requested At</a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leave_requests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['leave_id']) ?></td>
                                        <td><?= htmlspecialchars($request['name']) ?></td>
                                        <td><?= htmlspecialchars($request['user_type']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($request['start_date']))) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($request['end_date']))) ?></td>
                                        <td><?= htmlspecialchars($request['reason']) ?></td>
                                        <td class="status-<?= strtolower($request['status']) ?>"><?= ucfirst($request['status']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($request['requested_at']))) ?></td>
                                        <td class="action-buttons">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <form method="POST" action="leave.php" class="leave-action-form" data-leave-id="<?= $request['leave_id'] ?>">
                                                    <input type="hidden" name="leave_id" value="<?= $request['leave_id'] ?>">
                                                    <button type="button" class="btn-approve" onclick="confirmLeaveAction(<?= $request['leave_id'] ?>, 'approve')">Approve</button>
                                                    <button type="button" class="btn-reject" onclick="confirmLeaveAction(<?= $request['leave_id'] ?>, 'reject')">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span>Action Taken</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= ($page - 1) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&direction=<?= $direction ?>" class="btn btn-primary">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <span>
                                    Page <?= $page ?> of <?= $total_pages ?>
                                </span>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?= ($page + 1) ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>&direction=<?= $direction ?>" class="btn btn-primary">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No leave requests found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script type="module" src="/Scripts/common.js"></script>
    <script>
        function confirmLeaveAction(leaveId, action) {
            const message = `Are you sure you want to ${action} this leave request?`;
            if (confirm(message)) {
                const form = document.querySelector(`.leave-action-form[data-leave-id="${leaveId}"]`);
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = action;
                hiddenInput.value = action;
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
    </script>
</body>
</html>