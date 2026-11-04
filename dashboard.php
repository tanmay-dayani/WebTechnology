<?php
/**
 * StudentHub Portal - Student Dashboard
 * Practicals 2, 8, 10 Student Area & Enrolled Events
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

require_login();

$user = current_user();
$student_info = null;
$enrolled_events = [];
$status_msg = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'registered_event') {
        $status_msg = "Successfully registered for the event!";
    } elseif ($_GET['msg'] === 'profile_updated') {
        $status_msg = "Your profile has been updated.";
    } elseif ($_GET['msg'] === 'cancelled_event') {
        $status_msg = "Event registration was cancelled.";
    }
}

// Fetch student profile linked to this user
if ($db_connected && $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE user_id = ? OR email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "is", $user['id'], $user['email']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student_info = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    // Fetch enrolled events
    if ($student_info) {
        $q = "SELECT e.id, e.title, e.category, e.event_date, e.location, r.registered_at, r.status AS reg_status
              FROM registrations r
              JOIN events e ON r.event_id = e.id
              WHERE r.student_id = ?
              ORDER BY e.event_date ASC";
        $stmt2 = mysqli_prepare($conn, $q);
        mysqli_stmt_bind_param($stmt2, "i", $student_info['id']);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        while ($row = mysqli_fetch_assoc($res2)) {
            $enrolled_events[] = $row;
        }
        mysqli_stmt_close($stmt2);
    }
}

$page_title = "Student Dashboard";
$breadcrumbs = ["Home" => "index.php", "Dashboard" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 24px;">
  <h1>Welcome, <?php echo htmlspecialchars($student_info['full_name'] ?? $user['username']); ?></h1>
  <p>Here is your personalized campus hub. You can monitor your enrolled events, check academic details, and manage your account.</p>
  <?php if ($admin): ?>
    <div style="margin-top: 10px;">
      <a href="admin_dashboard.php" class="btn btn-primary btn-sm">Switch to Admin Control Panel</a>
    </div>
  <?php endif; ?>
</div>

<?php if ($status_msg): ?>
  <div class="alert alert-success" role="alert">
    <?php echo htmlspecialchars($status_msg); ?>
  </div>
<?php endif; ?>

<!-- Dashboard Summary Cards -->
<div class="grid-4" style="margin-bottom: 24px;">
  <div class="stat-card">
    <div class="stat-number"><?php echo count($enrolled_events); ?></div>
    <div class="stat-label">Enrolled Events</div>
  </div>
  <div class="stat-card">
    <div class="stat-number" style="font-size: 1.4rem;"><?php echo htmlspecialchars($student_info['student_id_code'] ?? 'N/A'); ?></div>
    <div class="stat-label">Student ID</div>
  </div>
  <div class="stat-card">
    <div class="stat-number" style="font-size: 1.4rem;"><?php echo htmlspecialchars($student_info['course'] ?? 'General'); ?></div>
    <div class="stat-label">Course Degree</div>
  </div>
  <div class="stat-card">
    <div class="stat-number" style="font-size: 1.4rem;"><?php echo htmlspecialchars($student_info['year'] ?? '1st Year'); ?></div>
    <div class="stat-label">Academic Year</div>
  </div>
</div>

<div class="grid-3" style="align-items: start;">
  <!-- Left 2 Columns: Enrolled Events Table -->
  <div style="grid-column: span 2;">
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h3>My Enrolled Events</h3>
        <a href="events.php" class="btn btn-primary btn-sm">Explore More Events</a>
      </div>

      <?php if (empty($enrolled_events)): ?>
        <div class="alert alert-info">
          You have not registered for any upcoming events yet. <a href="events.php">Browse campus events now</a>.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table>
            <thead>
              <tr>
                <th>Event Title</th>
                <th>Category</th>
                <th>Event Date</th>
                <th>Location</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($enrolled_events as $ev): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
                  <td><span class="badge badge-info"><?php echo htmlspecialchars($ev['category']); ?></span></td>
                  <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
                  <td><?php echo htmlspecialchars($ev['location']); ?></td>
                  <td><span class="badge badge-success"><?php echo htmlspecialchars($ev['reg_status']); ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right 1 Column: Profile & Actions Card -->
  <aside class="card">
    <h3>Student Profile</h3>
    <?php if ($student_info): ?>
      <p style="margin-bottom: 8px;"><strong>Name:</strong> <?php echo htmlspecialchars($student_info['full_name']); ?></p>
      <p style="margin-bottom: 8px;"><strong>Email:</strong> <?php echo htmlspecialchars($student_info['email']); ?></p>
      <p style="margin-bottom: 8px;"><strong>Phone:</strong> <?php echo htmlspecialchars($student_info['phone']); ?></p>
      <p style="margin-bottom: 8px;"><strong>Gender:</strong> <?php echo htmlspecialchars($student_info['gender']); ?></p>
      <p style="margin-bottom: 16px;"><strong>Status:</strong> <span class="badge badge-success"><?php echo htmlspecialchars($student_info['status']); ?></span></p>
      <a href="profile.php" class="btn btn-outline" style="width: 100%; text-align: center;">Edit Profile</a>
    <?php else: ?>
      <p>No student profile record linked to this account yet.</p>
      <a href="register.php" class="btn btn-primary btn-sm">Link Profile</a>
    <?php endif; ?>

    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 16px 0;">

    <h4>Need Help?</h4>
    <p style="font-size: 0.85rem;">Have questions about attendance, lab schedules, or event passes?</p>
    <div style="display: flex; gap: 8px; margin-top: 8px;">
      <a href="contact.php" class="btn btn-outline btn-sm">Contact Us</a>
      <a href="faq.php" class="btn btn-outline btn-sm">View FAQ</a>
    </div>
  </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

