<?php
/**
 * StudentHub Portal - Logout Handler
 * Practical 10: Secure Logout, Session Destruction, Cookie Invalidation
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'User';

    // Clear remember_token from database if set
    if ($user_id && $db_connected && $conn) {
        $clear_tok = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
        mysqli_stmt_bind_param($clear_tok, "i", $user_id);
        mysqli_stmt_execute($clear_tok);
        mysqli_stmt_close($clear_tok);
    }

    // Log the logout action in audit log
    record_audit_log('USER_LOGOUT', "User signed out.", $user_id, $username);
}

// Unset all session variables
$_SESSION = [];

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Invalidate remember_token cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

header("Location: login.php?msg=logged_out");
exit;

