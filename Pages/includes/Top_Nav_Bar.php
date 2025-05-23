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

// Fetch logged-in user data if available
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    $userEmail = $_SESSION['email'] ?? '';

    // Handle each role differently
    switch ($userRole) {
        case 'student':
            $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM students WHERE student_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $userName = $user['First_Name'] . ' ' . $user['Last_Name'];
                $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName);
            }
            break;
            
        case 'instructor':
            $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM instructor WHERE instructor_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $userName = $user['First_Name'] . ' ' . $user['Last_Name'];
                $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName);
            }
            break;
            
        case 'admin':
            // For admin, just show the email
            $userName = $userEmail;
            $profileImage = 'https://ui-avatars.com/api/?name=Admin&background=random';
            break;
    }
}
?>

<!-- Top Navigation Bar -->
<nav class="top-nav">
    <div class="nav-left">
        <button id="menu-toggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1>Dashboard</h1>
    </div>
    <div class="nav-right">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Home</a>
            <a href="#" class="nav-link">Courses</a>
            <a href="#" class="nav-link">Resources</a>
            <a href="#" class="nav-link">Help</a>
        </div>
        
        <!-- Notifications -->
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
                            <h4>New Assignment Posted</h4>
                            <p>Web Development Project #3 has been assigned</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item unread">
                        <div class="notification-icon grade">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="notification-content">
                            <h4>Grade Updated</h4>
                            <p>Your Database Design quiz has been graded: 95/100</p>
                            <span class="notification-time">5 hours ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item unread">
                        <div class="notification-icon announcement">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="notification-content">
                            <h4>Course Announcement</h4>
                            <p>Class schedule change for next week's lecture</p>
                            <span class="notification-time">1 day ago</span>
                        </div>
                        <div class="notification-dot"></div>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-icon message">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="notification-content">
                            <h4>New Message</h4>
                            <p>Instructor replied to your question about the project</p>
                            <span class="notification-time">2 days ago</span>
                        </div>
                    </div>
                    
                    <div class="notification-item">
                        <div class="notification-icon reminder">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="notification-content">
                            <h4>Deadline Reminder</h4>
                            <p>Assignment due tomorrow at 11:59 PM</p>
                            <span class="notification-time">3 days ago</span>
                        </div>
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
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>View Profile</span>
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