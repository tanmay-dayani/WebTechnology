<?php
/**
 * StudentHub Portal - Student Profile Management
 * Practicals 2, 8, 10
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

require_login();

$user = current_user();
$errors = [];
$success_msg = '';

// Fetch student profile
$student = null;
if ($db_connected && $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ? OR email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $user['email']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize_input($_POST['phone'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $year = sanitize_input($_POST['year'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');

    if (empty($phone) || !preg_match("/^[6-9]\d{9}$/", $phone)) {
        $errors[] = "Phone number must be a 10-digit number starting with 6-9.";
    }

    if (empty($course) || empty($year)) {
        $errors[] = "Course and academic year are required.";
    }

    if (empty($errors) && $student && $db_connected && $conn) {
        $up_sql = "UPDATE students SET phone = ?, course = ?, year = ?, gender = ? WHERE id = ?";
        $up_stmt = mysqli_prepare($conn, $up_sql);
        mysqli_stmt_bind_param($up_stmt, "ssssi", $phone, $course, $year, $gender, $student['id']);

        if (mysqli_stmt_execute($up_stmt)) {
            record_audit_log('PROFILE_UPDATE', "Student updated profile details.", $user['id'], $user['username']);
            $success_msg = "Profile updated successfully!";
            // Refresh local student record
            $student['phone'] = $phone;
            $student['course'] = $course;
            $student['year'] = $year;
            $student['gender'] = $gender;
        } else {
            $errors[] = "Failed to update profile: " . mysqli_error($conn);
        }
        mysqli_stmt_close($up_stmt);
    }
}

$page_title = "My Profile";
$breadcrumbs = ["Home" => "index.php", "Dashboard" => "dashboard.php", "Profile" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
  <div class="card">
    <h2>Student Profile</h2>
    <p>View and manage your academic and contact details stored in the campus directory.</p>

    <?php if ($success_msg): ?>
      <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" role="alert">
        <?php foreach ($errors as $e): ?>
          <div><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($student): ?>
      <form action="profile.php" method="POST">
        <div class="grid-2">
          <div class="form-group">
            <label>Student ID Code</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_id_code']); ?>" readonly style="background: rgba(0,0,0,0.05);">
          </div>
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" readonly style="background: rgba(0,0,0,0.05);">
          </div>
        </div>

        <div class="form-group">
          <label>Campus Email</label>
          <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" readonly style="background: rgba(0,0,0,0.05);">
        </div>

        <div class="form-group">
          <label for="phone">Phone / Mobile (Editable)</label>
          <input type="tel" id="phone" name="phone" class="form-control" maxlength="10" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
        </div>

        <div class="grid-2">
          <div class="form-group">
            <label for="course">Degree Course</label>
            <select id="course" name="course" class="form-control" required>
              <option value="B.Tech CSE" <?php echo $student['course'] === 'B.Tech CSE' ? 'selected' : ''; ?>>B.Tech Computer Science</option>
              <option value="B.Tech IT" <?php echo $student['course'] === 'B.Tech IT' ? 'selected' : ''; ?>>B.Tech Information Tech</option>
              <option value="B.Tech AI" <?php echo $student['course'] === 'B.Tech AI' ? 'selected' : ''; ?>>B.Tech AI &amp; Data Science</option>
              <option value="BCA" <?php echo $student['course'] === 'BCA' ? 'selected' : ''; ?>>BCA</option>
              <option value="MCA" <?php echo $student['course'] === 'MCA' ? 'selected' : ''; ?>>MCA</option>
            </select>
          </div>

          <div class="form-group">
            <label for="year">Year of Study</label>
            <select id="year" name="year" class="form-control" required>
              <option value="1st Year" <?php echo $student['year'] === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
              <option value="2nd Year" <?php echo $student['year'] === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
              <option value="3rd Year" <?php echo $student['year'] === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
              <option value="4th Year" <?php echo $student['year'] === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Gender</label>
          <div style="display: flex; gap: 20px; align-items: center; margin-top: 4px;">
            <label><input type="radio" name="gender" value="Male" <?php echo $student['gender'] === 'Male' ? 'checked' : ''; ?>> Male</label>
            <label><input type="radio" name="gender" value="Female" <?php echo $student['gender'] === 'Female' ? 'checked' : ''; ?>> Female</label>
            <label><input type="radio" name="gender" value="Other" <?php echo $student['gender'] === 'Other' ? 'checked' : ''; ?>> Other</label>
          </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
      </form>
    <?php else: ?>
      <div class="alert alert-warning">No student profile found for this user account.</div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

