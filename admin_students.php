<?php
/**
 * StudentHub Portal - Student Management CRUD
 * Practical 11: Add, View, Edit, Delete, Search, and Filter Student Records with MySQLi
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

require_admin();

$user = current_user();
$message = '';
$msg_type = 'info';

// 1. Handle Delete Operation (with confirmation)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_del = intval($_GET['id']);
    if ($db_connected && $conn) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
        mysqli_stmt_bind_param($del_stmt, "i", $id_to_del);
        if (mysqli_stmt_execute($del_stmt)) {
            record_audit_log('STUDENT_DELETED', "Admin deleted student record ID: $id_to_del", $user['id'], $user['username']);
            $message = "Student record #{$id_to_del} was deleted successfully.";
            $msg_type = 'success';
        } else {
            $message = "Could not delete student: " . mysqli_error($conn);
            $msg_type = 'danger';
        }
        mysqli_stmt_close($del_stmt);
    }
}

// 2. Handle Status Toggle (Soft Delete / Status Change - Practical 11 Advanced)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $id_to_toggle = intval($_GET['id']);
    if ($db_connected && $conn) {
        $toggle_stmt = mysqli_prepare($conn, "UPDATE students SET status = IF(status='Active','Inactive','Active') WHERE id = ?");
        mysqli_stmt_bind_param($toggle_stmt, "i", $id_to_toggle);
        if (mysqli_stmt_execute($toggle_stmt)) {
            record_audit_log('STUDENT_STATUS_TOGGLED', "Toggled status for student ID: $id_to_toggle", $user['id'], $user['username']);
            $message = "Student status updated.";
            $msg_type = 'success';
        }
        mysqli_stmt_close($toggle_stmt);
    }
}

// 3. Handle Add / Edit Student POST
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    if ($db_connected && $conn) {
        $e_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($e_stmt, "i", $edit_id);
        mysqli_stmt_execute($e_stmt);
        $res = mysqli_stmt_get_result($e_stmt);
        $edit_student = mysqli_fetch_assoc($res);
        mysqli_stmt_close($e_stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $year = sanitize_input($_POST['year'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? 'Male');
    $status = sanitize_input($_POST['status'] ?? 'Active');

    if (empty($full_name) || empty($email) || empty($phone) || empty($course)) {
        $message = "Please fill in all required fields.";
        $msg_type = 'danger';
    } else {
        if ($db_connected && $conn) {
            if ($student_id > 0) {
                // UPDATE Operation (Practical 11)
                $up_sql = "UPDATE students SET full_name = ?, email = ?, phone = ?, course = ?, year = ?, gender = ?, status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $up_sql);
                mysqli_stmt_bind_param($stmt, "sssssssi", $full_name, $email, $phone, $course, $year, $gender, $status, $student_id);
                if (mysqli_stmt_execute($stmt)) {
                    record_audit_log('STUDENT_UPDATED', "Updated student record for $email", $user['id'], $user['username']);
                    $message = "Student record updated successfully!";
                    $msg_type = 'success';
                    $edit_student = null; // Exit edit mode
                } else {
                    $message = "Update failed: " . mysqli_error($conn);
                    $msg_type = 'danger';
                }
                mysqli_stmt_close($stmt);
            } else {
                // INSERT Operation (Practical 11)
                $student_code = 'STU' . date('Y') . str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT);
                $ins_sql = "INSERT INTO students (student_id_code, full_name, email, phone, course, year, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $ins_sql);
                mysqli_stmt_bind_param($stmt, "ssssssss", $student_code, $full_name, $email, $phone, $course, $year, $gender, $status);
                if (mysqli_stmt_execute($stmt)) {
                    record_audit_log('STUDENT_ADDED', "Added new student $full_name ($student_code)", $user['id'], $user['username']);
                    $message = "New student #{$student_code} created successfully!";
                    $msg_type = 'success';
                } else {
                    $message = "Failed to add student (Check duplicate email): " . mysqli_error($conn);
                    $msg_type = 'danger';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 4. Search and Filter Query Execution (Practical 11)
$search = sanitize_input($_GET['search'] ?? '');
$filter_course = sanitize_input($_GET['course'] ?? 'All');
$students_list = [];

if ($db_connected && $conn) {
    $q = "SELECT * FROM students WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $q .= " AND (full_name LIKE ? OR email LIKE ? OR student_id_code LIKE ?)";
        $wild = "%{$search}%";
        $params[] = $wild;
        $params[] = $wild;
        $params[] = $wild;
        $types .= "sss";
    }

    if ($filter_course !== 'All' && !empty($filter_course)) {
        $q .= " AND course = ?";
        $params[] = $filter_course;
        $types .= "s";
    }

    $q .= " ORDER BY id DESC";

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $q);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $students_list[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $res = mysqli_query($conn, $q);
        while ($row = mysqli_fetch_assoc($res)) {
            $students_list[] = $row;
        }
    }
}

$page_title = "Student Directory & CRUD";
$breadcrumbs = ["Home" => "index.php", "Admin Control" => "admin_dashboard.php", "Students" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 20px;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1>Student Directory Management</h1>
      <p>Perform complete CRUD operations: add, search, filter, update, soft-delete, and export student records.</p>
    </div>
    <div>
      <a href="export_students.php" class="btn btn-primary btn-sm">Export All to CSV</a>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $msg_type; ?>" role="alert">
    <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<!-- Add or Edit Student Form (Practical 11) -->
<div class="card" style="margin-bottom: 24px;">
  <h3><?php echo $edit_student ? "Edit Student Record (#{$edit_student['student_id_code']})" : "Add New Student Record"; ?></h3>
  <form action="admin_students.php" method="POST" style="margin-top: 12px;">
    <input type="hidden" name="student_id" value="<?php echo $edit_student ? $edit_student['id'] : 0; ?>">

    <div class="grid-3">
      <div class="form-group">
        <label for="full_name">Full Name *</label>
        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Tanmay Sharma" value="<?php echo $edit_student ? htmlspecialchars($edit_student['full_name']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="student@studenthub.com" value="<?php echo $edit_student ? htmlspecialchars($edit_student['email']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number *</label>
        <input type="tel" id="phone" name="phone" class="form-control" placeholder="9876543210" maxlength="10" value="<?php echo $edit_student ? htmlspecialchars($edit_student['phone']) : ''; ?>" required>
      </div>
    </div>

    <div class="grid-4">
      <div class="form-group">
        <label for="course">Degree Course *</label>
        <select id="course" name="course" class="form-control" required>
          <option value="B.Tech CSE" <?php echo ($edit_student && $edit_student['course'] === 'B.Tech CSE') ? 'selected' : ''; ?>>B.Tech CSE</option>
          <option value="B.Tech IT" <?php echo ($edit_student && $edit_student['course'] === 'B.Tech IT') ? 'selected' : ''; ?>>B.Tech IT</option>
          <option value="B.Tech AI" <?php echo ($edit_student && $edit_student['course'] === 'B.Tech AI') ? 'selected' : ''; ?>>B.Tech AI</option>
          <option value="BCA" <?php echo ($edit_student && $edit_student['course'] === 'BCA') ? 'selected' : ''; ?>>BCA</option>
          <option value="MCA" <?php echo ($edit_student && $edit_student['course'] === 'MCA') ? 'selected' : ''; ?>>MCA</option>
        </select>
      </div>

      <div class="form-group">
        <label for="year">Year *</label>
        <select id="year" name="year" class="form-control" required>
          <option value="1st Year" <?php echo ($edit_student && $edit_student['year'] === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
          <option value="2nd Year" <?php echo ($edit_student && $edit_student['year'] === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
          <option value="3rd Year" <?php echo ($edit_student && $edit_student['year'] === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
          <option value="4th Year" <?php echo ($edit_student && $edit_student['year'] === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
        </select>
      </div>

      <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" class="form-control">
          <option value="Male" <?php echo ($edit_student && $edit_student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo ($edit_student && $edit_student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
          <option value="Other" <?php echo ($edit_student && $edit_student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="status">Account Status</label>
        <select id="status" name="status" class="form-control">
          <option value="Active" <?php echo ($edit_student && $edit_student['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
          <option value="Inactive" <?php echo ($edit_student && $edit_student['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
        </select>
      </div>
    </div>

    <div style="display: flex; gap: 10px;">
      <button type="submit" class="btn btn-primary"><?php echo $edit_student ? "Update Student" : "Save Student"; ?></button>
      <?php if ($edit_student): ?>
        <a href="admin_students.php" class="btn btn-outline">Cancel Edit</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Search & Course Filter Controls (Practical 11) -->
<div class="card" style="margin-bottom: 20px; padding: 14px;">
  <form action="admin_students.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
    <div style="flex: 2; min-width: 220px;">
      <label for="search" style="font-size: 0.85rem; font-weight: bold;">Search Student Records</label>
      <input type="text" id="search" name="search" class="form-control" placeholder="Search by name, email, or Student ID..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div style="flex: 1; min-width: 160px;">
      <label for="course-filter" style="font-size: 0.85rem; font-weight: bold;">Filter Course</label>
      <select id="course-filter" name="course" class="form-control">
        <option value="All" <?php echo $filter_course === 'All' ? 'selected' : ''; ?>>All Courses</option>
        <option value="B.Tech CSE" <?php echo $filter_course === 'B.Tech CSE' ? 'selected' : ''; ?>>B.Tech CSE</option>
        <option value="B.Tech IT" <?php echo $filter_course === 'B.Tech IT' ? 'selected' : ''; ?>>B.Tech IT</option>
        <option value="B.Tech AI" <?php echo $filter_course === 'B.Tech AI' ? 'selected' : ''; ?>>B.Tech AI</option>
        <option value="BCA" <?php echo $filter_course === 'BCA' ? 'selected' : ''; ?>>BCA</option>
        <option value="MCA" <?php echo $filter_course === 'MCA' ? 'selected' : ''; ?>>MCA</option>
      </select>
    </div>

    <div>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="admin_students.php" class="btn btn-outline">Reset</a>
    </div>
  </form>
</div>

<!-- Students Records Table -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
    <h3>Students List (Total: <?php echo count($students_list); ?> records)</h3>
  </div>

  <?php if (empty($students_list)): ?>
    <div class="alert alert-info">No student records found matching your criteria.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Course &amp; Year</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students_list as $s): ?>
            <tr>
              <td><code><?php echo htmlspecialchars($s['student_id_code']); ?></code></td>
              <td><strong><?php echo htmlspecialchars($s['full_name']); ?></strong></td>
              <td><?php echo htmlspecialchars($s['email']); ?></td>
              <td><?php echo htmlspecialchars($s['phone']); ?></td>
              <td><?php echo htmlspecialchars($s['course'] . ' (' . $s['year'] . ')'); ?></td>
              <td>
                <a href="admin_students.php?action=toggle_status&id=<?php echo $s['id']; ?>" title="Click to toggle status" style="text-decoration:none;">
                  <span class="badge <?php echo $s['status'] === 'Active' ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo htmlspecialchars($s['status']); ?> (Toggle)
                  </span>
                </a>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <a href="admin_students.php?edit_id=<?php echo $s['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                  <!-- Practical 11: Confirmation Dialog before delete -->
                  <a href="admin_students.php?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete student <?php echo addslashes($s['full_name']); ?>?');">Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

