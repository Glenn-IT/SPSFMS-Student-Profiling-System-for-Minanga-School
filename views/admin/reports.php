<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'reports';

$sigData = getSignatories($pdo, $user);

$type      = $_GET['type']       ?? '';
$grade     = $_GET['grade']      ?? '';
$section   = $_GET['section']    ?? '';
$sy        = isset($_GET['sy']) ? trim($_GET['sy']) : null;
$search    = $_GET['search']     ?? '';
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$period    = isset($_GET['period']) ? (int)$_GET['period'] : 0;

// Smart default for school year if not specified:
// If active school year has students, use it; otherwise fallback to the school year with student records
if ($sy === null) {
    $activeSy = getActiveSchoolYear($pdo);
    $chkSy = $pdo->prepare("SELECT COUNT(*) FROM students WHERE status='active' AND school_year = ?");
    $chkSy->execute([$activeSy]);
    if ((int)$chkSy->fetchColumn() > 0) {
        $sy = $activeSy;
    } else {
        // Fallback to the latest school year that actually has students, or default to active
        $recentSy = $pdo->query("SELECT school_year FROM students WHERE status='active' GROUP BY school_year ORDER BY id DESC LIMIT 1")->fetchColumn();
        $sy = $recentSy ?: $activeSy;
    }
}

$students = [];
if ($type && $type !== 'sf9' && $type !== 'gender') {
    $where = ['status="active"'];
    $params = [];
    if ($grade)   { $where[] = 'grade_level=?'; $params[] = $grade; }
    if ($section) { $where[] = 'section=?';     $params[] = $section; }
    if ($sy !== '') { $where[] = 'school_year=?'; $params[] = $sy; }
    if ($search)  { $where[] = '(first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ?)'; array_push($params, "%$search%", "%$search%", "%$search%"); }
    $stmt = $pdo->prepare('SELECT * FROM students WHERE '.implode(' AND ',$where).' ORDER BY grade_level,last_name,first_name');
    $stmt->execute($params);
    $students = $stmt->fetchAll();
}

// Data preparation for Gender Summary Report
$genderKpi = ['total' => 0, 'male' => 0, 'female' => 0, 'pct_male' => 0, 'pct_female' => 0];
$genderGradeData = [];
$genderSectionData = [];

