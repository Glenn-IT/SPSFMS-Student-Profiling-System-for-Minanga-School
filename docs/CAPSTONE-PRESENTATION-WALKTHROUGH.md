# SPSMIS — Capstone Defense Presentation Walkthrough & Panelist Demonstration Guide
<!-- System: Student Profiling and Management Information System for Minanga Integrated School (SPSMIS) -->
<!-- Target Audience: Capstone Panelists, Advisers, and Evaluators -->
<!-- Developers: Bea O. Eneres, Ritchilyn A. Orpilla, Christian Roque | BSIT 4th Year · Cagayan State University – Piat Campus -->

---

## 🧭 Executive Summary & Timing Strategy

| Phase | Section | Recommended Duration | Primary Interface |
| :--- | :--- | :--- | :--- |
| **Phase 1** | Project Rationale & Rural School Context | 1.5 mins | Title Slide / [index.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/index.php) |
| **Phase 2** | Technical Architecture, RBAC & Security Baseline | 1.0 min | [SYSTEM_MEMORY.md](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/SYSTEM_MEMORY.md) |
| **Phase 3** | Portal Gateway & Institutional Identity | 0.5 min | [index.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/index.php) |
| **Phase 4** | Authentication Security & 3-Attempt Lockout Defense | 1.0 min | [views/auth/login.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/auth/login.php) |
| **Phase 5** | Administrator Command Center & Live Enrollment Analytics | 1.0 min | [views/admin/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/dashboard.php) |
| **Phase 6** | DepEd Standard Student Profiling & Demographic Management | 1.5 mins | [views/admin/students.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/students.php) |
| **Phase 7** | Academic Structure: Sections, Subjects & School Year Control | 1.0 min | [views/admin/school-years.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/school-years.php) |
| **Phase 8** | Teacher Workload & Advisory Class Assignments | 1.0 min | [views/admin/teachers.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/teachers.php) |
| **Phase 9** | DepEd Form 9 (SF9) Report Card & Masterlist Reports | 1.5 mins | [views/admin/reports.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/reports.php) |
| **Phase 10** | User Governance, Status Toggling & Security Questions | 0.5 min | [views/admin/accounts.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/accounts.php) |
| **Phase 11** | Teacher Portal & Advisory Class Grading Center | 1.0 min | [views/teacher/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/dashboard.php) |
| **Phase 12** | DepEd SF10 Term Grading Grid & Automated Computation | 1.5 mins | [views/teacher/grades.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/grades.php) |
| **Phase 13** | Teacher Class Grade Summary & Printable SF9 Preview | 1.0 min | [views/teacher/reports.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/reports.php) |
| **Phase 14** | Mobile Student Portal & Real-time Academic Transparency | 1.0 min | [views/student/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/student/dashboard.php) |
| **Phase 15** | Automated System Integrity Check & Transition to Q&A | 0.5 min | [system_memory_check.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/system_memory_check.php) |
| **Total** | **Full System Presentation** | **~15.0 mins** | — |

---

## 🛠️ Pre-Defense Staging & Credentials Setup

Before starting your capstone defense presentation, prepare your workstation:

1. **Browser Windows Setup**:
   * **Window 1 (Main Desktop Browser):** Logged in as **Administrator** (`admin`) or **Teacher** (`teacher`). Keep this in standard desktop view (1024px+ width) to ensure the administrative interface renders completely.
   * **Window 2 (Incognito / Device Emulation):** Open Developer Tools (`F12`), toggle **Device Toolbar** (`Ctrl+Shift+M`), set display to **iPhone 14 / Mobile (375px–420px width)**, and log in as **Student** (`student2025`). This demonstrates the mobile responsive student interface without logging in and out.
2. **Standard Demo Accounts**:
   * **Administrator:** `admin` | Password: `admin123` *(Maria L. Reyes — School Administrator)*
   * **Teacher 1 (Adviser):** `teacher` | Password: `teacher123` *(Ricardo G. Santos — Grade 7 Rizal Adviser / Math Teacher)*
   * **Teacher 2 (Adviser):** `teacher2` | Password: `teacher123` *(Josephine A. Villanueva — Grade 1 Mabini Adviser)*
   * **Student (Demo):** `student2025` | Password: `student123` *(Juan P. Dela Cruz — LRN `123456789001`, Grade 7 Rizal)*
