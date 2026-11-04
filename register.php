<?php
/**
 * StudentHub Portal - Registration Page
 * Practical 5: Frontend Validation with Regular Expressions & Password Strength Meter
 * Practical 9: Secure Registration with MySQLi, Duplicate Email Check, and BCRYPT Hashing
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$success_msg = '';

// Handle Server-Side Form Submission (Practical 9)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $custom_user = sanitize_input($_POST['username'] ?? '');
    $email = strtolower(trim(sanitize_input($_POST['email'] ?? '')));
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $course = sanitize_input($_POST['course'] ?? '');
    $year = sanitize_input($_POST['year'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $terms = isset($_POST['terms']);

    // Server-Side Validations
    if (empty($full_name) || !preg_match("/^[a-zA-Z\s]{3,50}$/", $full_name)) {
        $errors[] = "Full Name must contain only letters (at least 3 characters).";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($phone) || !preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Phone number must be a valid 10-digit mobile number starting with 6-9.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($course)) {
        $errors[] = "Please select your course of study.";
    }

    if (empty($year)) {
        $errors[] = "Please select your academic year.";
    }

    if (empty($gender)) {
        $errors[] = "Please select your gender.";
    }

    if (!$terms) {
        $errors[] = "You must agree to the Terms and Conditions.";
    }

    // Database Duplicate Email and Username Check (Practical 9)
    if (empty($errors)) {
        if ($db_connected && $conn) {
            // Determine username
            if (!empty($custom_user)) {
                $username = preg_replace('/[^a-zA-Z0-9_]/', '', $custom_user);
            } else {
                $parts = explode('@', $email);
                $username = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[0]);
            }
            if (empty($username)) {
                $username = 'user' . rand(100, 999);
            }

            // Check if email already registered
            $check_sql = "SELECT id FROM users WHERE LOWER(email) = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "This email address is already registered. Please login instead.";
                mysqli_stmt_close($stmt);
            } else {
                mysqli_stmt_close($stmt);

                // Check username collision and suffix if needed
                $u_check = mysqli_prepare($conn, "SELECT id FROM users WHERE LOWER(username) = ? LIMIT 1");
                $lower_u = strtolower($username);
                mysqli_stmt_bind_param($u_check, "s", $lower_u);
                mysqli_stmt_execute($u_check);
                mysqli_stmt_store_result($u_check);
                if (mysqli_stmt_num_rows($u_check) > 0) {
                    $username .= '_' . rand(100, 999);
                }
                mysqli_stmt_close($u_check);

                // Hash Password using BCRYPT (Practical 9)
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insert into users table
                $insert_user = mysqli_prepare($conn, "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'student')");
                mysqli_stmt_bind_param($insert_user, "sss", $username, $email, $password_hash);
                
                if (mysqli_stmt_execute($insert_user)) {
                    $new_user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($insert_user);

                    // Generate Unique Student Identification Code
                    $student_code = 'STU' . date('Y') . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);

                    // Insert into students table
                    $insert_student = mysqli_prepare($conn, "INSERT INTO students (user_id, student_id_code, full_name, email, phone, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
                    mysqli_stmt_bind_param($insert_student, "isssssss", $new_user_id, $student_code, $full_name, $email, $phone, $course, $year, $gender);
                    mysqli_stmt_execute($insert_student);
                    mysqli_stmt_close($insert_student);

                    // Audit Log (Practical 14)
                    record_audit_log('USER_REGISTERED', "New student registered with email: $email and ID: $student_code", $new_user_id, $username);

                    $success_msg = "Account created successfully!<br><br>"
                                 . "You can now log in using any of the following credentials:<br>"
                                 . "• <strong>Student ID:</strong> " . htmlspecialchars($student_code) . "<br>"
                                 . "• <strong>Email:</strong> " . htmlspecialchars($email) . "<br>"
                                 . "• <strong>Username:</strong> " . htmlspecialchars($username);
                } else {
                    $errors[] = "Database insertion error: " . mysqli_error($conn);
                }
            }
        } else {
            $errors[] = "Database connection offline. Please ensure MySQL is started in XAMPP.";
        }
    }
}

$page_title = "Student Registration";
$breadcrumbs = ["Home" => "index.php", "Register" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 650px; margin: 0 auto;">
  <div class="card">
    <h2>Student Registration</h2>
    <p>Create your verified StudentHub account to register for workshops, manage campus activities, and access academic resources.</p>

    <?php if (!empty($success_msg)): ?>
      <div class="alert alert-success" role="alert">
        <strong>Registration Complete!</strong>
        <div style="margin-top: 8px; line-height: 1.6;"><?php echo $success_msg; ?></div>
        <div style="margin-top: 14px;">
          <a href="login.php" class="btn btn-primary btn-sm">Proceed to Login</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" role="alert">
        <strong>Please correct the following errors:</strong>
        <ul style="padding-left: 20px; margin-top: 5px;">
          <?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Registration Form (Practical 5) -->
    <form id="registration-form" action="register.php" method="POST" novalidate>
      <div class="grid-2">
        <div class="form-group">
          <label for="full_name">Full Name <span style="color:var(--danger-color)">*</span></label>
          <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Tanmay Sharma" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
          <small class="error-msg" id="name-error"></small>
        </div>

        <div class="form-group">
          <label for="username">Choose Username <span style="font-weight:normal; font-size:0.85rem;">(optional)</span></label>
          <input type="text" id="username" name="username" class="form-control" placeholder="e.g. tanmay26 (defaults to email prefix)" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="email">Campus / Personal Email <span style="color:var(--danger-color)">*</span></label>
          <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
          <small class="error-msg" id="email-error"></small>
        </div>

        <div class="form-group">
          <label for="phone">Mobile Number (10 Digits) <span style="color:var(--danger-color)">*</span></label>
          <input type="tel" id="phone" name="phone" class="form-control" placeholder="9876543210" maxlength="10" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
          <small class="error-msg" id="phone-error"></small>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="password">Password <span style="color:var(--danger-color)">*</span></label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
          <div class="password-meter-wrap">
            <div id="password-meter-bar" class="password-meter-bar"></div>
            <div id="password-meter-text" class="password-meter-text"></div>
          </div>
          <small class="error-msg" id="password-error"></small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password <span style="color:var(--danger-color)">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-type password" required>
          <small class="error-msg" id="confirm-error"></small>
        </div>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="course">Course / Degree <span style="color:var(--danger-color)">*</span></label>
          <select id="course" name="course" class="form-control" required>
            <option value="">-- Select Degree --</option>
            <option value="B.Tech CSE" <?php echo (isset($_POST['course']) && $_POST['course'] === 'B.Tech CSE') ? 'selected' : ''; ?>>B.Tech Computer Science</option>
            <option value="B.Tech IT" <?php echo (isset($_POST['course']) && $_POST['course'] === 'B.Tech IT') ? 'selected' : ''; ?>>B.Tech Information Tech</option>
            <option value="B.Tech AI" <?php echo (isset($_POST['course']) && $_POST['course'] === 'B.Tech AI') ? 'selected' : ''; ?>>B.Tech AI &amp; Data Science</option>
            <option value="BCA" <?php echo (isset($_POST['course']) && $_POST['course'] === 'BCA') ? 'selected' : ''; ?>>BCA (Computer Applications)</option>
            <option value="MCA" <?php echo (isset($_POST['course']) && $_POST['course'] === 'MCA') ? 'selected' : ''; ?>>MCA (Master of Computer App)</option>
          </select>
          <small class="error-msg" id="course-error"></small>
        </div>

        <div class="form-group">
          <label for="year">Academic Year <span style="color:var(--danger-color)">*</span></label>
          <select id="year" name="year" class="form-control" required>
            <option value="">-- Select Year --</option>
            <option value="1st Year" <?php echo (isset($_POST['year']) && $_POST['year'] === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
            <option value="2nd Year" <?php echo (isset($_POST['year']) && $_POST['year'] === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
            <option value="3rd Year" <?php echo (isset($_POST['year']) && $_POST['year'] === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
            <option value="4th Year" <?php echo (isset($_POST['year']) && $_POST['year'] === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
          </select>
          <small class="error-msg" id="year-error"></small>
        </div>
      </div>

      <fieldset style="border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius); margin-bottom: 14px;">
        <legend style="padding: 0 6px; font-weight: bold; font-size: 0.9rem;">Gender <span style="color:var(--danger-color)">*</span></legend>
        <div style="display: flex; gap: 20px; align-items: center;">
          <label><input type="radio" name="gender" value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'checked' : ''; ?>> Male</label>
          <label><input type="radio" name="gender" value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'checked' : ''; ?>> Female</label>
          <label><input type="radio" name="gender" value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'checked' : ''; ?>> Other</label>
        </div>
        <small class="error-msg" id="gender-error"></small>
      </fieldset>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
          <input type="checkbox" id="terms" name="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
          I agree to the StudentHub Code of Conduct and Terms of Service.
        </label>
        <small class="error-msg" id="terms-error"></small>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
    </form>

    <div style="margin-top: 16px; text-align: center; font-size: 0.9rem;">
      Already registered? <a href="login.php" style="color:var(--primary-color);">Sign in to your account</a>
    </div>
  </div>
</div>

<!-- Practical 5 Form Validation Script -->
<script src="assets/js/validation.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

