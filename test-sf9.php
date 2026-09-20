<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

// Fetch all available students from database for testing
$studentsList = [];
try {
    $stmt = $pdo->query("SELECT id, lrn, first_name, middle_name, last_name, grade_level, section, sex, birthdate, age, track_strand FROM students ORDER BY grade_level, last_name, first_name");
    $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Selected student ID or 'sample_tvl_12'
$selectedId = $_GET['student_id'] ?? 'sample_tvl_12';
$sy = $_GET['sy'] ?? '2026-2027';

// Default Sample Student based exactly on pdf\SF9-TVL-ICT12.pdf
$sampleStudent = [
    'id' => 'sample_tvl_12',
    'lrn' => '109283746199',
    'first_name' => 'Juan',
    'middle_name' => 'Protacio',
    'last_name' => 'Dela Cruz',
    'age' => '18',
    'sex' => 'Male',
    'grade_level' => 'Grade 12',
    'section' => 'TVL-ICT',
    'track_strand' => 'Technical-Vocational-Livelihood (TVL) - ICT',
    'adviser' => 'JOSEPH M. BATUYONG',
    'principal' => 'School Head'
];

$activeStudent = $sampleStudent;
$isRealStudent = false;

if ($selectedId !== 'sample_tvl_12' && is_numeric($selectedId)) {
    try {
        $stStmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stStmt->execute([(int)$selectedId]);
        $row = $stStmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $activeStudent = $row;
            $isRealStudent = true;
            if (empty($activeStudent['age']) && !empty($activeStudent['birthdate'])) {
                $bday = new DateTime($activeStudent['birthdate']);
                $today = new DateTime();
                $activeStudent['age'] = $bday->diff($today)->y;
            }
            if (empty($activeStudent['track_strand'])) {
                $activeStudent['track_strand'] = (strpos($activeStudent['grade_level'], '11') !== false || strpos($activeStudent['grade_level'], '12') !== false)
                    ? 'Technical-Vocational-Livelihood (TVL) - ICT'
                    : 'Basic Education Curriculum (K to 12)';
            }
        }
    } catch (Exception $e) {}
}

// Default curriculum categories for TVL-ICT 12 as per pdf\SF9-TVL-ICT12.pdf
$defaultTvlCategories = [
    'Core Subjects' => [
        ['name' => 'Media and Information Literacy', 't1' => 88, 't2' => 89, 't3' => 90],
        ['name' => 'Contemporary Philippine Arts from the Regions', 't1' => 85, 't2' => 86, 't3' => 88],
        ['name' => 'Earth and Life Science', 't1' => 84, 't2' => 85, 't3' => 86],
        ['name' => 'Physical Science', 't1' => 82, 't2' => 84, 't3' => 85],
        ['name' => 'Physical Education and Health', 't1' => 90, 't2' => 92, 't3' => 91],
    ],
    'Applied Subjects' => [
        ['name' => 'Filipino sa Piling Larang – Tech-Voc', 't1' => 87, 't2' => 88, 't3' => 89],
        ['name' => 'Practical Research II', 't1' => 86, 't2' => 87, 't3' => 89],
        ['name' => 'Entrepreneurship', 't1' => 88, 't2' => 89, 't3' => 90],
        ['name' => 'Inquiries, Investigations and Immersion', 't1' => 89, 't2' => 90, 't3' => 91],
    ],
    'Specialized Subjects' => [
        ['name' => 'Computer Systems Servicing NC II', 't1' => 92, 't2' => 93, 't3' => 94],
        ['name' => 'Work Immersion', 't1' => 93, 't2' => 94, 't3' => 95],
    ]
];

