<?php
/**
 * StudentHub Portal - Home Page
 * Practicals 1, 2, 3, 4 Integration
 */

$page_title = "Home - Campus Portal";
$breadcrumbs = ["Home" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Practical 4: Dismissable Notification Banner -->
<section id="notice-banner" class="notification-banner" aria-label="Campus Announcement">
  <div>
    <strong>Important Notice:</strong> Registrations for the <em>National Hackathon 2026</em> are now live! Limited seats available.
  </div>
  <button id="banner-close-btn" class="btn-close" aria-label="Dismiss Announcement">&times;</button>
</section>

<!-- Practical 1 & 4: Hero Section & Carousel -->
<section class="hero">
  <div class="slider-container" id="hero-slider">
    <div class="slider-slide" style="display: block;">
      <h1>Welcome to Student<strong>Hub</strong></h1>
      <p>The centralized academic, event management, and student services platform for modern campus life.</p>
    </div>
    <div class="slider-slide" style="display: none;">
      <h1>Join College Tech Workshops</h1>
      <p>Discover hands-on workshops in Cloud Computing, AI, Cybersecurity, and Web Development.</p>
    </div>
    <div class="slider-slide" style="display: none;">
      <h1>Effortless Event Registrations</h1>
      <p>One-click enrollment for sports tournaments, cultural fests, and competitive hackathons.</p>
    </div>
  </div>

  <div style="display:flex; justify-content:center; gap:12px; margin-top:16px;">
    <?php if (!$logged_in): ?>
      <a href="register.php" class="btn btn-primary">Create Student Account</a>
      <a href="login.php" class="btn btn-outline">Student / Admin Login</a>
    <?php elseif ($admin): ?>
      <a href="admin_dashboard.php" class="btn btn-primary">Open Admin Dashboard</a>
      <a href="admin_students.php" class="btn btn-outline">Manage Students</a>
    <?php else: ?>
      <a href="events.php" class="btn btn-primary">Browse Upcoming Events</a>
      <a href="dashboard.php" class="btn btn-outline">My Student Dashboard</a>
    <?php endif; ?>
    <button class="btn btn-secondary" onclick="openModal('quick-info-modal')">Portal Overview</button>
  </div>
</section>

<!-- Practical 3: Responsive Grid Cards -->
<section aria-labelledby="features-heading" style="margin-bottom: 32px;">
  <h2 id="features-heading" style="margin-bottom: 16px; font-size: 1.4rem;">Core Portal Modules</h2>

  <div class="grid-3">
    <article class="card">
      <h3>Student Management</h3>
      <p>Manage verified student profiles, enrollment numbers, academic courses, and active student status across all university faculties.</p>
      <div class="card-footer">
        <span style="font-size: 0.8rem; color: var(--secondary-color);">Practicals 2, 5, 11</span>
        <a href="about.php" class="btn btn-outline btn-sm">Read More</a>
      </div>
    </article>

    <article class="card">
      <h3>Campus Events</h3>
      <p>Browse interactive schedules for hackathons, guest lectures, cultural fests, and sports meets with real-time seat availability.</p>
      <div class="card-footer">
        <span style="font-size: 0.8rem; color: var(--secondary-color);">Practicals 6, 12, 13</span>
        <a href="events.php" class="btn btn-primary btn-sm">View Events</a>
      </div>
    </article>

    <article class="card">
      <h3>Secure Authentication</h3>
      <p>Industry-standard BCRYPT password hashing, session regeneration, 15-minute inactivity timeout, and role-based access control.</p>
      <div class="card-footer">
        <span style="font-size: 0.8rem; color: var(--secondary-color);">Practicals 9, 10, 14</span>
        <a href="faq.php" class="btn btn-outline btn-sm">Learn More</a>
      </div>
    </article>
  </div>
</section>

<!-- Quick Stats Preview (Practical 3 & 14) -->
<section aria-labelledby="stats-heading" style="margin-bottom: 32px;">
  <h2 id="stats-heading" style="margin-bottom: 16px; font-size: 1.4rem;">Campus Community at a Glance</h2>
  <div class="grid-4">
    <div class="stat-card">
      <div class="stat-number">1,250+</div>
      <div class="stat-label">Active Students</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">45+</div>
      <div class="stat-label">Annual Events</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">100%</div>
      <div class="stat-label">Accessible UI</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">24/7</div>
      <div class="stat-label">Portal Uptime</div>
    </div>
  </div>
</section>

<!-- Practical 4: Interactive Modal Popup -->
<div id="quick-info-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="modal-title">About StudentHub Portal</h3>
      <button class="btn-close" onclick="closeModal('quick-info-modal')" aria-label="Close dialog">&times;</button>
    </div>
    <div class="modal-body">
      <p>StudentHub is built with semantic HTML5, accessible form elements, responsive CSS Grid and Flexbox, JavaScript DOM manipulation, and PHP/MySQL backend security.</p>
      <br>
      <ul style="padding-left: 20px; font-size: 0.9rem;">
        <li>Full student and event CRUD operations</li>
        <li>Fetch API asynchronous operations without page reload</li>
        <li>Password hashing with BCRYPT algorithm</li>
        <li>Complete audit trail recording administrative actions</li>
      </ul>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('quick-info-modal')">Close</button>
      <a href="about.php" class="btn btn-primary">Full Architecture</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

