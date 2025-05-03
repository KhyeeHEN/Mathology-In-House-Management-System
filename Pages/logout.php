<?php
// logout.php
session_start();

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Delete remember me cookie if it exists
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/', '', true, true);
}

// If you're using database-stored remember tokens, you should also:
require_once 'setting.php';
if (isset($_SESSION['user_id'])) {
    $conn->query("UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE user_id = ".$_SESSION['user_id']);
}

// Redirect to login page
header("Location: login.php");
exit;
?>