// If real student selected, fetch grades from database
$studentGradeRows = [];
if ($isRealStudent) {
    try {
        $gStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? AND school_year = ?");
        $gStmt->execute([$activeStudent['id'], $sy]);
        $studentGradeRows = $gStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$fullName = trim(($activeStudent['last_name'] ?? '') . ', ' . ($activeStudent['first_name'] ?? '') . ' ' . ($activeStudent['middle_name'] ?? ''));
$lrn = $activeStudent['lrn'] ?? '';
$age = $activeStudent['age'] ?? '';
$sex = $activeStudent['sex'] ?? 'Male';
$grade = $activeStudent['grade_level'] ?? 'Grade 12';
$section = $activeStudent['section'] ?? 'TVL-ICT';
$track = $activeStudent['track_strand'] ?? 'Technical-Vocational-Livelihood (TVL) - ICT';
$adviserName = $activeStudent['adviser'] ?? 'JOSEPH M. BATUYONG';
$principalName = $activeStudent['principal'] ?? 'School Head';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SF9 Test Page - Minanga Integrated School</title>
  <link rel="stylesheet" href="assets/lib/bootstrap.min.css">
  <link rel="stylesheet" href="assets/lib/fontawesome/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
  <style>
    /* =========================================================
       EXACT REPLICA STYLES FOR SF9-TVL-ICT12.pdf
       Minanga Integrated School (Region 02, Cagayan, Piat District)
       ========================================================= */
    :root {
      --sf9-width: 11in;
      --sf9-height: 8.5in;
    }

    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    body {
      background: #e2e8f0;
      margin: 0;
      padding: 0;
      font-family: 'Tinos', 'Times New Roman', Times, serif;
      color: #000;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    /* Screen-only top action bar */
    .testpage-toolbar {
      width: 100%;
      background: #0f172a;
      color: #fff;
      padding: 10px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      position: sticky;
      top: 0;
      z-index: 1000;
      font-family: system-ui, -apple-system, sans-serif;
    }

    .testpage-toolbar .brand-title {
      font-weight: 700;
      font-size: 1.05rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .testpage-toolbar select, .testpage-toolbar button {
      font-size: 0.85rem;
    }

    /* The 11in x 8.5in Canvas */
    .sf9-page-wrapper {
      margin: 20px auto 40px;
      display: flex;
      justify-content: center;
    }

    .sf9-sheet {
      width: var(--sf9-width);
      height: var(--sf9-height);
      max-width: var(--sf9-width);
      max-height: var(--sf9-height);
      background: #ffffff;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      padding: 0.28in 0.35in 0.25in 0.35in;
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 0.42in;
      position: relative;
      overflow: hidden;
      font-size: 8pt;
      line-height: 1.15;
    }

    /* Center folding guideline (screen only) */
    .sf9-sheet::before {
      content: "";
      position: absolute;
      top: 0.25in;
      bottom: 0.25in;
      left: 50%;
      width: 1px;
      border-left: 1px dashed #cbd5e1;
      pointer-events: none;
    }

    .sf9-panel {
      display: flex;
      flex-direction: column;
      height: 100%;
      justify-content: flex-start;
    }

    /* =========================================================
       LEFT PANEL: HEADER & REPORT OF GRADES
       ========================================================= */
    .sf9-header-layout {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 4px;
    }

    .sf9-header-logo {
      width: 58px;
      height: 58px;
      object-fit: contain;
    }

    .sf9-header-text {
      text-align: center;
      line-height: 1.12;
      font-size: 7.8pt;
    }

    .sf9-header-text .gov {
      font-size: 8.5pt;
    }

    .sf9-header-text .division {
      font-weight: bold;
      font-size: 8.5pt;
      letter-spacing: 0.3px;
    }

    .sf9-header-text .school-name {
      font-weight: bold;
      font-size: 9.5pt;
      margin-top: 1px;
      letter-spacing: 0.5px;
    }

    .sf9-title-block {
      text-align: center;
      margin: 2px 0 6px;
    }

    .sf9-title-block .main-title {
      font-size: 9.8pt;
      font-weight: bold;
      letter-spacing: 0.5px;
    }

    .sf9-title-block .sy-title {
      font-size: 8.5pt;
    }

    /* Student info lines */
    .sf9-info-grid {
      display: flex;
      flex-direction: column;
      gap: 3px;
      font-size: 8.5pt;
      margin-bottom: 6px;
    }

    .sf9-info-row {
      display: flex;
      align-items: flex-end;
      width: 100%;
    }

    .sf9-underline {
      border-bottom: 1px solid #000;
      flex-grow: 1;
      padding: 0 4px;
      min-height: 15px;
      display: inline-block;
      font-weight: bold;
    }

    .sf9-dear-parents {
      font-size: 7.8pt;
      line-height: 1.2;
      margin-bottom: 6px;
    }

    .sf9-section-heading {
      text-align: center;
      font-weight: bold;
      font-size: 8.8pt;
      letter-spacing: 0.3px;
      margin: 2px 0 4px;
    }

    /* Academic Grades Table */
    .sf9-grades-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      font-size: 7.8pt;
      margin-bottom: 4px;
    }

    .sf9-grades-table th, .sf9-grades-table td {
      border: 1px solid #000;
      padding: 2.5px 3px;
      vertical-align: middle;
    }

    .sf9-grades-table thead th {
      text-align: center;
      font-weight: bold;
    }

    .sf9-grades-table td.text-center {
      text-align: center;
    }

    .sf9-group-hdr {
      font-weight: bold;
      background: #fafafa;
      padding-left: 6px !important;
    }

    .sf9-subj-name {
      padding-left: 12px !important;
    }

    .sf9-gen-avg {
      font-weight: bold;
      text-align: center;
    }

    /* Performance Descriptors Table */
    .sf9-descriptors-block {
      margin-top: 4px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sf9-descriptors-title {
      font-size: 8.2pt;
      font-weight: bold;
      text-align: center;
      margin-bottom: 2px;
    }

    .sf9-descriptors-table {
      width: 86%;
      border-collapse: collapse;
      font-size: 7.6pt;
      text-align: center;
    }

    .sf9-descriptors-table th {
      font-weight: bold;
      padding: 1px 4px;
    }

    .sf9-descriptors-table td {
      padding: 1px 4px;
    }

    /* =========================================================
       RIGHT PANEL: ATTENDANCE, COMMENTS, TRANSFER
       ========================================================= */
    .sf9-attendance-table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      font-size: 7.5pt;
      text-align: center;
      margin-bottom: 8px;
    }

    .sf9-attendance-table th, .sf9-attendance-table td {
      border: 1px solid #000;
      padding: 2px 2px;
      vertical-align: middle;
    }

    .sf9-attendance-table thead th {
      font-weight: bold;
    }

    .sf9-attendance-title {
      font-weight: bold;
      font-size: 8.2pt;
      letter-spacing: 0.5px;
    }

    /* Comments / Remarks Box */
    .sf9-comments-box {
      border: 1.5px solid #000;
      display: flex;
      flex-direction: column;
      margin-bottom: 8px;
      font-size: 8pt;
    }

    .sf9-comments-header {
      font-weight: bold;
      text-align: center;
      padding: 2px 0;
      border-bottom: 1px solid #000;
      letter-spacing: 0.3px;
    }

    .sf9-comment-term {
      padding: 3px 6px;
      min-height: 48px;
      border-bottom: 1px solid #000;
    }

    .sf9-comment-term:last-child {
      border-bottom: none;
    }

    .sf9-comment-term-label {
      font-weight: bold;
      margin-bottom: 2px;
    }

    /* Parents Signature */
    .sf9-signatures-block {
      margin-bottom: 10px;
      font-size: 8pt;
    }

    .sf9-signatures-title {
      font-weight: bold;
      text-align: center;
      margin-bottom: 4px;
      letter-spacing: 0.3px;
    }

    .sf9-sig-row {
      display: flex;
      align-items: flex-end;
      margin-bottom: 4px;
      padding: 0 15px;
    }

    .sf9-sig-row label {
      width: 55px;
      font-weight: normal;
      margin: 0;
    }

    .sf9-sig-line {
      flex-grow: 1;
      border-bottom: 1px solid #000;
      height: 14px;
    }

    /* Certificate of Transfer */
    .sf9-cert-transfer {
      margin-bottom: 8px;
      font-size: 7.8pt;
      line-height: 1.25;
    }

    .sf9-cert-title {
      font-weight: bold;
      text-align: center;
      font-size: 8.5pt;
      margin-bottom: 3px;
      letter-spacing: 0.3px;
    }

    .sf9-cert-lines {
      margin-top: 4px;
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .sf9-cert-row {
      display: flex;
      align-items: flex-end;
    }

    .sf9-cert-signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
      padding: 0 10px;
    }

    .sf9-cert-sig-box {
      text-align: center;
      width: 45%;
    }

    .sf9-cert-sig-line {
      border-bottom: 1px solid #000;
      min-height: 18px;
      font-weight: bold;
      margin-bottom: 2px;
    }

    /* Cancellation of Eligibility */
    .sf9-cancel-block {
      font-size: 7.8pt;
      margin-top: 2px;
    }

    .sf9-cancel-title {
      font-weight: bold;
      text-align: center;
      font-size: 8.2pt;
      margin-bottom: 3px;
      letter-spacing: 0.3px;
    }

    .sf9-cancel-row {
      display: flex;
      align-items: flex-end;
      gap: 15px;
      margin-bottom: 8px;
    }

    /* Editable highlight when edit mode is toggled */
    .is-editing [contenteditable="true"] {
      background: #fef08a !important;
      outline: 1px solid #eab308;
      cursor: text;
    }

    /* =========================================================
       NATIVE PRINT OPTIMIZATION FOR 11" x 8.5" LANDSCAPE
       ========================================================= */
    @page {
      size: letter landscape !important;
      margin: 0 !important;
    }

    @media print {
      html, body {
        width: 11in !important;
        height: 8.5in !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
      }

      .testpage-toolbar, .no-print {
        display: none !important;
      }

      .sf9-page-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        width: 11in !important;
        height: 8.5in !important;
      }

      .sf9-sheet {
        box-shadow: none !important;
        border: none !important;
        width: 11in !important;
        height: 8.5in !important;
        padding: 0.28in 0.35in 0.25in 0.35in !important;
      }

      .sf9-sheet::before {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Screen Toolbar -->
  <div class="testpage-toolbar no-print">
    <div class="brand-title">
      <i class="fas fa-file-invoice text-success"></i>
      <span>Minanga IS — SF9 TVL-ICT & K-12 Test Page</span>
      <span class="badge bg-warning text-dark ms-2 fw-semibold">11" x 8.5" Landscape</span>
    </div>

    <form method="get" class="d-flex align-items-center gap-2 m-0">
      <label class="text-white small fw-bold mb-0">Learner:</label>
      <select name="student_id" class="form-select form-select-sm" style="width: 280px;" onchange="this.form.submit()">
        <option value="sample_tvl_12" <?= $selectedId === 'sample_tvl_12' ? 'selected' : '' ?>>
          ★ Sample Grade 12 TVL-ICT Learner (from PDF)
        </option>
        <?php foreach ($studentsList as $stu): ?>
        <option value="<?= $stu['id'] ?>" <?= (string)$selectedId === (string)$stu['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($stu['last_name'] . ', ' . $stu['first_name'] . ' (' . $stu['grade_level'] . ' - ' . $stu['section'] . ')') ?>
        </option>
        <?php endforeach; ?>
      </select>

      <label class="text-white small fw-bold mb-0 ms-2">School Year:</label>
      <select name="sy" class="form-select form-select-sm" style="width: 120px;" onchange="this.form.submit()">
        <option value="2026-2027" <?= $sy === '2026-2027' ? 'selected' : '' ?>>2026-2027</option>
        <option value="2025-2026" <?= $sy === '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
      </select>
    </form>

    <div class="d-flex align-items-center gap-2">
      <button type="button" id="editToggleBtn" class="btn btn-sm btn-outline-light" onclick="toggleEditMode()">
        <i class="fas fa-edit me-1"></i>Edit Sheet
      </button>
      <button type="button" class="btn btn-sm btn-success fw-bold px-3 shadow-sm" onclick="window.print()">
        <i class="fas fa-print me-1"></i>Print SF9 (Landscape)
      </button>
      <a href="views/admin/reports.php" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
      </a>
    </div>
  </div>

  <!-- SF9 11" x 8.5" Sheet Container -->
  <div class="sf9-page-wrapper">
    <div class="sf9-sheet" id="sf9Sheet">

      <!-- ==========================================
           LEFT PANEL: HEADER & REPORT ON GRADES
           ========================================== -->
      <div class="sf9-panel">

        <!-- DepEd & Minanga IS Official Header -->
        <div class="sf9-header-layout">
          <img src="assets/img/deped_logo.png" alt="DepEd Logo" class="sf9-header-logo">
          
          <div class="sf9-header-text">
            <div class="gov">Republic of the Philippines</div>
            <div class="gov">Department of Education</div>
            <div>Region 02</div>
            <div class="division">SCHOOLS DIVISION OF CAGAYAN</div>
            <div>Piat District</div>
            <div>Minanga, Piat, Cagayan</div>
            <div class="school-name">MINANGA INTEGRATED SCHOOL</div>
          </div>

          <img src="assets/img/MIS-Logo.jpg" alt="Minanga Integrated School Logo" class="sf9-header-logo">
        </div>

        <!-- Report Title -->
        <div class="sf9-title-block">
          <div class="main-title">LEARNER’S PERFORMANCE REPORT</div>
          <div class="sy-title">School Year <span contenteditable="false" class="editable-field" id="sf9SyText"><?= htmlspecialchars($sy) ?></span></div>
        </div>

        <!-- Student Information Grid -->
        <div class="sf9-info-grid">
          <div class="sf9-info-row">
            <span style="min-width: 45px;">Name:</span>
            <span class="sf9-underline editable-field" contenteditable="false" style="margin-right: 15px;"><?= htmlspecialchars($fullName) ?></span>
            <span style="min-width: 32px;">Age:</span>
            <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 45px; margin-right: 15px;"><?= htmlspecialchars($age) ?></span>
            <span style="min-width: 30px;">Sex:</span>
            <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 70px;"><?= htmlspecialchars($sex) ?></span>
          </div>
          <div class="sf9-info-row">
            <span style="min-width: 45px;">LRN:</span>
            <span class="sf9-underline editable-field" contenteditable="false" style="margin-right: 15px; font-family: monospace;"><?= htmlspecialchars($lrn) ?></span>
            <span style="min-width: 42px;">Grade:</span>
            <span class="sf9-underline editable-field text-center" contenteditable="false" style="max-width: 65px; margin-right: 15px;"><?= htmlspecialchars($grade) ?></span>
            <span style="min-width: 50px;">Section:</span>
            <span class="sf9-underline editable-field" contenteditable="false"><?= htmlspecialchars($section) ?></span>
          </div>
          <div class="sf9-info-row">
            <span style="min-width: 45px;">Track:</span>
            <span class="sf9-underline editable-field" contenteditable="false"><?= htmlspecialchars($track) ?></span>
          </div>
        </div>

        <!-- Dear Parents Message -->
        <div class="sf9-dear-parents">
          <div><strong>Dear Parents,</strong></div>
          <div style="text-indent: 14px;">
            This Performance Report shows the ability and progress your child has made in the different learning areas as well as his/her core values.
          </div>
          <div>The school welcomes you should you desire to know more about your child’s progress.</div>
        </div>

        <div class="sf9-section-heading">LEARNING PROGRESS AND ACHIEVEMENT</div>

        <!-- Academic Grades Table -->
        <table class="sf9-grades-table" id="sf9GradesTable">
          <thead>
            <tr>
              <th rowspan="2" style="width: 54%; text-align: center;">Learning Areas</th>
              <th colspan="3" style="width: 24%; text-align: center;">TERM</th>
              <th rowspan="2" style="width: 11%; text-align: center;">Final<br>Grade</th>
              <th rowspan="2" style="width: 11%; text-align: center;">Remarks</th>
            </tr>
            <tr>
              <th style="width: 8%; text-align: center;">1</th>
              <th style="width: 8%; text-align: center;">2</th>
              <th style="width: 8%; text-align: center;">3</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $allFinals = [];

            if ($selectedId === 'sample_tvl_12' || empty($studentGradeRows)):
              // Render exact TVL-ICT 12 categories from SF9-TVL-ICT12.pdf
              foreach ($defaultTvlCategories as $catName => $subjs):
            ?>
              <tr>
                <td colspan="6" class="sf9-group-hdr"><?= htmlspecialchars($catName) ?></td>
              </tr>
              <?php foreach ($subjs as $s):
                $t1 = $s['t1']; $t2 = $s['t2']; $t3 = $s['t3'];
                $fg = round(($t1 + $t2 + $t3) / 3);
                $rem = ($fg >= 75) ? 'Passed' : 'Failed';
                $allFinals[] = $fg;
              ?>
              <tr class="sf9-subject-row">
                <td class="sf9-subj-name editable-field" contenteditable="false"><?= htmlspecialchars($s['name']) ?></td>
                <td class="text-center grade-cell grade-t1 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t1 ?></td>
                <td class="text-center grade-cell grade-t2 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t2 ?></td>
                <td class="text-center grade-cell grade-t3 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t3 ?></td>
                <td class="text-center grade-final fw-bold"><?= $fg ?></td>
                <td class="text-center grade-remarks fw-bold"><?= $rem ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>

            <?php else:
              // Render Real Student Grades from Database
              $gradeMap = [];
              foreach ($studentGradeRows as $r) { $gradeMap[$r['subject']] = $r; }
              $subjList = getSubjectsForGrade($grade, $pdo);
              foreach ($subjList as $subName):
                $r = $gradeMap[$subName] ?? null;
                $t1 = $r ? ($r['t1'] ?? $r['q1'] ?? null) : null;
                $t2 = $r ? ($r['t2'] ?? $r['q2'] ?? null) : null;
                $t3 = $r ? ($r['t3'] ?? $r['q3'] ?? null) : null;
                $terms = array_filter([$t1, $t2, $t3], fn($v) => is_numeric($v));
                $fg = !empty($terms) ? round(array_sum($terms) / count($terms)) : '—';
                $rem = is_numeric($fg) ? (($fg >= 75) ? 'Passed' : 'Failed') : '—';
                if (is_numeric($fg)) { $allFinals[] = $fg; }
            ?>
              <tr class="sf9-subject-row">
                <td class="sf9-subj-name editable-field" contenteditable="false"><?= htmlspecialchars($subName) ?></td>
                <td class="text-center grade-cell grade-t1 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t1 ?? '—' ?></td>
                <td class="text-center grade-cell grade-t2 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t2 ?? '—' ?></td>
                <td class="text-center grade-cell grade-t3 editable-field" contenteditable="false" oninput="recalcRow(this)"><?= $t3 ?? '—' ?></td>
                <td class="text-center grade-final fw-bold"><?= $fg ?></td>
                <td class="text-center grade-remarks fw-bold"><?= $rem ?></td>
              </tr>
            <?php endforeach; endif; ?>

            <!-- General Average Row -->
            <?php
              $genAvg = !empty($allFinals) ? round(array_sum($allFinals) / count($allFinals)) : '—';
              $genRemarks = is_numeric($genAvg) ? (($genAvg >= 75) ? 'Passed' : 'Failed') : '—';
            ?>
            <tr class="sf9-genavg-row">
              <td class="fw-bold" style="text-align: center;">General Average</td>
              <td class="text-center fw-bold" id="sf9AvgT1"></td>
              <td class="text-center fw-bold" id="sf9AvgT2"></td>
              <td class="text-center fw-bold" id="sf9AvgT3"></td>
              <td class="text-center fw-bold fs-6" id="sf9FinalAvg"><?= $genAvg ?></td>
              <td class="text-center fw-bold" id="sf9FinalRemarks"><?= $genRemarks ?></td>
            </tr>
          </tbody>
        </table>

        <!-- Performance Descriptors (Exact copy from SF9-TVL-ICT12.pdf) -->
        <div class="sf9-descriptors-block">
          <div class="sf9-descriptors-title">PERFORMANCE DESCRIPTORS</div>
          <table class="sf9-descriptors-table">
            <thead>
              <tr>
                <th style="width: 33%;">Grading Scale</th>
                <th style="width: 37%;">Description</th>
                <th style="width: 30%;">Remarks</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>90-100</td><td>Advancing</td><td>Passed</td></tr>
              <tr><td>80-89</td><td>Benchmarking</td><td>Passed</td></tr>
              <tr><td>75-79</td><td>Connecting</td><td>Passed</td></tr>
              <tr><td>65-74</td><td>Developing</td><td>Failed</td></tr>
              <tr><td>0-64</td><td>Emerging</td><td>Failed</td></tr>
            </tbody>
          </table>
        </div>

      </div><!-- /.sf9-panel (Left) -->


      <!-- ==========================================
           RIGHT PANEL: ATTENDANCE, COMMENTS, SIGNATURES
           ========================================== -->
      <div class="sf9-panel">

        <!-- Attendance Record -->
        <table class="sf9-attendance-table">
          <thead>
            <tr>
              <th colspan="12" class="sf9-attendance-title">ATTENDANCE RECORD</th>
            </tr>
            <tr>
              <th style="width: 25%; text-align: left; padding-left: 4px;">Month</th>
              <th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th>
              <th style="width: 10%;">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align: left; padding-left: 4px;">No. of Class Days</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">22</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="editable-field" contenteditable="false">15</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="editable-field" contenteditable="false">22</td>
              <td class="editable-field" contenteditable="false">18</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="fw-bold editable-field" contenteditable="false">200</td>
            </tr>
            <tr>
              <td style="text-align: left; padding-left: 4px;">No. of Days Present</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">22</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="editable-field" contenteditable="false">15</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">20</td>
              <td class="editable-field" contenteditable="false">21</td>
              <td class="editable-field" contenteditable="false">18</td>
              <td class="editable-field" contenteditable="false">19</td>
              <td class="fw-bold editable-field" contenteditable="false">197</td>
            </tr>
            <tr>
              <td style="text-align: left; padding-left: 4px;">No. of Days Absent</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">1</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">1</td>
              <td class="editable-field" contenteditable="false">0</td>
              <td class="editable-field" contenteditable="false">1</td>
              <td class="fw-bold editable-field" contenteditable="false">3</td>
            </tr>
          </tbody>
        </table>

        <!-- Teacher's Comments / Remarks Box -->
        <div class="sf9-comments-box">
          <div class="sf9-comments-header">TEACHER’S COMMENTS/REMARKS</div>
          <div class="sf9-comment-term">
            <div class="sf9-comment-term-label">Term 1</div>
            <div class="editable-field" contenteditable="false" style="min-height: 28px;">Demonstrates exceptional focus and diligence in all core and applied learning tasks.</div>
          </div>
          <div class="sf9-comment-term">
            <div class="sf9-comment-term-label">Term 2</div>
            <div class="editable-field" contenteditable="false" style="min-height: 28px;">Shows outstanding technical problem-solving skills in Computer Systems Servicing.</div>
          </div>
          <div class="sf9-comment-term">
            <div class="sf9-comment-term-label">Term 3</div>
            <div class="editable-field" contenteditable="false" style="min-height: 28px;">Successfully completed the specialized curriculum requirements with high commendation.</div>
          </div>
        </div>

        <!-- Parents / Guardian's Signature -->
        <div class="sf9-signatures-block">
          <div class="sf9-signatures-title">PARENTS/GUARDIAN’S SIGNATURE</div>
          <div class="sf9-sig-row">
            <label>Term 1</label>
            <div class="sf9-sig-line"></div>
          </div>
          <div class="sf9-sig-row">
            <label>Term 2</label>
            <div class="sf9-sig-line"></div>
          </div>
          <div class="sf9-sig-row">
            <label>Term 3</label>
            <div class="sf9-sig-line"></div>
          </div>
        </div>

        <!-- Certificate of Transfer -->
        <div class="sf9-cert-transfer">
          <div class="sf9-cert-title">CERTIFICATE OF TRANSFER</div>
          <div>This is to certify that the above-named learner has satisfactorily completed the requirements for the grade level indicated.</div>
          
          <div class="sf9-cert-lines">
            <div class="sf9-cert-row">
              <span style="min-width: 110px;">Admitted to Grade:</span>
              <span class="sf9-underline editable-field" contenteditable="false"><?= $grade === 'Grade 12' ? 'Graduated / Higher Education' : 'Grade ' . ((int)filter_var($grade, FILTER_SANITIZE_NUMBER_INT) + 1) ?></span>
            </div>
            <div class="sf9-cert-row">
              <span style="min-width: 165px;">Eligible for Admission to Grade:</span>
              <span class="sf9-underline editable-field" contenteditable="false"><?= $grade === 'Grade 12' ? 'Tertiary Level / Employment' : 'Grade ' . ((int)filter_var($grade, FILTER_SANITIZE_NUMBER_INT) + 1) ?></span>
            </div>
          </div>

          <div class="sf9-cert-signatures" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 8px;">
            <div style="width: 48%; text-align: left;">
              <div style="font-size: 8pt; margin-bottom: 14px;">Approved:</div>
              <div style="width: 85%; text-align: center;">
                <div class="sf9-cert-sig-line editable-field" contenteditable="false"><?= htmlspecialchars($principalName) ?></div>
                <div style="font-size: 7.5pt;">School Head</div>
              </div>
            </div>
            <div style="width: 48%; text-align: center; margin-top: 4px;">
              <div class="sf9-cert-sig-line editable-field" contenteditable="false" style="font-weight: bold;"><?= htmlspecialchars($adviserName) ?></div>
              <div style="font-size: 7.5pt;">Adviser</div>
            </div>
          </div>
        </div>

        <!-- Cancellation of Eligibility to Transfer -->
        <div class="sf9-cancel-block">
          <div class="sf9-cancel-title">CANCELLATION OF ELIGIBILITY TO TRANSFER</div>
          <div class="sf9-cancel-row">
            <span style="min-width: 75px;">Admitted in:</span>
            <span class="sf9-underline editable-field" contenteditable="false" style="flex-grow: 1;"></span>
            <span style="min-width: 35px;">Date:</span>
            <span class="sf9-underline editable-field" contenteditable="false" style="width: 120px;"></span>
          </div>

          <div style="width: 50%; margin: 8px auto 0; text-align: center;">
            <div class="sf9-cert-sig-line editable-field" contenteditable="false"></div>
            <div style="font-size: 7.5pt;">School Head</div>
          </div>
        </div>

      </div><!-- /.sf9-panel (Right) -->

    </div><!-- /.sf9-sheet -->
  </div><!-- /.sf9-page-wrapper -->

  <script>
    let isEditing = false;

    function toggleEditMode() {
      isEditing = !isEditing;
      const sheet = document.getElementById('sf9Sheet');
      const btn = document.getElementById('editToggleBtn');

      if (isEditing) {
        sheet.classList.add('is-editing');
        btn.classList.remove('btn-outline-light');
        btn.classList.add('btn-warning');
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Finish Editing';
        document.querySelectorAll('.editable-field').forEach(el => {
          el.setAttribute('contenteditable', 'true');
        });
      } else {
        sheet.classList.remove('is-editing');
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-outline-light');
        btn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit Sheet';
        document.querySelectorAll('.editable-field').forEach(el => {
          el.setAttribute('contenteditable', 'false');
        });
        recalculateAverages();
      }
    }

    function recalcRow(cell) {
      const row = cell.closest('tr');
      if (!row) return;

      const t1Cell = row.querySelector('.grade-t1');
      const t2Cell = row.querySelector('.grade-t2');
      const t3Cell = row.querySelector('.grade-t3');
      const finalCell = row.querySelector('.grade-final');
      const remarksCell = row.querySelector('.grade-remarks');

      const v1 = parseFloat(t1Cell ? t1Cell.textContent.trim() : '');
      const v2 = parseFloat(t2Cell ? t2Cell.textContent.trim() : '');
      const v3 = parseFloat(t3Cell ? t3Cell.textContent.trim() : '');

      const validVals = [v1, v2, v3].filter(v => !isNaN(v));
      if (validVals.length > 0) {
        const avg = Math.round(validVals.reduce((a, b) => a + b, 0) / validVals.length);
        if (finalCell) finalCell.textContent = avg;
        if (remarksCell) {
          remarksCell.textContent = avg >= 75 ? 'Passed' : 'Failed';
          remarksCell.style.color = avg < 75 ? '#dc2626' : '#000000';
        }
      } else {
        if (finalCell) finalCell.textContent = '—';
        if (remarksCell) {
          remarksCell.textContent = '—';
          remarksCell.style.color = 'inherit';
        }
      }

      recalculateAverages();
    }

    function recalculateAverages() {
      const rows = document.querySelectorAll('.sf9-subject-row');
      const finals = [];
      const t1Vals = [];
      const t2Vals = [];
      const t3Vals = [];

      rows.forEach(r => {
        const fg = parseFloat(r.querySelector('.grade-final')?.textContent.trim());
        if (!isNaN(fg)) finals.push(fg);

        const v1 = parseFloat(r.querySelector('.grade-t1')?.textContent.trim());
        if (!isNaN(v1)) t1Vals.push(v1);

        const v2 = parseFloat(r.querySelector('.grade-t2')?.textContent.trim());
        if (!isNaN(v2)) t2Vals.push(v2);

        const v3 = parseFloat(r.querySelector('.grade-t3')?.textContent.trim());
        if (!isNaN(v3)) t3Vals.push(v3);
      });

      const avgT1El = document.getElementById('sf9AvgT1');
      const avgT2El = document.getElementById('sf9AvgT2');
      const avgT3El = document.getElementById('sf9AvgT3');
      const finalAvgEl = document.getElementById('sf9FinalAvg');
      const finalRemEl = document.getElementById('sf9FinalRemarks');

      if (avgT1El && t1Vals.length) avgT1El.textContent = Math.round(t1Vals.reduce((a, b) => a + b, 0) / t1Vals.length);
      if (avgT2El && t2Vals.length) avgT2El.textContent = Math.round(t2Vals.reduce((a, b) => a + b, 0) / t2Vals.length);
      if (avgT3El && t3Vals.length) avgT3El.textContent = Math.round(t3Vals.reduce((a, b) => a + b, 0) / t3Vals.length);

      if (finals.length > 0) {
        const genAvg = Math.round(finals.reduce((a, b) => a + b, 0) / finals.length);
        if (finalAvgEl) finalAvgEl.textContent = genAvg;
        if (finalRemEl) {
          finalRemEl.textContent = genAvg >= 75 ? 'Passed' : 'Failed';
          finalRemEl.style.color = genAvg < 75 ? '#dc2626' : '#000000';
        }
      } else {
        if (finalAvgEl) finalAvgEl.textContent = '—';
        if (finalRemEl) {
          finalRemEl.textContent = '—';
          finalRemEl.style.color = 'inherit';
        }
      }
    }

    // Initial calculation on load
    window.addEventListener('DOMContentLoaded', recalculateAverages);
  </script>
</body>
</html>
