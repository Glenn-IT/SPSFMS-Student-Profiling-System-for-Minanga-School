# Student Profiling System for Minanga Integrated School (SPSMIS)

A full-stack Student Profiling and Management Information System for Minanga Integrated School. Built with PHP 8, MySQL, Bootstrap 5, and Chart.js. Features three role-based portals — Admin, Teacher, and Student — backed by a real database with session-based authentication.

> **Status:** Ongoing · Progress: **80%** · See [`docs/MISSING.md`](docs/MISSING.md) for what's left.

---

## Portals

| Portal        | Target Device     | Default Credentials        |
| ------------- | ----------------- | -------------------------- |
| Administrator | Desktop (1024px+) | `admin / admin123`         |
| Teacher       | Desktop (1024px+) | `teacher / teacher123`     |
| Student       | Mobile (375px+)   | `student2025 / student123` |

---

## Requirements

- **XAMPP** (Apache + MySQL) — PHP 8.0 or higher
- Browser: Chrome, Firefox, or Edge (latest)

---

## Getting Started

1. Place the project folder inside `C:/xampp/htdocs/`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Set up the database — open your browser and go to:

```
http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/database/setup.php
```

This will create the `spsmis` database, all tables, and seed sample data (users, students, grades, announcements).

4. After setup completes, open the system:

```
http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/
```

5. Select a role and log in using the credentials above.

> Run `setup.php` only once. If you need to reset, drop the `spsmis` database in phpMyAdmin first.

---

## Features

### Administrator Portal

- **Dashboard** — KPI cards (Total, Elementary, JHS, SHS counts) + 3 live Chart.js charts (enrollment, gender, per-grade breakdown)
- **Student Management** — Add, View, Edit, Search, and Filter student records (backed by MySQL)
- **Reports** — Enrollment List, Student Masterlist, Gender Summary with print support
- **Account Management** — View all user accounts, toggle active/inactive status
- **Analytics** — Enrollment by grade level, gender distribution, section breakdown
- **Settings** — Profile update, password change, security question, logout

### Teacher Portal

- **Dashboard** — Advisory class overview with graded/pending count per student
- **Student Profiles** — Read-only view of all active student records with search and grade filter
- **Grade Management (SF10)** — DepEd-style SF10 grade entry with Q1–Q4 inputs, auto-computed final grade and remarks, saved to database
- **Reports** — Class grade summary with print support
- **Settings** — Password change, logout

### Student Portal (Mobile)

- **Dashboard** — Profile summary, announcements, full grade list with quarterly breakdown
- **Profile** — Full read-only student profile (personal, family, contact info)
- **Settings** — Password change, logout
- **Bottom Navigation** — Home, Grades, Profile, Settings

### Auth System

- Role-based login (Admin / Teacher / Student portals are separate)
- 3-attempt lockout with countdown timer
- Forgot password via security question (3-step flow)
- Session-based authentication with server-side role guards

---

## Tech Stack

| Technology         | Purpose                              |
| ------------------ | ------------------------------------ |
| PHP 8              | Backend logic, routing, API          |
| MySQL (PDO)        | Database — users, students, grades   |
| Bootstrap 5        | Styling and responsive layout        |
| Chart.js 4         | Dashboard and analytics charts       |
| Font Awesome 6     | Icons                                |
| Vanilla JavaScript | Fetch API calls, UI interactions     |

---

## Project Structure

