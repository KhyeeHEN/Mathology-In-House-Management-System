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