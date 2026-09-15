# SPSMIS SYSTEM MEMORY & SYNCHRONIZATION BLUEPRINT
**Student Profiling and Management Information System — Minanga Integrated School**  
**School Year:** 2025–2026 | **Version:** 2.0 Full-Stack (PHP 8 + MySQL PDO + Bootstrap 5)

---

## 1. System Overview & Core Architecture

SPSMIS is a role-based school management portal for Minanga Integrated School (Minanga, Piat, Cagayan). It is built with native PHP 8 (PDO), MySQL, Bootstrap 5, Chart.js 4, and Vanilla JavaScript.

### Core Roles & Target Devices
- **Administrator Portal** (`views/admin/`): Desktop-only (1024px+). System config, student/teacher/section/subject/account management, analytics, and DepEd reports.
- **Teacher Portal** (`views/teacher/`): Desktop-only (1024px+). Advisory class overview, student profiles, DepEd SF10 quarterly grading (Q1–Q4 auto-calc), and grade summaries.
- **Student Portal** (`views/student/`): Fully responsive (mobile 320px+, tablet 768px+, desktop 1024px+). Grades viewer (SF10 table & mobile cards), announcements, profile overview, top desktop navbar, bottom mobile navigation.
- **Auth System** (`views/auth/`, `api/auth/`): Role-aware login with 3-attempt lockout, 3-step security question password reset, and session role guards.

---

## 2. Master File Map & Directory Inventory

```
SPSFMS/
├── index.php                           # Entry point & role selector
├── system_memory_check.php             # Automated system integrity & sync checker
├── SYSTEM_MEMORY.md                    # System architecture & dependency blueprint
├── Progress.md                         # Project progress tracker
├── README.md                           # General documentation
├── .htaccess                           # URL rewrites & security headers
│
├── config/
│   ├── database.php                    # PDO MySQL connection & session bootstrap
│   └── constants.php                   # School metadata, grade levels, sections, subjects
│
├── includes/
│   ├── head.php                        # Shared <head> meta, CSS libraries (Bootstrap, FA)
│   ├── auth_check.php                  # requireAuth(), getLoggedInUser(), redirectByRole()
│   ├── admin-sidebar.php               # Admin navigation menu ($activePage driven)
│   ├── teacher-sidebar.php             # Teacher navigation menu ($activePage driven)
│   ├── student-navbar.php              # Responsive student navigation (desktop topbar & mobile bottom nav)
│   └── chart-download-menu.php         # Dropdown menu helper for exporting Chart.js
│
├── database/
│   ├── schema.sql                      # Complete MySQL database schema (9 tables)
│   ├── setup.php                       # Web database installer & sample data seeder
│   └── spsmis.sql                      # Full phpMyAdmin SQL dump
│
├── views/
│   ├── auth/
│   │   ├── login.php                   # Role-aware login page with lockout UI
│   │   ├── forgot-password.php         # 3-step security question reset
│   │   └── register.php                # Student account registration
│   ├── admin/
│   │   ├── dashboard.php               # KPI cards & 3 live Chart.js graphs
│   │   ├── students.php                # Student profiling CRUD (modals, filters)
│   │   ├── teachers.php                # Teacher management (adviser & subject assignments)
│   │   ├── sections.php                # Class sections management
│   │   ├── subjects.php                # Subject catalogue management (Elem, JHS, SHS)
│   │   ├── reports.php                 # Printable DepEd masterlists & enrollment reports
│   │   ├── signatories.php             # Report signatory customization
│   │   ├── accounts.php                # System users & account status toggling
│   │   ├── analytics.php               # Demographic charts & enrollment analytics
│   │   ├── settings.php                # Admin profile, credentials, security QT
│   │   ├── security-questions.php      # Global security question catalogue
│   │   └── school-years.php            # Academic calendar & active school year management
│   ├── teacher/
│   │   ├── dashboard.php               # Advisory overview & grading completion status
│   │   ├── student-profiles.php        # Read-only student browser for teachers
│   │   ├── grades.php                  # SF10 grade entry grid (Q1-Q4, remarks, auto-save)
│   │   ├── reports.php                 # Printable class grade summary & individual learner SF10
│   │   └── settings.php                # Teacher credentials update
│   └── student/
│       ├── dashboard.php               # Announcements & summary grade cards
│       ├── profile.php                 # Student personal/family profile with self-edit capability
│       └── settings.php                # Student credentials update
│
├── api/
│   ├── auth/
│   │   ├── login.php                   # POST: Authenticates credentials & starts session
│   │   ├── logout.php                  # GET: Clears session & redirects to login
│   │   ├── register.php                # POST: Registers new student account
│   │   ├── forgot-step1.php            # POST: Returns user's security question
│   │   ├── forgot-step2.php            # POST: Validates answer & resets password
│   │   └── change-password.php         # POST: Updates logged-in user's password
│   ├── students/
│   │   ├── index.php                   # GET: Filter/list students · POST: Create student
│   │   └── manage.php                  # GET: Single student · PUT/POST: Update student
│   ├── teachers/
│   │   └── index.php                   # GET/POST/PUT/DELETE: Teacher profiles & classes
│   ├── sections/
│   │   └── index.php                   # GET/POST/PUT/DELETE: Grade level sections
│   ├── subjects/
│   │   └── index.php                   # GET/POST/PUT/DELETE: Subjects by level group
│   ├── grades/
│   │   ├── index.php                   # GET: Class grade list by level/section
│   │   └── student.php                 # GET/POST: Student grades entry & retrieval
│   ├── accounts/
│   │   ├── index.php                   # GET: All user accounts
│   │   ├── create.php                  # POST: Create user account
│   │   ├── toggle.php                  # POST: Toggle active/inactive status
│   │   ├── update-profile.php          # POST: Edit user profile details
│   │   └── update-security.php         # POST: Update security question/answer
│   ├── signatories/
│   │   └── index.php                   # GET/POST: Manage report signatories
│   ├── security-questions/
│   │   └── index.php                   # GET/POST/PUT/DELETE: Security question bank
│   ├── school-years/
│   │   └── index.php                   # GET/POST: School years CRUD & active activation
│   └── analytics/
│       └── index.php                   # GET: Aggregated enrollment & demographic data
│
└── assets/
    ├── css/
    │   ├── theme.css                   # Global CSS variables, colors, typography, tables
    │   ├── admin.css                   # Sidebar, topbar, cards, desktop warning overlay
    │   └── student-mobile.css          # Mobile layout, bottom navigation, card views
    ├── js/
    │   └── components.js               # Toast alerts, confirm modal, loading spinner
    └── lib/                            # Vendored offline libraries (Bootstrap, FontAwesome, Chart.js)
```

