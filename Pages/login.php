<?php
session_start();
require_once 'setting.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Plain text password check (no hashing)
            if ($password === $user['password']) {
                // Set session and redirect
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                if ($user['role'] === 'student') {
                    header("Location: client/dashboardclient.php");
                    exit;
                } elseif ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    exit;
                } elseif ($user['role'] === 'instructor') {
                    header("Location: instructors/dashboardInstructors.php");
                    exit;
                } else {
                    $error = 'Unauthorized role.';
                }
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'No user found with this email.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link rel="stylesheet" href="../Styles/login.css">
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>