3. **Database Setup & Re-seeding**:
   * Setup & sample seeder script is accessible at [database/setup.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/database/setup.php). Access `http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/database/setup.php` in your browser at any time to re-initialize tables and fresh demo records.
4. **Integrity Validation Check**:
   * Run `php system_memory_check.php` in your terminal prior to presentation to verify **0 errors** across syntax, includes, endpoints, and database tables.

---

### 👥 The Seeded Demo Students & Live Advisory Classes

All student records are pre-configured with authentic DepEd Learner Reference Numbers (LRN), local addresses in Minanga, Piat, Cagayan, family background, and academic grades:

| # | Student Name | LRN | Grade & Section | Track / Type | Academic Standing |
| :- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Juan P. Dela Cruz** | `123456789001` | Grade 7 — Rizal | Junior High | Average: 87.25 (Passed · All Subjects) |
| 2 | **Maria C. Santos** | `123456789002` | Grade 7 — Rizal | Junior High | Average: 91.50 (With Honors) |
| 3 | **Pedro A. Reyes** | `123456789003` | Grade 7 — Rizal | Junior High | Average: 79.13 (Passed · Ready for review) |
| 4 | **Lourdes B. Fernandez** | `123456789004` | Grade 7 — Rizal | Junior High | Average: 83.38 (Passed) |
| 5 | **Ramon E. Bautista** | `123456789005` | Grade 7 — Rizal | Junior High | Average: 73.38 (Needs Improvement) |
| 6 | **Ana B. Garcia** | `100000000001` | Grade 1 — Mabini | Elementary | Enrolled (Adviser: Teacher Villanueva) |
| 7 | **Carlo D. Mendoza** | `100000000002` | Grade 1 — Mabini | Elementary | Enrolled (Adviser: Teacher Villanueva) |
| 8 | **Gerald M. Pascual** | `100000000006` | Grade 4 — Bonifacio | Elementary | Enrolled |
| 9 | **Sofia G. Aquino** | `123456789006` | Grade 8 — Luna | Junior High | Enrolled |
| 10 | **Jerome O. Castillo** | `123456789009` | Grade 10 — Mabini | Junior High | Enrolled |
| 11 | **Lorenzo P. Miranda** | `123456789011` | Grade 11 — STEM | Senior High | Average: 89.25 (Passed) |
| 12 | **Michelle R. Santos** | `123456789012` | Grade 11 — STEM | Senior High | Average: 92.00 (With Honors) |
| 13 | **Paolo N. Cruz** | `123456789015` | Grade 11 — ABM | Senior High | Enrolled |
| 14 | **Rafael J. Morales** | `123456789017` | Grade 12 — HUMSS | Senior High | Enrolled |

---

### 🏫 Academic Catalogue & Level Groupings

| Level Group | Grade Levels Included | Sample Sections | DepEd Standard Subject Scope |
| :--- | :--- | :--- | :--- |
| **Kindergarten** | Kindergarten | Sampaguita | Literacy & Language, Mathematics, Socio-Emotional, Values Ed |
| **Elementary** | Grades 1 to 6 | Mabini, Bonifacio | Filipino, English, Math, Science, AP, EsP, MAPEH, Mother Tongue |
| **Junior High School (JHS)** | Grades 7 to 10 | Rizal, Luna, Mabini | Filipino, English, Math, Science, AP, EsP, TLE, MAPEH |
| **Senior High School (SHS)** | Grades 11 and 12 | STEM, ABM, HUMSS | Oral Comm, Reading & Writing, Gen Math, Statistics, Earth & Life Sci |

---

## 🎬 Step-by-Step Presentation Script (From First to Last)

---

