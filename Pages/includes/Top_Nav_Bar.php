<?php
// Start session only if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch logged-in user data if available
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Database connection
    require_once '../setting.php';

    // Fetch student details using the user_id (you can modify this as per your actual schema)
    $stmt = $conn->prepare("SELECT First_Name, Last_Name FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists in the students table
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userName = $user['First_Name'] . ' ' . $user['Last_Name'];
        $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName); // Avatar based on real name
    } else {
        $userName = 'Guest';  // Fallback name if not found
        $profileImage = 'https://via.placeholder.com/150'; // Fallback image
    }
} else {
    // Fallback for non-logged in users
    $userName = 'Guest';
    $profileImage = 'https://via.placeholder.com/150'; // Fallback image
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
