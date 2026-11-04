<?php
/**
 * StudentHub Portal - Stored Submissions Inspector
 * Practical 7 Intermediate Extension: Display Stored CSV/JSON Records on a Webpage
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';

require_admin();

// 1. Read CSV Contact Inquiries
$csv_file = __DIR__ . '/data/contact_submissions.csv';
$csv_rows = [];
if (file_exists($csv_file)) {
    if (($handle = fopen($csv_file, 'r')) !== false) {
        $header = fgetcsv($handle); // First row header
        while (($row = fgetcsv($handle)) !== false) {
            $csv_rows[] = $row;
        }
        fclose($handle);
    }
}
$csv_rows = array_reverse($csv_rows); // Latest first

// 2. Read JSON Feedback Submissions
$json_file = __DIR__ . '/data/feedback.json';
$feedback_items = [];
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $feedback_items = json_decode($json_content, true) ?: [];
}
$feedback_items = array_reverse($feedback_items);

$page_title = "Inquiries & Feedback Submissions";
$breadcrumbs = ["Home" => "index.php", "Admin Control" => "admin_dashboard.php", "Submissions" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="hero" style="text-align: left; padding: 20px;">
  <h1>Stored Form Submissions (CSV &amp; JSON)</h1>
  <p>Practical 7 Implementation: Viewing file-based storage records submitted by users without SQL database involvement.</p>
</div>

<!-- Section 1: CSV Contact Inquiries (Practical 7) -->
<div class="card" style="margin-bottom: 24px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
    <h3>Contact Form Inquiries (<code>data/contact_submissions.csv</code>)</h3>
    <span class="badge badge-info"><?php echo count($csv_rows); ?> Inquiries</span>
  </div>

  <?php if (empty($csv_rows)): ?>
    <div class="alert alert-info">No contact inquiries found in CSV file.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Sender Name</th>
            <th>Email</th>
            <th>Category / Subject</th>
            <th>Message</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($csv_rows as $row): ?>
            <tr>
              <td style="white-space:nowrap; font-size:0.8rem;"><?php echo htmlspecialchars($row[0] ?? ''); ?></td>
              <td><strong><?php echo htmlspecialchars($row[1] ?? ''); ?></strong></td>
              <td><?php echo htmlspecialchars($row[2] ?? ''); ?></td>
              <td><span class="badge badge-info"><?php echo htmlspecialchars($row[3] ?? ''); ?></span></td>
              <td style="font-size:0.88rem;"><?php echo htmlspecialchars($row[4] ?? ''); ?></td>
              <td style="font-size:0.8rem; color:var(--secondary-color);"><?php echo htmlspecialchars($row[5] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Section 2: JSON Feedback Reviews (Practical 7) -->
<div class="card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
    <h3>User Feedback Reviews (<code>data/feedback.json</code>)</h3>
    <span class="badge badge-info"><?php echo count($feedback_items); ?> Reviews</span>
  </div>

  <?php if (empty($feedback_items)): ?>
    <div class="alert alert-info">No feedback entries found in JSON file.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Date &amp; Time</th>
            <th>Name</th>
            <th>Category</th>
            <th>Rating</th>
            <th>User Comments</th>
            <th>IP</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($feedback_items as $item): ?>
            <tr>
              <td style="white-space:nowrap; font-size:0.8rem;"><?php echo htmlspecialchars($item['submitted_at'] ?? ''); ?></td>
              <td><strong><?php echo htmlspecialchars($item['name'] ?? ''); ?></strong></td>
              <td><span class="badge badge-info"><?php echo htmlspecialchars($item['category'] ?? ''); ?></span></td>
              <td>
                <?php
                  $r = intval($item['rating'] ?? 5);
                  echo $r . ' / 5';
                ?>
              </td>
              <td style="font-size:0.88rem;"><?php echo htmlspecialchars($item['comments'] ?? ''); ?></td>
              <td style="font-size:0.8rem; color:var(--secondary-color);"><?php echo htmlspecialchars($item['ip_address'] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

