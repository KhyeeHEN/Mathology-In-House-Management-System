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
        <!-- Admin Notifications -->
        <div class="notifications">
            <button class="notification-btn" id="notification-toggle">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">2</span>
            </button>
            <div class="notification-popover" id="notification-popover">
                <div class="notification-header">
                    <h3>Admin Notifications</h3>
                    <button class="mark-all-read">Mark all as read</button>
                </div>
                <div class="notification-list">
                    <div class="notification-item unread">
                        <div class="notification-icon system">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="notification-content">
                            <h4>System Update Available</h4>
                            <p>New version 2.5.1 is ready to install</p>
                            <span class="notification-time">1 hour ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item unread">
                        <div class="notification-icon user">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <h4>New User Registration</h4>
                            <p>3 new users need approval</p>
                            <span class="notification-time">4 hours ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-icon report">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="notification-content">
                            <h4>Monthly Report Generated</h4>
                            <p>June system report is ready</p>
                            <span class="notification-time">1 day ago</span>
                        </div>
                    </div>
                </div>
                <div class="notification-footer">
                    <a href="admin_notifications.php" class="view-all-link">View all notifications</a>
                </div>
            </div>
        </div>
        
        <div class="user-profile">
            <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-img">
            <div class="profile-dropdown">
                <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
                <a href="adminProfile.php" class="dropdown-item">
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

<script>
// Notification toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notification-toggle');
    const notificationPopover = document.getElementById('notification-popover');
    const markAllReadBtn = document.querySelector('.mark-all-read');
    
    // Toggle notification popover
    notificationBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationPopover.classList.toggle('active');
    });
    
    // Close popover when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationBtn.contains(e.target) && !notificationPopover.contains(e.target)) {
            notificationPopover.classList.remove('active');
        }
    });
    
    // Mark all as read functionality
    markAllReadBtn.addEventListener('click', function() {
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            item.classList.remove('unread');
        });
        
        // Update badge count
        const badge = document.querySelector('.notification-badge');
        badge.textContent = '0';
        badge.style.display = 'none';
    });
    
    // Individual notification click
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('unread');
            updateBadgeCount();
        });
    });
    
    // Update badge count
    function updateBadgeCount() {
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;
        const badge = document.querySelector('.notification-badge');
        badge.textContent = unreadCount;
        if (unreadCount === 0) {
            badge.style.display = 'none';
        }
    }
});
</script>