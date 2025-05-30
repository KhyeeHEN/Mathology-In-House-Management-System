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

            <div class="search-bar">
                <form method="GET" action="users.php"
                    style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <!-- General Search -->
                    <input type="text" name="search" placeholder="Search users..."
                        value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">

                    <!-- Filter by Role -->
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="">All</option>
                        <option value="student" <?php if (isset($role) && $role === 'student')
                            echo 'selected'; ?>>Student
                        </option>
                        <option value="instructor" <?php if (isset($role) && $role === 'instructor')
                            echo 'selected'; ?>>
                            Instructor</option>
                        <option value="admin" <?php if (isset($role) && $role === 'admin')
                            echo 'selected'; ?>>Admin
                        </option>
                    </select>

                    <!-- Filter by Course (optional, if courses available) -->
                    <label for="course">Course:</label>
                    <select id="course" name="course">
                        <option value="">All</option>
                        <?php
                        // Populate courses dropdown if $courseOptions is available from backend
                        if (isset($courseOptions)) {
                            foreach ($courseOptions as $cid => $cname) {
                                $selected = (isset($course) && $course == $cid) ? 'selected' : '';
                                echo "<option value=\"$cid\" $selected>$cname</option>";
                            }
                        }
                        ?>
                    </select>

                    <!-- Sort By -->
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort">
                        <option value="user_id" <?php if (isset($sort) && $sort === 'user_id')
                            echo 'selected'; ?>>User ID
                        </option>
                        <option value="name" <?php if (isset($sort) && $sort === 'name')
                            echo 'selected'; ?>>Name</option>
                        <option value="role" <?php if (isset($sort) && $sort === 'role')
                            echo 'selected'; ?>>Role</option>
                        <option value="email" <?php if (isset($sort) && $sort === 'email')
                            echo 'selected'; ?>>Email
                        </option>
                        <option value="course" <?php if (isset($sort) && $sort === 'course')
                            echo 'selected'; ?>>Course
                        </option>
                    </select>

                    <!-- Sort Direction -->
                    <select id="direction" name="direction">
                        <option value="ASC" <?php if (isset($direction) && $direction === 'ASC')
                            echo 'selected'; ?>>
                            Ascending</option>
                        <option value="DESC" <?php if (isset($direction) && $direction === 'DESC')
                            echo 'selected'; ?>>
                            Descending</option>
                    </select>

                    <!-- Buttons -->
                    <button type="submit">Search/Filter</button>
                    <button type="button" id="reset-button" onclick="window.location='users.php'">Reset</button>
                    <button type="button" onclick="window.location='add_entry.php'">
                        <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add User
                    </button>
                </form>
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
    <script type="module" src="/Scripts/common.js"></script>
    <script src="/Scripts/users.js?v=<?php echo time(); ?>"></script>
</body>

</html>