```
SPSFMS/
├── index.php                        # Entry point — role selector portal
├── .htaccess                        # URL and access rules
│
├── config/
│   ├── database.php                 # PDO connection + session start
│   └── constants.php                # App name, school year, grade levels, subjects
│
├── includes/
│   ├── auth_check.php               # requireAuth() — session guard per role
│   ├── head.php                     # Shared <head> (meta, CSS, fonts)
│   ├── admin-sidebar.php            # Admin navigation sidebar
│   └── teacher-sidebar.php          # Teacher navigation sidebar
│
├── views/
│   ├── auth/
│   │   ├── login.php                # Role-aware login page
│   │   └── forgot-password.php      # 3-step security question password reset
│   ├── admin/
│   │   ├── dashboard.php            # Admin home with KPI cards + charts
│   │   ├── students.php             # Student management (CRUD)
│   │   ├── analytics.php            # Enrollment analytics charts
│   │   ├── reports.php              # Printable enrollment and masterlist reports
│   │   ├── accounts.php             # User account management
│   │   └── settings.php             # Admin profile and password settings
│   ├── teacher/
│   │   ├── dashboard.php            # Advisory class overview
│   │   ├── student-profiles.php     # Read-only student browser
│   │   ├── grades.php               # SF10 grade entry per student
│   │   ├── reports.php              # Class grade summary report
│   │   └── settings.php             # Teacher password settings
│   └── student/
│       ├── dashboard.php            # Student home (grades + announcements)
│       ├── profile.php              # Student profile view
│       └── settings.php             # Student password settings
│
├── api/
│   ├── auth/
│   │   ├── login.php                # POST — authenticate and create session
│   │   ├── logout.php               # GET  — destroy session and redirect
│   │   ├── forgot-step1.php         # POST — look up username, return security question
│   │   ├── forgot-step2.php         # POST — verify security answer
│   │   └── change-password.php      # POST — change authenticated user's password
│   ├── students/
│   │   ├── index.php                # GET (list + filter) · POST (add)
│   │   └── manage.php               # GET (single) · POST (update)
│   ├── grades/
│   │   ├── index.php                # GET — list grades by class
│   │   └── student.php              # GET/POST — grades for one student
│   ├── accounts/
│   │   ├── index.php                # GET — list all users
│   │   ├── toggle.php               # POST — activate/deactivate user
│   │   ├── update-profile.php       # POST — update user profile
│   │   └── update-security.php      # POST — update security question/answer
│   └── analytics/
│       └── index.php                # GET — enrollment stats for admin analytics
│
├── database/
│   ├── schema.sql                   # Raw SQL schema (run once in phpMyAdmin)
│   └── setup.php                    # Web-based setup + seeder (run once via browser)
│
├── assets/
│   ├── css/
│   │   ├── theme.css                # CSS variables, base styles, shared components
│   │   ├── admin.css                # Admin/Teacher layout (sidebar, navbar, cards)
│   │   └── student-mobile.css       # Student mobile portal styles
│   ├── js/
│   │   └── components.js            # Toast, modal, logout, desktop-only helpers
│   └── lib/                         # Vendored libraries (Bootstrap, Chart.js, FA)
│
└── docs/
    ├── MISSING.md                   # What's incomplete and why (priority build list)
    ├── FULLSTACK_PLAN.md            # Full-stack migration plan
    ├── PLAN.md                      # Implementation phases
    ├── AUDIT.md                     # Spec audit and risk notes
    └── SPSMIS_Claude_AI_Prototype_Specification.pdf
```

---

## Database

Database name: **`spsmis`**

| Table           | Description                                       |
| --------------- | ------------------------------------------------- |
| `users`         | Login accounts for admin, teachers, and students  |
| `students`      | Full student profile records                      |
| `grades`        | Quarterly grades per student per subject (SF10)   |
| `announcements` | School announcements shown on student dashboard   |

---

## Sample Data (seeded by `setup.php`)

- **4 user accounts** — 1 admin, 2 teachers, 1 student login
- **17 students** across Grade 1, 4, 7, 8, 10, 11, and 12
- **Grade levels:** Elementary (Gr. 1–6), Junior High (Gr. 7–10), Senior High (Gr. 11–12 — STEM, ABM, HUMSS)
- **Pre-loaded grades** for Grade 7 Rizal (5 students × 8 subjects) and Grade 11 STEM (2 students × 8 subjects)
- **3 announcements** — one for all, one for students, one for teachers

---

## Docs

| File | Description |
| ---- | ----------- |
| [`docs/MISSING.md`](docs/MISSING.md) | Incomplete features — what's left and build priority |
| [`docs/FULLSTACK_PLAN.md`](docs/FULLSTACK_PLAN.md) | Full-stack PHP/MySQL migration plan |
| [`docs/PLAN.md`](docs/PLAN.md) | Phase-by-phase implementation plan |
| [`docs/AUDIT.md`](docs/AUDIT.md) | Specification audit — gaps, risks, recommendations |
| [`docs/SPSMIS_Claude_AI_Prototype_Specification.pdf`](docs/SPSMIS_Claude_AI_Prototype_Specification.pdf) | Original project specification |

---

## Notes

- Admin and Teacher portals require a desktop screen (1024px+). A full-screen warning is shown on smaller devices.
- The Student portal is mobile-first and optimized for 320px–768px screens.
- Print CSS is included on Reports pages — use `Ctrl+P` for clean output.
- All API endpoints return JSON and require an active session with the correct role.

---

_Minanga Integrated School — S.Y. 2025–2026 | SPSMIS v2.0 | PHP + MySQL_
