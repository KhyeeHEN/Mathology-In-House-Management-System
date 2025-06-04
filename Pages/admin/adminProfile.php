<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/adminProfile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
    // Start session and check if user is logged in as admin
    session_start();
    require_once("../setting.php");
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: /login.php");
        exit();
    }
    
    // Fetch admin user data
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Extract first name for avatar if full name isn't available
    $name = $user['email']; // Default to email if no name field exists
    if (isset($user['name'])) {
        $name = $user['name'];
    } else {
        // Extract first part of email as name
        $nameParts = explode('@', $user['email']);
        $name = $nameParts[0];
    }
    ?>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <?php require("../includes/Top_Nav_Bar_Admin.php"); ?>

            <!-- Profile Content -->
            <div class="profile-container">
                <div class="profile-header">
                    <h1>Profile Settings</h1>
                    <p>Manage your personal information and account settings</p>
                </div>

                <div class="profile-content">
                    <div class="profile-image-section">
                        <div class="image-container">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>" alt="Profile" class="profile-image">
                            <label for="profile-upload" class="image-upload-label">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="profile-upload" accept="image/*" class="hidden">
                            </label>
                        </div>
                    </div>

                    <form class="profile-form">
                        <div class="form-group">
                            <label for="name">Email Address</label>
                            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars(ucfirst($user['role'])); ?>" class="form-input" readonly>
                        </div>

                        <div class="form-group">
                            <label for="created_at">Account Created</label>
                            <input type="text" id="created_at" name="created_at" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" class="form-input" readonly>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>