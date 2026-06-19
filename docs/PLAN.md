# SPSMIS Frontend Prototype — Implementation Plan

**Date:** 2026-06-20
**Project:** Student Profiling System for Minanga Integrated School (SPSMIS)
**Type:** Frontend-only prototype (HTML5 / CSS3 / Bootstrap 5 / JavaScript)
**No backend. No PHP. No database. localStorage + mock JSON only.**

---

## Folder Structure

```
SPSFMS/
├── index.html                    # Entry point — role selector / redirect
├── views/
│   ├── auth/
│   │   ├── login.html
│   │   └── forgot-password.html
│   ├── admin/
│   │   ├── dashboard.html
│   │   ├── students.html
│   │   ├── reports.html
│   │   ├── accounts.html
│   │   ├── analytics.html
│   │   └── settings.html
│   ├── teacher/
│   │   ├── dashboard.html
│   │   ├── student-profiles.html
│   │   ├── grades.html
│   │   ├── reports.html
│   │   └── settings.html
│   └── student/
│       ├── dashboard.html
│       ├── profile.html
│       └── settings.html
├── components/
│   ├── admin-sidebar.html
│   ├── admin-navbar.html
│   ├── teacher-sidebar.html
│   ├── student-bottom-nav.html
│   ├── footer.html
│   ├── modal-confirm.html
│   └── toast.html
├── assets/
│   ├── css/
│   │   ├── theme.css             # Shared colors, fonts, variables
│   │   ├── admin.css
│   │   ├── teacher.css
│   │   └── student-mobile.css
│   ├── js/
│   │   ├── auth.js
│   │   ├── components.js         # Component loader
│   │   ├── storage.js            # localStorage helpers
│   │   └── charts.js
│   └── img/
│       └── school-logo.png
└── data/
    ├── mock-users.js
    ├── mock-students.js
    └── mock-grades.js
```

---

## Mock Credentials (define in data/mock-users.js)

| Role          | Username    | Password   |
| ------------- | ----------- | ---------- |
| Administrator | admin       | admin123   |
| Teacher       | teacher     | teacher123 |
| Student       | student2025 | student123 |

---

## Mock Data Targets (define in data/mock-students.js)

- **25 students** across: Grade 1 (Section Mabini), Grade 7 (Section Rizal), Grade 11 (STEM)
- Named students required: Juan Dela Cruz, Maria Santos, Pedro Reyes (+ 22 generated)
- Each student record: LRN, Grade Level, Section, Full Name, Sex, Birthdate, Age, Mother Tongue, Religion, Address, Parent Info, Guardian Info, Contact, Email

---

## Phase Plan

---

### Phase 0 — Project Setup & Mock Data

**Goal:** Establish folder structure, theme, and all mock data before any UI work.

**Tasks:**

- [ ] Create full folder structure as defined above
- [ ] Write `assets/css/theme.css` — define CSS variables: primary color, secondary color, font family, border-radius
- [ ] Write `data/mock-users.js` — admin, teacher, student credentials + profiles
- [ ] Write `data/mock-students.js` — 25 student records across 3 grade levels
- [ ] Write `data/mock-grades.js` — quarterly grades per subject for all students
- [ ] Define school year format: `2025-2026`
- [ ] Define grade levels: Grade 1–6 (Elementary), Grade 7–10 (JHS), Grade 11–12 (SHS — STEM, ABM, HUMSS)

**Deliverable:** All data and theme files ready; no HTML yet.

---

### Phase 1 — Authentication Pages

**Goal:** Login and Forgot Password pages for all roles.

**Pages:**

- `index.html` — role selection card (Admin / Teacher / Student)
- `views/auth/login.html` — shared login form, role passed via URL param or localStorage
- `views/auth/forgot-password.html` — mock security question flow

**Features:**

- Validate credentials against `data/mock-users.js`
- Store session in localStorage: `{ role, userId, name }`
- On success: redirect to role dashboard
- On failure: show inline error toast
- Logout clears localStorage and redirects to login

**Mobile:** Login page must be responsive (works on 375px for Student login).

**Deliverable:** Working login flow for all 3 roles. Logout functional.

---

### Phase 2 — Reusable Layout System

**Goal:** Build shared components loaded by all pages.

**Components to build:**

- `components/admin-sidebar.html` — vertical nav with: Dashboard, Student Management, Reports, Accounts, Analytics, Settings, Logout
- `components/admin-navbar.html` — top bar with school name, user avatar, breadcrumb
- `components/teacher-sidebar.html` — Dashboard, Student Profiles, Grade Management, Reports, Settings, Logout
- `components/student-bottom-nav.html` — 4 tabs: Home (dashboard), Grades, Profile, Settings
- `components/footer.html` — copyright line
- `components/modal-confirm.html` — reusable confirm/cancel modal
- `components/toast.html` — success/error/info toast notification

**Component loading:** `assets/js/components.js` — uses `fetch()` to inject HTML into placeholder `<div>` elements on each page.

**Desktop-only guard:** JavaScript snippet that checks `window.innerWidth < 1024` on Admin and Teacher pages and shows a full-screen overlay warning.

**Deliverable:** Layout shell working on Admin and Teacher portals. Bottom nav working on Student portal.

---

### Phase 3 — Admin Dashboard & Analytics

**Goal:** Admin landing page with KPI cards and charts.

**Page:** `views/admin/dashboard.html`

**KPI Cards:**

- Total Students (sum of all mock students)
- Elementary Students (Grade 1–6)
- Junior High Students (Grade 7–10)
- Senior High Students (Grade 11–12)

**Charts (Chart.js):**

- Enrollment Distribution — Doughnut chart (Elementary / JHS / SHS)
- Gender Distribution — Bar chart (Male vs Female per level)
- Students Per Grade Level — Horizontal bar chart

