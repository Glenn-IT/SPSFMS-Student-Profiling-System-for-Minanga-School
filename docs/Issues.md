# Issues Log & Resolutions

## 1. User Activity Logs: "Error connecting to server"
- **URL**: `http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/user-logs.php`
- **Error**: `Error connecting to server.`
- **Root Cause**:
  In `api/logs/index.php`, line 6 invoked `$user = requireAuth();` with zero arguments. The `requireAuth(string $role)` helper requires exactly 1 argument (the required role string). In PHP 8, calling it without arguments triggered an uncaught `ArgumentCountError` (Fatal Error), returning a 500 response that failed JSON decoding in the view's client-side `fetch()`, thus displaying "Error connecting to server."
- **Resolution**:
  - Updated `api/logs/index.php` to authenticate session credentials via standard API role validation:
    ```php
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'teacher'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Unauthorized access.']);
        exit;
    }
    ```
  - Verified API functionality for both `admin` (view all audit logs with filters & KPIs) and `teacher` (view own activity history).
  - Synchronized `SYSTEM_MEMORY.md` to include `api/logs/index.php`, `views/admin/user-logs.php`, and `views/teacher/activity-logs.php`.
- **Status**: **RESOLVED**

## 2. Kindergarten (K-3) Letter-Based Grading Sheet & Report Cards
- **Reference**: `img/Grade for k-3.jpg` (Official DepEd K-3 Progress Report standard)
- **Requirement**:
  On the grading sheet and official report cards for Kindergarten, grades must be entered and presented as **Letters** (`A`, `B`, `C`, `D`, `E`) based on the official descriptor scale:
  - **A** : Advancing (90–100) — Passed
  - **B** : Benchmarking (80–89) — Passed
  - **C** : Connecting (75–79) — Passed
  - **D** : Developing (65–74) — Failed
  - **E** : Emerging (0–64) — Failed
- **Resolution**:
  - Migrated `grades` table columns (`t1`, `t2`, `t3`, `q1`, `q2`, `q3`, `q4`, `final_grade`) to `VARCHAR(10)` in `database/schema.sql`, `database/setup.php`, and `config/database.php` auto-migration.
  - Added Kindergarten helper functions in `config/constants.php` (`isKindergartenGrade`, `getKinderLetterScore`, `scoreToKinderLetter`, `isKinderLetter`).
  - Updated `views/teacher/grades.php`:
    - Kindergarten mode presents letter dropdowns (`A`, `B`, `C`, `D`, `E`) for Term 1, Term 2, and Term 3 in both Subject Class Sheet and SF10 tabs.
    - Updated standards legend bar with quick-fill actions (`Set Blank to A / B / C`).
    - Live calculation of Final Grade letter and Remarks.
  - Enhanced `api/grades/index.php` and `api/grades/student.php` to calculate and store letter grades for Kindergarten while preserving numeric grades for Grades 1–12.
  - Updated `assets/js/sf9-renderer.js` and `views/teacher/reports.php` to format Kindergarten learner progress and report cards using the official letter descriptors.
- **Status**: **RESOLVED**
