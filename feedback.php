<?php
/**
 * StudentHub Portal - Feedback Form
 * Practical 7: Form Processing with JSON File Storage & Server Validation
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $category = sanitize_input($_POST['category'] ?? '');
    $comments = sanitize_input($_POST['comments'] ?? '');

    // Server-side validation
    if (empty($name) || strlen($name) < 3) {
        $errors[] = "Please provide your name (at least 3 characters).";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Please select a rating between 1 and 5 stars.";
    }

    if (empty($category)) {
        $errors[] = "Please choose a feedback category.";
    }

    if (empty($comments) || strlen($comments) < 5) {
        $errors[] = "Comments must be at least 5 characters long.";
    }

    // JSON file writing (Practical 7)
    if (empty($errors)) {
        $json_file = __DIR__ . '/data/feedback.json';
        $current_data = [];

        if (file_exists($json_file)) {
            $json_str = file_get_contents($json_file);
            $current_data = json_decode($json_str, true) ?: [];
        }

        $new_entry = [
            'id' => count($current_data) + 1,
            'name' => $name,
            'email' => $email,
            'rating' => $rating,
            'category' => $category,
            'comments' => $comments,
            'submitted_at' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];

        $current_data[] = $new_entry;

        if (file_put_contents($json_file, json_encode($current_data, JSON_PRETTY_PRINT))) {
            record_audit_log('FEEDBACK_SUBMITTED', "Feedback rating $rating submitted by $email");
            $success_msg = "Thank you! Your feedback has been stored in JSON format and submitted for review.";
            $_POST = [];
        } else {
            $errors[] = "Could not write to JSON data store. Please check folder write permissions.";
        }
    }
}

$page_title = "Portal Feedback";
$breadcrumbs = ["Home" => "index.php", "Feedback" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
  <div class="card">
    <h2>StudentHub Feedback</h2>
    <p>Help us improve! Share your experience regarding navigation speed, event registration, or design accessibility.</p>

    <?php if ($success_msg): ?>
      <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" role="alert">
        <ul style="padding-left: 20px;">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="feedback.php" method="POST">
      <div class="form-group">
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Tanmay Sharma" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. tanmay@studenthub.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category" class="form-control" required>
            <option value="">-- Select Category --</option>
            <option value="Portal Usability">Portal Usability</option>
            <option value="Mobile Experience">Mobile Experience</option>
            <option value="Event Registration Process">Event Registration Process</option>
            <option value="Theme & Contrast">Theme &amp; Contrast</option>
            <option value="Other Suggestion">Other Suggestion</option>
          </select>
        </div>

        <div class="form-group">
          <label for="rating">Satisfaction Rating</label>
          <select id="rating" name="rating" class="form-control" required>
            <option value="">-- Choose Rating --</option>
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Very Good</option>
            <option value="3">3 - Good</option>
            <option value="2">2 - Fair</option>
            <option value="1">1 - Needs Improvement</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="comments">Your Feedback / Review Comments</label>
        <textarea id="comments" name="comments" class="form-control" rows="4" placeholder="Tell us what you liked or how we can improve..." required><?php echo isset($_POST['comments']) ? htmlspecialchars($_POST['comments']) : ''; ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Feedback (JSON Storage)</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

