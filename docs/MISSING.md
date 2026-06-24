# SPSMIS — What's Missing (Why It's at 80%)

> Last reviewed: 2026-06-20
> Current progress: **80%** (Frontend 75% · Backend 80% · Database 90%)

---

## Database — 90% (Missing 10%)

| # | Missing | Detail |
|---|---------|--------|
| 1 | **`teacher_classes` table** | No proper table linking teachers to their assigned grade level/section. The teacher dashboard currently guesses the advisory class by parsing the `position` string (`strpos($user['position'], 'Grade 7')`), which is fragile and unscalable. |
| 2 | **`photo_path` column on `students`** | No column to store a student profile photo path. Students only display initials. |
| 3 | **Security answers are plain text** | `sec_answer` in `users` table stores answers as plain text. They should be hashed (e.g., `password_hash()`). |
| 4 | **No audit/activity log table** | No `activity_logs` table to track who added, edited, or deleted a record and when. |
| 5 | **`age` column will become stale** | `age` is stored as a static `TINYINT` inserted at enrollment time. It will not update over time. Should be computed from `birthdate` instead. |
| 6 | **Missing query indexes** | No indexes on `students.grade_level`, `students.section`, `students.status` — columns used in nearly every filtered query. |

---

## Backend — 80% (Missing 20%)

| # | Missing | Detail |
|---|---------|--------|
| 1 | **No DELETE / deactivate endpoint for students** | `api/students/manage.php` only handles `GET` (fetch one) and `POST` (update). There is no way to deactivate or archive a student through the API. |
| 2 | **No announcements CRUD API** | There is no `api/announcements/` folder or endpoint. Announcements can only be seeded via `database/setup.php`. Admins cannot create, edit, or delete announcements through the UI. |
| 3 | **No CSV / export endpoint** | No API endpoint to export student lists or grade reports as CSV or Excel. |
| 4 | **Security answer not verified properly** | `api/auth/forgot-step2.php` compares the submitted answer against plain text in the DB. If answer hashing is added to the DB (item above), this logic must also be updated to use `password_verify()`. |
| 5 | **Teacher-to-class assignment is hardcoded** | `views/teacher/dashboard.php` determines the advisory class from the `position` field string. There is no API or admin UI to assign a teacher to a section. |
| 6 | **No server-side rate limiting on login** | Login lockout (3 attempts → 15s wait) is enforced only in the browser JavaScript. The backend `api/auth/login.php` has no rate limiting — anyone can bypass it by calling the API directly. |
| 7 | **No CSRF protection** | POST forms and fetch calls do not use CSRF tokens. Any page can make cross-site requests to the API endpoints while a session is active. |
| 8 | **Final grade is not auto-computed server-side** | `grades.final_grade` is submitted by the frontend. The backend does not recompute or validate it as the average of `(q1 + q2 + q3 + q4) / 4`. A malicious or buggy client could submit incorrect finals. |
| 9 | **No student photo upload endpoint** | No `api/students/upload-photo.php` or file handling logic exists yet. |
| 10 | **`api/accounts/index.php` only returns data** | It lists users (`GET`) but there is no `POST` endpoint in this file to create a new user account from the admin UI — creation relies on separate `update-profile.php`. |

---

## Frontend — 75% (Missing 25%)

| # | Missing | Detail |
|---|---------|--------|
| 1 | **No student deactivate / delete UI** | The admin student management page (`views/admin/students.php`) has Add and Edit but no deactivate or archive button wired to the backend. |
| 2 | **No announcements management page for Admin** | There is no `views/admin/announcements.php`. Admins cannot post, edit, or delete announcements from the dashboard. |
| 3 | **Student photo upload UI missing** | Student add/edit modal has no file input for a profile photo. |
| 4 | **Teacher advisory class assignment UI missing** | Admins have no UI to assign a teacher to a specific grade level and section. |
| 5 | **Analytics page may be a shell** | `views/admin/analytics.php` includes the sidebar and navbar but the actual chart/data rendering via `api/analytics/index.php` has not been verified as fully wired. |
| 6 | **No real PDF export** | Teacher and admin report pages have `@media print` CSS but no actual PDF generation (e.g., using a library like TCPDF or dompdf). The "print" button triggers the browser print dialog only. |
| 7 | **SF10 grade form not validated per subject rules** | The grade entry form in `views/teacher/grades.php` accepts any number in grade inputs but does not enforce DepEd rules (e.g., minimum passing grade of 75, valid range 60–100). |
| 8 | **No pagination on student list** | The admin student management page loads all students from the API in one call. With large datasets this will be slow and the table will become unmanageable. |
| 9 | **Teacher dashboard advisory class is hardcoded** | The teacher dashboard shows only the class inferred from `position` string. If a teacher handles multiple sections or the position field is blank, the view breaks or shows the wrong class. |
| 10 | **Student dashboard announcements section** | Announcements on the student dashboard are fetched but only 3 are shown with no "view all" or pagination. If there are no announcements, there is no fallback empty state message visible. |
| 11 | **No account creation form for Admin** | The admin accounts page shows user list and can toggle status, but there is no modal or form to create a brand-new teacher or student user account from the UI. |
| 12 | **Mobile view only for students** | Student portal uses `student-mobile.css` and works on phones. Admin and Teacher portals show a full-screen "Desktop Required" overlay on mobile — no responsive fallback for those roles. |

---

## Summary Table

| Layer    | Done | Missing | Score |
|----------|------|---------|-------|
| Database | 90%  | 10%     | 9/10  |
| Backend  | 80%  | 20%     | 8/10  |
| Frontend | 75%  | 25%     | 7.5/10 |
| **Overall** | **80%** | **20%** | **8/10** |

---

## Priority Order (What to Build Next)

1. **Announcements CRUD** — API + admin page (high impact, users can't post anything)
2. **Student deactivate** — DELETE/toggle endpoint + UI button
3. **Teacher-class assignment** — DB table + admin UI + teacher dashboard fix
4. **Server-side rate limiting** — Security fix, quick to add
5. **Final grade server-side validation** — Prevent bad data entry
6. **CSV export** — Admin and teacher reports need this
7. **CSRF tokens** — Security hardening
8. **Student photo upload** — DB column + API + UI
9. **Pagination** — Performance, needed before real data
10. **Security answer hashing** — Data protection fix