---

## 3. Database Schema Memory (All 9 Tables)

### Table Relationships & Key Entities
```
           ┌──────────────┐
           │    users     │
           └──────┬───────┘
                  │ 1:N
                  ▼
         ┌─────────────────┐
         │ teacher_classes │ (teacher_id -> users.id)
         └─────────────────┘
                  
           ┌──────────────┐
           │   students   │
           └──────┬───────┘
                  │ 1:N
                  ▼
         ┌─────────────────┐
         │     grades      │ (student_id -> students.id)
         └─────────────────┘
```

1. **`users`**: System authentication accounts for `admin`, `teacher`, `student`.
   - Fields: `id`, `role` (enum), `username`, `password` (bcrypt), `name`, `email`, `position`, `advisory_grade`, `advisory_subject`, `teaching_subjects`, `lrn`, `grade_level`, `section`, `status` (`active`/`inactive`), `sec_question`, `sec_answer`, timestamps.
2. **`teacher_classes`**: Multi-class advisory/subject assignment for teachers.
   - Fields: `id`, `teacher_id` (FK -> `users.id` CASCADE), `grade_level`, `section`, `created_at`. Unique key on `(teacher_id, grade_level, section)`.
3. **`students`**: DepEd standard student demographic and profiling data.
   - Fields: `id`, `lrn` (unique), `grade_level`, `section`, `first_name`, `middle_name`, `last_name`, `sex` (`Male`/`Female`), `birthdate`, `age`, `mother_tongue`, `religion`, `address`, `mother_name`, `father_name`, `guardian_name`, `guardian_relation`, `contact`, `email`, `school_year`, `status`, timestamps.
