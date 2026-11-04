<?php
/**
 * StudentHub Portal - About Us
 * Practicals 1, 2, 3 Semantic & Responsive Architecture
 */

$page_title = "About StudentHub";
$breadcrumbs = ["Home" => "index.php", "About Us" => ""];
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" style="text-align: left;">
  <h1>About Student<strong>Hub</strong></h1>
  <p>StudentHub is an integrated, full-stack campus management platform developed as part of the semester laboratory curriculum covering Web Technology Practicals 1 through 15.</p>
</section>

<div class="grid-2" style="margin-bottom: 24px;">
  <article class="card">
    <h2>Problem Scope &amp; Purpose</h2>
    <p>Modern educational institutions manage numerous student queries, dynamic event schedules, club registrations, and administrative records across disparate tools.</p>
    <p>StudentHub unifies these workflows into a single lightweight, accessible, mobile-first web portal with strict role-based authorization for students and campus administrators.</p>
  </article>

  <article class="card">
    <h2>Target User Roles</h2>
    <ul style="padding-left: 20px; font-size: 0.95rem; line-height: 1.8;">
      <li><strong>Students:</strong> Register accounts, browse campus events, participate in hackathons/workshops, track enrolled events, and submit inquiries.</li>
      <li><strong>Administrators:</strong> Create and update student records, publish event posters with file validation, inspect CSV inquiries, and review system audit logs.</li>
      <li><strong>Guests:</strong> Explore public event listings, consult searchable FAQs, and reach campus helpdesk.</li>
    </ul>
  </article>
</div>

<article class="card" style="margin-bottom: 24px;">
  <h2>Technology Stack &amp; Curriculum Mapping</h2>
  <div class="table-responsive" style="margin-top: 12px;">
    <table>
      <thead>
        <tr>
          <th>Practical</th>
          <th>Core Topic &amp; Focus Area</th>
          <th>Implementation in StudentHub</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Prac 1-2</strong></td>
          <td>Sitemap, Wireframing &amp; Semantic HTML5</td>
          <td>Proper header, nav, main, section, article, aside, footer, skip link, breadcrumbs.</td>
        </tr>
        <tr>
          <td><strong>Prac 3</strong></td>
          <td>Responsive UI with CSS Grid &amp; Flexbox</td>
          <td>Mobile-first media queries, clean responsive card layouts, tables, and buttons.</td>
        </tr>
        <tr>
          <td><strong>Prac 4</strong></td>
          <td>DOM Manipulation &amp; Interactivity</td>
          <td>Theme switcher (localStorage), modal dialogs, collapsible FAQ accordion, hamburger menu.</td>
        </tr>
        <tr>
          <td><strong>Prac 5</strong></td>
          <td>Registration Validation &amp; Regex</td>
          <td>Real-time email/mobile regex, password strength meter, inline error messages.</td>
        </tr>
        <tr>
          <td><strong>Prac 6</strong></td>
          <td>External JSON &amp; Fetch API</td>
          <td>Dynamic rendering, search, category filtering, sort, and pagination via Fetch API.</td>
        </tr>
        <tr>
          <td><strong>Prac 7</strong></td>
          <td>PHP Forms &amp; CSV/JSON File Storage</td>
          <td>Server-side validation and sanitized storage to CSV and JSON files.</td>
        </tr>
        <tr>
          <td><strong>Prac 8-10</strong></td>
          <td>MySQL, BCRYPT Hashing &amp; RBAC</td>
          <td>Normalized database, prepared statements, password_hash, sessions, timeout, remember-me.</td>
        </tr>
        <tr>
          <td><strong>Prac 11-12</strong></td>
          <td>Student &amp; Event CRUD + Poster Upload</td>
          <td>Add/Edit/Delete records, MIME type and size checks for poster uploads, CSV export.</td>
        </tr>
        <tr>
          <td><strong>Prac 13</strong></td>
          <td>PHP JSON API &amp; AJAX CRUD</td>
          <td>Asynchronous list, add, edit, and delete operations without full page reload.</td>
        </tr>
        <tr>
          <td><strong>Prac 14-15</strong></td>
          <td>Admin Dashboard, Audit Logs &amp; Integration</td>
          <td>Aggregated statistics, dynamic menus, audit logs, test coverage report, and deployment.</td>
        </tr>
      </tbody>
    </table>
  </div>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

