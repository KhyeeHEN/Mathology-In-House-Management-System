<?php
include '../setting.php';
session_start();

// Protect route
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Fetch user type and ID
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['role'] ?? 'student'; // Default to student if role not set

// Fetch the actual student_id or instructor_id from users table (for consistency)
$user_sql = "SELECT user_id FROM users WHERE user_id = ? AND role IN ('student', 'instructor')";
$user_stmt = $conn->prepare($user_sql);
if (!$user_stmt) {
    echo "<p>Error: Unable to retrieve user information. Please try again later.</p>";
    exit();
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows === 0) {
    echo "<p>Error: User not found.</p>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $reason = $conn->real_escape_string($_POST['reason']);

    $stmt = $conn->prepare("INSERT INTO leave_requests (user_id, user_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo "<p>Error: Unable to submit leave request. Please try again later.</p>";
        exit();
    }
    $stmt->bind_param("issss", $user_id, $user_type, $start_date, $end_date, $reason);
    if ($stmt->execute()) {
        $message = "Leave request submitted successfully!";
    } else {
        $error = "Failed to submit leave request: " . $conn->error;
    }
    $stmt->close();
}

// Fetch user's leave requests
$leave_sql = "SELECT leave_id, start_date, end_date, reason, status, requested_at FROM leave_requests WHERE user_id = ? ORDER BY requested_at DESC";
$leave_stmt = $conn->prepare($leave_sql);
if (!$leave_stmt) {
    echo "<p>Error: Unable to fetch leave requests. Please try again later.</p>";
    exit();
}
$leave_stmt->bind_param("i", $user_id);
$leave_stmt->execute();
$leave_result = $leave_stmt->get_result();
$leave_requests = [];
while ($row = $leave_result->fetch_assoc()) {
    $leave_requests[] = $row;
}
$leave_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/attendence.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .leave-form, .leave-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            background-color: #4CAF50;
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
        }
        .leave-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .leave-table .status-pending { color: #888; }
        .leave-table .status-approved { color: green; }
        .leave-table .status-rejected { color: red; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-left">
                    <button id="menu-toggle" class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Apply Leave</h1>
                </div>
                <div class="nav-right">
                    <div class="nav-links">
                        <a href="dashboard.html" class="nav-link">Home</a>
                        <a href="#" class="nav-link">Courses</a>
                        <a href="#" class="nav-link">Resources</a>
                        <a href="#" class="nav-link">Help</a>
                    </div>
                    <div class="user-profile">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'User') ?>" alt="Profile" class="profile-img">
                        <div class="profile-dropdown">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="profile.html" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>View Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="content-container">
                <?php if (isset($message)): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="leave-form">
                    <h2>Apply for Leave</h2>
                    <form method="POST" action="leave.php">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea name="reason" id="reason" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_leave" class="btn">Submit Leave Request</button>
                    </form>
                </div>

                <div class="leave-table">
                    <h2>My Leave Requests</h2>
                    <?php if (!empty($leave_requests)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Leave ID</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leave_requests as $request): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($request['leave_id']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($request['start_date']))) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($request['end_date']))) ?></td>
                                        <td><?= htmlspecialchars($request['reason']) ?></td>
                                        <td class="status-<?= strtolower($request['status']) ?>"><?= ucfirst($request['status']) ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($request['requested_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No leave requests found.</p>
                    <?php endif; ?>
                </div>

                <!-- Admin View (to be expanded separately)
                <div class="leave-table" id="admin-view" style="display: <?php echo $user_type === 'admin' ? 'block' : 'none'; ?>">
                    <h2>All Leave Requests</h2>
                    <?php if ($user_type === 'admin'): ?>
                        <?php
                        $admin_sql = "SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.user_id = u.user_id ORDER BY requested_at DESC";
                        $admin_result = $conn->query($admin_sql);
                        if ($admin_result && $admin_result->num_rows > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Requested At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $admin_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['username']) ?></td>
                                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['start_date']))) ?></td>
                                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['end_date']))) ?></td>
                                            <td><?= htmlspecialchars($row['reason']) ?></td>
                                            <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
                                            <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($row['requested_at']))) ?></td>
                                            <td>
                                                <button onclick="approveLeave(<?= $row['leave_id'] ?>, 'approved')">Approve</button>
                                                <button onclick="approveLeave(<?= $row['leave_id'] ?>, 'rejected')">Reject</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No leave requests to review.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                -->
            </div>
        </main>
    </div>
    <script type="module" src="../../scripts/common.js"></script>
    <!--
    <script>
        function approveLeave(leaveId, status) {
            if (confirm(`Are you sure you want to ${status} this leave request?`)) {
                fetch('approve_leave.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `leave_id=${leaveId}&status=${status}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert(data.message);
                });
            }
        }
    </script>
    -->
</body>
</html>