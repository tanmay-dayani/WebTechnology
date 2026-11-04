<?php
/**
 * StudentHub Portal - Admin Dashboard
 * Practical 14: Admin Dashboard with RBAC, Dynamic Menu, SQL Aggregation, and Audit Logs
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

// Practical 14: Restrict access to admin users only
require_admin();

$total_students = 0;
$total_users = 0;
$total_events = 0;
$total_registrations = 0;
$recent_registrations = [];
$recent_audit_logs = [];

if ($db_connected && $conn) {
    // 1. Dynamic Metric Aggregations
    $r1 = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM students");
    if ($r1) $total_students = mysqli_fetch_assoc($r1)['cnt'];

    $r2 = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM users");
    if ($r2) $total_users = mysqli_fetch_assoc($r2)['cnt'];

    $r3 = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM events WHERE status = 'Open'");
    if ($r3) $total_events = mysqli_fetch_assoc($r3)['cnt'];

    $r4 = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM registrations");
    if ($r4) $total_registrations = mysqli_fetch_assoc($r4)['cnt'];

    // 2. Recent Registrations
    $reg_query = "SELECT r.id, s.full_name, s.course, s.student_id_code, e.title AS event_title, r.registered_at, r.status
                  FROM registrations r
                  JOIN students s ON r.student_id = s.id
                  JOIN events e ON r.event_id = e.id
                  ORDER BY r.registered_at DESC
                  LIMIT 6";
    $reg_res = mysqli_query($conn, $reg_query);
    if ($reg_res) {
        while ($row = mysqli_fetch_assoc($reg_res)) {
            $recent_registrations[] = $row;
        }
    }

    // 3. Recent Audit Logs (Practical 14)
    $log_query = "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 8";
    $log_res = mysqli_query($conn, $log_query);
    if ($log_res) {
        while ($row = mysqli_fetch_assoc($log_res)) {
            $recent_audit_logs[] = $row;
        }
    }
}

$page_title = "Admin Control Dashboard";
$breadcrumbs = ["Home" => "index.php", "Admin Control" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 24px;">
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
      <h1>Administrative Control Center</h1>
      <p>System overview, live student counts, event participation metrics, and audit logs.</p>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="admin_students.php" class="btn btn-primary btn-sm">+ Manage Students</a>
      <a href="admin_events.php" class="btn btn-primary btn-sm">+ Create Event</a>
      <a href="export_students.php" class="btn btn-outline btn-sm">Export CSV</a>
    </div>
  </div>
</div>

<!-- Aggregated Metric Cards (Practical 14) -->
<div class="grid-4" style="margin-bottom: 24px;">
  <div class="stat-card">
    <div class="stat-number"><?php echo $total_students; ?></div>
    <div class="stat-label">Total Students</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?php echo $total_events; ?></div>
    <div class="stat-label">Active Events</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?php echo $total_registrations; ?></div>
    <div class="stat-label">Event Registrations</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?php echo $total_users; ?></div>
    <div class="stat-label">System Users</div>
  </div>
</div>

<div class="grid-2" style="align-items: start; margin-bottom: 24px;">
  <!-- Recent Event Registrations -->
  <div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
      <h3>Recent Event Registrations</h3>
      <a href="events.php" class="btn btn-outline btn-sm">All Events</a>
    </div>

    <?php if (empty($recent_registrations)): ?>
      <div class="alert alert-info">No registrations recorded yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Course</th>
              <th>Enrolled Event</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_registrations as $r): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($r['full_name']); ?></strong><br>
                  <small style="color:var(--secondary-color);"><?php echo htmlspecialchars($r['student_id_code']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($r['course']); ?></td>
                <td><?php echo htmlspecialchars($r['event_title']); ?></td>
                <td><span class="badge badge-success"><?php echo htmlspecialchars($r['status']); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Quick Admin Actions & Portal Status -->
  <div class="card">
    <h3>Quick Management Shortcuts</h3>
    <p>Direct navigation to administrative workflows:</p>
    
    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
      <a href="admin_students.php" class="btn btn-outline" style="text-align: left;">
        <strong>Student Directory &amp; CRUD:</strong> Add, edit, filter, or soft-delete student records
      </a>
      <a href="admin_events.php" class="btn btn-outline" style="text-align: left;">
        <strong>Event Management &amp; Posters:</strong> Publish events with poster upload validation
      </a>
      <a href="admin_submissions.php" class="btn btn-outline" style="text-align: left;">
        <strong>CSV &amp; JSON Storage:</strong> Inspect user contact tickets &amp; feedback reviews
      </a>
    </div>
  </div>
</div>

<!-- System Audit Logs Table (Practical 14) -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
    <h3>Recent System Audit Logs (Security Traceability)</h3>
    <a href="admin_audit_logs.php" class="btn btn-outline btn-sm">View Full Log Trail</a>
  </div>

  <?php if (empty($recent_audit_logs)): ?>
    <div class="alert alert-info">No audit logs available.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Username</th>
            <th>Action Code</th>
            <th>Details</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_audit_logs as $log): ?>
            <tr>
              <td style="white-space: nowrap; font-size: 0.8rem;"><?php echo htmlspecialchars($log['created_at']); ?></td>
              <td><span class="badge badge-info"><?php echo htmlspecialchars($log['username']); ?></span></td>
              <td><strong><?php echo htmlspecialchars($log['action']); ?></strong></td>
              <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($log['details']); ?></td>
              <td style="font-size: 0.8rem; color: var(--secondary-color);"><?php echo htmlspecialchars($log['ip_address']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

