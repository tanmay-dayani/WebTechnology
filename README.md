# StudentHub Portal
### Comprehensive Web Technology Project (Practicals 1 to 15 Integration)
**Developed by:** Tanmay  
**Project Location:** `D:\tanmay`  
**Technology Stack:** HTML5, CSS3 (Grid & Flexbox), Vanilla JavaScript (ES6+), PHP 8+, MySQL (MySQLi), Git  

---

## 📋 Table of Contents
1. [Project Overview & Problem Scope](#1-project-overview--problem-scope)
2. [Key Questions & Analysis](#2-key-questions--analysis)
   - [What is a URL and its parts?](#21-what-is-a-url-and-its-parts)
   - [How does a browser process an HTML file?](#22-how-does-a-browser-process-an-html-file)
   - [How is page navigation flow managed?](#23-how-is-page-navigation-flow-managed)
   - [How are GitHub commits maintained across practicals?](#24-how-are-github-commits-maintained-across-practicals)
3. [User Roles & Functional Modules](#3-user-roles--functional-modules)
4. [Sitemap & Page Navigation Tree](#4-sitemap--page-navigation-tree)
5. [Low-Fidelity & Responsive Wireframes](#5-low-fidelity--responsive-wireframes)
6. [Complete Project Folder Structure](#6-complete-project-folder-structure)
7. [Database Schema & ER Model](#7-database-schema--er-model)
8. [Practical Curriculum Mapping (Practicals 1 to 15)](#8-practical-curriculum-mapping-practicals-1-to-15)
9. [Local Deployment & XAMPP Installation Guide](#9-local-deployment--xampp-installation-guide)
10. [Test Cases & Security Verification Report](#10-test-cases--security-verification-report)

---

## 1. Project Overview & Problem Scope
**StudentHub** is a unified campus portal engineered to solve fragmented university communications, student record keeping, workshop registrations, and administrative event workflows. 

### Scope:
- **Centralized Student Directory:** Maintain enrolled student records, courses, years of study, and account status with full CRUD operations.
- **Campus Event Lifecycle:** Facilitate publishing hackathons, workshops, and sports meets with poster image upload validation and real-time student registration.
- **Multi-Role Security:** Granular separation between Guests, Authenticated Students, and Administrators via secure PHP sessions and BCRYPT password hashing.
- **Multi-Format Storage:** Hybrid persistence combining normalized MySQL relational database storage with CSV and JSON file-based logging.
- **Modern Interactivity without Bloat:** Vanilla JavaScript DOM manipulation, Light/Dark theme switching (`localStorage`), collapsible FAQ accordions, modal dialogs, and single-page AJAX CRUD with debounced live search.

---

## 2. Key Questions & Analysis

### 2.1 What is a URL and its parts?
A **URL** (*Uniform Resource Locator*) is the global address used to locate and access resources on the World Wide Web.

```
https://www.studenthub.edu:8080/events/workshop.php?category=Technical&year=2026#register
└─┬─┘   └──────┬──────┘  └─┬──┘ └────────┬─────────┘ └──────────────┬───────────────┘ └───┬────┘
Scheme    Domain/Host     Port         Path                    Query String              Fragment
```

1. **Scheme / Protocol (`https://`):** Defines the transfer protocol. HTTPS encrypts communication over Transport Layer Security (TLS).
2. **Subdomain (`www`):** Sub-division of the root domain.
3. **Domain Name / Host (`studenthub.edu`):** Human-readable server identifier resolved to an IP address by DNS servers.
4. **Port Number (`:8080`):** Specific network port on the host (defaults to `80` for HTTP and `443` for HTTPS).
5. **Path (`/events/workshop.php`):** Hierarchical route pointing to the specific file or endpoint on the web server.
6. **Query String (`?category=Technical&year=2026`):** Key-value parameter pairs passed to the server for filtering, searching, or pagination, separated by `&`.
7. **Fragment / Anchor (`#register`):** Client-side bookmark directing the browser viewport to an element with the corresponding `id="register"` without reloading the page.

---

### 2.2 How does a browser process an HTML file?
When a browser receives an HTML document over HTTP/HTTPS, the **Critical Rendering Path (CRP)** processes it in six systematic phases:

```
HTML Bytes ──> Characters ──> Tokens ──> DOM Tree ──┐
                                                    ├──> Render Tree ──> Layout / Reflow ──> Painting ──> Compositing
CSS Bytes  ──> Characters ──> Tokens ──> CSSOM Tree ┘
```

1. **Conversion & Tokenization:** The browser decodes raw binary network bytes into characters (UTF-8), then tokenizes them into distinct start/end tags (`<html>`, `<body>`, `<main>`).
2. **DOM Tree Construction:** Tokens are converted into node objects with parent-child relationships, producing the **Document Object Model (DOM)** tree.
3. **CSSOM Tree Construction:** The browser parses stylesheets (`style.css`), converting selectors and declaration blocks into the **CSS Object Model (CSSOM)** tree.
4. **Render Tree Generation:** The DOM and CSSOM trees combine into the **Render Tree**, omitting non-visible elements (`<head>`, `display: none`) and matching CSS rules with corresponding DOM nodes.
5. **Layout (Reflow):** The browser calculates the exact geometric coordinates, width, and height of each visible element relative to the device viewport.
6. **Painting & Compositing:** Pixels are rasterized to the screen (colors, borders, text, shadows), and layered surfaces are composited by the GPU for display.

---

### 2.3 How is page navigation flow managed?
In StudentHub, navigation is managed using a **modular, semantic, and role-driven layout architecture**:
- **Semantic Consistency:** Every page incorporates `includes/header.php`, `includes/breadcrumbs.php`, and `includes/footer.php` to maintain identical DOM hierarchies.
- **Dynamic Role Navigation:** The navigation bar inspects `$_SESSION['role']` at runtime:
  - *Guest (Unauthenticated):* Home, About, Events, FAQ, Contact, Feedback, Login, Register.
  - *Student:* Home, Dashboard, Profile, Events, FAQ, Contact, Feedback, Logout.
  - *Admin:* Home, Admin Dashboard, Students Directory (CRUD), Event Management (CRUD), Inquiries (CSV/JSON), Audit Logs, AJAX CRUD Demo, Logout.
- **Active State Highlighting:** Current page URLs are compared with `basename($_SERVER['PHP_SELF'])` to append the `.active` CSS class.
- **Accessible Breadcrumbs:** Hierarchical paths (`Home > Dashboard > Profile`) provide contextual position and keyboard-navigable backward links.

---

### 2.4 How are GitHub commits maintained across practicals?
Commits follow **Conventional Commits** (`type: subject`) with branch segregation:
- **`main` / `master` Branch:** Stable, production-ready integrated semester submission (Practical 15).
- **Practical Branches / Feature Commits:** Each practical commits focused changes:
  1. `git commit -m "feat(prac-1): project initiation, sitemap, wireframes, readme"`
  2. `git commit -m "feat(prac-2): semantic html5 skeletons, skip-link, breadcrumbs"`
  3. `git commit -m "feat(prac-3): mobile-first responsive layout with grid and flexbox"`
  4. `git commit -m "feat(prac-4): dom interactivity, theme switcher, collapsible faq, modal"`
  5. `git commit -m "feat(prac-5): student registration validation, regex, strength meter"`
  6. `git commit -m "feat(prac-6): fetch api external json rendering, search, pagination"`
  7. `git commit -m "feat(prac-7): php form processing with csv and json storage"`
  8. `git commit -m "feat(prac-8): mysql schema, er model, mysqli db connection"`
  9. `git commit -m "feat(prac-9): secure registration, duplicate email check, bcrypt"`
  10. `git commit -m "feat(prac-10): session authentication, rbac, 15m timeout, remember-me"`
  11. `git commit -m "feat(prac-11): student crud, course filter, soft-delete, csv export"`
  12. `git commit -m "feat(prac-12): event crud with poster upload validation (2mb limit)"`
  13. `git commit -m "feat(prac-13): php json rest api, fetch async crud without reload"`
  14. `git commit -m "feat(prac-14): admin dashboard metrics, dynamic menu, audit logging"`
  15. `git commit -m "feat(prac-15): full integration, testing, documentation, deployment"`

---

## 3. User Roles & Functional Modules

| User Role | Permitted Actions & Access Scope |
|---|---|
| **Public / Guest** | Browse public home page, about page, upcoming events, searchable FAQs, submit contact inquiries (CSV), submit feedback (JSON), register a new student account, login. |
| **Student** | Access personalized dashboard, enroll in campus events, view list of enrolled events, update personal phone/course profile, toggle light/dark theme, cancel event participation. |
| **Administrator** | Access system KPI dashboard, perform full Student CRUD (Add/Edit/Delete/Toggle), Event CRUD with poster uploads, execute Fetch API single-page CRUD, inspect CSV/JSON inquiries, review system audit trail, export student directory to CSV. |

---

## 4. Sitemap & Page Navigation Tree

```
StudentHub Portal (Root: D:/tanmay)
│
├── 🌐 Public / Guest Routes
│   ├── index.php                 (Home page, hero carousel, quick stats, overview modal)
│   ├── about.php                 (Project problem statement, role definitions, tech stack)
│   ├── events.php                (Campus event listing, category filter, event registration)
│   ├── faq.php                   (Collapsible FAQ accordion, live DOM filter, Fetch API toggle)
│   ├── contact.php               (Inquiry form, server sanitization, CSV storage)
│   ├── feedback.php              (Feedback form, rating selector, JSON storage)
│   ├── register.php              (Student registration, regex validation, BCRYPT hashing)
│   └── login.php                 (Session login, timeout check, remember-me token)
│
├── 🎓 Student Authenticated Routes (Requires login)
│   ├── dashboard.php             (Personalized hub, enrolled events, profile summary)
│   ├── profile.php               (View & update contact number, course, and year)
│   └── logout.php                (Session invalidation, cookie destruction, audit log)
│
├── 🛡️ Administrator Protected Routes (Requires role: admin)
│   ├── admin_dashboard.php       (Dynamic statistics, recent registrations, audit log table)
│   ├── admin_students.php        (Student directory CRUD, search, course filter, soft-delete)
│   ├── admin_events.php          (Event CRUD with poster image upload and validation)
│   ├── admin_submissions.php     (Viewer for stored CSV inquiries & JSON feedback entries)
│   ├── admin_audit_logs.php      (System audit trail filter by action/actor/IP)
│   ├── ajax_events_demo.php      (Single-page Fetch API CRUD without page reload)
│   └── export_students.php       (Direct CSV download handler for student records)
│
└── ⚡ REST JSON API Endpoints
    └── api/events.php            (GET: list/search, POST: create, PUT: edit, DELETE: remove)
```

---

## 5. Low-Fidelity & Responsive Wireframes

### 5.1 Desktop Wireframe Layout (Screen Width > 768px)
```
+-------------------------------------------------------------------------------+
| [Logo] StudentHub 2026             [Home] [About] [Events] [FAQ] [🌙 Theme]   |
+-------------------------------------------------------------------------------+
| Breadcrumbs: Home > Events                                                    |
+-------------------------------------------------------------------------------+
| HERO / ANNOUNCEMENT BANNER                                                    |
| "Welcome to StudentHub Campus Portal"                                         |
| [Create Student Account]  [Browse Events]  [Portal Overview Modal]            |
+-------------------------------------------------------------------------------+
| SEARCH & FILTER BAR                                                           |
| [Search keyword...                    ] [Category: All v] [Filter] [Reset]   |
+-------------------------------------------------------------------------------+
| THREE-COLUMN RESPONSIVE GRID (CSS Grid)                                       |
| +---------------------+ +---------------------+ +---------------------+       |
| | [Poster Image]      | | [Poster Image]      | | [Poster Image]      |       |
| | Technical  [Open]   | | Workshop   [Open]   | | Sports     [Open]   |       |
| | Hackathon 2026      | | Web 3.0 Workshop    | | Sports Fest 'Urja'  |       |
| | Date: 2026-11-15    | | Date: 2026-11-18    | | Date: 2026-11-25    |       |
| | [Details] [Register]| | [Details] [Register]| | [Details] [Register]|       |
| +---------------------+ +---------------------+ +---------------------+       |
+-------------------------------------------------------------------------------+
| FOOTER (3-Column Semantic Grid)                                               |
| About StudentHub       | Quick Links          | WCAG Standards & Copyright   |
+-------------------------------------------------------------------------------+
```

### 5.2 Mobile Wireframe Layout (Screen Width <= 768px)
```
+-----------------------------------+
| [Logo] StudentHub   [🌙] [☰ Menu] |
+-----------------------------------+
| (When ☰ Toggled)                  |
| - Home                            |
| - About                           |
| - Events                          |
| - FAQ                             |
| - Login / Register                |
+-----------------------------------+
| Breadcrumbs: Home > Events        |
+-----------------------------------+
| HERO SECTION                      |
| StudentHub 2026                   |
| [Browse Events Button]            |
+-----------------------------------+
| SEARCH & FILTER (Stacked)         |
| [Search keyword...              ] |
| [Category: All               v]   |
| [Filter Button]                   |
+-----------------------------------+
| SINGLE COLUMN STACKED CARDS       |
| +-------------------------------+ |
| | [Poster Image]                | |
| | Technical  [Open]             | |
| | Hackathon 2026                | |
| | [Register Button]             | |
| +-------------------------------+ |
| +-------------------------------+ |
| | [Poster Image]                | |
| | Workshop   [Open]             | |
| | Web 3.0 Workshop              | |
| | [Register Button]             | |
| +-------------------------------+ |
+-----------------------------------+
| FOOTER (Single Column)            |
| © 2026 StudentHub by Tanmay       |
+-----------------------------------+
```

---

## 6. Complete Project Folder Structure

```
D:\tanmay\
│
├── .gitignore                         <- Git ignore rules (logs, IDE files, OS caches)
├── README.md                          <- Comprehensive project documentation (Practical 1-15)
├── studenthub.sql                     <- Database schema and seeded records (20+ students, events)
│
├── config/
│   ├── db.php                         <- MySQLi database connection and sanitization helpers
│   ├── auth.php                       <- Sessions, 15m timeout, remember-me token, CSRF helpers
│   └── logger.php                     <- Audit log helper (records to MySQL and fallback txt)
│
├── includes/
│   ├── header.php                     <- Semantic <header>, skip link, theme toggle, dynamic menu
│   ├── footer.php                     <- Semantic <footer>, accessibility notice, script tags
│   └── breadcrumbs.php                <- Dynamic breadcrumb trail generator
│
├── assets/
│   ├── css/
│   │   └── style.css                  <- Clean basic CSS: Grid, Flexbox, media queries, light/dark
│   ├── js/
│   │   ├── main.js                    <- Theme toggle (localStorage), hamburger, modal, accordion
│   │   ├── validation.js              <- Registration regex, strength meter, inline error messages
│   │   ├── json_renderer.js           <- Fetch API JSON rendering with search, filter, pagination
│   │   └── ajax_crud.js               <- Fetch API asynchronous CRUD with debounced search
│   └── images/
│       ├── logo.svg                   <- Brand vector logo
│       ├── default-event.svg          <- Scalable event poster SVG placeholder
│       └── default-event.png          <- Fallback event poster PNG
│
├── data/
│   ├── events.json                    <- Seeded external events dataset (15 records)
│   ├── students.json                  <- Seeded external students dataset (15 records)
│   ├── faqs.json                      <- Seeded campus FAQ dataset (15 records)
│   ├── contact_submissions.csv        <- File-based storage for contact messages (Practical 7)
│   └── feedback.json                  <- File-based storage for user feedback reviews (Practical 7)
│
├── uploads/
│   └── events/                        <- Target directory for admin uploaded poster images
│       └── .gitkeep
│
├── api/
│   └── events.php                     <- REST-style JSON API endpoint (GET, POST, PUT, DELETE)
│
├── index.php                          <- Page 1: Home page with carousel and overview modal
├── about.php                          <- Page 2: About portal, mission, and architecture
├── register.php                       <- Page 3: Student registration with regex and BCRYPT
├── login.php                          <- Page 4: Secure login, remember-me, session timeout
├── logout.php                         <- Sign out, cookie clearance, audit logging
├── dashboard.php                      <- Page 5: Student hub, enrolled events, profile snapshot
├── events.php                         <- Page 6: Event directory, search, filter, register action
├── profile.php                        <- Page 7: Student profile view and contact update
├── contact.php                        <- Page 8: Contact form with server sanitization and CSV storage
├── feedback.php                       <- Page 9: Feedback form with rating and JSON storage
├── faq.php                            <- Page 10: Collapsible FAQ accordion with live DOM search
├── admin_dashboard.php                <- Page 11: Admin dashboard with KPI metrics and audit logs
├── admin_students.php                 <- Page 12: Student management CRUD, search, soft-delete
├── admin_events.php                   <- Page 13: Event management CRUD with poster file upload
├── admin_submissions.php             <- Page 14: Stored CSV inquiries and JSON feedback viewer
├── admin_audit_logs.php               <- Page 15: System security audit trail viewer
├── ajax_events_demo.php               <- Page 16: Asynchronous single-page Fetch CRUD demo
└── export_students.php                <- Practical 11: CSV exporter with HTTP download headers
```

---

## 7. Database Schema & ER Model

### Entity-Relationship Diagram (Textual / Relational)
```
  +------------------+             +--------------------+
  |      users       |1           *|     students       |
  +------------------+-------------+--------------------+
  | id (PK)          |             | id (PK)            |
  | username         |             | user_id (FK)       |
  | email            |             | student_id_code    |
  | password_hash    |             | full_name          |
  | role             |             | email              |
  | remember_token   |             | phone              |
  | last_login       |             | course             |
  | created_at       |             | year               |
  +--------+---------+             | gender             |
           | 1                     | status             |
           |                       +---------+----------+
           |                                 | 1
           | *                               |
  +--------v---------+                       |
  |    audit_logs    |                       |
  +------------------+                       |
  | id (PK)          |                       |
  | user_id (FK)     |                       |
  | username         |                       |
  | action           |                       |
  | details          |                       |
  | ip_address       |                       |
  | created_at       |                       |
  +------------------+                       |
                                             | *
  +------------------+             +---------v----------+
  |      events      |1           *|   registrations    |
  +------------------+-------------+--------------------+
  | id (PK)          |             | id (PK)            |
  | title            |             | student_id (FK)    |
  | category         |             | event_id (FK)      |
  | description      |             | registered_at      |
  | event_date       |             | status             |
  | location         |             +--------------------+
  | poster_image     |             UNIQUE(student_id, event_id)
  | status           |
  +------------------+
```

---

## 8. Practical Curriculum Mapping (Practicals 1 to 15)

| Practical | Module / Requirement | Implemented Feature in `D:\tanmay` |
|---|---|---|
| **Prac 1** | Initiation, Sitemap, Wireframe & Git | Defined scope, user roles, folder structure, ASCII wireframes, `.gitignore`, initial git repo. |
| **Prac 2** | Semantic HTML5 & Accessibility | Tags: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`, `<footer>`. Included `.skip-link`, breadcrumbs, form labels, and image alt text. |
| **Prac 3** | Responsive UI (Grid & Flexbox) | `assets/css/style.css` provides mobile-first media queries (`@media (max-width: 768px)`), CSS Grid columns, and Flexbox navbars. |
| **Prac 4** | DOM Manipulation & UI Interactivity | `assets/js/main.js`: Dark/Light theme switcher with `localStorage`, hamburger menu, collapsible FAQ accordion, and modal dialog. |
| **Prac 5** | Registration Form Frontend Validation | `assets/js/validation.js`: Regular expressions for email/mobile, live password strength meter bar, matching password confirmation, inline error messages. |
| **Prac 6** | External JSON Rendering via Fetch API | `assets/js/json_renderer.js`: Consumes `data/events.json`, `data/students.json`, and `data/faqs.json` with live search, filtering, and pagination. |
| **Prac 7** | PHP Forms with CSV/JSON File Storage | `contact.php` (stores to `data/contact_submissions.csv`), `feedback.php` (stores to `data/feedback.json`), viewed via `admin_submissions.php`. |
| **Prac 8** | MySQL Schema, ER Model & Prepared Stmts | `studenthub.sql` schema with 5 normalized tables. `config/db.php` connects via MySQLi with prepared statements. |
| **Prac 9** | Secure Registration & Hashing | `register.php`: Checks duplicate email/username, hashes password using `password_hash($pass, PASSWORD_BCRYPT)`, inserts into `users` and `students`. |
| **Prac 10** | Secure Login, Sessions, RBAC & Timeout | `login.php` & `config/auth.php`: `password_verify()`, `session_regenerate_id()`, 15-minute inactivity session timeout, remember-me token, and RBAC redirection. |
| **Prac 11** | Student Management CRUD & Search | `admin_students.php`: Add, view, edit, delete with confirmation, search by name/email, filter by course, soft-delete toggle, export to CSV. |
| **Prac 12** | Event Management CRUD & Poster Upload | `admin_events.php`: CRUD for events, validates poster file upload MIME types (JPG, PNG, WEBP), enforces 2MB size limit, and renames file securely. |
| **Prac 13** | PHP JSON API & AJAX Single-Page CRUD | `api/events.php` (GET, POST, PUT, DELETE JSON API) consumed by `ajax_events_demo.php` and `assets/js/ajax_crud.js` with debounced search. |
| **Prac 14** | Admin Dashboard, Dynamic Menu & Audit Logs | `admin_dashboard.php`: Aggregates statistics (students, events, registrations), dynamic role-based menus, and comprehensive `audit_logs` tracking. |
| **Prac 15** | Full Integration, Testing & Deployment | End-to-end integration, zero-warning code, verified test cases, XAMPP execution guide, clean Git repository. |

---

## 9. Local Deployment & XAMPP Installation Guide

### Option A: Running with XAMPP (Apache + MySQL)
1. **Start Services:** Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. **Import Database:**
   - Open your browser and navigate to `http://localhost/phpmyadmin/`.
   - Click on the **Import** tab.
   - Choose the file: `D:\tanmay\studenthub.sql` and click **Go**.
   - The database `studenthub_db` will be created with all tables and seeded test records.
3. **Link to Apache:**
   - You can create an Apache virtual host or a directory junction/symlink in `D:\xampp\htdocs\tanmay`:
     ```powershell
     cmd /c mklink /J "D:\xampp\htdocs\tanmay" "D:\tanmay"
     ```
   - Open in browser: `http://localhost/tanmay/index.php`.

### Option B: Running with PHP Built-in Web Server
If you prefer running directly from `D:\tanmay`:
```powershell
cd D:\tanmay
& "D:\xampp\php\php.exe" -S localhost:8000
```
Open in browser: `http://localhost:8000/index.php`.

### Default Seeded Test Credentials:
- **Administrator Account:**
  - Username: `admin` (or `admin@studenthub.com`)
  - Password: `admin123`
  - Access: Full access to Admin Dashboard, Student CRUD, Event CRUD, CSV/JSON Inquiries, Audit Logs, and AJAX Demo.
- **Student Demo Account:**
  - Username: `tanmay` (or `tanmay@studenthub.com`)
  - Password: `student123`
  - Access: Student Dashboard, Enrolled Events, Profile Edit, Event Registration, and Inquiries.

---

## 10. Test Cases & Security Verification Report

| Test ID | Test Description | Input Data | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| **TC01** | Student Registration Valid Input | Name: `Aditya Rao`, Email: `aditya.rao@gmail.com`, Phone: `9876543210`, Pass: `Secret123`, Course: `B.Tech CSE` | Account created in `users` and `students`, hashed password stored, audit log saved. | Account created, redirected to login with student ID. | **PASS** |
| **TC02** | Registration Duplicate Email | Email: `tanmay@studenthub.com` | Registration rejected with "Email already registered" error message. | Error displayed, submission aborted. | **PASS** |
| **TC03** | Phone Number Regex Validation | Phone: `12345` (invalid format) | Form blocked on frontend and backend by `/^[6-9]\d{9}$/`. | Inline error "Enter valid 10-digit mobile starting with 6-9". | **PASS** |
| **TC04** | Password Strength Meter | Passwords of varying complexity | Bar width and color change dynamically (Weak: Red, Medium: Yellow, Strong: Green). | Visual bar and text update in real time. | **PASS** |
| **TC05** | Admin Login & Redirection | `admin` / `admin123` | Password verified with `password_verify()`, session regenerated, redirected to `admin_dashboard.php`. | Admin logged in, redirected to Admin Dashboard. | **PASS** |
| **TC06** | Student Login & Redirection | `tanmay` / `student123` | Password verified, session regenerated, redirected to `dashboard.php`. | Student logged in, redirected to Student Dashboard. | **PASS** |
| **TC07** | Invalid Password Attempt | `admin` / `wrongpass` | Login rejected, error shown, failed attempt recorded in `audit_logs`. | Error displayed, audit entry recorded with client IP. | **PASS** |
| **TC08** | Inactivity Session Timeout | Idle for 15+ minutes (900s) | Session destroyed, redirected to `login.php?msg=timeout`. | User redirected with expiration warning banner. | **PASS** |
| **TC09** | Role-Based Access Control (RBAC) | Guest accessing `admin_dashboard.php` | Access blocked, redirected to `login.php?msg=login_required`. | Access denied, redirected immediately. | **PASS** |
| **TC10** | Student Search and Filter | Search: `Sharma`, Course: `B.Tech CSE` | Filters students table dynamically via prepared SQL query. | Displays matching records accurately. | **PASS** |
| **TC11** | Student Record Delete Confirmation | Click Delete button on Student row | Browser triggers native `confirm()` modal before sending delete request. | Confirmation modal displayed; deletion executes only on approval. | **PASS** |
| **TC12** | Event Poster File Upload Validation | File: `document.pdf` (invalid MIME) | Rejected with "Invalid file type. Only JPG, PNG, and WEBP allowed". | Upload rejected with error alert. | **PASS** |
| **TC13** | Event Poster Size Check | File > 2MB | Upload blocked with "Poster file size exceeds 2MB limit". | Upload rejected with error alert. | **PASS** |
| **TC14** | Contact Form CSV File Storage | Name: `Riya Sen`, Message: `Need syllabus copy` | Sanitized row appended to `data/contact_submissions.csv`. | Row written successfully, visible in Admin Submissions table. | **PASS** |
| **TC15** | Feedback JSON Storage | Rating: 5 Stars, Category: Usability | JSON object appended to `data/feedback.json`. | Entry persisted with timestamp and IP address. | **PASS** |
| **TC16** | Asynchronous Fetch API CRUD | Add event via AJAX without page reload | POST sent to `api/events.php`, table row inserted into DOM dynamically. | New event added live without reloading page. | **PASS** |
| **TC17** | Debounced Live Search | Typing quickly into search input | Network requests throttled by 350ms debounce timer. | Search query triggered cleanly after keystrokes cease. | **PASS** |
| **TC18** | Dark / Light Theme Persistence | Toggle theme to Dark Mode | `data-theme="dark"` set on `<html>`, saved to `localStorage`, restored on refresh. | Dark theme persists across page reloads. | **PASS** |
| **TC19** | Accessibility & Skip Link | Press `Tab` key on page load | Skip link focuses and reveals at top of viewport, linking directly to `#main-content`. | Focus moves directly to main content landmark. | **PASS** |
| **TC20** | CSV Student Directory Export | Click "Export All to CSV" | Browser initiates download of `studenthub_students_YYYY-MM-DD.csv`. | Complete student table downloaded as CSV. | **PASS** |

---

## 11. Viva Voce Quick Revision Notes
1. **Why use `password_hash()` instead of `md5()` or `sha1()`?**  
   MD5 and SHA-1 are fast hashing algorithms vulnerable to rainbow table attacks and brute force. `password_hash()` utilizes BCRYPT with automatic salt generation and adjustable algorithmic cost, making it resistant to hardware-accelerated attacks.
2. **Why use Prepared Statements?**  
   Prepared statements separate SQL query code from user data. Parameters are transmitted independently of the query structure, neutralizing SQL Injection vulnerabilities.
3. **What is the purpose of `session_regenerate_id(true)`?**  
   It issues a new unique session identifier upon login and deletes the old session file, preventing Session Fixation attacks.
4. **How does CSS Flexbox differ from CSS Grid?**  
   Flexbox is 1-dimensional (arranging items in a row OR column), ideal for navigation bars and buttons. CSS Grid is 2-dimensional (handling rows AND columns simultaneously), ideal for card grids, dashboards, and page layouts.
5. **How does the Fetch API work asynchronously?**  
   `fetch()` returns a JavaScript `Promise`. It initiates non-blocking background HTTP requests via the browser engine without freezing the UI thread or reloading the web page.

---
*End of Documentation — StudentHub Portal Practical 1 to 15 Integration by Tanmay*

