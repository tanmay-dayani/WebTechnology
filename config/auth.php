<?php
/**
 * Authentication & Session Management Helper
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, Remember-Me
 */

if (session_status() === PHP_SESSION_NONE) {
    // Basic session hardening
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

require_once __DIR__ . '/db.php';

// Inactivity Timeout threshold (15 minutes = 900 seconds)
define('SESSION_TIMEOUT_SECONDS', 900);

/**
 * Handle Session Inactivity Timeout
 */
function handle_session_timeout() {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS)) {
            // Session expired due to inactivity
            session_unset();
            session_destroy();
            header("Location: login.php?msg=timeout");
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
handle_session_timeout();

/**
 * Handle Remember-Me Cookie Verification (Practical 10)
 */
function check_remember_me() {
    global $conn, $db_connected;
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && $db_connected) {
        $token = $_COOKIE['remember_token'];
        $sql = "SELECT id, username, email, role FROM users WHERE remember_token = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($user = mysqli_fetch_assoc($result)) {
                // Regenerate session ID and restore session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
            }
            mysqli_stmt_close($stmt);
        }
    }
}
check_remember_me();

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an administrator
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current authenticated user details
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'User',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'student'
    ];
}

/**
 * Require general login to access the page
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php?msg=login_required");
        exit;
    }
}

/**
 * Require admin privileges to access the page (RBAC)
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: dashboard.php?msg=access_denied");
        exit;
    }
}

/**
 * CSRF Token Helpers (Practical 7, 9 Extension)
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

