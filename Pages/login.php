<?php
session_start();

// Database configuration (It's better to keep this in setting.php, but for self-contained example)
define('DB_HOST', 'localhost:3310');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mathology');

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
            // Create connection
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Check connection
            if ($conn->connect_error) {
                error_log("Connection failed: " . $conn->connect_error);
                throw new Exception("Database connection error: " . $conn->connect_error);
            }

            // Fetch user from database
            $stmt = $conn->prepare("SELECT user_id, email, password, role, name FROM users WHERE email = ?");
            if (!$stmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                throw new Exception("Database query error (prepare): " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                throw new Exception("Database query error (execute): " . $stmt->error);
            }

            $result = $stmt->get_result();
            if (!$result) {
                error_log("Get result failed: (" . $stmt->errno . ") " . $stmt->error);
                throw new Exception("Database query error (get_result): " . $stmt->error);
            }

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

                    // Fetch related IDs (instructor_id or student_id) based on role
                    if ($user['role'] === 'instructor') {
                        $stmt = $conn->prepare("SELECT instructor_id FROM instructor WHERE instructor_id = ? LIMIT 1"); // Assuming instructor_id is the PK
                        if (!$stmt) {
                            error_log("Prepare failed (instructor_id): (" . $conn->errno . ") " . $conn->error);
                            throw new Exception("Database query error (prepare - instructor_id): " . $conn->error);
                        }
                        $stmt->bind_param("i", $user['related_id']); // Use related_id from users table
                        if (!$stmt->execute()) {
                            error_log("Execute failed (instructor_id): (" . $stmt->errno . ") " . $stmt->error);
                            throw new Exception("Database query error (execute - instructor_id): " . $stmt->error);
                        }
                        $instructor_result = $stmt->get_result();
                        if (!$instructor_result) {
                            error_log("Get result failed (instructor_id): (" . $stmt->errno . ") " . $stmt->error);
                            throw new Exception("Database query error (get_result - instructor_id): " . $stmt->error);
                        }
                        if ($instructor_result->num_rows === 1) {
                            $instructor = $instructor_result->fetch_assoc();
                            $_SESSION['instructor_id'] = $instructor['instructor_id'];
                        }
                    } elseif ($user['role'] === 'student') {
                        $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? LIMIT 1"); // Assuming student_id is the PK
                        if (!$stmt) {
                            error_log("Prepare failed (student_id): (" . $conn->errno . ") " . $conn->error);
                            throw new Exception("Database query error (prepare - student_id): " . $conn->error);
                        }
                        $stmt->bind_param("i", $user['related_id']); // Use related_id from users table
                        if (!$stmt->execute()) {
                            error_log("Execute failed (student_id): (" . $stmt->errno . ") " . $stmt->error);
                            throw new Exception("Database query error (execute - student_id): " . $stmt->error);
                        }
                        $student_result = $stmt->get_result();
                        if (!$student_result) {
                            error_log("Get result failed (student_id): (" . $stmt->errno . ") " . $stmt->error);
                            throw new Exception("Database query error (get_result - student_id): " . $stmt->error);
                        }
                        if ($student_result->num_rows === 1) {
                            $student = $student_result->fetch_assoc();
                            $_SESSION['student_id'] = $student['student_id'];
                        }
                    }

                    // Set secure remember me cookie if checked
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = time() + (86400 * 30); // 30 days

                        // Store token in database
                        $updateStmt = $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE user_id = ?");
                        if (!$updateStmt) {
                            error_log("Prepare failed (remember_token): (" . $conn->errno . ") " . $conn->error);
                            throw new Exception("Database query error (prepare - remember_token): " . $conn->error);
                        }
                        $updateStmt->bind_param("ssi", $token, date('Y-m-d H:i:s', $expiry), $user['user_id']);
                        if (!$updateStmt->execute()) {
                            error_log("Execute failed (remember_token): (" . $updateStmt->errno . ") " . $updateStmt->error);
                            throw new Exception("Database query error (execute - remember_token): " . $conn->error);
                        }

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

            $conn->close(); // Close the connection
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again. ' . $e->getMessage();
            if (isset($conn)) {
                $conn->close(); // Ensure connection is closed on error
            }
        }
    }
}

