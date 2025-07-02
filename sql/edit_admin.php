<?php
require_once '../../Pages/setting.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get admin user_id from GET or session
$admin_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : intval($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $updates = [];

    // Email update
    if (!empty($email)) {
        $updates[] = "email = '$email'";
    }

    // Password update
    if (!empty($password)) {
        $hashed = password_hash($conn->real_escape_string($password), PASSWORD_BCRYPT);
        $updates[] = "password = '$hashed'";
    }

    if (!empty($updates)) {
        $update_sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE user_id = $admin_id AND role = 'admin'";
        if ($conn->query($update_sql)) {
            $success = "Admin details updated successfully.";
            // If email changed and this is your own profile, update session
            if (!empty($email) && $admin_id == $_SESSION['user_id']) {
                $_SESSION['email'] = $email;
            }
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}

// Fetch admin info
$admin = $conn->query("SELECT * FROM users WHERE user_id = $admin_id AND role = 'admin'")->fetch_assoc();
if (!$admin) {
    echo "Admin user not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <link rel="stylesheet" href="../../Styles/forms.css">
</head>
<body>
    <h2>Edit Admin Details</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
    <form method="POST">
        <div class="form-row">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
        </div>
        <div class="form-row">
            <label for="password">Password: <span style="font-weight:normal;">(Leave blank to keep unchanged)</span></label>
            <input type="password" name="password" id="password" placeholder="Enter new password">
        </div>
        <button type="submit">Update</button>
        <a href="users.php?active_tab=admins">Cancel</a>
    </form>
</body>
</html>