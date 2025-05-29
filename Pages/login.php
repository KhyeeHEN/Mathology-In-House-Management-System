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
                    header("Location: ../client/dashboardclient.php");
                    exit;
                } elseif ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboardAdmin.php");
                    exit;
                } elseif ($user['role'] === 'instructor') {
                    header("Location: ../instructors/dashboardInstructors.php");
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login to Your Account</title>
  <link rel="stylesheet" href="../Styles/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
  <div class="login-container">
    <h2>Welcome Back</h2>
    
    <?php if ($error): ?>
      <div class="error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>
      
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
      </div>
      
      <button type="submit">
        Sign In <i class="fas fa-arrow-right"></i>
      </button>
      
      <a href="#" class="forgot-password">Forgot password?</a>
      
      <div class="register-link">
        Don't have an account? <a href="register.php">Sign up</a>
      </div>
    </form>
  </div>
</body>
</html>