<?php
/**
 * StudentHub Portal - Campus Events Page
 * Practicals 2, 4, 6, 8, 10
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

$message = '';
$msg_type = 'info';

// Handle Event Registration POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_event') {
    if (!is_logged_in()) {
        header("Location: login.php?msg=login_required");
        exit;
    }

    $event_id = intval($_POST['event_id'] ?? 0);
    $user = current_user();

    if ($event_id > 0 && $db_connected && $conn) {
        // Find student ID
        $s_stmt = mysqli_prepare($conn, "SELECT id FROM students WHERE user_id = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($s_stmt, "is", $user['id'], $user['email']);
        mysqli_stmt_execute($s_stmt);
        $s_res = mysqli_stmt_get_result($s_stmt);
        $student = mysqli_fetch_assoc($s_res);
        mysqli_stmt_close($s_stmt);

        if ($student) {
            $student_id = $student['id'];

            // Check if already registered
            $chk = mysqli_prepare($conn, "SELECT id FROM registrations WHERE student_id = ? AND event_id = ?");
            mysqli_stmt_bind_param($chk, "ii", $student_id, $event_id);
            mysqli_stmt_execute($chk);
            mysqli_stmt_store_result($chk);

            if (mysqli_stmt_num_rows($chk) > 0) {
                $message = "You are already registered for this event!";
                $msg_type = 'warning';
            } else {
                mysqli_stmt_close($chk);
                // Insert registration
                $ins = mysqli_prepare($conn, "INSERT INTO registrations (student_id, event_id, status) VALUES (?, ?, 'Confirmed')");
                mysqli_stmt_bind_param($ins, "ii", $student_id, $event_id);
                if (mysqli_stmt_execute($ins)) {
                    record_audit_log('EVENT_REGISTRATION', "Student #$student_id enrolled in Event #$event_id", $user['id'], $user['username']);
                    header("Location: dashboard.php?msg=registered_event");
                    exit;
                } else {
                    $message = "Registration failed: " . mysqli_error($conn);
                    $msg_type = 'danger';
                }
                mysqli_stmt_close($ins);
            }
        } else {
            $message = "Could not find an active student profile linked to your user account.";
            $msg_type = 'danger';
        }
    }
}

// Fetch events from DB or JSON
$search = sanitize_input($_GET['search'] ?? '');
$filter_category = sanitize_input($_GET['category'] ?? 'All');
$events_list = [];

if ($db_connected && $conn) {
    $query = "SELECT * FROM events WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $search_wild = "%{$search}%";
        $params[] = $search_wild;
        $params[] = $search_wild;
        $params[] = $search_wild;
        $types .= "sss";
    }

    if ($filter_category !== 'All' && !empty($filter_category)) {
        $query .= " AND category = ?";
        $params[] = $filter_category;
        $types .= "s";
    }

    $query .= " ORDER BY event_date ASC";

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $events_list[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $res = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $events_list[] = $row;
        }
    }
} else {
    // Fallback to JSON dataset (Practical 6)
    $json_content = @file_get_contents(__DIR__ . '/data/events.json');
    if ($json_content) {
        $events_list = json_decode($json_content, true) ?: [];
    }
}

$page_title = "Campus Events & Workshops";
$breadcrumbs = ["Home" => "index.php", "Events" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 24px;">
  <h1>Campus Events &amp; Workshops</h1>
  <p>Discover hackathons, seminars, sports competitions, and cultural celebrations taking place across our campus.</p>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $msg_type; ?>" role="alert">
    <?php echo htmlspecialchars($message); ?>
  </div>
<?php endif; ?>

<!-- Search & Filter Bar -->
<div class="card" style="margin-bottom: 24px; padding: 16px;">
  <form action="events.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
    <div style="flex: 2; min-width: 220px;">
      <label for="search" style="font-size: 0.85rem; font-weight: bold;">Search Events</label>
      <input type="text" id="search" name="search" class="form-control" placeholder="Search by title, topic, or venue..." value="<?php echo htmlspecialchars($search); ?>">
    </div>

    <div style="flex: 1; min-width: 160px;">
      <label for="category" style="font-size: 0.85rem; font-weight: bold;">Category</label>
      <select id="category" name="category" class="form-control">
        <option value="All" <?php echo $filter_category === 'All' ? 'selected' : ''; ?>>All Categories</option>
        <option value="Technical" <?php echo $filter_category === 'Technical' ? 'selected' : ''; ?>>Technical</option>
        <option value="Workshop" <?php echo $filter_category === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
        <option value="Sports" <?php echo $filter_category === 'Sports' ? 'selected' : ''; ?>>Sports</option>
        <option value="Cultural" <?php echo $filter_category === 'Cultural' ? 'selected' : ''; ?>>Cultural</option>
        <option value="Seminar" <?php echo $filter_category === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
        <option value="Business" <?php echo $filter_category === 'Business' ? 'selected' : ''; ?>>Business</option>
      </select>
    </div>

    <div>
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="events.php" class="btn btn-outline">Reset</a>
    </div>
  </form>
</div>

<!-- Events Grid (Practical 3 CSS Grid) -->
<?php if (empty($events_list)): ?>
  <div class="alert alert-info" style="text-align: center;">
    No events found matching your query. Try clearing the search or category filter.
  </div>
<?php else: ?>
  <div class="grid-3">
    <?php foreach ($events_list as $ev): ?>
      <?php
        $ev_id = $ev['id'];
        $ev_title = $ev['title'];
        $ev_cat = $ev['category'] ?? 'Campus';
        $ev_date = $ev['event_date'] ?? ($ev['date'] ?? 'TBD');
        $ev_loc = $ev['location'] ?? 'Campus';
        $ev_desc = $ev['description'] ?? '';
        $ev_status = $ev['status'] ?? 'Open';
        $ev_img = !empty($ev['poster_image']) ? $ev['poster_image'] : 'assets/images/default-event.svg';
      ?>
      <article class="card">
        <img src="<?php echo htmlspecialchars($ev_img); ?>" alt="<?php echo htmlspecialchars($ev_title); ?> poster" style="width: 100%; height: 160px; object-fit: cover; border-radius: var(--radius); margin-bottom: 12px; background: #e2e8f0;">
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
          <span class="badge badge-info"><?php echo htmlspecialchars($ev_cat); ?></span>
          <span class="badge <?php echo $ev_status === 'Open' ? 'badge-success' : 'badge-danger'; ?>"><?php echo htmlspecialchars($ev_status); ?></span>
        </div>

        <h3><?php echo htmlspecialchars($ev_title); ?></h3>
        <p style="font-size: 0.85rem; color: var(--primary-color); margin-bottom: 6px;">
          <strong>Date:</strong> <?php echo htmlspecialchars($ev_date); ?> | <strong>Venue:</strong> <?php echo htmlspecialchars($ev_loc); ?>
        </p>
        <p style="font-size: 0.9rem; line-height: 1.5;">
          <?php echo htmlspecialchars(mb_strimwidth($ev_desc, 0, 120, '...')); ?>
        </p>

        <div class="card-footer">
          <button class="btn btn-outline btn-sm" onclick="showEventModal(<?php echo htmlspecialchars(json_encode($ev)); ?>)">Details</button>

          <?php if ($ev_status === 'Open'): ?>
            <form action="events.php" method="POST" style="margin: 0;">
              <input type="hidden" name="action" value="register_event">
              <input type="hidden" name="event_id" value="<?php echo $ev_id; ?>">
              <button type="submit" class="btn btn-primary btn-sm">Register</button>
            </form>
          <?php else: ?>
            <span class="badge badge-danger">Registration Closed</span>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Event Details Modal (Practical 4) -->
<div id="event-detail-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-event-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="modal-event-title">Event Title</h3>
      <button class="btn-close" onclick="closeModal('event-detail-modal')" aria-label="Close dialog">&times;</button>
    </div>
    <div class="modal-body">
      <p id="modal-event-meta" style="color:var(--primary-color); font-weight:bold; margin-bottom: 10px;"></p>
      <p id="modal-event-desc" style="line-height:1.6;"></p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('event-detail-modal')">Close</button>
    </div>
  </div>
</div>

<script>
function showEventModal(ev) {
  document.getElementById('modal-event-title').textContent = ev.title;
  const dateStr = ev.event_date || ev.date || 'TBD';
  const locStr = ev.location || 'Campus';
  const catStr = ev.category || 'General';
  document.getElementById('modal-event-meta').textContent = `Category: ${catStr} | Date: ${dateStr} | Venue: ${locStr}`;
  document.getElementById('modal-event-desc').textContent = ev.description;
  openModal('event-detail-modal');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

