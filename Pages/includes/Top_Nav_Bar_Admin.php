<?php
// Start session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once '../setting.php';

// Initialize variables
$userName = 'Guest';
$profileImage = 'https://via.placeholder.com/150'; // Fallback image
$userEmail = '';

// Fetch admin data
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['email'] ?? '';
    $userName = $userEmail;
    $profileImage = 'https://ui-avatars.com/api/?name=Admin&background=random';
}
?>

<!-- Admin Top Navigation Bar -->
<nav class="top-nav">
    <div class="nav-left">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1>Admin Dashboard</h1>
    </div>
    <div class="nav-right">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="manage_users.php" class="nav-link">Users</a>
            <a href="manage_courses.php" class="nav-link">Courses</a>
            <a href="system_settings.php" class="nav-link">Settings</a>
            <a href="reports.php" class="nav-link">Reports</a>
        </div>
        
        <div class="user-profile">
            <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-img">
            <div class="profile-dropdown">
                <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
                <a href="admin_profile.php" class="dropdown-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Admin Profile</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>