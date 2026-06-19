# Student Profiling System for Minanga Integrated School (SPSMIS)

A presentation-ready frontend prototype for the Student Profiling System of Minanga Integrated School. Built with HTML5, CSS3, Bootstrap 5, JavaScript, and Chart.js. No backend, no database — all data is powered by `localStorage` and mock JSON.

---

## Portals

| Portal        | Target Device     | Access                     |
| ------------- | ----------------- | -------------------------- |
| Administrator | Desktop (1024px+) | `admin / admin123`         |
| Teacher       | Desktop (1024px+) | `teacher / teacher123`     |
| Student       | Mobile (375px+)   | `student2025 / student123` |

---

## Getting Started

Requires a local web server (XAMPP recommended).

1. Place the project folder inside `C:/xampp/htdocs/`
2. Start Apache in XAMPP Control Panel
3. Open your browser and go to:

```
http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/
```

4. Select a role and log in using the demo credentials above.

---

## Features

### Administrator Portal

- **Dashboard** — KPI cards (Total, Elementary, JHS, SHS students) + 3 Chart.js charts
- **Student Management** — Add, View, Edit, Search, and Filter student records (localStorage)
- **Reports** — Enrollment List, Student Masterlist, Gender Summary with print support
- **Account Management** — Activate/Deactivate user accounts
- **Analytics** — 3-year enrollment trend, level distribution, top sections, gender by level
- **Settings** — Profile, password change, security question, logout

### Teacher Portal

- **Dashboard** — Advisory class overview with grade status per student
- **Student Profiles** — Read-only view of all student records
- **Grade Management (SF10)** — DepEd-style SF10 grade card with quarter entry, auto-computed final grade and remarks, save to localStorage
- **Reports** — Class grade summary with print support
- **Settings** — Profile, password change, security question, logout

### Student Portal (Mobile)

- **Dashboard** — Profile summary card, quick actions, announcements, grade list
- **Profile** — Full read-only student profile
- **Settings** — Password change, security question, logout
- **Bottom Navigation** — Home, Grades, Profile, Settings

---

## Tech Stack

| Technology         | Purpose                           |
| ------------------ | --------------------------------- |
| HTML5              | Page structure                    |
| CSS3 + Bootstrap 5 | Styling and layout                |
| JavaScript (ES6)   | Logic, interactions, localStorage |
| Chart.js 4         | Dashboard and analytics charts    |
| Font Awesome 6     | Icons                             |

---

## Project Structure

```
SPSFMS/
├── index.html                    # Entry point — role selector
├── views/
│   ├── auth/
│   │   ├── login.html            # Shared login page (role-aware)
│   │   └── forgot-password.html  # 3-step security question reset
│   ├── admin/                    # Admin portal pages
│   ├── teacher/                  # Teacher portal pages
│   └── student/                  # Student mobile portal pages
├── components/
│   ├── admin-sidebar.html        # Reusable admin navigation
│   └── teacher-sidebar.html      # Reusable teacher navigation
├── assets/
│   ├── css/
│   │   ├── theme.css             # Shared CSS variables and base styles
│   │   ├── admin.css             # Admin/Teacher layout styles
│   │   └── student-mobile.css    # Mobile portal styles
│   └── js/
│       ├── storage.js            # Session and localStorage helpers
│       ├── components.js         # Component loader, toasts, modals, utilities
│       └── auth.js               # Role-based auth guards and page init
└── data/
    ├── mock-users.js             # 4 user accounts (admin, 2 teachers, 1 student)
    ├── mock-students.js          # 25 student records across all grade levels
    └── mock-grades.js            # Quarterly grades for sample students
```

---

## Mock Data

- **25 students** across Grade 1, 4, 7, 8, 10, 11, and 12
- **Grade levels covered:** Elementary (Gr. 1–6), Junior High (Gr. 7–10), Senior High (Gr. 11–12 — STEM, ABM, HUMSS)
- **Required students included:** Juan Dela Cruz, Maria Santos, Pedro Reyes
- **Pre-loaded grades** for Grade 7 and Grade 11 STEM students

All data persists in `localStorage` across page refreshes. To reset to defaults, clear `localStorage` in browser DevTools.

---

## Docs

| File                                                | Description                                            |
| --------------------------------------------------- | ------------------------------------------------------ |
| `docs/PLAN.md`                                      | Full phase-by-phase implementation plan                |
| `docs/AUDIT.md`                                     | Specification audit — gaps, risks, and recommendations |
| `docs/SPSMIS_Claude_AI_Prototype_Specification.pdf` | Original project specification                         |

---

## Notes

- No backend, PHP, or database of any kind
- All changes (add student, edit grades, toggle accounts) are saved to `localStorage`
- The Admin and Teacher portals show a full-screen warning on screens below 1024px
- The Student portal is optimized for 320px–768px (mobile-first)
- Print CSS is included on Reports pages — use browser print (`Ctrl+P`) for clean output

---

_Minanga Integrated School — S.Y. 2025–2026 | SPSMIS v1.0_