if ($type === 'gender') {
    $gWhere = ['status="active"'];
    $gParams = [];
    if ($grade)   { $gWhere[] = 'grade_level=?'; $gParams[] = $grade; }
    if ($section) { $gWhere[] = 'section=?';     $gParams[] = $section; }
    if ($sy !== '' && $sy !== null) { $gWhere[] = 'school_year=?'; $gParams[] = $sy; }

    $gWhereSql = implode(' AND ', $gWhere);

    // KPI Totals
    $kpiStmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female_count
        FROM students WHERE $gWhereSql");
    $kpiStmt->execute($gParams);
    $kpiRow = $kpiStmt->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'male_count' => 0, 'female_count' => 0];
    
    $tot = (int)($kpiRow['total'] ?? 0);
    $m   = (int)($kpiRow['male_count'] ?? 0);
    $f   = (int)($kpiRow['female_count'] ?? 0);
    $genderKpi = [
        'total'      => $tot,
        'male'       => $m,
        'female'     => $f,
        'pct_male'   => $tot > 0 ? round(($m / $tot) * 100, 1) : 0,
        'pct_female' => $tot > 0 ? round(($f / $tot) * 100, 1) : 0,
    ];

    // Grade Level aggregation
    $grStmt = $pdo->prepare("SELECT 
        grade_level,
        SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female_count,
        COUNT(*) as total_count
        FROM students WHERE $gWhereSql
        GROUP BY grade_level");
    $grStmt->execute($gParams);
    $grRows = $grStmt->fetchAll(PDO::FETCH_ASSOC);
    $grMap = [];
    foreach ($grRows as $gr) {
        $grMap[$gr['grade_level']] = [
            'male'   => (int)$gr['male_count'],
            'female' => (int)$gr['female_count'],
            'total'  => (int)$gr['total_count'],
        ];
    }
    $targetLevels = $grade ? [$grade] : GRADE_LEVELS;
    foreach ($targetLevels as $gl) {
        $glMale = $grMap[$gl]['male'] ?? 0;
        $glFem  = $grMap[$gl]['female'] ?? 0;
        $glTot  = $grMap[$gl]['total'] ?? ($glMale + $glFem);
        $genderGradeData[] = [
            'grade_level' => $gl,
            'male'        => $glMale,
            'female'      => $glFem,
            'total'       => $glTot,
            'pct_male'    => $glTot > 0 ? round(($glMale / $glTot) * 100, 1) : 0,
            'pct_female'  => $glTot > 0 ? round(($glFem / $glTot) * 100, 1) : 0,
        ];
    }
    if (!$grade) {
        foreach ($grMap as $gName => $gInfo) {
            if (!in_array($gName, GRADE_LEVELS)) {
                $genderGradeData[] = [
                    'grade_level' => $gName,
                    'male'        => $gInfo['male'],
                    'female'      => $gInfo['female'],
                    'total'       => $gInfo['total'],
                    'pct_male'    => $gInfo['total'] > 0 ? round(($gInfo['male'] / $gInfo['total']) * 100, 1) : 0,
                    'pct_female'  => $gInfo['total'] > 0 ? round(($gInfo['female'] / $gInfo['total']) * 100, 1) : 0,
                ];
            }
        }
    }

    // Section-level aggregation
    $secStmt = $pdo->prepare("SELECT 
        grade_level,
        section,
        SUM(CASE WHEN sex = 'Male' THEN 1 ELSE 0 END) as male_count,
        SUM(CASE WHEN sex = 'Female' THEN 1 ELSE 0 END) as female_count,
        COUNT(*) as total_count
        FROM students WHERE $gWhereSql
        GROUP BY grade_level, section
        ORDER BY grade_level, section");
    $secStmt->execute($gParams);
    $secRows = $secStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($secRows as $sr) {
        $srMale = (int)$sr['male_count'];
        $srFem  = (int)$sr['female_count'];
        $srTot  = (int)$sr['total_count'];
        $genderSectionData[] = [
            'grade_level' => $sr['grade_level'],
            'section'     => $sr['section'],
            'adviser'     => $adviserMap[$sr['grade_level'].'|'.$sr['section']] ?? '—',
            'male'        => $srMale,
            'female'      => $srFem,
            'total'       => $srTot,
            'pct_male'    => $srTot > 0 ? round(($srMale / $srTot) * 100, 1) : 0,
            'pct_female'  => $srTot > 0 ? round(($srFem / $srTot) * 100, 1) : 0,
        ];
    }
}

$allActiveStudents = $pdo->query("SELECT id, lrn, first_name, middle_name, last_name, grade_level, section FROM students WHERE status='active' ORDER BY grade_level, last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);

// Adviser lookup map per grade and section
$adviserMap = [];
try {
    $advRows = $pdo->query("SELECT tc.grade_level, tc.section, u.name as adviser_name 
                            FROM teacher_classes tc 
                            JOIN users u ON u.id = tc.teacher_id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($advRows as $ar) {
        $adviserMap[$ar['grade_level'].'|'.$ar['section']] = $ar['adviser_name'];
    }
} catch (Exception $e) {}

// Encode SECTION_MAP for JS
$sectionMapJson = json_encode(SECTION_MAP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
    $pageTitle = 'Reports — Admin';
    if ($type === 'gender') $pageTitle = 'Gender Summary Report — Admin';
    elseif ($type === 'masterlist') $pageTitle = 'Student Masterlist — Admin';
    elseif ($type === 'sf9') $pageTitle = 'SF9 Report Card — Admin';
    include __DIR__ . '/../../includes/head.php'; 
  ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sf9.css">
  <style>
    @media print {
      .no-print { display:none !important; }
      .sidebar,.top-navbar { display:none !important; }
      .main-content { margin:0 !important; }
      .signatories { margin-top: 3rem; page-break-inside: avoid; }
      .report-header-logo { max-height: 95px !important; display: inline-block !important; vertical-align: top !important; }
      .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
      .table-bordered th, .table-bordered td { border: 1px solid #555 !important; }
      .table thead th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .progress, .progress-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    .report-header { text-align: center; margin-bottom: 1.75rem; }
    .report-header h3 { font-weight: 800; color: #000; letter-spacing: 0.5px; }
    .report-header-logo { height: 95px; width: auto; object-fit: contain; flex-shrink: 0; }

    /* Signatories */
    .signatories {
      margin-top: 2.5rem;
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 1.5rem;
    }
    .signatory-block { text-align: center; }
    .signatory-block .sig-label {
      font-size: .72rem;
      color: var(--gray-600);
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-bottom: .25rem;
    }
    .signatory-block .sig-name {
      font-weight: 700;
      font-size: .85rem;
      border-top: 1.5px solid var(--dark);
      padding-top: .35rem;
      margin-top: 2rem;
    }
    .signatory-block .sig-position {
      font-size: .75rem;
      color: var(--gray-600);
    }

    /* Live search spinner */
    #search-spinner { display:none; }
    #search-spinner.visible { display:inline-block; }

    /* Section filter highlight */
    #section-select option { font-size:.875rem; }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar no-print">
      <div>
        <div class="page-title">
          <?= $type === 'gender' ? 'Gender Summary Report' : ($type === 'sf9' ? 'SF9 Report Card' : ($type === 'masterlist' ? 'Student Masterlist' : 'Reports')) ?>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <?php if ($type): ?>
            <li class="breadcrumb-item"><a href="reports.php" class="text-decoration-none">Reports</a></li>
            <li class="breadcrumb-item active">
              <?= $type === 'gender' ? 'Gender Summary' : ($type === 'sf9' ? 'SF9 Report Card' : 'Masterlist') ?>
            </li>
            <?php else: ?>
            <li class="breadcrumb-item active">Reports</li>
            <?php endif; ?>
          </ol>
        </nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header no-print">
      <h3>
        <?= $type === 'gender' ? 'Gender Summary Report' : ($type === 'sf9' ? 'SF9 Report Card' : ($type === 'masterlist' ? 'Student Masterlist' : 'Reports')) ?>
      </h3>
      <p>
        <?= $type === 'gender' 
          ? 'Enrollment demographics and gender distribution breakdown by grade level and section' 
          : ($type === 'sf9' 
              ? 'Generate official DepEd Form 9 (SF9) Progress Report Card' 
              : ($type === 'masterlist' 
                  ? 'Complete enrolled student masterlist with contact and guardian details' 
                  : 'Generate and print student enrollment reports and DepEd forms')) ?>
      </p>
    </div>

    <!-- Report type selector -->
    <?php if (!$type): ?>
    <div class="row g-3 no-print">
      <?php foreach ([
        ['masterlist','fa-id-card','Student Masterlist','Complete student records with all fields and live search','primary'],
        ['gender','fa-venus-mars','Gender Summary Report','Enrollment and gender distribution breakdown by grade and section','info'],
        ['sf9','fa-award','SF9 Report Card','DepEd Form 9 Learner’s Progress Report Card (US Letter Landscape)','success'],
      ] as [$t,$icon,$label,$desc,$color]): ?>
      <div class="col-md-4">
        <a href="?type=<?= $t ?>&sy=<?= htmlspecialchars($sy) ?>" style="text-decoration:none;">
          <div class="card h-100" style="cursor:pointer;transition:.2s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.12)'" onmouseout="this.style.boxShadow=''">
            <div class="card-body text-center py-4">
              <div style="width:56px;height:56px;background:var(--<?= $color ?>-light,#e8f0fe);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--<?= $color ?>);margin:0 auto .75rem;"><i class="fas <?= $icon ?>"></i></div>
              <h6 class="fw-bold"><?= $label ?></h6>
              <p class="text-muted" style="font-size:.82rem;"><?= $desc ?></p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <?php elseif ($type === 'sf9'): ?>

    <!-- SF9 Filters Card -->
    <div class="card mb-3 no-print">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Filter Grade</label>
            <select id="sf9-grade-filter" class="form-select form-select-sm">
              <option value="">All Grades</option>
              <?php foreach (GRADE_LEVELS as $gl): ?>
              <option value="<?= $gl ?>" <?= $grade===$gl?'selected':'' ?>><?= $gl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Filter Section</label>
            <select id="sf9-section-filter" class="form-select form-select-sm">
              <option value="">All Sections</option>
              <?php
                $currSecs = $grade && isset(SECTION_MAP[$grade]) ? SECTION_MAP[$grade] : [];
                foreach ($currSecs as $sec):
              ?>
              <option value="<?= $sec ?>" <?= $section===$sec?'selected':'' ?>><?= $sec ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Select Learner <span class="text-danger">*</span></label>
            <select id="sf9-student-select" class="form-select form-select-sm" onchange="onSf9StudentChange()">
              <option value="">— Choose a student —</option>
              <?php foreach ($allActiveStudents as $s): ?>
              <option value="<?= $s['id'] ?>" data-grade="<?= htmlspecialchars($s['grade_level']) ?>" data-section="<?= htmlspecialchars($s['section']) ?>" <?= $studentId === (int)$s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?> (<?= $s['grade_level'] ?> - <?= $s['section'] ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Grading Term</label>
            <select id="sf9-period-select" class="form-select form-select-sm" onchange="loadSf9Report()">
              <option value="0" <?= $period===0?'selected':'' ?>>All Terms</option>
              <option value="1" <?= $period===1?'selected':'' ?>>1st Term</option>
              <option value="2" <?= $period===2?'selected':'' ?>>2nd Term</option>
              <option value="3" <?= $period===3?'selected':'' ?>>3rd Term</option>
            </select>
          </div>
          <div class="col-md-1">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">S.Y.</label>
            <select id="sf9-sy-select" class="form-select form-select-sm" onchange="loadSf9Report()">
              <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
              <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['is_active'] || $syItem['year_label'] === $sy) ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-1">
            <button class="btn btn-primary btn-sm w-100" onclick="loadSf9Report()" title="Generate / Reload"><i class="fas fa-sync me-1"></i>Load</button>
          </div>
          <div class="col-md-1">
            <a href="reports.php" class="btn btn-outline-secondary btn-sm w-100" title="Back to Reports menu"><i class="fas fa-times"></i></a>
          </div>
        </div>

        <!-- Signatories Live-Sync Bar -->
        <div class="row g-2 align-items-end mt-2 pt-2 border-top">
          <div class="col-md-6">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600" for="sf9AdviserInput">
              <i class="fas fa-chalkboard-teacher me-1 text-success"></i>Class Adviser (Prepared by)
            </label>
            <input type="text" id="sf9AdviserInput" class="form-control form-control-sm" placeholder="Class Adviser Name" oninput="updateSf9SignatoriesLive()">
          </div>
          <div class="col-md-6">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600" for="sf9PrincipalInput">
              <i class="fas fa-user-tie me-1 text-primary"></i>School Head / Principal (Approved by)
            </label>
            <input type="text" id="sf9PrincipalInput" class="form-control form-control-sm" placeholder="School Head / Principal Name" oninput="updateSf9SignatoriesLive()">
          </div>
        </div>
      </div>
    </div>

    <!-- SF9 Output Container -->
    <div id="sf9-report-content">
      <div class="card p-5 text-center text-muted no-print" id="sf9-empty-notice">
        <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
        <h5>Select a Learner to Preview SF9</h5>
        <p class="small mb-0">Choose a student and grading term above to generate the official DepEd Form 9 Progress Report Card.</p>
      </div>
    </div>

    <?php elseif ($type === 'gender'): ?>

    <!-- Gender Report Filters -->
    <form method="GET" action="reports.php" class="card mb-3 no-print" id="genderFilterForm">
      <input type="hidden" name="type" value="gender">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <!-- Grade Level -->
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Grade Level</label>
            <select name="grade" id="gender-grade-select" class="form-select form-select-sm" onchange="onGenderGradeChange()">
              <option value="">All Grades</option>
              <?php foreach (GRADE_LEVELS as $gl): ?>
              <option value="<?= $gl ?>" <?= $grade===$gl?'selected':'' ?>><?= $gl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Section (dynamic) -->
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">Section</label>
            <select name="section" id="gender-section-select" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">All Sections</option>
              <?php
                $currSecs = $grade && isset(SECTION_MAP[$grade]) ? SECTION_MAP[$grade] : [];
                foreach ($currSecs as $sec):
              ?>
              <option value="<?= $sec ?>" <?= $section===$sec?'selected':'' ?>><?= $sec ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- School Year -->
          <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600">School Year</label>
            <select name="sy" id="gender-sy-select" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="" <?= $sy===''?'selected':'' ?>>All School Years</option>
              <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
              <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['year_label'] === $sy) ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Actions -->
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i>Apply Filter</button>
          </div>
          <div class="col-md-2">
            <a href="<?= BASE_URL ?>/views/admin/signatories.php?return_to=<?= urlencode($_SERVER['REQUEST_URI'] ?? '') ?>" class="btn btn-outline-secondary btn-sm w-100" title="Configure Report Signatories">
              <i class="fas fa-file-signature me-1"></i>Signatories
            </a>
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-light btn-sm w-100 border" onclick="window.print()" title="Print Report"><i class="fas fa-print"></i></button>
          </div>
          <div class="col-md-1">
            <a href="reports.php" class="btn btn-light btn-sm w-100 border" title="Back to Reports menu"><i class="fas fa-times"></i></a>
          </div>
        </div>
      </div>
    </form>

    <!-- Gender Report Document Output -->
    <div class="card">
      <div class="card-body p-4">
        <!-- DepEd Header -->
        <div class="report-header text-center mb-4">
          <div class="d-inline-flex align-items-start justify-content-center gap-4">
            <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="School Logo" class="report-header-logo" style="margin-top: 4px;">
            <div class="text-center">
              <div style="font-size:.95rem;color:#222;font-weight:400;margin-bottom:.2rem;">Republic of the Philippines · Department of Education</div>
              <div style="font-size:1.15rem;color:#000;font-weight:700;margin-bottom:.2rem;"><?= SCHOOL_NAME ?></div>
              <div style="font-size:.95rem;color:#333;font-weight:400;margin-bottom:1.5rem;"><?= SCHOOL_ADDRESS ?></div>

              <h3 class="text-center text-uppercase text-dark fw-bold mb-1" style="letter-spacing:0.5px;">
                ENROLLMENT GENDER SUMMARY REPORT
              </h3>
              <div style="font-size:.95rem;color:#444;" class="text-center">
                School Year <?= htmlspecialchars($sy ?: 'All School Years') ?><?= $grade ? ' · '.$grade : '' ?><?= $section ? ' · Section '.$section : '' ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Demographic KPI Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3 col-sm-6">
            <div class="card border h-100 p-3 shadow-none text-center" style="background:#f8faff;border-left:4px solid var(--primary,#1a56db)!important;">
              <div class="text-muted small fw-semibold text-uppercase">Total Enrolled</div>
              <div class="fs-2 fw-bold text-dark mt-1"><?= number_format($genderKpi['total']) ?></div>
              <div class="text-muted" style="font-size:0.75rem;">Active Learners</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card border h-100 p-3 shadow-none text-center" style="background:#f4f8ff;border-left:4px solid #0d6efd!important;">
              <div class="text-muted small fw-semibold text-uppercase"><i class="fas fa-mars text-primary me-1"></i>Male Learners</div>
              <div class="fs-2 fw-bold text-primary mt-1"><?= number_format($genderKpi['male']) ?></div>
              <div class="text-primary fw-semibold" style="font-size:0.75rem;"><?= $genderKpi['pct_male'] ?>% of total</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card border h-100 p-3 shadow-none text-center" style="background:#fff6f9;border-left:4px solid #e83e8c!important;">
              <div class="text-muted small fw-semibold text-uppercase"><i class="fas fa-venus me-1" style="color:#e83e8c;"></i>Female Learners</div>
              <div class="fs-2 fw-bold mt-1" style="color:#e83e8c;"><?= number_format($genderKpi['female']) ?></div>
              <div class="fw-semibold" style="color:#e83e8c;font-size:0.75rem;"><?= $genderKpi['pct_female'] ?>% of total</div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="card border h-100 p-3 shadow-none text-center" style="background:#fbfbfb;border-left:4px solid #6c757d!important;">
              <div class="text-muted small fw-semibold text-uppercase">Gender Ratio</div>
              <div class="fs-2 fw-bold text-secondary mt-1">
                <?php
                  if ($genderKpi['female'] > 0 && $genderKpi['male'] > 0) {
                    echo round($genderKpi['male'] / $genderKpi['female'], 2) . ' : 1';
                  } elseif ($genderKpi['male'] > 0) {
                    echo '100% M';
                  } elseif ($genderKpi['female'] > 0) {
                    echo '100% F';
                  } else {
                    echo '—';
                  }
                ?>
              </div>
              <div class="text-muted" style="font-size:0.75rem;">(Male : Female Ratio)</div>
            </div>
          </div>
        </div>

        <!-- Grade-Level Summary Table -->
        <div class="mb-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0">
              <i class="fas fa-layer-group me-2 text-primary"></i>Grade-Level Gender Distribution
            </h6>
            <span class="badge bg-light text-dark border fw-normal" style="font-size:0.75rem;">Summary by Grade</span>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light text-center">
                <tr>
                  <th style="width:25%;" class="text-start ps-3">Grade Level</th>
                  <th style="width:12%;">Male</th>
                  <th style="width:12%;">Female</th>
                  <th style="width:14%;">Total</th>
                  <th style="width:12%;">% Male</th>
                  <th style="width:12%;">% Female</th>
                  <th style="width:13%;">Distribution</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($genderGradeData) || $genderKpi['total'] === 0): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-1"></i> No enrolled student records found matching the selected filter.
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($genderGradeData as $row): ?>
                <tr>
                  <td class="fw-semibold text-start ps-3"><?= htmlspecialchars($row['grade_level']) ?></td>
                  <td class="text-center"><?= number_format($row['male']) ?></td>
                  <td class="text-center"><?= number_format($row['female']) ?></td>
                  <td class="text-center fw-bold"><?= number_format($row['total']) ?></td>
                  <td class="text-center text-primary"><?= $row['pct_male'] ?>%</td>
                  <td class="text-center" style="color:#e83e8c;"><?= $row['pct_female'] ?>%</td>
                  <td class="text-center">
                    <?php if ($row['total'] > 0): ?>
                    <div class="progress" style="height: 10px; min-width: 80px;" title="Male: <?= $row['pct_male'] ?>%, Female: <?= $row['pct_female'] ?>%">
                      <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $row['pct_male'] ?>%"></div>
                      <div class="progress-bar" role="progressbar" style="width: <?= $row['pct_female'] ?>%; background-color:#e83e8c;"></div>
                    </div>
                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot class="table-light fw-bold text-center">
                <tr>
                  <td class="text-start ps-3">TOTAL</td>
                  <td><?= number_format($genderKpi['male']) ?></td>
                  <td><?= number_format($genderKpi['female']) ?></td>
                  <td><?= number_format($genderKpi['total']) ?></td>
                  <td class="text-primary"><?= $genderKpi['pct_male'] ?>%</td>
                  <td style="color:#e83e8c;"><?= $genderKpi['pct_female'] ?>%</td>
                  <td>
                    <?php if ($genderKpi['total'] > 0): ?>
                    <div class="progress" style="height: 10px; min-width: 80px;">
                      <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $genderKpi['pct_male'] ?>%"></div>
                      <div class="progress-bar" role="progressbar" style="width: <?= $genderKpi['pct_female'] ?>%; background-color:#e83e8c;"></div>
                    </div>
                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Section-Level Breakdown Table -->
        <div class="mb-4">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold text-dark mb-0">
              <i class="fas fa-th-list me-2 text-primary"></i>Section Breakdown & Class Adviser Assignment
            </h6>
            <span class="badge bg-light text-dark border fw-normal" style="font-size:0.75rem;">Disaggregated by Section</span>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
              <thead class="table-light text-center">
                <tr>
                  <th style="width:5%;">#</th>
                  <th style="width:18%;" class="text-start ps-2">Grade Level</th>
                  <th style="width:17%;" class="text-start ps-2">Section</th>
                  <th style="width:24%;" class="text-start ps-2">Class Adviser</th>
                  <th style="width:9%;">Male</th>
                  <th style="width:9%;">Female</th>
                  <th style="width:9%;">Total</th>
                  <th style="width:9%;">% Male</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($genderSectionData)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-1"></i> No section student records found matching the selected filter.
                  </td>
                </tr>
                <?php else: ?>
                <?php foreach ($genderSectionData as $idx => $sRow): ?>
                <tr>
                  <td class="text-center text-muted"><?= $idx + 1 ?></td>
                  <td class="text-start ps-2"><?= htmlspecialchars($sRow['grade_level']) ?></td>
                  <td class="text-start ps-2 fw-semibold"><?= htmlspecialchars($sRow['section']) ?></td>
                  <td class="text-start ps-2"><?= htmlspecialchars($sRow['adviser']) ?></td>
                  <td class="text-center"><?= number_format($sRow['male']) ?></td>
                  <td class="text-center"><?= number_format($sRow['female']) ?></td>
                  <td class="text-center fw-bold"><?= number_format($sRow['total']) ?></td>
                  <td class="text-center text-primary"><?= $sRow['pct_male'] ?>%</td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot class="table-light fw-bold text-center">
                <tr>
                  <td colspan="4" class="text-start ps-3">TOTAL (<?= count($genderSectionData) ?> Section<?= count($genderSectionData) === 1 ? '' : 's' ?>)</td>
                  <td><?= number_format($genderKpi['male']) ?></td>
                  <td><?= number_format($genderKpi['female']) ?></td>
                  <td><?= number_format($genderKpi['total']) ?></td>
                  <td class="text-primary"><?= $genderKpi['pct_male'] ?>%</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Official Signatories Block -->
        <div class="signatories" id="signatories-block">
          <div class="signatory-block">
            <div class="sig-label">Prepared by</div>
            <div class="sig-name"><?= htmlspecialchars($sigData['prepared_by_name']) ?></div>
            <div class="sig-position"><?= htmlspecialchars($sigData['prepared_by_title']) ?></div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Noted by</div>
            <div class="sig-name"><?= htmlspecialchars($sigData['noted_by_name'] ?: ' ') ?></div>
            <div class="sig-position"><?= htmlspecialchars($sigData['noted_by_title']) ?></div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Date Generated</div>
            <div class="sig-name"><?= date('F j, Y') ?></div>
            <div class="sig-position"><?= date('g:i A') ?></div>
          </div>
        </div>

        <div style="font-size:.75rem;color:var(--gray-400);text-align:right;margin-top:1.5rem;" class="no-print">
          Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($sigData['prepared_by_name']) ?>
        </div>
      </div>
    </div>

    <?php else: ?>

    <!-- Filters -->
    <div class="card mb-3 no-print">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <!-- Grade Level -->
          <div class="col-md-2">
            <label class="form-label mb-1">Grade Level</label>
            <select id="grade-select" class="form-select">
              <option value="">All Grades</option>
              <?php foreach (GRADE_LEVELS as $gl): ?>
              <option value="<?= $gl ?>" <?= $grade===$gl?'selected':'' ?>><?= $gl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Section (dynamic) -->
          <div class="col-md-2">
            <label class="form-label mb-1">Section</label>
            <select id="section-select" class="form-select">
              <option value="">All Sections</option>
              <?php
                $currentSections = $grade && isset(SECTION_MAP[$grade]) ? SECTION_MAP[$grade] : [];
                foreach ($currentSections as $sec):
              ?>
              <option value="<?= $sec ?>" <?= $section===$sec?'selected':'' ?>><?= $sec ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- School Year -->
          <div class="col-md-2">
            <label class="form-label mb-1">School Year</label>
            <select id="sy-select" class="form-select">
              <option value="" <?= $sy===''?'selected':'' ?>>All School Years</option>
              <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
              <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['year_label'] === $sy) ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Live Search -->
          <div class="col-md-2">
            <label class="form-label mb-1">
              Search
              <span id="search-spinner" class="ms-1">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
              </span>
            </label>
            <input type="text" id="live-search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
          </div>
          <!-- Signatories Settings Shortcut -->
          <div class="col-md-2">
            <a href="<?= BASE_URL ?>/views/admin/signatories.php?return_to=<?= urlencode($_SERVER['REQUEST_URI'] ?? '') ?>" class="btn btn-outline-secondary w-100" title="Configure Report Signatories">
              <i class="fas fa-file-signature me-1"></i>Signatories
            </a>
          </div>
          <!-- Actions -->
          <div class="col-md-1">
            <button class="btn btn-light w-100" onclick="window.print()" title="Print"><i class="fas fa-print"></i></button>
          </div>
          <div class="col-md-1">
            <a href="reports.php" class="btn btn-light w-100" title="Close"><i class="fas fa-times"></i></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Output -->
    <div class="card">
      <div class="card-body">
        <div class="report-header text-center mb-4">
          <div class="d-inline-flex align-items-start justify-content-center gap-4">
            <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="School Logo" class="report-header-logo" style="margin-top: 4px;">
            <div class="text-center">
              <div style="font-size:.95rem;color:#222;font-weight:400;margin-bottom:.2rem;">Republic of the Philippines · Department of Education</div>
              <div style="font-size:1.15rem;color:#000;font-weight:700;margin-bottom:.2rem;"><?= SCHOOL_NAME ?></div>
              <div style="font-size:.95rem;color:#333;font-weight:400;margin-bottom:1.75rem;"><?= SCHOOL_ADDRESS ?></div>

              <h3 class="text-center text-uppercase text-dark fw-bold mb-1" id="report-title" style="letter-spacing:0.5px;">
                STUDENT MASTERLIST
              </h3>
              <div style="font-size:.95rem;color:#333;" class="text-center" id="report-subtitle">
                School Year <span id="report-sy"><?= htmlspecialchars($sy ?: 'All School Years') ?></span><?= $grade ? ' · '.$grade : '' ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Masterlist table — live-updated by JS -->
        <div id="masterlist-table-wrap">
          <table class="table table-bordered table-sm" id="masterlist-table">
            <thead>
              <tr>
                <th>#</th><th>LRN</th><th>Full Name</th><th>Grade</th><th>Section</th><th>Sex</th><th>Age</th>
                <th>Contact No.</th><th>Guardian</th>
              </tr>
            </thead>
            <tbody id="masterlist-body">
              <?php if (empty($students)): ?>
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <div class="mb-2"><i class="fas fa-user-graduate fa-2x text-secondary opacity-50"></i></div>
                  <div class="fw-semibold">No enrolled students found for S.Y. <?= htmlspecialchars($sy ?: 'Selected Filter') ?></div>
                  <div class="small text-muted mt-1">
                    Try selecting a previous school year (e.g. <strong>2025–2026</strong>) or <strong>All School Years</strong> from the filter above.
                  </div>
                </td>
              </tr>
              <?php else: foreach ($students as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td style="font-family:monospace;font-size:.78rem;"><?= htmlspecialchars($s['lrn']) ?></td>
                <td><?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?></td>
                <td><?= htmlspecialchars($s['grade_level']) ?></td>
                <td><?= htmlspecialchars($s['section']) ?></td>
                <td><?= htmlspecialchars($s['sex']) ?></td>
                <td><?= $s['age'] ?></td>
                <td><?= htmlspecialchars($s['contact']??'—') ?></td>
                <td><?= htmlspecialchars($s['guardian_name']??'—') ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
            <tfoot id="masterlist-foot">
              <?php if (!empty($students)): ?>
              <tr class="fw-bold"><td colspan="9">Total: <?= count($students) ?> student(s)</td></tr>
              <?php endif; ?>
            </tfoot>
          </table>
        </div>

        <!-- Signatories -->
        <div class="signatories" id="signatories-block">
          <div class="signatory-block">
            <div class="sig-label">Prepared by</div>
            <div class="sig-name"><?= htmlspecialchars($sigData['prepared_by_name']) ?></div>
            <div class="sig-position"><?= htmlspecialchars($sigData['prepared_by_title']) ?></div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Noted by</div>
            <div class="sig-name"><?= htmlspecialchars($sigData['noted_by_name'] ?: ' ') ?></div>
            <div class="sig-position"><?= htmlspecialchars($sigData['noted_by_title']) ?></div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Date Generated</div>
            <div class="sig-name"><?= date('F j, Y') ?></div>
            <div class="sig-position"><?= date('g:i A') ?></div>
          </div>
        </div>

        <div style="font-size:.75rem;color:var(--gray-400);text-align:right;margin-top:1rem;" class="no-print">
          Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($sigData['prepared_by_name']) ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script src="<?= BASE_URL ?>/assets/js/sf9-renderer.js"></script>
<script>
showDesktopOnlyWarning();

<?php if ($type === 'gender'): ?>
(function() {
  const SECTION_MAP = <?= $sectionMapJson ?>;
  window.onGenderGradeChange = function() {
    const gradeSelect = document.getElementById('gender-grade-select');
    const secSelect   = document.getElementById('gender-section-select');
    const form        = document.getElementById('genderFilterForm');
    if (!gradeSelect || !secSelect) return;
    
    const g = gradeSelect.value;
    const secs = (g && SECTION_MAP[g]) ? SECTION_MAP[g] : [];
    
    secSelect.innerHTML = '<option value="">All Sections</option>';
    secs.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s;
      opt.textContent = s;
      secSelect.appendChild(opt);
    });
    
    if (form) form.submit();
  };
})();
<?php endif; ?>

<?php if ($type === 'masterlist'): ?>
(function () {
  const SECTION_MAP  = <?= $sectionMapJson ?>;
  const BASE_URL     = '<?= BASE_URL ?>';

  const gradeSelect   = document.getElementById('grade-select');
  const sectionSelect = document.getElementById('section-select');
  const sySelect      = document.getElementById('sy-select');
  const liveSearch    = document.getElementById('live-search');
  const spinner       = document.getElementById('search-spinner');
  const tbody         = document.getElementById('masterlist-body');
  const tfoot         = document.getElementById('masterlist-foot');

  /* ── Section dropdown — updates when grade changes ── */
  function refreshSections() {
    const grade    = gradeSelect.value;
    const sections = (grade && SECTION_MAP[grade]) ? SECTION_MAP[grade] : [];
    const prev     = sectionSelect.value;

    sectionSelect.innerHTML = '<option value="">All Sections</option>';
    sections.forEach(sec => {
      const opt = document.createElement('option');
      opt.value = sec;
      opt.textContent = sec;
      if (sec === prev) opt.selected = true;
      sectionSelect.appendChild(opt);
    });
  }

  /* ── Fetch + re-render table ── */
  async function fetchStudents() {
    spinner.classList.add('visible');

    const params = new URLSearchParams({
      grade:   gradeSelect.value,
      section: sectionSelect.value,
      year:    sySelect.value,
      search:  liveSearch.value.trim(),
      status:  'active',
    });

    const syLabel = sySelect.options[sySelect.selectedIndex]?.text || sySelect.value || 'All School Years';
    const repSyEl = document.getElementById('report-sy');
    if (repSyEl) repSyEl.textContent = sySelect.value ? sySelect.value : 'All School Years';

    try {
      const res  = await fetch(`${BASE_URL}/api/students/index.php?${params}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.message);

      const students = data.students;

      if (students.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted py-4">
          <div class="mb-2"><i class="fas fa-user-graduate fa-2x text-secondary opacity-50"></i></div>
          <div class="fw-semibold">No enrolled students found for ${escHtml(syLabel)}.</div>
          <div class="small text-muted mt-1">Try selecting <strong>All School Years</strong> or <strong>2025–2026</strong> from the filter above.</div>
        </td></tr>`;
        tfoot.innerHTML = '';
        return;
      }

      tbody.innerHTML = students.map((s, i) => `
        <tr>
          <td>${i + 1}</td>
          <td style="font-family:monospace;font-size:.78rem;">${escHtml(s.lrn)}</td>
          <td>${escHtml(s.last_name + ', ' + s.first_name + ' ' + (s.middle_name ?? ''))}</td>
          <td>${escHtml(s.grade_level)}</td>
          <td>${escHtml(s.section)}</td>
          <td>${escHtml(s.sex)}</td>
          <td>${s.age}</td>
          <td>${escHtml(s.contact || '—')}</td>
          <td>${escHtml(s.guardian_name || '—')}</td>
        </tr>`).join('');

      tfoot.innerHTML = `<tr class="fw-bold"><td colspan="9">Total: ${students.length} student(s)</td></tr>`;

    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-3">Error loading data.</td></tr>`;
      tfoot.innerHTML = '';
    } finally {
      spinner.classList.remove('visible');
    }
  }

  function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
  }

  /* ── Debounce helper ── */
  function debounce(fn, delay) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  }

  const debouncedFetch = debounce(fetchStudents, 350);

  /* ── Event listeners ── */
  gradeSelect.addEventListener('change', () => {
    refreshSections();
    fetchStudents();
  });
  sectionSelect.addEventListener('change', fetchStudents);
  sySelect.addEventListener('change', fetchStudents);
  liveSearch.addEventListener('input', debouncedFetch);

  /* ── Init ── */
  refreshSections();
})();
<?php endif; ?>

<?php if ($type === 'sf9'): ?>
(function() {
  const BASE_URL = '<?= BASE_URL ?>';
  const SECTION_MAP = <?= $sectionMapJson ?>;
  const adviserMap = <?= json_encode($adviserMap) ?>;
  const defaultPrincipal = <?= json_encode($sigData['noted_by_name'] ?: 'School Principal') ?>;
  const defaultAdviser = <?= json_encode($sigData['prepared_by_name'] ?: 'Class Adviser') ?>;

  const gradeFilter = document.getElementById('sf9-grade-filter');
  const sectionFilter = document.getElementById('sf9-section-filter');
  const studentSelect = document.getElementById('sf9-student-select');

  function refreshSf9Sections() {
    if (!gradeFilter || !sectionFilter) return;
    const g = gradeFilter.value;
    const secs = (g && SECTION_MAP[g]) ? SECTION_MAP[g] : [];
    sectionFilter.innerHTML = '<option value="">All Sections</option>';
    secs.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s; opt.textContent = s;
      sectionFilter.appendChild(opt);
    });
    filterSf9StudentDropdown();
  }

  function filterSf9StudentDropdown() {
    const gVal = gradeFilter ? gradeFilter.value : '';
    const sVal = sectionFilter ? sectionFilter.value : '';
    if (!studentSelect) return;

    const opts = studentSelect.querySelectorAll('option');
    let currentSelectedHidden = false;

    opts.forEach(opt => {
      if (!opt.value) return;
      const optG = opt.dataset.grade || '';
      const optS = opt.dataset.section || '';
      const matchG = !gVal || optG === gVal;
      const matchS = !sVal || optS === sVal;
      if (matchG && matchS) {
        opt.style.display = '';
        opt.disabled = false;
      } else {
        opt.style.display = 'none';
        opt.disabled = true;
        if (opt.selected) currentSelectedHidden = true;
      }
    });

    if (currentSelectedHidden) {
      studentSelect.value = '';
      loadSf9Report();
    }
  }

  window.filterSf9StudentDropdown = filterSf9StudentDropdown;

  window.updateSf9SignatoriesLive = function() {
    const advVal = document.getElementById('sf9AdviserInput')?.value || '';
    const prinVal = document.getElementById('sf9PrincipalInput')?.value || '';
    const advEl = document.getElementById('sf9AdviserName');
    const prinEl = document.getElementById('sf9SchoolHeadName');
    if (advEl) advEl.textContent = advVal || '—';
    if (prinEl) prinEl.textContent = prinVal || '—';

    const stuId = studentSelect ? studentSelect.value : '';
    if (stuId) {
      if (advVal) localStorage.setItem('spsmis_sf9_adv_' + stuId, advVal);
      if (prinVal) localStorage.setItem('spsmis_sf9_prin_' + stuId, prinVal);
    }
  };

  window.onSf9StudentChange = function() {
    loadSf9Report();
  };

  window.loadSf9Report = async function() {
    const stuId = studentSelect ? studentSelect.value : '';
    const sy = document.getElementById('sf9-sy-select')?.value || '<?= $sy ?>';
    const period = parseInt(document.getElementById('sf9-period-select')?.value || '0');
    const container = document.getElementById('sf9-report-content');

    if (!stuId) {
      container.innerHTML = `
        <div class="card p-5 text-center text-muted no-print">
          <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
          <h5>Please Select a Learner</h5>
          <p class="small mb-0">Choose a student above to generate their SF9 Learner's Progress Report Card.</p>
        </div>`;
      return;
    }

    showLoading('Generating SF9 Report Card...', 'Loading student grades & DepEd Form 9 template...');
    try {
      const res = await fetch(`${BASE_URL}/api/grades/student.php?student_id=${stuId}&school_year=${sy}`);
      const data = await res.json();
      hideLoading();

      if (!data.ok) {
        showToast(data.message || 'Failed to load student record', 'error');
        return;
      }

      const student = data.student;
      const classKey = (student.grade_level || '') + '|' + (student.section || '');
      const savedAdv = localStorage.getItem('spsmis_sf9_adv_' + stuId) || adviserMap[classKey] || defaultAdviser;
      const savedPrin = localStorage.getItem('spsmis_sf9_prin_' + stuId) || defaultPrincipal;

      const advInput = document.getElementById('sf9AdviserInput');
      const prinInput = document.getElementById('sf9PrincipalInput');
      if (advInput) advInput.value = savedAdv;
      if (prinInput) prinInput.value = savedPrin;

      container.innerHTML = renderSf9ReportCard(data, {
        period: period,
        adviser: savedAdv,
        schoolHead: savedPrin,
        baseUrl: BASE_URL
      });

      // Two-way sync with inline edit
      const advEl = document.getElementById('sf9AdviserName');
      if (advEl) {
        advEl.addEventListener('input', () => {
          const val = advEl.textContent.trim();
          if (advInput) advInput.value = val;
          localStorage.setItem('spsmis_sf9_adv_' + stuId, val);
        });
      }
      const prinEl = document.getElementById('sf9SchoolHeadName');
      if (prinEl) {
        prinEl.addEventListener('input', () => {
          const val = prinEl.textContent.trim();
          if (prinInput) prinInput.value = val;
          localStorage.setItem('spsmis_sf9_prin_' + stuId, val);
        });
      }

    } catch (err) {
      hideLoading();
      showToast('Error generating SF9: ' + err.message, 'error');
    }
  };

  if (gradeFilter) gradeFilter.addEventListener('change', refreshSf9Sections);
  if (sectionFilter) sectionFilter.addEventListener('change', filterSf9StudentDropdown);

  // Auto load if student_id is selected
  const initialStuId = studentSelect ? studentSelect.value : '';
  if (initialStuId) {
    window.addEventListener('DOMContentLoaded', () => {
      loadSf9Report();
    });
  }
})();
<?php endif; ?>
</script>
</body>
</html>