**Analytics page:** `views/admin/analytics.html`

- Trend line (enrollment by school year — mock data for 3 years)
- Top sections by student count

**Deliverable:** Admin dashboard fully interactive with live chart renders from mock data.

---

### Phase 4 — Student Management Module

**Goal:** Full student list with Add / View / Edit / Search / Filter.

**Page:** `views/admin/students.html`

**Table columns:** LRN, Full Name, Grade Level, Section, Sex, Actions (View, Edit)

**Search:** by Name or LRN (client-side filter)

**Filters:** Grade Level dropdown, Section dropdown, School Year dropdown

**Add Student Modal:**
Fields: LRN, Grade Level, Section, First Name, Middle Name, Last Name, Sex, Birthdate, Age (auto-calculated), Mother Tongue, Religion, Address, Parent Info, Guardian Info, Contact, Email
Save to localStorage.

**View Student Modal:** Read-only display of all fields.

**Edit Student Modal:** Pre-filled form, saves updates to localStorage.

**Deliverable:** Full CRUD on mock students via localStorage. Table updates without page reload.

---

### Phase 5 — Teacher Grade Management

**Goal:** SF10-style grade management for teachers.

**Page:** `views/teacher/grades.html`

**Display:**

- Student selector (dropdown or search)
- SF10 card layout: student info header + grade table
- Subjects: Filipino, English, Mathematics, Science, AP, ESP, TLE/MAPEH
- Quarters: Q1, Q2, Q3, Q4 + Final Grade + Remarks (Passed/Failed)

**Actions:**

- Grade Entry Form (modal) — numeric input per subject per quarter
- Auto-compute Final Grade (average of 4 quarters)
- Auto-compute Remarks (≥75 = Passed)
- Save to localStorage
- View grades (read-only modal)

**Deliverable:** Teacher can enter and save grades for any student. SF10 card renders correctly.

---

### Phase 6 — Student Mobile Portal

**Goal:** Mobile-first student-facing portal.

**Target resolutions:** 320px, 375px, 425px, 768px

**Pages:**

- `views/student/dashboard.html` — Profile summary card (name, grade, section, LRN), Quick Actions (View Grades, View Profile), Recent announcements (mock)
- `views/student/profile.html` — Full read-only profile card
- `views/student/settings.html` — Change Password form, Security Question, Logout button with confirmation modal

**Bottom Navigation (4 tabs):**

- Home (fa-home) → dashboard.html
- Grades (fa-chart-bar) → grades section on dashboard
- Profile (fa-user) → profile.html
- Settings (fa-cog) → settings.html

**UI Standards:**

- Cards replace tables
- Large touch targets (min 44px tap area)
- No horizontal scroll
- Google Classroom-inspired card layout

**Deliverable:** Student portal fully functional on mobile viewport. Bottom nav highlights active tab.

---

### Phase 7 — Reports & Settings Modules

**Goal:** Reports generation and settings pages for Admin and Teacher.

**Admin Reports (`views/admin/reports.html`):**

- Filters: School Year, Grade Level, Section
- Search: Name or LRN
- Report types: Enrollment List, Student Masterlist, Grade Summary
- Table output with print button
- Print CSS: hides navbar/sidebar, formats table for A4

**Admin Account Management (`views/admin/accounts.html`):**

- Table: Username, Role, Status (Active/Inactive), Actions
- Activate / Deactivate toggle (updates localStorage)
- View Account modal

**Admin Settings (`views/admin/settings.html`):**

- Profile Settings: edit display name, email
- Change Password: old password, new password, confirm (mock validation)
- Security Questions: select + answer
- Logout Confirmation Modal

**Teacher Settings (`views/teacher/settings.html`):**

- Same structure as Admin Settings

**Deliverable:** Reports printable. Account management toggles functional. Settings save to localStorage.

---

### Phase 8 — UI Polish, Responsiveness & Testing

**Goal:** Production-ready visual quality for demo/presentation.

**Tasks:**

- [x] Review all pages at 1366×768 (Admin/Teacher) and 375px (Student)
- [x] Add CSS transitions on sidebar links, card hovers, button clicks
- [x] Add loading skeleton placeholders on tables/charts
- [x] Add empty state messages (e.g., "No students found")
- [x] Add page transition fade-in animation
- [x] Test all localStorage operations (add, edit, delete, persist on refresh)
- [x] Test logout clears session and blocks back-button access
- [x] Test desktop-only warning triggers at <1024px on Admin/Teacher
- [x] Cross-browser test: Chrome, Edge, Firefox
- [x] Print test for Reports module
- [x] Final accessibility pass: alt text, aria-labels, contrast ratio

**Deliverable:** Demo-ready prototype. All 8 phases complete.

---

## Timeline Estimate

| Phase     | Description                 | Estimated Effort |
| --------- | --------------------------- | ---------------- |
| 0         | Setup & Mock Data           | 0.5 day          |
| 1         | Authentication              | 0.5 day          |
| 2         | Layout System               | 1 day            |
| 3         | Admin Dashboard & Analytics | 1 day            |
| 4         | Student Management          | 1.5 days         |
| 5         | Teacher Grade Management    | 1.5 days         |
| 6         | Student Mobile Portal       | 1 day            |
| 7         | Reports & Settings          | 1.5 days         |
| 8         | Polish & Testing            | 1 day            |
| **Total** |                             | **~9.5 days**    |

---

## Definition of Done

- All 3 role portals are accessible via login
- All features listed in the spec are implemented and interactive
- Data persists via localStorage across page refreshes
- No backend, server, or PHP code exists anywhere
- Prototype is presentable at 1366×768 (Admin/Teacher) and 375px (Student)
- Print layout works for Reports module
