<?php
/**
 * StudentHub Portal - Login Page
 * Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error_msg = '';
$info_msg = '';

// Check query messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $info_msg = "You have been logged out securely.";
    } elseif ($_GET['msg'] === 'timeout') {
        $error_msg = "Your session expired due to inactivity (15 minutes). Please sign in again.";
    } elseif ($_GET['msg'] === 'login_required') {
        $error_msg = "Please log in to access this page.";
    } elseif ($_GET['msg'] === 'access_denied') {
        $error_msg = "Access restricted: You do not have permission to view that resource.";
    }
}

// Handle Login POST Submission (Practical 10)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = sanitize_input($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);

    if (empty($identifier) || empty($password)) {
        $error_msg = "Please enter your username/email and password.";
    } else {
        if ($db_connected && $conn) {
            // Find user by username, email, student_id_code, or full name
            $clean_id = strtolower(trim($identifier));
            $sql = "SELECT u.id, u.username, u.email, u.password_hash, u.role 
                    FROM users u 
                    LEFT JOIN students s ON (s.user_id = u.id OR LOWER(s.email) = LOWER(u.email))
                    WHERE LOWER(u.username) = ? 
                       OR LOWER(u.email) = ? 
                       OR LOWER(s.student_id_code) = ? 
                       OR LOWER(s.full_name) = ?
                    LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $clean_id, $clean_id, $clean_id, $clean_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                // Verify password against BCRYPT hash (Practical 10)
                if (password_verify($password, $user['password_hash'])) {
                    // Security: Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    // Intermediate Extension: Update last_login timestamp
                    $update_login = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
                    mysqli_stmt_bind_param($update_login, "i", $user['id']);
                    mysqli_stmt_execute($update_login);
                    mysqli_stmt_close($update_login);

                    // Advanced Extension: Remember-Me Random Token
                    if ($remember) {
                        $random_token = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $random_token);

                        $update_tok = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
                        mysqli_stmt_bind_param($update_tok, "si", $token_hash, $user['id']);
                        mysqli_stmt_execute($update_tok);
                        mysqli_stmt_close($update_tok);

                        // Set cookie for 14 days with HttpOnly flag
                        setcookie('remember_token', $token_hash, [
                            'expires' => time() + (86400 * 14),
                            'path' => '/',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    // Audit Log Login
                    record_audit_log('USER_LOGIN', "Successful login as role: {$user['role']}", $user['id'], $user['username']);

                    // Role-based redirection (Practical 10)
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                } else {
                    $error_msg = "Invalid credentials. Please verify your password.";
                    record_audit_log('FAILED_LOGIN', "Failed login attempt for identifier: $identifier");
                }
            } else {
                $error_msg = "User account not found. Please register.";
                record_audit_log('FAILED_LOGIN', "Non-existent user attempt: $identifier");
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Database connection error. Please ensure MySQL is running in XAMPP.";
        }
    }
}

$page_title = "Sign In";
$breadcrumbs = ["Home" => "index.php", "Login" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 480px; margin: 20px auto;">
  <div class="card">
    <h2>Student &amp; Admin Login</h2>
    <p>Sign in to access your dashboard, registered campus events, or administrative controls.</p>

    <?php if (!empty($info_msg)): ?>
      <div class="alert alert-info" role="alert">
        <?php echo htmlspecialchars($info_msg); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div class="form-group">
        <label for="identifier">Username, Email Address, or Student ID</label>
        <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Enter username, email, or Student ID (e.g. STU...)" value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>" required autofocus>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer;">
          <input type="checkbox" name="remember_me" value="1">
          Remember me on this browser (14 days)
        </label>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>

    <div style="margin-top: 16px; padding: 12px; background: rgba(0,0,0,0.03); border-radius: var(--radius); font-size: 0.85rem;">
      <strong>Demo Accounts:</strong>
      <ul style="padding-left: 18px; margin-top: 4px;">
        <li><strong>Admin:</strong> <code>admin</code> / <code>admin123</code></li>
        <li><strong>Student:</strong> <code>tanmay</code> / <code>student123</code></li>
      </ul>
    </div>

    <div style="margin-top: 16px; text-align: center; font-size: 0.9rem;">
      New student? <a href="register.php" style="color:var(--primary-color);">Create an account here</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