### Step 1: Opening & Local Problem Statement
* **Screen Display:** Title Slide or [index.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/index.php) hero section
* **Estimated Time:** 1.5 minutes
* **Screen Action:** Present the title screen featuring the official seal of Minanga Integrated School ([img/MIS-Logo.jpg](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/img/MIS-Logo.jpg)) and Cagayan State University ([img/CSU-Logo.png](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/img/CSU-Logo.png)).
* **🗣️ Verbal Script:**
  > *"Good morning, honorable members of the panel, our adviser, and guests. We are Bea Eneres, Ritchilyn Orpilla, and Christian Roque, 4th-year BSIT students of Cagayan State University – Piat Campus. Today, we are proud to present our capstone project: **SPSMIS — Student Profiling and Management Information System for Minanga Integrated School**.*
  >
  > *In public rural schools such as Minanga Integrated School here in Piat, student records management has historically relied on physical paper files, manual logbooks, and fragmented spreadsheet files. This traditional setup creates critical challenges: records are vulnerable to physical damage or misplacement, computing term grades across multiple subjects is labor-intensive for teachers, generating official DepEd Form 9 (SF9) progress report cards causes significant administrative delays, and students in remote barangays have no direct access to review their academic progress without traveling to school.*
  >
  > *SPSMIS directly addresses these problems by providing an integrated, role-based information system that centralizes DepEd-compliant student profiling, automates term grade computations, renders printable official report cards, and provides students with an instant mobile portal to track their academic performance."*

---

### Step 2: Technical Architecture, RBAC & Security Baseline
* **Screen Display:** Architecture Diagram or [SYSTEM_MEMORY.md](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/SYSTEM_MEMORY.md)
* **Estimated Time:** 1.0 minute
* **Screen Action:** Point to the system blueprint highlighting the three-tier architecture and security mechanisms.
* **🗣️ Verbal Script:**
  > *"Architecturally, SPSMIS is engineered using native PHP 8 with MySQL via PDO prepared statements, styled with Bootstrap 5, and driven by vanilla JavaScript and Chart.js 4 for real-time reporting.*
  >
  > *The system enforces a rigorous security baseline:
  > 1. **Role-Based Access Control (RBAC):** Defined in [includes/auth_check.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/includes/auth_check.php), strictly isolating `Admin`, `Teacher`, and `Student` sessions.
  > 2. **Ergonomic Device Restraints:** Admin and Teacher grading interfaces enforce a desktop overlay (`min-width: 1024px`) so complex multi-column grading grids and DepEd forms are never distorted on narrow screens.
  > 3. **Defensive Coding:** 100% of database interactions utilize PDO parameter binding in [config/database.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/config/database.php), neutralizing SQL injection risks.
  > 4. **Brute-Force Lockout Engine:** A 3-attempt account lockout mechanism with an automatic 30-second security countdown protects authentication endpoints."*

---

### Step 3: Portal Gateway & Institutional Identity
* **Screen Display:** [index.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/index.php)
* **Estimated Time:** 0.5 minute
* **Screen Action:** Scroll through the clean landing page showing the school branding, system description, role selector cards (*Administrator*, *Teacher*, *Student*), and click the **Developers** link in the footer to trigger [includes/developers-modal.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/includes/developers-modal.php).
* **🗣️ Verbal Script:**
  > *"Starting at our main entry point, users are greeted by the institutional identity of Minanga Integrated School and CSU Piat. The portal provides dedicated login pathways for each user role. Notice in the footer, our Developers Modal provides verified researcher and institution attribution."*

---

