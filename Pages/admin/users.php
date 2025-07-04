<?php
include '../setting.php';

// Check for messages or errors in the URL
$message = isset($_GET['message']) ? $_GET['message'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/users.css?ver=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script type="module" src="/Scripts/common.js"></script>
    <script src="/Scripts/users.js?v=<?php echo time(); ?>"></script>
</head>

<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <!-- Display messages or errors -->
            <?php if ($message): ?>
                <div style="color: green; font-weight: bold;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="color: red; font-weight: bold;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <br>
            <br>

            <div class="users-controls-row">
                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="GET" action="users.php" id="search-form" style="display: flex; gap: 8px;">
                        <input type="text" name="search" id="search-input" placeholder="Search users by name or ID"
                            value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                        <input type="hidden" name="active_tab" id="active_tab"
                            value="<?php echo isset($_GET['active_tab']) ? $_GET['active_tab'] : 'students'; ?>">
                        <input type="hidden" name="students_page" id="students_page" value="1">
                        <input type="hidden" name="instructors_page" id="instructors_page" value="1">
                        <button type="submit" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" id="reset-button" title="Reset">
                            <i class="fas fa-undo"></i>
                        </button>
                    </form>
                </div>

                <!-- Add Entry Button -->
                <div class="add-entry-button">
                    <form action="../../sql/add_entry.php" method="get" style="margin: 0;">
                        <button type="submit" style="margin-left: 8px;" title="Add Entry">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    </form>
                </div>
                
                <!-- Filter Dropdown -->
                <div class="filter-dropdown">
                    <select id="user-filter-select">
                        <option value="students-table" <?php echo (!isset($_GET['active_tab']) || $_GET['active_tab'] === 'students') ? 'selected' : ''; ?>>Students</option>
                        <option value="instructors-table" <?php echo (isset($_GET['active_tab']) && $_GET['active_tab'] === 'instructors') ? 'selected' : ''; ?>>Instructors</option>
                        <option value="admins-table" <?php echo (isset($_GET['active_tab']) && $_GET['active_tab'] === 'admins') ? 'selected' : ''; ?>>Admins</option>
                    </select>
                </div>
                <script>
                    document.getElementById('user-filter-select').addEventListener('change', function () {
                        showTable(this.value);
                    });
                </script>
            </div>

            <!-- Display User Data -->
            <div class="table-container <?php echo (!isset($_GET['active_tab']) || $_GET['active_tab'] === 'students') ? 'active' : ''; ?>"
                id="students-table">
                <?php include '../../sql/students_data.php'; ?>
            </div>
            <div class="table-container <?php echo (isset($_GET['active_tab']) && $_GET['active_tab'] === 'instructors') ? 'active' : ''; ?>"
                id="instructors-table">
                <?php include '../../sql/instructors_data.php'; ?>
            </div>
            <div class="table-container <?php echo (isset($_GET['active_tab']) && $_GET['active_tab'] === 'admins') ? 'active' : ''; ?>"
                id="admins-table">
                <?php include '../../sql/admins_data.php'; ?>
            </div>
        </main>
    </div>
</body>

</html>