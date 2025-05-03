<?php
session_start();
require_once 'setting.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Initialize variables
$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        try {
            // Fetch user from database
            $stmt = $conn->prepare("SELECT user_id, email, password, role, name FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];
                    
                    // Set secure remember me cookie if checked
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (86400 * 30); // 30 days
                        
                        // Store token in database
                        $updateStmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE user_id = ?");
                        $updateStmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expiry), $user['user_id']);
                        $updateStmt->execute();
                        
                        setcookie(
                            'remember_me',
                            $token,
                            [
                                'expires' => $expiry,
                                'path' => '/',
                                'domain' => '',
                                'secure' => true,
                                'httponly' => true,
                                'samesite' => 'Strict'
                            ]
                        );
                    }
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again.';
        }
    }
}

// Handle remember me cookie
if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    try {
        $token = $_COOKIE['remember_me'];
        $stmt = $conn->prepare("SELECT user_id, email, role, name FROM users WHERE remember_token = ? AND token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            header('Location: dashboard.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Remember me error: " . $e->getMessage());
        // Clear invalid cookie
        setcookie('remember_me', '', time() - 3600, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Styles/login.css">
    <link rel="stylesheet" href="../Styles/common.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <svg class="logo" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 5C11.716 5 5 11.716 5 20C5 28.284 11.716 35 20 35C28.284 35 35 28.284 35 20C35 11.716 28.284 5 20 5Z" fill="#4f46e5"/>
                <path d="M12 20.5L17 25.5L28 14.5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h2>School Dashboard</h2>
        </div>
        
        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    <label for="remember">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn">Log In</button>
        </form>
        
        <div class="form-footer">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Client-side validation if needed
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>