### Step 4: Authentication Security & 3-Attempt Lockout Defense
* **Screen Display:** [views/auth/login.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/auth/login.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Open the login page.
  2. Intentionally enter an incorrect password 3 times in succession.
  3. Show the dynamic warning badge, account lockout trigger, and real-time JavaScript countdown timer disabling the submit button.
  4. Once timer expires or upon refreshing, demonstrate the **Forgot Password** link leading to [views/auth/forgot-password.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/auth/forgot-password.php) showing the 3-step security question recovery flow.
  5. Log in with valid credentials: `admin` / `admin123`.
* **🗣️ Verbal Script:**
  > *"To protect confidential student data against unauthorized access, SPSMIS incorporates proactive brute-force defenses. If an unauthorized individual attempts 3 consecutive incorrect logins, the interface triggers an automatic security lockout with a visual countdown timer.*
  >
  > *In the event of forgotten credentials, the system features a 3-step password recovery mechanism: users submit their username, answer their pre-configured security question from our secure question bank, and establish a new encrypted password without requiring administrator database intervention."*

---

### Step 5: Administrator Command Center & Live Enrollment Analytics
* **Screen Display:** [views/admin/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/dashboard.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Point out the top 4 KPI metric cards: *Total Enrolled Students, Elementary Level, Junior High School, Senior High School*.
  2. Highlight the 3 interactive Chart.js graphs:
     - **Enrollment Distribution by Level** (Doughnut chart)
     - **Gender Ratio Breakdown** (Bar chart showing Male vs Female counts)
     - **Per-Grade Level Enrollment** (Horizontal bar chart from Kinder to Grade 12)
  3. Show the **Chart Download Menu** ([includes/chart-download-menu.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/includes/chart-download-menu.php)) allowing instant export of chart graphics for school board presentations.
* **🗣️ Verbal Script:**
  > *"Upon entering the Administrator Portal, the school principal and administrative officers have an immediate bird's-eye view of institutional statistics.
  >
  > *The KPI cards calculate live enrollment figures directly from our database. Three dynamic Chart.js visualizations illustrate enrollment distributions across elementary, junior high, and senior high departments, as well as school-wide gender ratios. Each chart includes our custom download utility, enabling administrators to export high-resolution chart images directly for DepEd divisional reports."*

---

### Step 6: DepEd Standard Student Profiling & Demographic Management
* **Screen Display:** [views/admin/students.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/students.php)
* **Estimated Time:** 1.5 minutes
* **Screen Action:**
  1. Demonstrate the search bar (filter by student name or LRN).
  2. Filter by Grade Level (`Grade 7`) and Section (`Rizal`).
  3. Click **"View Profile"** on student *Juan P. Dela Cruz* to open the detailed modal:
     - Point out the 12-digit DepEd LRN, Birthdate, Age calculation, Mother Tongue, Religion, and Address.
     - Highlight the complete Family & Guardian information (Mother, Father, Guardian contact number).
  4. Click **"Add Student"** to briefly show the registration modal with input validation.
* **🗣️ Verbal Script:**
  > *"The Student Management module is built specifically according to Department of Education basic education information standards. 
  >
  > *Each student profile captures essential demographic fields: the official 12-digit Learner Reference Number (LRN), birthdate, auto-computed age, mother tongue, religion, complete residence address, and emergency guardian contacts. Administrators can instantly search, filter by section or grade level, and maintain complete student lifecycle records."*

---

### Step 7: Academic Structure: Sections, Subjects & School Year Control
* **Screen Display:** [views/admin/school-years.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/school-years.php) & [views/admin/subjects.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/subjects.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. In **School Years**, point out the active indicator for `2025–2026` and the 1-click active activation button.
  2. Navigate to [views/admin/subjects.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/subjects.php): show subjects grouped by level: *Kindergarten, Elementary, Junior High School, Senior High School*.
  3. Navigate to [views/admin/sections.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/sections.php): demonstrate section management per grade.
* **🗣️ Verbal Script:**
  > *"To ensure institutional flexibility year after year, SPSMIS does not hardcode academic calendars or subjects.
  >
  > *Under School Year management, administrators can toggle the active academic year with a single click, automatically cascading active dates across all grading sheets and report cards. Furthermore, our curriculum catalogue in [views/admin/subjects.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/subjects.php) groups subjects strictly by DepEd curriculum level — Kindergarten, Elementary, JHS, and SHS — ensuring teachers only grade subjects relevant to their assigned level."*

---

### Step 8: Teacher Workload & Advisory Class Assignments
* **Screen Display:** [views/admin/teachers.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/teachers.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Locate teacher *Ricardo G. Santos*.
  2. Point out his assigned advisory class: `Grade 7 — Rizal`.
  3. Show the multi-class management capability in `teacher_classes` allowing assignment of advisory classes and teaching loads.
* **🗣️ Verbal Script:**
  > *"In Teacher Management, administrators govern faculty accounts and assign advisory responsibilities. By assigning Mr. Ricardo Santos to Grade 7 Rizal, the database creates a foreign key association in `teacher_classes`. This ensures that when Mr. Santos logs into his portal, he is granted strict, authorized access to grade and monitor only his designated students, upholding data privacy."*

---

### Step 9: DepEd Form 9 (SF9) Report Card & Masterlist Reporting
* **Screen Display:** [views/admin/reports.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/reports.php) & [views/admin/signatories.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/signatories.php)
* **Estimated Time:** 1.5 minutes
* **Screen Action:**
  1. Briefly show [views/admin/signatories.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/signatories.php) where *Prepared By* (Teacher/Adviser) and *Noted By* (School Head / Principal) can be dynamically configured.
  2. Return to **Reports**, select Report Type: **DepEd Form 9 (SF9)**, select student *Juan P. Dela Cruz*, and click **Generate Report**.
  3. Highlight the official DepEd SF9 Progress Report Card rendered via [assets/js/sf9-renderer.js](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/js/sf9-renderer.js) and [assets/css/sf9.css](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/css/sf9.css):
     - Show the **Front Cover**: DepEd Region II header, Division of Cagayan, District of Piat, Learner's Personal Information, Attendance Record (Months, Days of School, Days Present), and Observed Values matrix.
     - Show the **Academic Records Page**: All learning areas (Filipino, English, Math, Science, AP, EsP, TLE, MAPEH), Term 1, 2, 3 grades, Final Grade average, and Passing Remarks.
     - Click **Print / Preview** to demonstrate the standard 11" x 8.5" Letter Landscape print stylesheet with zero page overflows.
* **🗣️ Verbal Script:**
  > *"A crowning feature of SPSMIS is our automated DepEd Form 9 (SF9) Progress Report Card generator. 
  >
  > *Traditionally, teachers manually handwriting or formatting SF9 cards spend weeks balancing computations. SPSMIS renders the complete, DepEd-compliant Letter Landscape report card directly in the browser. 
  >
  > *It displays official division headers, learning areas, quarterly/term evaluations, passing status, attendance tracking, observed DepEd core values, and customizable institutional signatories. When sent to print, our custom [assets/css/sf9.css](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/css/sf9.css) stylesheet guarantees exact two-page alignment without manual margin tuning."*

---

### Step 10: User Governance, Status Toggling & Announcements
* **Screen Display:** [views/admin/accounts.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/accounts.php) & [views/admin/announcements.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/announcements.php)
* **Estimated Time:** 0.5 minute
* **Screen Action:**
  1. Show the Accounts list with color-coded role badges (*Admin, Teacher, Student*).
  2. Point out the 1-click **Active / Inactive** toggle button that instantly suspends or restores user access.
  3. Briefly switch to Announcements to show how notices can be broadcasted to `All`, `Students`, or `Teachers`.
* **🗣️ Verbal Script:**
  > *"User management grants administrators full control over account status. Any account can be deactivated instantly with one click, immediately terminating that user's session.
  >
  > *Furthermore, school announcements posted here are immediately distributed to teachers and students based on audience filtering."*

---

### Step 11: Teacher Portal & Advisory Class Grading Center
* **Screen Display:** Log out of Admin and log in as Teacher: `teacher` / `teacher123` -> [views/teacher/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/dashboard.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Show Teacher Ricardo Santos' dashboard.
  2. Point out the KPI cards: *Advisory Class: Grade 7 Rizal, Total Students: 5, Completed Graded: 4, Pending: 1*.
  3. Highlight the visual grading completion progress bar (80% Complete).
  4. Navigate to [views/teacher/student-profiles.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/student-profiles.php) to demonstrate the teacher's read-only advisory student browser.
* **🗣️ Verbal Script:**
  > *"Now switching to the Teacher Portal. As Mr. Ricardo Santos, the dashboard immediately identifies his assigned advisory section: Grade 7 Rizal.
  >
  > *Notice the grading progress tracker: teachers can see at a glance how many of their advisory students have complete grades encoded. Under Student Profiles, teachers have quick access to essential emergency contact numbers and medical/demographic notes without administrative editing privileges."*

---

### Step 12: DepEd SF10 Term Grading Grid & Automated Computation
* **Screen Display:** [views/teacher/grades.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/grades.php)
* **Estimated Time:** 1.5 minutes
* **Screen Action:**
  1. Select student *Pedro A. Reyes* (LRN: `123456789003`).
  2. The table loads all Grade 7 subjects (Filipino, English, Math, Science, AP, EsP, TLE, MAPEH).
  3. Demonstrate live editing: change Pedro's Term 3 grade in Mathematics from `81` to `86`.
  4. Observe the live recalculation: the Final Grade automatically recalculates from `82.00` to `83.67`, and the remark dynamically displays a green `Passed` badge.
  5. Enter a failing grade (< 75) in a test subject to show the automated red `Failed` remark badge.
  6. Click **"Save All Grades"**; show the instant AJAX toast notification confirmation without page refresh.
* **🗣️ Verbal Script:**
  > *"In the Grade Management module, teachers encode academic grades conforming to DepEd SF10 standards.
  >
  > *As grades are entered for Term 1, Term 2, and Term 3, the client-side JavaScript engine automatically computes the running Final Grade average: (T1 + T2 + T3) / 3. If the computed grade is 75 or above, the system automatically assigns a 'Passed' remark; otherwise, it assigns 'Failed'.
  >
  > *When clicking Save, the data is transmitted asynchronously to [api/grades/student.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/api/grades/student.php) using prepared PDO statements, guaranteeing atomic database persistence with zero data loss."*

---

### Step 13: Teacher Class Grade Summary & Printable SF9 Preview
* **Screen Display:** [views/teacher/reports.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/reports.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Show the **Class Grade Summary** tab listing all 5 advisory students in Grade 7 Rizal with their subject-by-subject grades and General Averages.
  2. Switch to the **DepEd Form 9 (SF9)** tab.
  3. Select student *Maria C. Santos* (General Average: 91.50) and show her generated SF9 card.
  4. Point out the automatic signature blocks for Adviser Ricardo G. Santos and Principal Maria L. Reyes.
* **🗣️ Verbal Script:**
  > *"Under Teacher Reports, teachers can generate a comprehensive Class Grade Masterlist for their advisory section. In addition, teachers can generate individual DepEd SF9 report cards ready for distribution during quarterly card-giving days.
  >
  > *Teacher reports are strictly confined to their own advisory sections, completely preventing cross-class grade viewing or unauthorized alterations."*

---

### Step 14: Mobile Student Portal & Real-Time Academic Transparency
* **Screen Display:** Switch to Window 2 (Mobile View / iPhone 14 frame): [views/student/dashboard.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/student/dashboard.php)
* **Estimated Time:** 1.0 minute
* **Screen Action:**
  1. Display the responsive mobile layout designed with [assets/css/student-mobile.css](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/css/student-mobile.css).
  2. Point out the bottom navigation bar: **Home**, **Grades**, **Profile**, **Settings**.
  3. Show student Juan Dela Cruz's welcome header, school announcements feed, and General Average badge (`87.25 — Passed`).
  4. Scroll through the mobile grade cards: each card cleanly displays the Subject, Term 1, Term 2, Term 3 scores, Final Grade, and color-coded status badge.
  5. Tap **Profile** ([views/student/profile.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/student/profile.php)) to show the read-only student personal and guardian information.
* **🗣️ Verbal Script:**
  > *"Now switching to the Student Portal, optimized for mobile smartphones. Many students and parents in Minanga access the internet primarily via mobile devices.
  >
  > *Our student portal features an intuitive mobile app experience with a bottom navigation bar. Students can view official school announcements, check their term grades the moment teachers post them, review their General Average, and verify their demographic records. 
  >
  > *This eliminates the anxiety and logistical expense of traveling to school simply to verify quarterly grades."*

---

### Step 15: Automated System Integrity Check, Conclusion & Transition to Q&A
* **Screen Display:** Terminal running `php system_memory_check.php` or [system_memory_check.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/system_memory_check.php) in browser
* **Estimated Time:** 0.5 minute
* **Screen Action:**
  1. Run the command or show the test output:
     ```
     SYNTAX       [ OK ] (Passed: 61, Issues: 0)
     INCLUDES     [ OK ] (Passed: 51, Issues: 0)
     APIS         [ OK ] (Passed: 10, Issues: 0)
     SIDEBAR      [ OK ] (Passed: 18, Issues: 0)
     AUTH         [ OK ] (Passed: 21, Issues: 0)
     DATABASE     [ OK ] (Passed: 20, Issues: 0)
     ASSETS       [ OK ] (Passed: 9, Issues: 0)
     ✔ SYSTEM SYNCHRONIZATION STATUS: HEALTHY (0 Critical Failures)
     ```
  2. Face the panel for concluding remarks.
* **🗣️ Verbal Script:**
  > *"To ensure enterprise-grade stability and zero broken links, SPSMIS includes an automated system integrity test suite. As seen here, our validator checks 61 PHP files, 51 include dependencies, all API routes, sidebar links, authentication guards, database schemas, and local assets — passing 100% of checks with zero failures.
  >
  > *In conclusion, SPSMIS provides Minanga Integrated School with a secure, efficient, and DepEd-compliant information management ecosystem that modernizes student profiling, streamlines teacher grading, and fosters academic transparency for students and parents.
  >
  > *Thank you very much, honorable panelists. We are now ready to accept your questions and feedback."*

---

## 🛡️ Capstone Defense Panelist Q&A Cheat Sheet

| Question | Recommended Answer |
| :--- | :--- |
| **Q1: Why create a custom system instead of using DepEd's Learner Information System (LIS)?** | *"DepEd LIS is a national-level enrollment registry that does not handle day-to-day term grade calculations, teacher advisory grade monitoring, local subject catalogues, or direct student mobile grade viewing. SPSMIS serves as the school's localized School Management Information System (SMIS) that bridges this gap while remaining 100% aligned with DepEd Form 9 (SF9) and Form 137 (SF10) standards."* |
| **Q2: How does SPSMIS prevent unauthorized grade alterations or tampering?** | *"Grade modifications are strictly protected across three levels: (1) Session role authentication restricts grading to verified `teacher` accounts; (2) Advisory scoping in [views/teacher/grades.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/teacher/grades.php) ensures teachers can only encode grades for students officially enrolled in their advisory classes via `teacher_classes`; (3) Server-side validation in [api/grades/student.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/api/grades/student.php) executes prepared SQL statements with numeric bounds checking."* |
| **Q3: Why are Admin and Teacher portals restricted to desktop screens?** | *"Admin and Teacher workflows involve high-density tabular data: DepEd SF9 report card matrices, 8-subject term grading grids, and multi-column student masterlists. Viewing and entering such extensive data on a 5-inch smartphone screen leads to high user input errors. Restricting Admin/Teacher to 1024px+ ensures ergonomic accuracy, while the Student Portal is custom-tailored with responsive mobile cards specifically for smartphones."* |
| **Q4: How does the system handle security and compliance with the Data Privacy Act (RA 10173)?** | *"We strictly follow data privacy principles: sensitive passwords are encrypted using bcrypt via `password_hash()`, role separation ensures students only view their own personal records by matching session LRN, brute-force attacks are mitigated by a 3-attempt lockout countdown, and direct SQL parameterization in [config/database.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/config/database.php) prevents unauthorized database extraction."* |
| **Q5: Can the school update school years and curriculum subjects without modifying source code?** | *"Yes. Under Admin settings, administrators can create and switch active School Years in [views/admin/school-years.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/school-years.php) and add or modify class subjects in [views/admin/subjects.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/subjects.php). The system dynamically loads these records from MySQL into dropdowns and grading grids without needing any code edits."* |
| **Q6: What happens if an advisory teacher is reassigned to another section?** | *"Administrators can update teacher assignments in [views/admin/teachers.php](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/views/admin/teachers.php). The database updates the `teacher_classes` table, automatically transferring advisory permissions and student rosters while preserving all previously recorded historical grades in the `grades` table."* |
| **Q7: What ensures that printed SF9 report cards fit standard paper without printing errors?** | *"We developed a dedicated print stylesheet in [assets/css/sf9.css](file:///C:/xampp/htdocs/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/css/sf9.css) configured for standard DepEd 11" x 8.5" Letter Landscape dimensions. All browser navigation bars, sidebars, and control buttons are suppressed via `@media print` rules, guaranteeing clean 2-page printouts ready for school head signatures."* |

---

## 💡 Pro-Tips for Defense Day

1. **Split-Screen / Dual Browser Strategy**:
   * Keep **Window 1 (Admin/Teacher)** on one half of the monitor and **Window 2 (Student Mobile)** on the other half. When you edit a grade in the Teacher portal and save, refresh the Student window to show that the grade and General Average update instantly in real time.
2. **Emphasize Local Impact for Minanga**:
   * Remind the panelists that Minanga Integrated School is located in a rural district of Piat, Cagayan. Highlighting how the mobile student portal saves parents and learners travel costs makes the project strongly socially relevant.
3. **Showcase the Automated Memory Checker**:
   * Running `php system_memory_check.php` live during Step 15 or in response to technical panel questions demonstrates professional engineering discipline that impresses panel evaluators.
4. **Physical Backup**:
   * Have 2–3 copies of printed DepEd Form 9 (SF9) sample report cards on hand to physically distribute to panelists when reaching Step 9.
