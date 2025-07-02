<?php
require_once '../setting.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$message = '';
$error = '';

if ($admin_id === 0) {
    $error = "Invalid admin ID.";
} else {
    // Fetch admin details
    $result = $conn->query("SELECT user_id, email FROM users WHERE user_id = $admin_id AND role = 'admin'");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        $error = "Admin not found.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = trim($_POST['password']);
    $update_query = "";

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET email='$email', password='$password_hash' WHERE user_id=$admin_id AND role='admin'";
    } else {
        $update_query = "UPDATE users SET email='$email' WHERE user_id=$admin_id AND role='admin'";
    }

    if ($conn->query($update_query)) {
        $message = "Admin updated successfully.";
        // Refresh admin details
        $result = $conn->query("SELECT user_id, email FROM users WHERE user_id = $admin_id AND role = 'admin'");
        $admin = $result->fetch_assoc();
    } else {
        $error = "Error updating admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <link rel="stylesheet" href="../../Styles/common.css">
    <link rel="stylesheet" href="../../Styles/forms.css">
</head>
<body>
    <div class="container">
        <h2>Edit Admin</h2>
        <?php if ($message) echo "<p class='success'>$message</p>"; ?>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>

        <?php if (isset($admin)) { ?>
        <form method="POST">
            <div class="form-row">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            </div>
            <div class="form-row">
                <label for="password">New Password:</label>
                <input type="password" name="password" id="password" placeholder="Leave blank to keep unchanged">
            </div>
            <button type="submit" name="update_admin">Update Admin</button>
            <a href="users.php?active_tab=admins">Back</a>
        </form>
        <?php } ?>
    </div>
</body>
</html>