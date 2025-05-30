<?php
// Include the database settings
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
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/users.css?ver=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content Area -->
        <main class="main-content">
            <?php require("../includes/Top_Nav_Bar.php"); ?>

            <!-- Do your content here -->

            <!-- Filter Buttons -->
            <div class="search-filter-bar" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1em;">
                <form id="userSearchForm" method="GET"
                    style="display: flex; gap: 1rem; align-items: center; width: 100%;">
                    <input type="text" name="search" id="search" placeholder="Search users by name, email, etc."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        style="flex: 1; min-width: 200px; padding: 0.5em; border-radius: 4px; border: 1px solid #ccc;" />
                    <select name="role" id="role" style="padding: 0.5em; border-radius: 4px; border: 1px solid #ccc;">
                        <option value="">All Roles</option>
                        <option value="student" <?php if (isset($_GET['role']) && $_GET['role'] == 'student')
                            echo 'selected'; ?>>Student</option>
                        <option value="instructor" <?php if (isset($_GET['role']) && $_GET['role'] == 'instructor')
                            echo 'selected'; ?>>Instructor</option>
                    </select>
                    <button type="submit"
                        style="padding: 0.5em 1.2em; border-radius: 4px; border: none; background-color: #3366cc; color: #fff;">
                        Search
                    </button>
                    <a href="users.php"
                        style="text-decoration: none; color: #fff; background: #aaa; padding: 0.5em 1.2em; border-radius: 4px; margin-left: 0.5em;">Reset</a>
                </form>
            </div>

            <div class="add-entry-container">
                <a href="../../sql/add_entry.php" class="add-entry-button">Add Entry</a>
            </div>

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
                <?php include '../sql/admins_data.php'; ?>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
    <script src="/Scripts/users.js?v=<?php echo time(); ?>"></script>
</body>

</html>