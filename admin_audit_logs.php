<?php
/**
 * StudentHub Portal - Audit Trail Viewer
 * Practical 14: System Audit Logging, Security Traceability, and Moderation
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

require_admin();

$filter_action = sanitize_input($_GET['action_filter'] ?? 'All');
$search = sanitize_input($_GET['search'] ?? '');
$logs = [];

if ($db_connected && $conn) {
    $sql = "SELECT * FROM audit_logs WHERE 1=1";
    $params = [];
    $types = "";

    if ($filter_action !== 'All' && !empty($filter_action)) {
        $sql .= " AND action = ?";
        $params[] = $filter_action;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
        $w = "%{$search}%";
        $params[] = $w;
        $params[] = $w;
        $params[] = $w;
        $types .= "sss";
    }

    $sql .= " ORDER BY created_at DESC LIMIT 100";

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $logs[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $res = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {
            $logs[] = $row;
        }
    }
}

$page_title = "Security Audit Trail";
$breadcrumbs = ["Home" => "index.php", "Admin Control" => "admin_dashboard.php", "Audit Logs" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 20px;">
  <h1>System Security &amp; Audit Trail</h1>
  <p>Practical 14 Specification: Complete traceability of authentication attempts, student record additions, event modifications, and errors.</p>
</div>

<!-- Search & Filter Card -->
<div class="card" style="margin-bottom: 20px; padding: 14px;">
  <form action="admin_audit_logs.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
    <div style="flex: 2; min-width: 220px;">
      <label for="search" style="font-size: 0.85rem; font-weight: bold;">Search Logs</label>
      <input type="text" id="search" name="search" class="form-control" placeholder="Search by username, IP, or details..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div style="flex: 1; min-width: 180px;">
      <label for="action_filter" style="font-size: 0.85rem; font-weight: bold;">Action Type</label>
      <select id="action_filter" name="action_filter" class="form-control">
        <option value="All" <?php echo $filter_action === 'All' ? 'selected' : ''; ?>>All Actions</option>
        <option value="USER_LOGIN" <?php echo $filter_action === 'USER_LOGIN' ? 'selected' : ''; ?>>USER_LOGIN</option>
        <option value="FAILED_LOGIN" <?php echo $filter_action === 'FAILED_LOGIN' ? 'selected' : ''; ?>>FAILED_LOGIN</option>
        <option value="USER_LOGOUT" <?php echo $filter_action === 'USER_LOGOUT' ? 'selected' : ''; ?>>USER_LOGOUT</option>
        <option value="USER_REGISTERED" <?php echo $filter_action === 'USER_REGISTERED' ? 'selected' : ''; ?>>USER_REGISTERED</option>
        <option value="STUDENT_ADDED" <?php echo $filter_action === 'STUDENT_ADDED' ? 'selected' : ''; ?>>STUDENT_ADDED</option>
        <option value="STUDENT_UPDATED" <?php echo $filter_action === 'STUDENT_UPDATED' ? 'selected' : ''; ?>>STUDENT_UPDATED</option>
        <option value="STUDENT_DELETED" <?php echo $filter_action === 'STUDENT_DELETED' ? 'selected' : ''; ?>>STUDENT_DELETED</option>
        <option value="EVENT_CREATED" <?php echo $filter_action === 'EVENT_CREATED' ? 'selected' : ''; ?>>EVENT_CREATED</option>
        <option value="EVENT_REGISTRATION" <?php echo $filter_action === 'EVENT_REGISTRATION' ? 'selected' : ''; ?>>EVENT_REGISTRATION</option>
      </select>
    </div>

    <div>
      <button type="submit" class="btn btn-primary">Filter Logs</button>
      <a href="admin_audit_logs.php" class="btn btn-outline">Reset</a>
    </div>
  </form>
</div>

<!-- Logs Table -->
<div class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
    <h3>Recorded System Logs (Showing up to 100 entries)</h3>
    <span class="badge badge-info"><?php echo count($logs); ?> Total</span>
  </div>

  <?php if (empty($logs)): ?>
    <div class="alert alert-info">No audit records match your query.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Log ID</th>
            <th>Timestamp</th>
            <th>Actor</th>
            <th>Action</th>
            <th>Details &amp; Parameters</th>
            <th>Client IP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td><code>#<?php echo $l['id']; ?></code></td>
              <td style="white-space:nowrap; font-size:0.8rem;"><?php echo htmlspecialchars($l['created_at']); ?></td>
              <td>
                <strong><?php echo htmlspecialchars($l['username']); ?></strong>
                <?php if ($l['user_id']): ?>
                  <small style="color:var(--secondary-color);">(#<?php echo $l['user_id']; ?>)</small>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-info"><?php echo htmlspecialchars($l['action']); ?></span></td>
              <td style="font-size:0.88rem;"><?php echo htmlspecialchars($l['details']); ?></td>
              <td><code style="font-size:0.8rem;"><?php echo htmlspecialchars($l['ip_address']); ?></code></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

