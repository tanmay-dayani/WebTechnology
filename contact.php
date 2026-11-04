<?php
/**
 * StudentHub Portal - Contact Us Form
 * Practical 7: PHP Form Processing with Server-Side Validation,
 * Sanitization, CSRF Protection, and CSV File Storage
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/logger.php';

$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Token Verification (Practical 7 Advanced Extension)
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        $errors[] = "Invalid CSRF security token. Please refresh and try again.";
    }

    // 2. Input Sanitization (Practical 7)
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // 3. Server-side Validations
    if (empty($name) || strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }

    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    // 4. Safe CSV File Storage (Practical 7)
    if (empty($errors)) {
        $csv_file = __DIR__ . '/data/contact_submissions.csv';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $timestamp = date('Y-m-d H:i:s');

        // Check if file exists, if not write header
        $file_exists = file_exists($csv_file);
        $fp = @fopen($csv_file, 'a');

        if ($fp) {
            if (!$file_exists) {
                fputcsv($fp, ['timestamp', 'name', 'email', 'subject', 'message', 'ip_address']);
            }
            fputcsv($fp, [$timestamp, $name, $email, $subject, $message, $ip_address]);
            fclose($fp);

            record_audit_log('CONTACT_SUBMITTED', "Contact message from $email ($subject)");
            $success_msg = "Thank you! Your message has been safely recorded in our support records. We will contact you shortly.";
            
            // Clear post fields after success
            $_POST = [];
        } else {
            $errors[] = "Could not open data storage file for writing. Please check folder permissions.";
        }
    }
}

$page_title = "Contact Support";
$breadcrumbs = ["Home" => "index.php", "Contact" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<div class="grid-2" style="align-items: start;">
  <!-- Contact Form -->
  <div class="card">
    <h2>Get in Touch</h2>
    <p>Submit campus queries, technical issues with the portal, or event participation questions.</p>

    <?php if ($success_msg): ?>
      <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger" role="alert">
        <strong>Please fix the following:</strong>
        <ul style="padding-left: 20px; margin-top: 5px;">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="contact.php" method="POST">
      <!-- CSRF Token (Practical 7 Advanced) -->
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

      <div class="form-group">
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Tanmay Sharma" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="email">Your Email Address</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. tanmay@studenthub.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      </div>

      <div class="form-group">
        <label for="subject">Subject / Inquiry Topic</label>
        <select id="subject" name="subject" class="form-control" required>
          <option value="">-- Choose Category --</option>
          <option value="Event Registration Issue" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Event Registration Issue') ? 'selected' : ''; ?>>Event Registration Issue</option>
          <option value="Account Login Help" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Account Login Help') ? 'selected' : ''; ?>>Account Login Help</option>
          <option value="Attendance / Certificate Query" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'Attendance / Certificate Query') ? 'selected' : ''; ?>>Attendance / Certificate Query</option>
          <option value="General Campus Question" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'General Campus Question') ? 'selected' : ''; ?>>General Campus Question</option>
        </select>
      </div>

      <div class="form-group">
        <label for="message">Detailed Message</label>
        <textarea id="message" name="message" class="form-control" placeholder="Please elaborate your query..." rows="4" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Inquiry (CSV Storage)</button>
    </form>
  </div>

  <!-- Campus Info & Map/Helpdesk card -->
  <aside class="card">
    <h3>Campus IT Helpdesk</h3>
    <p>Our academic administration and technical helpdesk staff are available Monday through Friday.</p>
    
    <div style="margin: 16px 0; font-size: 0.92rem; line-height: 1.8;">
      <p><strong>Location:</strong> Ground Floor, Computer Center Block</p>
      <p><strong>Phone:</strong> +91 (020) 2550-1000</p>
      <p><strong>Email:</strong> helpdesk@studenthub.com</p>
      <p><strong>Office Hours:</strong> 9:00 AM – 5:00 PM (Mon – Sat)</p>
    </div>

    <hr style="border:0; border-top: 1px solid var(--border-color); margin: 16px 0;">

    <h4>Storage &amp; Traceability Note</h4>
    <p style="font-size: 0.85rem; color: var(--secondary-color);">
      Messages submitted through this form are validated server-side and recorded to <code>data/contact_submissions.csv</code> in compliance with Practical 7 specifications.
    </p>

    <?php if (is_admin()): ?>
      <div style="margin-top: 12px;">
        <a href="admin_submissions.php" class="btn btn-outline btn-sm">Inspect Stored CSV Inquiries</a>
      </div>
    <?php endif; ?>
  </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

