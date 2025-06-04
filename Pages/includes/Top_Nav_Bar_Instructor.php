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

// Fetch logged-in instructor data if available
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'instructor') {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['email'] ?? '';
    
    // First get the instructor_id from users table
    $stmt = $conn->prepare("SELECT instructor_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $instructorId = $user['instructor_id'];
        
        // Now get instructor details from instructor table
        if ($instructorId) {
            $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM instructor WHERE instructor_id = ?");
            $stmt->bind_param("i", $instructorId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $instructor = $result->fetch_assoc();
                $userName = $instructor['First_Name'] . ' ' . $instructor['Last_Name'];
                $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName);
            }
        }
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