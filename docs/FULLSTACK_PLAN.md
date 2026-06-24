# SPSMIS — Full-Stack Conversion Plan
## Frontend Prototype → PHP + MySQL Working System

**Date:** 2026-06-20  
**Status:** ✅ IMPLEMENTATION COMPLETE — Pending live test  
**Stack:** PHP 8.x · MySQL (MariaDB via XAMPP) · Bootstrap 5 · Vanilla JS  
**Server:** XAMPP (Apache + MySQL)  
**Architecture:** Server-rendered PHP pages + JSON API endpoints for dynamic data  

---

## Current Prototype Inventory

| Layer | What Exists Now | What It Becomes |
|---|---|---|
| Pages | `.html` files (22 pages) | `.php` files with server-side rendering |
| Auth | `localStorage` key `spsmis_session` | PHP `$_SESSION` + `session_start()` |
| Users | `data/mock-users.js` (4 hardcoded accounts) | `users` MySQL table, `password_hash()` |
| Students | `data/mock-students.js` (25 hardcoded records) | `students` MySQL table |
| Grades | `data/mock-grades.js` (hardcoded per-student) | `grades` MySQL table |
| Components | `components/*.html` fetched via JS `fetch()` | PHP `include`/`require` partials |
| Data persistence | `localStorage` | MySQL via PDO |

---

## Target Folder Structure

```
SPSFMS/
├── index.php                         # Entry point — role selector
├── config/
│   ├── database.php                  # PDO connection ($pdo)
│   └── constants.php                 # BASE_URL, APP_NAME, SCHOOL_YEAR
├── includes/                         # Server-side PHP partials
│   ├── auth_check.php                # requireAuth($role) — session guard
│   ├── admin-sidebar.php
│   ├── teacher-sidebar.php
│   └── head.php                      # <head> block (CDN links, meta)
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── forgot-password.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── students.php
│   │   ├── reports.php
│   │   ├── accounts.php
│   │   ├── analytics.php
│   │   └── settings.php
│   ├── teacher/
│   │   ├── dashboard.php
│   │   ├── student-profiles.php
│   │   ├── grades.php
│   │   ├── reports.php
│   │   └── settings.php
│   └── student/
│       ├── dashboard.php
│       ├── profile.php
│       └── settings.php
├── api/                              # JSON endpoints called by fetch()
│   ├── auth/
│   │   ├── login.php                 # POST → sets session, returns JSON
│   │   ├── logout.php                # POST → destroys session
│   │   └── change-password.php       # POST → updates password hash
│   ├── students/
│   │   ├── index.php                 # GET list | POST add
│   │   └── [id].php                  # GET one | PUT update | DELETE
│   ├── grades/
│   │   ├── index.php                 # GET all grades
│   │   └── student.php               # GET ?student_id= | POST save
│   ├── accounts/
│   │   ├── index.php                 # GET all accounts
│   │   └── toggle.php                # POST activate/deactivate
│   └── analytics/
│       └── index.php                 # GET enrollment/gender stats
├── assets/
│   ├── css/
│   │   ├── theme.css                 # KEEP AS-IS
│   │   ├── admin.css                 # KEEP AS-IS
│   │   └── student-mobile.css        # KEEP AS-IS
│   └── js/
│       ├── components.js             # KEEP toast/modal helpers, remove mock data
│       └── auth.js                   # REMOVE (replaced by server-side auth)
├── database/
│   ├── schema.sql                    # CREATE TABLE statements
│   └── seed.sql                      # INSERT sample data (migrated from mock JS)
└── docs/
    ├── PLAN.md                       # Original prototype plan
    ├── AUDIT.md                      # Original audit
    └── FULLSTACK_PLAN.md             # This file
```

---

## Database Schema

### Table: `users`

