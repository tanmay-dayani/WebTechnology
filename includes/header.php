<?php
/**
 * Header Component
 * Practical 2: Semantic HTML5 Header, Skip Link, Accessibility
 * Practical 14: Dynamic Menu Options Based on User Role
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/breadcrumbs.php';

$current_page = basename($_SERVER['PHP_SELF']);
$logged_in = is_logged_in();
$admin = is_admin();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="StudentHub - Integrated Semester Portal for Students and Campus Administration">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | StudentHub' : 'StudentHub Portal'; ?></title>
  
  <!-- Main Stylesheet (Practical 3: Grid, Flexbox, Mobile-First) -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Practical 2 Advanced Extension: Skip-to-content accessible link -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <!-- Semantic Header -->
  <header>
    <div class="nav-container">
      <a href="index.php" class="brand" aria-label="StudentHub Home">
        <svg width="28" height="28" viewBox="0 0 40 40" aria-hidden="true">
          <circle cx="20" cy="20" r="18" fill="#2563eb"/>
          <path d="M 12 21 L 20 12 L 28 21 Z" fill="#ffffff" />
          <rect x="18" y="21" width="4" height="7" fill="#ffffff" />
        </svg>
        <span>Student<strong>Hub</strong></span>
        <span class="brand-badge">2026</span>
      </a>

      <!-- Dynamic Role-Based Navigation Menu (Practical 14) -->
      <nav aria-label="Main Navigation">
        <ul id="nav-menu">
          <li><a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
          <li><a href="about.php" class="<?php echo $current_page === 'about.php' ? 'active' : ''; ?>">About</a></li>
          <li><a href="events.php" class="<?php echo $current_page === 'events.php' ? 'active' : ''; ?>">Events</a></li>
          <li><a href="faq.php" class="<?php echo $current_page === 'faq.php' ? 'active' : ''; ?>">FAQ</a></li>

          <?php if ($admin): ?>
            <!-- Admin Specific Dynamic Menu -->
            <li><a href="admin_dashboard.php" class="<?php echo $current_page === 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="admin_students.php" class="<?php echo $current_page === 'admin_students.php' ? 'active' : ''; ?>">Students</a></li>
            <li><a href="admin_events.php" class="<?php echo $current_page === 'admin_events.php' ? 'active' : ''; ?>">Manage Events</a></li>
            <li><a href="admin_submissions.php" class="<?php echo $current_page === 'admin_submissions.php' ? 'active' : ''; ?>">Inquiries</a></li>
            <li><a href="admin_audit_logs.php" class="<?php echo $current_page === 'admin_audit_logs.php' ? 'active' : ''; ?>">Audit Logs</a></li>
          <?php elseif ($logged_in): ?>
            <!-- Student Specific Dynamic Menu -->
            <li><a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">Profile</a></li>
            <li><a href="contact.php" class="<?php echo $current_page === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            <li><a href="feedback.php" class="<?php echo $current_page === 'feedback.php' ? 'active' : ''; ?>">Feedback</a></li>
          <?php else: ?>
            <!-- Guest Dynamic Menu -->
            <li><a href="contact.php" class="<?php echo $current_page === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
            <li><a href="feedback.php" class="<?php echo $current_page === 'feedback.php' ? 'active' : ''; ?>">Feedback</a></li>
            <li><a href="login.php" class="<?php echo $current_page === 'login.php' ? 'active' : ''; ?>">Login</a></li>
            <li><a href="register.php" class="btn btn-primary btn-sm" style="color:#ffffff;">Register</a></li>
          <?php endif; ?>

          <?php if ($logged_in): ?>
            <li>
              <span class="nav-role-badge"><?php echo htmlspecialchars($user['role']); ?>: <?php echo htmlspecialchars($user['username']); ?></span>
            </li>
            <li><a href="logout.php" class="btn btn-outline btn-sm">Logout</a></li>
          <?php endif; ?>
        </ul>
      </nav>

      <!-- Utility Actions: Theme Switcher & Hamburger Menu (Practical 4) -->
      <div class="nav-actions">
        <button id="theme-toggle-btn" class="btn-icon" title="Toggle Theme" aria-label="Toggle Light and Dark Mode">Dark Mode</button>
        <button id="hamburger-btn" class="btn-icon hamburger-btn" aria-label="Toggle navigation menu" aria-expanded="false">Menu</button>
      </div>
    </div>
  </header>

  <?php
  // Render breadcrumbs if defined on the page
  if (isset($breadcrumbs)) {
      render_breadcrumbs($breadcrumbs);
  }
  ?>

  <!-- Main Landmark for Semantic & Accessible Structure -->
  <main id="main-content" tabindex="-1">
    <div class="container">

