<?php
include '../setting.php';
session_start();

// Protect route: Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Messages
$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

// Handle instructor update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_instructor'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $highest_education = $conn->real_escape_string(trim($_POST['highest_education']));
    $employment_type = in_array($_POST['employment_type'], ['Part-Time', 'Full-Time']) ? $_POST['employment_type'] : 'Full-Time';
    $working_days = $employment_type === 'Part-Time' ? $conn->real_escape_string(trim($_POST['working_days'])) : NULL;
    $worked_days = (int)$_POST['worked_days'];
    $remark = $conn->real_escape_string(trim($_POST['remark']));

    // Validate worked_days (must be non-negative)
    if ($worked_days < 0) {
        $error = "Worked days cannot be negative.";
    } else {
        // Update the instructor record
        $stmt = $conn->prepare("UPDATE instructor SET Highest_Education = ?, Employment_Type = ?, Working_Days = ?, Worked_Days = ?, Remark = ? WHERE instructor_id = ?");
        if (!$stmt) {
            $error = "Failed to prepare update statement: " . $conn->error;
        } else {
            $stmt->bind_param("sssisi", $highest_education, $employment_type, $working_days, $worked_days, $remark, $instructor_id);
            if ($stmt->execute()) {
                $message = "Instructor updated successfully!";
            } else {
                $error = "Failed to update instructor: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Redirect to avoid form resubmission, preserving search term
    $query_params = [];
    if ($message) $query_params['message'] = urlencode($message);
    if ($error) $query_params['error'] = urlencode($error);
    if (isset($_GET['search'])) $query_params['search'] = urlencode($_GET['search']);
    $query_string = http_build_query($query_params);
    header("Location: instructor_info.php?" . $query_string);
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$search_query = $search ? "WHERE (Last_Name LIKE '%$search%' OR First_Name LIKE '%$search%' OR instructor_id LIKE '%$search%')" : "";

// Fetch instructors with search filter
$sql = "SELECT * FROM instructor $search_query ORDER BY instructor_id";
$result = $conn->query($sql);
if (!$result) {
    $error = "Failed to fetch instructors: " . $conn->error;
}
$instructors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Instructor Information</title>
    <link rel="stylesheet" href="../../Styles/common.css" />
    <link rel="stylesheet" href="../../Styles/attendance.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .content-container {
            padding: 20px;
            max-width: 1200px;
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
        .search-bar button {
            padding: 10px;
            border-radius: 4px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .instructor-table {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .instructor-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .instructor-table th, .instructor-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .instructor-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .instructor-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
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
        .btn-edit {
            background-color: #2196F3;
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
        .edit-form {
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .edit-form input, .edit-form select {
            margin: 5px 0;
            padding: 5px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .edit-form button {
            margin-top: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <div class="content-container">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="search-bar">
                    <form method="GET" action="instructor_info.php">
                        <input type="text" name="search" placeholder="Search by name or ID..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <div class="instructor-table">
                    <h2>Manage Instructor Information</h2>
                    <?php if (!empty($instructors)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Instructor ID</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Highest Education</th>
                                    <th>Employment Type</th>
                                    <th>Working Days</th>
                                    <th>Worked Days</th>
                                    <th>Remark</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($instructors as $instructor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($instructor['instructor_id']) ?></td>
                                        <td><?= htmlspecialchars($instructor['Last_Name']) ?></td>
                                        <td><?= htmlspecialchars($instructor['First_Name']) ?></td>
                                        <td><?= htmlspecialchars($instructor['Highest_Education']) ?></td>
                                        <td><?= htmlspecialchars($instructor['Employment_Type']) ?></td>
                                        <td><?= htmlspecialchars($instructor['Working_Days'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($instructor['Worked_Days'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($instructor['Remark'] ?? 'N/A') ?></td>
                                        <td class="action-buttons">
                                            <button type="button" class="btn-edit" onclick="toggleEditForm(<?= $instructor['instructor_id'] ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="edit-row-<?= $instructor['instructor_id'] ?>" style="display: none;">
                                        <td colspan="9">
                                            <form method="POST" action="instructor_info.php" class="edit-form">
                                                <input type="hidden" name="instructor_id" value="<?= $instructor['instructor_id'] ?>">
                                                <label>Highest Education:</label>
                                                <input type="text" name="highest_education" value="<?= htmlspecialchars($instructor['Highest_Education']) ?>" required>
                                                <br>
                                                <label>Employment Type:</label>
                                                <select name="employment_type" onchange="toggleWorkingDays(this, 'working-days-<?= $instructor['instructor_id'] ?>')">
                                                    <option value="Full-Time" <?= $instructor['Employment_Type'] === 'Full-Time' ? 'selected' : '' ?>>Full-Time</option>
                                                    <option value="Part-Time" <?= $instructor['Employment_Type'] === 'Part-Time' ? 'selected' : '' ?>>Part-Time</option>
                                                </select>
                                                <br>
                                                <label>Working Days (Part-Time only):</label>
                                                <input type="text" name="working_days" id="working-days-<?= $instructor['instructor_id'] ?>" value="<?= htmlspecialchars($instructor['Working_Days'] ?? '') ?>" placeholder="e.g., Monday,Wednesday,Friday" <?= $instructor['Employment_Type'] === 'Full-Time' ? 'disabled' : '' ?>>
                                                <br>
                                                <label>Worked Days:</label>
                                                <input type="number" name="worked_days" value="<?= htmlspecialchars($instructor['Worked_Days'] ?? 0) ?>" min="0" required>
                                                <br>
                                                <label>Remark:</label>
                                                <input type="text" name="remark" value="<?= htmlspecialchars($instructor['Remark'] ?? '') ?>" maxlength="100">
                                                <br>
                                                <button type="submit" name="update_instructor" class="btn-save">Save</button>
                                                <button type="button" class="btn-cancel" onclick="toggleEditForm(<?= $instructor['instructor_id'] ?>)">Cancel</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No instructors found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../Scripts/attendance.js?v=<?php echo time(); ?>"></script>
    <script>
        function toggleEditForm(instructorId) {
            const row = document.getElementById(`edit-row-${instructorId}`);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }

        function toggleWorkingDays(select, workingDaysId) {
            const workingDaysInput = document.getElementById(workingDaysId);
            workingDaysInput.disabled = select.value === 'Full-Time';
            if (select.value === 'Full-Time') {
                workingDaysInput.value = '';
            }
        }
    </script>
</body>
</html>