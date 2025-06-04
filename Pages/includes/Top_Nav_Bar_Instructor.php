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

// Fetch instructor data
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'instructor') {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM instructor WHERE instructor_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userName = $user['First_Name'] . ' ' . $user['Last_Name'];
        $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName);
    }
}
?>

<!-- Instructor Top Navigation Bar -->
<nav class="top-nav">
    <div class="nav-left">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1>Instructor Dashboard</h1>
    </div>
    <div class="nav-right">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="my_courses.php" class="nav-link">My Courses</a>
            <a href="gradebook.php" class="nav-link">Gradebook</a>
            <a href="announcements.php" class="nav-link">Announcements</a>
            <a href="instructor_resources.php" class="nav-link">Resources</a>
        </div>
        
        <!-- Instructor Notifications -->
        <div class="notifications">
            <button class="notification-btn" id="notification-toggle">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <div class="notification-popover" id="notification-popover">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="mark-all-read">Mark all as read</button>
                </div>
                <div class="notification-list">
                    <div class="notification-item unread">
                        <div class="notification-icon assignment">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="notification-content">
                            <h4>New Submission</h4>
                            <p>Student submitted Assignment #3 in Web Development</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item unread">
                        <div class="notification-icon question">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="notification-content">
                            <h4>Student Question</h4>
                            <p>New question about Database Design project</p>
                            <span class="notification-time">5 hours ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                </div>
                <div class="notification-footer">
                    <a href="#" class="view-all-link">View all notifications</a>
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
                <a href="instructor_profile.php" class="dropdown-item">
                    <i class="fas fa-user-tie"></i>
                    <span>My Profile</span>
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
// Notification toggle functionality (same as in student version)
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