4. **`grades`**: DepEd Form 137 / SF10 quarterly grades.
   - Fields: `id`, `student_id` (FK -> `students.id` CASCADE), `school_year`, `grade_level`, `section`, `subject`, `q1`, `q2`, `q3`, `q4`, `final_grade`, `remarks` (`Passed`/`Failed`), timestamps. Unique key on `(student_id, school_year, subject)`.
5. **`announcements`**: School notices shown on student & teacher dashboards.
   - Fields: `id`, `title`, `body`, `audience` (`all`, `student`, `teacher`), `posted_at`.
6. **`sections`**: Masterlist of active class sections per grade level.
   - Fields: `id`, `grade_level`, `section_name`, `created_at`. Unique key on `(grade_level, section_name)`.
7. **`subjects`**: Subject catalog per grade group (`elementary`, `jhs`, `shs`).
   - Fields: `id`, `name`, `grade_type` (enum), `created_at`. Unique key on `(name, grade_type)`.
8. **`security_questions`**: Global question bank for password resets.
   - Fields: `id`, `question` (unique), `created_at`.
9. **`report_signatories`**: Custom signatories configured for SF10 and masterlist printouts.
   - Fields: `id`, `prepared_by_type`, `prepared_by_user_id`, `prepared_by_name`, `prepared_by_title`, `noted_by_type`, `noted_by_user_id`, `noted_by_name`, `noted_by_title`, `updated_at`.
10. **`school_years`**: Masterlist of school years and current active academic calendar.
   - Fields: `id`, `year_label` (unique), `is_active` (boolean flag), `start_date`, `end_date`, `created_at`.

---

## 4. Inter-File Synchronization Dependency Matrix

Whenever any file or logic in the system is changed or refactored, the developer or AI agent **MUST** synchronize the corresponding dependent files according to this matrix:

| If You Modify... | You MUST Synchronize These Files: | What to Check & Update |
| :--- | :--- | :--- |
| **Database Table / Column** | 1. `database/schema.sql`<br>2. `database/setup.php`<br>3. `config/constants.php`<br>4. Relevant `api/*/*.php`<br>5. Consuming `views/*/*.php`<br>6. `system_memory_check.php` | • Ensure table/column definition is in both `schema.sql` & `setup.php`.<br>• Update SQL queries (`SELECT`, `INSERT`, `UPDATE`).<br>• Update form modal fields & table column headers.<br>• Update JS fetch payload and response rendering.<br>• Update expected tables list in memory checker. |
| **API Endpoint** (`api/...`) | 1. Views making `fetch()` calls<br>2. HTTP method handlers (`GET`, `POST`, `PUT`, `DELETE`)<br>3. Role validation `$_SESSION['user']['role']`<br>4. Error status codes & JSON keys (`ok`, `message`) | • Ensure client-side JS sends the expected keys and Content-Type.<br>• Ensure response JSON matches `{ok: true/false, ...}` structure.<br>• Verify role guard matches who is allowed to call the API. |
| **Admin Navigation / View** | 1. `includes/admin-sidebar.php`<br>2. `$activePage` variable in view<br>3. `includes/auth_check.php`<br>4. Desktop overlay in view | • Add link to `$navItems` in `admin-sidebar.php`.<br>• Set `$activePage = '<key>'` before including sidebar.<br>• Include `requireAuth('admin')` at line 2.<br>• Include desktop required overlay. |
| **Teacher Navigation / View** | 1. `includes/teacher-sidebar.php`<br>2. `$activePage` variable in view<br>3. `includes/auth_check.php`<br>4. `views/teacher/reports.php` | • Add link to `$navItems` in `teacher-sidebar.php`.<br>• Set `$activePage = '<key>'` before including sidebar.<br>• Include `requireAuth('teacher')` at line 2.<br>• Teacher reports are strictly locked to their assigned advisory class only. |
| **Student Navigation / View** | 1. Mobile bottom navigation bar<br>2. Desktop top navigation bar (`includes/student-navbar.php`)<br>3. `assets/css/student-mobile.css`<br>4. `includes/auth_check.php` | • Ensure nav links match view files (`dashboard.php`, `profile.php`, `settings.php`).<br>• Include `requireAuth('student')` at top.<br>• Verify mobile viewport responsiveness (320px–767px) and desktop layout (768px–1200px+). |
| **School Year** | 1. `config/constants.php` (`SCHOOL_YEAR`, `getActiveSchoolYear()`, `getSchoolYearsList()`)<br>2. `database/schema.sql` & `database/setup.php`<br>3. `api/school-years/index.php`<br>4. `views/admin/school-years.php`<br>5. Dropdowns in `views/teacher/grades.php`, `views/teacher/reports.php`, `views/admin/students.php` | • Ensure only one school year has `is_active = 1`.<br>• Active school year dynamically populates as the default across teacher and admin modules.<br>• Dropdown options load dynamically from `getSchoolYearsList()`. |
| **Grade Levels / Sections** | 1. `config/constants.php` (`GRADE_LEVELS`, `SECTION_MAP`)<br>2. `database/schema.sql` (default section inserts)<br>3. `database/setup.php` (default section inserts)<br>4. Grade dropdown filters across views (`students.php`, `grades.php`, `sections.php`, `teachers.php`) | • Ensure grade level string matches format `"Grade X"`.<br>• Dynamic section map loads from `sections` table with fallback to constants.<br>• Filter dropdowns populate accurately. |
| **Subjects** | 1. `config/constants.php` (`getSubjectsForGrade()`)<br>2. `database/schema.sql` & `database/setup.php`<br>3. `api/subjects/index.php`<br>4. `views/admin/subjects.php`<br>5. `api/grades/student.php`<br>6. `views/teacher/grades.php` | • `getSubjectsForGrade()` queries `subjects` table dynamically by `grade_type` with constant fallback.<br>• Newly added/edited subjects in Admin automatically appear in Teacher SF10 grade cards.<br>• Previously recorded subject grades are preserved even if catalog changes. |
| **Auth / Roles / Sessions** | 1. `api/auth/login.php`<br>2. `api/auth/logout.php`<br>3. `includes/auth_check.php`<br>4. `index.php` role redirection | • Ensure session keys (`$_SESSION['user']`, `['role']`, `['id']`, `['name']`) remain uniform.<br>• Ensure `redirectByRole()` handles all active roles.<br>• Verify cache control headers prevent back-button after logout. |

---

## 5. Automated System Memory Checker

The system includes an automated integrity checker (`system_memory_check.php`) that runs 7 validation suites:
1. **PHP Syntax & Tokenizer Check**: Validates syntax across all PHP files.
2. **Include / Require Dead-Link Detection**: Verifies all `require_once` and `include` paths resolve.
3. **API Endpoint & Client Fetch Synchronization**: Extracts all `fetch()` calls in views/scripts and validates target endpoints.
4. **Sidebar Navigation vs Existing Views**: Checks all sidebar links for missing views or unlinked pages.
5. **Role Authentication Guards**: Ensures every portal view contains strict `requireAuth(...)` checks.
6. **Database Schema & Table Synchronization**: Verifies all 9 core tables in `schema.sql`, `setup.php`, and live MySQL.
7. **Static Asset Verification**: Confirms all CSS, JS, icon fonts, and school logos exist locally.

### How to Run the Memory Checker:
- **Via Command Line**:
  ```bash
  php system_memory_check.php
  ```
- **Via Web Browser**:
  ```
  http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/system_memory_check.php
  ```

---

## 6. Developer & AI Refactoring Protocol

When modifying or refactoring code in this repository:
1. **Consult This Memory Document**: Review dependencies in Section 4 before touching any file.
2. **Execute Atomic Changes**: Keep logic consistent across database, API, and UI layers.
3. **Never Leave Disconnected Code**: If an API is changed, immediately update the client script calling it.
4. **Run Verification Command**: Always run `php system_memory_check.php` after making any change to guarantee 0 broken links and 100% synchronization.