// Handle remember me cookie
if (empty($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    try {
        // Create connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            error_log("Connection failed (remember me): " . $conn->connect_error);
            throw new Exception("Database connection error (remember me): " . $conn->connect_error);
        }

        $token = $_COOKIE['remember_me'];
        $stmt = $conn->prepare("SELECT user_id, email, role, name, related_id FROM users WHERE remember_token = ? AND token_expiry > NOW()"); // Include related_id
        if (!$stmt) {
            error_log("Prepare failed (remember me): (" . $conn->errno . ") " . $conn->error);
            throw new Exception("Database query error (prepare - remember me): " . $conn->error);
        }
        $stmt->bind_param("s", $token);
        if (!$stmt->execute()) {
            error_log("Execute failed (remember me): (" . $stmt->errno . ") " . $stmt->error);
            throw new Exception("Database query error (execute - remember me): " . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            error_log("Get result failed (remember me): (" . $stmt->errno . ") " . $stmt->error);
            throw new Exception("Database query error (get_result - remember me): " . $stmt->error);
        }

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Regenerate session ID
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // Fetch related IDs (instructor_id or student_id) based on role
            if ($user['role'] === 'instructor') {
                $stmt = $conn->prepare("SELECT instructor_id FROM instructor WHERE instructor_id = ? LIMIT 1"); // Assuming instructor_id is the PK
                if (!$stmt) {
                    error_log("Prepare failed (remember me - instructor_id): (" . $conn->errno . ") " . $conn->error);
                    throw new Exception("Database query error (prepare - remember me - instructor_id): " . $conn->error);
                }
                $stmt->bind_param("i", $user['related_id']);  // Use related_id
                if (!$stmt->execute()) {
                    error_log("Execute failed (remember me - instructor_id): (" . $stmt->errno . ") " . $stmt->error);
                    throw new Exception("Database query error (execute - remember me - instructor_id): " . $conn->error);
                }
                $instructor_result = $stmt->get_result();
                if (!$instructor_result) {
                    error_log("Get result failed (remember me - instructor_id): (" . $stmt->errno . ") " . $stmt->error);
                    throw new Exception("Database query error (get_result - remember me - instructor_id): " . $conn->error);
                }
                if ($instructor_result->num_rows === 1) {
                    $instructor = $instructor_result->fetch_assoc();
                    $_SESSION['instructor_id'] = $instructor['instructor_id'];
                }
            } elseif ($user['role'] === 'student') {
                $stmt = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? LIMIT 1");  // Assuming student_id is the PK
                if (!$stmt) {
                    error_log("Prepare failed (remember me - student_id): (" . $conn->errno . ") " . $conn->error);
                    throw new Exception("Database query error (prepare - remember me - student_id): " . $conn->error);
                }
                $stmt->bind_param("i", $user['related_id']); // Use related_id
                if (!$stmt->execute()) {
                    error_log("Execute failed (remember me - student_id): (" . $stmt->errno . ") " . $stmt->error);
                    throw new Exception("Database query error (execute - remember me - student_id): " . $conn->error);
                }
                $student_result = $stmt->get_result();
                if (!$student_result) {
                    error_log("Get result failed (remember me - student_id): (" . $stmt->errno . ") " . $stmt->error);
                    throw new Exception("Database query error (get_result - remember me - student_id): " . $conn->error);
                }
                if ($student_result->num_rows === 1) {
                    $student = $student_result->fetch_assoc();
                    $_SESSION['student_id'] = $student['student_id'];
                }
            }

            header('Location: dashboard.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Remember me error: " . $e->getMessage());
        // Clear invalid cookie
        setcookie('remember_me', '', time() - 3600, '/');
    } finally {
        if (isset($conn)) {
            $conn->close(); // Ensure connection is closed
        }
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
                <path d="M20 5C11.716 5 5 11.716 5 20C5 28.284 11.716 35 20 35C28.284 35 35 28.284 35 20C35 11.716 28.284 5 20 5Z" fill="#4f46e5" />
                <path d="M12 20.5L17 25.5L28 14.5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h2>School Dashboard</h2>
        </div>

        <div class="form-header">
            <h2>Welcome back</h2>
            <p>Enter your credentials to access your account</p>
        </div>

        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
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
            <p>