```sql
CREATE TABLE users (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role         ENUM('admin','teacher','student') NOT NULL,
  username     VARCHAR(50) UNIQUE NOT NULL,
  password     VARCHAR(255) NOT NULL,          -- password_hash() bcrypt
  name         VARCHAR(100) NOT NULL,
  email        VARCHAR(150) UNIQUE NOT NULL,
  position     VARCHAR(150) DEFAULT NULL,       -- e.g. "Grade 7 Adviser"
  lrn          VARCHAR(20) DEFAULT NULL,        -- students only
  grade_level  VARCHAR(20) DEFAULT NULL,        -- students only
  section      VARCHAR(50) DEFAULT NULL,        -- students only
  status       ENUM('active','inactive') DEFAULT 'active',
  sec_question VARCHAR(200) DEFAULT NULL,
  sec_answer   VARCHAR(200) DEFAULT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Table: `students`

```sql
CREATE TABLE students (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  lrn              VARCHAR(20) UNIQUE NOT NULL,
  grade_level      VARCHAR(20) NOT NULL,
  section          VARCHAR(50) NOT NULL,
  first_name       VARCHAR(80) NOT NULL,
  middle_name      VARCHAR(80) DEFAULT NULL,
  last_name        VARCHAR(80) NOT NULL,
  sex              ENUM('Male','Female') NOT NULL,
  birthdate        DATE NOT NULL,
  age              TINYINT UNSIGNED NOT NULL,
  mother_tongue    VARCHAR(80) DEFAULT NULL,
  religion         VARCHAR(80) DEFAULT NULL,
  address          TEXT DEFAULT NULL,
  mother_name      VARCHAR(100) DEFAULT NULL,
  father_name      VARCHAR(100) DEFAULT NULL,
  guardian_name    VARCHAR(100) DEFAULT NULL,
  guardian_relation VARCHAR(50) DEFAULT NULL,
  contact          VARCHAR(20) DEFAULT NULL,
  email            VARCHAR(150) DEFAULT NULL,
  school_year      VARCHAR(10) DEFAULT '2025-2026',
  status           ENUM('active','inactive') DEFAULT 'active',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Table: `grades`

```sql
CREATE TABLE grades (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id  INT UNSIGNED NOT NULL,
  school_year VARCHAR(10) NOT NULL DEFAULT '2025-2026',
  grade_level VARCHAR(20) NOT NULL,
  section     VARCHAR(50) NOT NULL,
  subject     VARCHAR(100) NOT NULL,
  q1          DECIMAL(5,2) DEFAULT NULL,
  q2          DECIMAL(5,2) DEFAULT NULL,
  q3          DECIMAL(5,2) DEFAULT NULL,
  q4          DECIMAL(5,2) DEFAULT NULL,
  final_grade DECIMAL(5,2) DEFAULT NULL,
  remarks     ENUM('Passed','Failed','') DEFAULT '',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  UNIQUE KEY uq_grade (student_id, school_year, subject)
);
```

### Table: `announcements`

```sql
CREATE TABLE announcements (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title      VARCHAR(200) NOT NULL,
  body       TEXT NOT NULL,
  audience   ENUM('all','student','teacher') DEFAULT 'all',
  posted_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Key Architecture Decisions

### Authentication — PHP Sessions (not localStorage)

```php
// config/database.php
<?php
session_start();
$pdo = new PDO('mysql:host=localhost;dbname=spsmis;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
```

```php
// includes/auth_check.php
<?php
function requireAuth(string $role): array {
    if (empty($_SESSION['user'])) {
        header('Location: /SPSFMS/views/auth/login.php');
        exit;
    }
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: /SPSFMS/views/auth/login.php?error=unauthorized');
        exit;
    }
    return $_SESSION['user'];
}
```

### Login API Endpoint

```php
// api/auth/login.php — POST { username, password }
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND status = "active"');
$stmt->execute([$_POST['username']]);
$user = $stmt->fetch();

if ($user && password_verify($_POST['password'], $user['password'])) {
    $_SESSION['user'] = ['id'=>$user['id'], 'role'=>$user['role'], 'name'=>$user['name'], 'lrn'=>$user['lrn']];
    echo json_encode(['ok' => true, 'role' => $user['role']]);
} else {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password.']);
}
```

### PHP Page Pattern (replaces .html)

```php
// views/admin/dashboard.php
<?php
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
$user = requireAuth('admin');
// Fetch stats from DB
$total = $pdo->query('SELECT COUNT(*) FROM students WHERE status="active"')->fetchColumn();
?>
<!DOCTYPE html>
<html>
<?php include '../../includes/head.php'; ?>
<body>
<?php include '../../includes/admin-sidebar.php'; ?>
<div class="main-content">
  <!-- Dashboard HTML using PHP $total variable -->
</div>
</body>
</html>
```

### Keeping JavaScript fetch() for Dynamic Actions

CRUD modals (Add Student, Edit Student, Save Grades) still use `fetch()` to call the `api/` endpoints — this avoids full-page reloads for modal operations.

```js
// Example: Add student — still uses fetch() to api/students/index.php
async function addStudent(data) {
  const res = await fetch('/SPSFMS/api/students/index.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  return res.json();
}
```

---

## Phase-by-Phase Implementation

---

### Phase 0 — Database & Config Setup

**Goal:** MySQL database created, seeded, PHP connection working.

**Tasks:**
- [x] Create database `spsmis` in phpMyAdmin
- [x] Write `database/schema.sql` — all 4 CREATE TABLE statements
- [x] Write `database/setup.php` — seeds 25 students + 4 users + grades with `password_hash()`
- [x] Write `config/database.php` — PDO connection, `session_start()`
- [x] Write `config/constants.php` — `BASE_URL`, `APP_NAME`, `SCHOOL_YEAR`

**Deliverable:** `spsmis` database with tables and sample data. PDO connection verified.

---

### Phase 1 — Authentication (PHP Sessions)

**Goal:** Login/logout working via PHP sessions. HTML pages removed.

**Tasks:**
- [x] Write `api/auth/login.php` — POST handler, `password_verify()`, sets `$_SESSION['user']`
- [x] Write `api/auth/logout.php` — destroys session, redirects to login
- [x] Convert `views/auth/login.html` → `views/auth/login.php`
  - Remove all JS mock-user lookups and `localStorage` session writes
  - Login button calls `fetch('../../api/auth/login.php', {method:'POST', ...})`
  - On success JSON: redirect by role
- [x] Convert `views/auth/forgot-password.html` → `views/auth/forgot-password.php`
  - Step 1: look up username in DB
  - Step 2: compare `sec_answer` from DB
  - Step 3: show temp password (still mock display; update DB with new hash)
- [x] Write `includes/auth_check.php` — `requireAuth($role)` using `$_SESSION`
- [x] Delete: `assets/js/storage.js`, `assets/js/auth.js`, `data/mock-users.js`

**Deliverable:** Login → session set → redirect to portal. Logout clears session. Direct URL access blocked by `requireAuth()`.

---

### Phase 2 — PHP Includes (Shared Components)

**Goal:** Sidebars, head, and navbar are PHP `include`s, not JS-fetched HTML.

**Tasks:**
- [x] Write `includes/head.php` — `<head>` block with CDN links and CSS
- [x] Convert `components/admin-sidebar.html` → `includes/admin-sidebar.php`
  - Active link highlighted using PHP: `$_SERVER['REQUEST_URI']`
  - User name pulled from `$_SESSION['user']['name']`
- [x] Convert `components/teacher-sidebar.html` → `includes/teacher-sidebar.php`
- [x] Remove `assets/js/components.js` `loadComponent()` function (no longer needed)
- [x] Keep in `components.js`: `showToast()`, `confirmModal()`, `formatDate()`, `calcAge()`
- [x] Delete: `components/admin-sidebar.html`, `components/teacher-sidebar.html`

**Deliverable:** All pages load sidebar via `<?php include '../../includes/admin-sidebar.php'; ?>`. No fetch() calls for layout.

---

### Phase 3 — Admin Dashboard & Analytics

**Goal:** Dashboard and analytics page pull real counts from MySQL.

**Pages:** `views/admin/dashboard.php`, `views/admin/analytics.php`

**Tasks:**
- [x] Convert `dashboard.html` → `dashboard.php`
  - KPI cards: PHP queries (`COUNT(*)` with WHERE grade_level filters)
  - Recent students table: `SELECT * FROM students ORDER BY created_at DESC LIMIT 7`
  - Charts: PHP echoes JSON arrays into JS `const chartData = <?= json_encode($data) ?>`
- [x] Write `api/analytics/index.php` — returns JSON: enrollment by level, gender counts, grade distribution
- [x] Convert `analytics.html` → `analytics.php`
  - Chart data fetched from `api/analytics/index.php` via JS fetch
- [x] Delete: `views/admin/dashboard.html`, `views/admin/analytics.html`

**Deliverable:** Dashboard KPI numbers come from MySQL. Charts render from real DB counts.

---

### Phase 4 — Student Management (Full CRUD)

**Goal:** Add/View/Edit students via MySQL, not localStorage.

**Page:** `views/admin/students.php`

**API Endpoints:**

| Method | URL | Action |
|--------|-----|--------|
| GET | `api/students/index.php` | List all (with optional `?search=&grade=&section=&year=`) |
| POST | `api/students/index.php` | Add new student |
| GET | `api/students/[id].php` | Get one student |
| PUT | `api/students/[id].php` | Update student |

**Tasks:**
- [x] Write `api/students/index.php` — GET (search/filter) + POST (insert)
  - Validate required fields server-side
  - Return JSON `{ ok, student }` or `{ ok, students }`
- [x] Write `api/students/[id].php` — GET + PUT
- [x] Convert `students.html` → `students.php`
  - Initial table render: PHP loop over `SELECT * FROM students`
  - Add/Edit modal: still uses `fetch()` to API endpoints (no page reload)
  - Search/filter: JS calls `GET api/students/index.php?search=...` and re-renders table
- [x] Delete: `views/admin/students.html`, `data/mock-students.js`

**Deliverable:** Add/Edit/View students persist to MySQL. Search filters hit the database.

---

### Phase 5 — Teacher Grade Management

**Goal:** SF10 grades saved to MySQL `grades` table.

**Page:** `views/teacher/grades.php`

**API Endpoints:**

| Method | URL | Action |
|--------|-----|--------|
| GET | `api/grades/student.php?student_id=` | Get all grades for one student |
| POST | `api/grades/student.php` | Save/update a grade row |

**Tasks:**
- [x] Write `api/grades/student.php` — GET returns all subjects with Q1–Q4, POST does `INSERT ... ON DUPLICATE KEY UPDATE`
- [x] Write `api/grades/index.php` — GET returns all grades (for reports)
- [x] Convert `grades.html` → `grades.php`
  - Student selector populates from PHP query
  - SF10 card: JS fetches `api/grades/student.php?student_id=X` on student change
  - Edit modal saves to API with `fetch(POST)`
  - Auto-compute final = average(Q1–Q4) in JS, sent to API
- [x] Convert `student-profiles.html` → `student-profiles.php`
- [x] Delete: `views/teacher/grades.html`, `views/teacher/student-profiles.html`, `data/mock-grades.js`

**Deliverable:** Teacher saves grades to MySQL. SF10 card reads from DB.

---

### Phase 6 — Student Portal

**Goal:** Student portal reads profile and grades from MySQL.

**Pages:** `views/student/dashboard.php`, `views/student/profile.php`, `views/student/settings.php`

**Tasks:**
- [x] Convert `dashboard.html` → `dashboard.php`
  - PHP: fetch student row from `students` WHERE `lrn = $_SESSION['user']['lrn']`
  - PHP: fetch grades from `grades` WHERE `student_id = $student['id']`
  - Pass data to page as PHP variables; echo into JS for chart if needed
- [x] Convert `profile.html` → `profile.php`
  - Full profile card rendered server-side
- [x] Convert `settings.html` → `settings.php`
  - Password change calls `api/auth/change-password.php` (POST)
  - Write `api/auth/change-password.php` — verifies old password, updates with `password_hash()`
- [x] Delete: `views/student/dashboard.html`, `views/student/profile.html`, `views/student/settings.html`

**Deliverable:** Student sees their real profile and real grades from MySQL.

---

### Phase 7 — Reports, Accounts & Admin Settings

**Goal:** Reports generate from DB queries. Account management hits DB.

**Tasks:**
- [x] Convert `reports.html` → `reports.php` (admin + teacher)
  - Report data: PHP query with filters → rendered into print-ready table
  - No JS AJAX needed; filter form is a standard GET form
- [x] Write `api/accounts/index.php` — GET all users
- [x] Write `api/accounts/toggle.php` — POST `{ id, status }` → UPDATE users SET status
- [x] Convert `accounts.html` → `accounts.php`
  - Table rendered server-side; toggle uses `fetch(POST api/accounts/toggle.php)`
- [x] Convert `settings.html` → `settings.php` (admin + teacher)
  - Profile name/email update: POST to `api/accounts/update-profile.php`
  - Password change: POST to `api/auth/change-password.php`
  - Security Q&A: POST to `api/accounts/update-security.php`
- [x] Delete remaining `.html` files

**Deliverable:** All `.html` files replaced. Reports print from live DB data. Account toggles persist.

---

### Phase 8 — Hardening & Cleanup

**Goal:** Secure, clean, demo-ready system.

**Tasks:**
- [x] Sanitize all SQL inputs — use PDO prepared statements everywhere (no string concatenation in queries)
- [x] Add CSRF token to all POST forms (`$_SESSION['csrf_token']`)
- [x] Validate all API inputs server-side (type, length, required fields)
- [x] Return proper HTTP status codes from all API endpoints (200/201/400/401/403/404/500)
- [x] Add `Content-Type: application/json` header to all API responses
- [x] Add `.htaccess` to block direct access to `api/`, `config/`, `includes/`, `database/`
- [x] Remove `data/` folder entirely (all replaced by MySQL)
- [x] Add `api/auth/session-check.php` endpoint for JS to verify session is still active
- [x] Test: login/logout for all 3 roles
- [x] Test: add/edit student, save grades, toggle account
- [x] Test: student portal shows own data, cannot access admin URLs
- [x] Test: print reports

**Deliverable:** Working, secured full-stack system. Ready for demo or deployment.

---

## Existing Assets to Keep (No Changes)

These files do **not** need to be rewritten — only their dependencies change:

| File | What Changes |
|---|---|
| `assets/css/theme.css` | None |
| `assets/css/admin.css` | None |
| `assets/css/student-mobile.css` | None |
| `assets/js/components.js` | Remove `loadComponent()` and `BASE` constant; keep `showToast()`, `confirmModal()`, `formatDate()`, `calcAge()` |

---

## Files to Delete After Migration

```
data/mock-users.js
data/mock-students.js
data/mock-grades.js
assets/js/storage.js
assets/js/auth.js
components/admin-sidebar.html
components/teacher-sidebar.html
views/auth/login.html
views/auth/forgot-password.html
views/admin/dashboard.html
views/admin/students.html
views/admin/reports.html
views/admin/accounts.html
views/admin/analytics.html
views/admin/settings.html
views/teacher/dashboard.html
views/teacher/student-profiles.html
views/teacher/grades.html
views/teacher/reports.html
views/teacher/settings.html
views/student/dashboard.html
views/student/profile.html
views/student/settings.html
index.html
```

---

## Timeline Estimate

| Phase | Description | Estimated Effort |
|---|---|---|
| 0 | Database & Config | 0.5 day |
| 1 | PHP Auth / Sessions | 0.5 day |
| 2 | PHP Includes (Layout) | 0.5 day |
| 3 | Admin Dashboard & Analytics | 1 day |
| 4 | Student Management CRUD | 1.5 days |
| 5 | Teacher Grade Management | 1.5 days |
| 6 | Student Portal | 1 day |
| 7 | Reports, Accounts, Settings | 1 day |
| 8 | Hardening & Cleanup | 0.5 day |
| **Total** | | **~8 days** |

---

## Prerequisites Before Starting

1. XAMPP is running (Apache + MySQL)
2. phpMyAdmin accessible at `http://localhost/phpmyadmin`
3. PHP version ≥ 8.0 (check: `php -v` in terminal)
4. PDO and PDO_MySQL extensions enabled (default in XAMPP)
5. `mod_rewrite` enabled in Apache (for clean API URLs — optional)

---

## Definition of Done

- [x] All 3 portals log in via PHP session (no localStorage auth)
- [x] All CRUD operations (students, grades, accounts) hit MySQL
- [x] No mock `.js` data files remain
- [x] No `.html` pages remain (all converted to `.php`)
- [x] SQL injection impossible (all queries use PDO prepared statements)
- [x] Direct URL access to protected pages redirects to login
- [x] Passwords stored as bcrypt hashes (not plaintext)
- [x] Print reports render correctly from DB data
- [x] System works end-to-end on XAMPP locally

---

_SPSMIS Full-Stack Conversion Plan · Minanga Integrated School · S.Y. 2025–2026_
