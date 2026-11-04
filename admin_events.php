<?php
/**
 * StudentHub Portal - Event Management CRUD with File Upload
 * Practical 12: Event Management CRUD, Poster Upload, File Type & Size Validation, Audit Trail
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

require_admin();

$user = current_user();
$message = '';
$msg_type = 'info';

// 1. Delete Event Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    if ($db_connected && $conn) {
        $del = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
        mysqli_stmt_bind_param($del, "i", $del_id);
        if (mysqli_stmt_execute($del)) {
            record_audit_log('EVENT_DELETED', "Admin deleted Event ID: $del_id", $user['id'], $user['username']);
            $message = "Event #{$del_id} deleted successfully.";
            $msg_type = 'success';
        } else {
            $message = "Delete failed: " . mysqli_error($conn);
            $msg_type = 'danger';
        }
        mysqli_stmt_close($del);
    }
}

// 2. Fetch Event for Editing
$edit_event = null;
if (isset($_GET['edit_id'])) {
    $e_id = intval($_GET['edit_id']);
    if ($db_connected && $conn) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $e_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $edit_event = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

// 3. Handle Add / Edit Event with Poster Upload (Practical 12)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = intval($_POST['event_id'] ?? 0);
    $title = sanitize_input($_POST['title'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $event_date = sanitize_input($_POST['event_date'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'Open');

    if (empty($title) || empty($event_date) || empty($location)) {
        $message = "Please fill in all required event details.";
        $msg_type = 'danger';
    } else {
        $poster_path = $edit_event['poster_image'] ?? 'assets/images/default-event.svg';

        // Poster File Upload & Validation (Practical 12)
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['poster']['tmp_name'];
            $file_size = $_FILES['poster']['size'];
            $file_name = $_FILES['poster']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $max_size = 2 * 1024 * 1024; // 2 Megabytes limit

            if (!in_array($file_ext, $allowed_exts)) {
                $message = "Invalid file type. Only JPG, PNG, and WEBP posters are allowed.";
                $msg_type = 'danger';
            } elseif ($file_size > $max_size) {
                $message = "Poster file size exceeds the 2MB limit.";
                $msg_type = 'danger';
            } else {
                // Generate safe sanitized unique filename (Advanced Extension)
                $new_file_name = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                $target_dir = __DIR__ . '/uploads/events/';
                if (!is_dir($target_dir)) {
                    @mkdir($target_dir, 0755, true);
                }
                $destination = $target_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    $poster_path = 'uploads/events/' . $new_file_name;
                } else {
                    $message = "Failed to save uploaded poster file.";
                    $msg_type = 'danger';
                }
            }
        }

        if (empty($message) && $db_connected && $conn) {
            if ($event_id > 0) {
                // Update Event (Practical 12)
                $up_sql = "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, description = ?, poster_image = ?, status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $up_sql);
                mysqli_stmt_bind_param($stmt, "sssssssi", $title, $category, $event_date, $location, $description, $poster_path, $status, $event_id);
                if (mysqli_stmt_execute($stmt)) {
                    record_audit_log('EVENT_UPDATED', "Updated event: $title (ID: $event_id)", $user['id'], $user['username']);
                    $message = "Event updated successfully!";
                    $msg_type = 'success';
                    $edit_event = null;
                } else {
                    $message = "Update failed: " . mysqli_error($conn);
                    $msg_type = 'danger';
                }
                mysqli_stmt_close($stmt);
            } else {
                // Insert New Event (Practical 12)
                $ins_sql = "INSERT INTO events (title, category, event_date, location, description, poster_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $ins_sql);
                mysqli_stmt_bind_param($stmt, "sssssss", $title, $category, $event_date, $location, $description, $poster_path, $status);
                if (mysqli_stmt_execute($stmt)) {
                    $new_id = mysqli_insert_id($conn);
                    record_audit_log('EVENT_CREATED', "Created new event: $title (ID: $new_id)", $user['id'], $user['username']);
                    $message = "New event published successfully!";
                    $msg_type = 'success';
                } else {
                    $message = "Failed to publish event: " . mysqli_error($conn);
                    $msg_type = 'danger';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// 4. Fetch All Events
$events = [];
if ($db_connected && $conn) {
    $res = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $events[] = $row;
        }
    }
}

$page_title = "Event Management CRUD";
$breadcrumbs = ["Home" => "index.php", "Admin Control" => "admin_dashboard.php", "Event Management" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 20px;">
  <div>
    <h1>Campus Event Management</h1>
    <p>Publish and update college events, upload posters with validation, and set registration statuses.</p>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $msg_type; ?>" role="alert">
    <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<!-- Create / Edit Event Form (Practical 12) -->
<div class="card" style="margin-bottom: 24px;">
  <h3><?php echo $edit_event ? "Edit Event: " . htmlspecialchars($edit_event['title']) : "Create New Campus Event"; ?></h3>
  
  <form action="admin_events.php" method="POST" enctype="multipart/form-data" style="margin-top: 12px;">
    <input type="hidden" name="event_id" value="<?php echo $edit_event ? $edit_event['id'] : 0; ?>">

    <div class="grid-2">
      <div class="form-group">
        <label for="title">Event Title *</label>
        <input type="text" id="title" name="title" class="form-control" placeholder="e.g. AI & Cloud Hackathon 2026" value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" name="category" class="form-control" required>
          <option value="Technical" <?php echo ($edit_event && $edit_event['category'] === 'Technical') ? 'selected' : ''; ?>>Technical</option>
          <option value="Workshop" <?php echo ($edit_event && $edit_event['category'] === 'Workshop') ? 'selected' : ''; ?>>Workshop</option>
          <option value="Sports" <?php echo ($edit_event && $edit_event['category'] === 'Sports') ? 'selected' : ''; ?>>Sports</option>
          <option value="Cultural" <?php echo ($edit_event && $edit_event['category'] === 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
          <option value="Seminar" <?php echo ($edit_event && $edit_event['category'] === 'Seminar') ? 'selected' : ''; ?>>Seminar</option>
          <option value="Business" <?php echo ($edit_event && $edit_event['category'] === 'Business') ? 'selected' : ''; ?>>Business</option>
          <option value="Social" <?php echo ($edit_event && $edit_event['category'] === 'Social') ? 'selected' : ''; ?>>Social</option>
        </select>
      </div>
    </div>

    <div class="grid-3">
      <div class="form-group">
        <label for="event_date">Event Date *</label>
        <input type="date" id="event_date" name="event_date" class="form-control" value="<?php echo $edit_event ? htmlspecialchars($edit_event['event_date']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="location">Venue / Location *</label>
        <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Campus Audi-1" value="<?php echo $edit_event ? htmlspecialchars($edit_event['location']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="status">Registration Status</label>
        <select id="status" name="status" class="form-control">
          <option value="Open" <?php echo ($edit_event && $edit_event['status'] === 'Open') ? 'selected' : ''; ?>>Open for Registration</option>
          <option value="Closed" <?php echo ($edit_event && $edit_event['status'] === 'Closed') ? 'selected' : ''; ?>>Closed / Archived</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="description">Event Description &amp; Instructions</label>
      <textarea id="description" name="description" class="form-control" rows="3" placeholder="Provide schedule, eligibility, guidelines..." required><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
    </div>

    <!-- Poster File Upload (Practical 12) -->
    <div class="form-group">
      <label for="poster">Event Poster Image (JPG, PNG, WEBP - Max 2MB)</label>
      <input type="file" id="poster" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
      <?php if ($edit_event && !empty($edit_event['poster_image'])): ?>
        <small style="color:var(--secondary-color); margin-top:4px;">Current Poster: <code><?php echo htmlspecialchars($edit_event['poster_image']); ?></code></small>
      <?php endif; ?>
    </div>

    <div style="display: flex; gap: 10px; margin-top: 8px;">
      <button type="submit" class="btn btn-primary"><?php echo $edit_event ? "Update Event" : "Publish Event"; ?></button>
      <?php if ($edit_event): ?>
        <a href="admin_events.php" class="btn btn-outline">Cancel Edit</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Events Table -->
<div class="card">
  <h3>Existing Campus Events (<?php echo count($events); ?>)</h3>

  <div class="table-responsive" style="margin-top: 12px;">
    <table>
      <thead>
        <tr>
          <th>Poster</th>
          <th>Title</th>
          <th>Category</th>
          <th>Event Date</th>
          <th>Location</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td>
              <img src="<?php echo htmlspecialchars($ev['poster_image'] ?: 'assets/images/default-event.svg'); ?>" alt="Poster" style="width: 48px; height: 36px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
            </td>
            <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
            <td><span class="badge badge-info"><?php echo htmlspecialchars($ev['category']); ?></span></td>
            <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
            <td><?php echo htmlspecialchars($ev['location']); ?></td>
            <td><span class="badge <?php echo $ev['status'] === 'Open' ? 'badge-success' : 'badge-danger'; ?>"><?php echo htmlspecialchars($ev['status']); ?></span></td>
            <td>
              <div style="display: flex; gap: 6px;">
                <a href="admin_events.php?edit_id=<?php echo $ev['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                <a href="admin_events.php?action=delete&id=<?php echo $ev['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Permanently remove event &quot;<?php echo addslashes($ev['title']); ?>&quot;?');">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

