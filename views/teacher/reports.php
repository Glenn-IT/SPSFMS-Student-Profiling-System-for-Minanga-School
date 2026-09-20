<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'reports';

$sigData = getSignatories($pdo, $user);

// Fetch fresh user profile details to resolve advisory class defaults
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$freshUser = $uStmt->fetch() ?: $user;

// Get only the teacher's assigned advisory classes
$myClasses = getTeacherAdvisoryClasses($pdo, $freshUser['id']);
$hasAdvisory = !empty($myClasses);

$reportType = $_GET['type'] ?? 'summary'; // 'summary' or 'sf10'
if (!in_array($reportType, ['summary', 'sf10'])) {
    $reportType = 'summary';
}

$selectedClassIdx = 0;
$grade   = '';
$section = '';

if ($hasAdvisory) {
    if (isset($_GET['class_idx']) && isset($myClasses[(int)$_GET['class_idx']])) {
        $selectedClassIdx = (int)$_GET['class_idx'];
        $grade   = $myClasses[$selectedClassIdx]['grade_level'];
        $section = $myClasses[$selectedClassIdx]['section'];
    } elseif (isset($_GET['grade']) && isset($_GET['section'])) {
        $matched = false;
        foreach ($myClasses as $idx => $cls) {
            if ($cls['grade_level'] === $_GET['grade'] && $cls['section'] === $_GET['section']) {
                $selectedClassIdx = $idx;
                $grade   = $cls['grade_level'];
                $section = $cls['section'];
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $grade   = $myClasses[0]['grade_level'];
            $section = $myClasses[0]['section'];
            $selectedClassIdx = 0;
        }
    } else {
        $grade   = $myClasses[0]['grade_level'];
        $section = $myClasses[0]['section'];
        $selectedClassIdx = 0;
    }
}

$sy = $_GET['sy'] ?? SCHOOL_YEAR;

// Fetch all active students across all advisory classes for quick search and selection
$allAdvisoryStudents = [];
if ($hasAdvisory) {
    $advisoryClauses = [];
    $advisoryParams  = [];
    foreach ($myClasses as $cls) {
        $advisoryClauses[] = "(grade_level = ? AND section = ?)";
        $advisoryParams[]  = $cls['grade_level'];
        $advisoryParams[]  = $cls['section'];
    }
    $allSql = "SELECT id, lrn, first_name, middle_name, last_name, grade_level, section, sex, birthdate, age 
               FROM students 
               WHERE status = 'active' AND (" . implode(' OR ', $advisoryClauses) . ") 
               ORDER BY grade_level, section, last_name, first_name";
    $allStmt = $pdo->prepare($allSql);
    $allStmt->execute($advisoryParams);
    $allAdvisoryStudents = $allStmt->fetchAll();
}

// Data for Class Summary Mode
$summaryStudents = [];
if ($hasAdvisory && $grade && $section && $reportType === 'summary') {
    $stmt = $pdo->prepare("SELECT s.id, s.last_name, s.first_name, s.middle_name, s.lrn, s.sex,
        AVG(g.final_grade) as avg_grade,
        COUNT(g.id) as graded_count
        FROM students s
        LEFT JOIN grades g ON g.student_id=s.id AND g.school_year=?
        WHERE s.grade_level=? AND s.section=? AND s.status='active'
        GROUP BY s.id ORDER BY s.last_name, s.first_name");
    $stmt->execute([$sy, $grade, $section]);
    $summaryStudents = $stmt->fetchAll();
}

// Data for Individual Learner SF10 Mode
$selectedStudentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$sf10Student = null;
$sf10Grades  = [];
$sf10Subjects = [];
$sf10GeneralAverage = null;
$sf10GeneralRemarks = '—';
$sf10AdviserName = $user['name'];

if ($reportType === 'sf10' && $selectedStudentId > 0 && $hasAdvisory) {
    // Security check: verify this student belongs to the teacher's advisory classes
    foreach ($allAdvisoryStudents as $stu) {
        if ((int)$stu['id'] === $selectedStudentId) {
            // Found and authorized
            $sf10Student = $stu;
            break;
        }
    }

    if ($sf10Student) {
        // Fetch full profile info for student
        $fullStuStmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
        $fullStuStmt->execute([$selectedStudentId]);
        $fullStu = $fullStuStmt->fetch();
        if ($fullStu) {
            $sf10Student = $fullStu;
        }

        // Get subjects for this student's grade level
        $sf10Subjects = getSubjectsForGrade($sf10Student['grade_level'], $pdo);

        // Fetch grades for this student and school year
        $gStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id = ? AND school_year = ?");
        $gStmt->execute([$selectedStudentId, $sy]);
        $gradeRows = $gStmt->fetchAll();

        $gradeMap = [];
        foreach ($gradeRows as $gr) {
            $gradeMap[$gr['subject']] = $gr;
            if (!in_array($gr['subject'], $sf10Subjects)) {
                $sf10Subjects[] = $gr['subject'];
            }
        }

        $totalFinal = 0;
        $gradedSubjectsCount = 0;

        foreach ($sf10Subjects as $subj) {
            $row = $gradeMap[$subj] ?? null;
            $t1 = $row && ($row['t1'] !== null || $row['q1'] !== null) ? (float)($row['t1'] ?? $row['q1']) : null;
            $t2 = $row && ($row['t2'] !== null || $row['q2'] !== null) ? (float)($row['t2'] ?? $row['q2']) : null;
            $t3 = $row && ($row['t3'] !== null || $row['q3'] !== null) ? (float)($row['t3'] ?? $row['q3']) : null;
            $final = $row && $row['final_grade'] !== null ? (float)$row['final_grade'] : null;

            if ($final === null) {
                $terms = array_filter([$t1, $t2, $t3], fn($v) => $v !== null);
                if (count($terms) === 3) {
                    $final = round(array_sum($terms) / 3, 2);
                }
            }

            $remarks = '';
            if ($final !== null) {
                $remarks = ($final >= 75) ? 'Passed' : 'Failed';
                $totalFinal += $final;
                $gradedSubjectsCount++;
            }

            $sf10Grades[$subj] = [
                't1' => $t1,
                't2' => $t2,
                't3' => $t3,
                'q1' => $t1,
                'q2' => $t2,
                'q3' => $t3,
                'final_grade' => $final,
                'remarks' => $remarks
            ];
        }

        if ($gradedSubjectsCount > 0) {
            $sf10GeneralAverage = round($totalFinal / $gradedSubjectsCount, 2);
            $sf10GeneralRemarks = ($sf10GeneralAverage >= 75) ? 'Passed' : 'Failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Reports — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sf9.css">
  <style>
    @media print {
      .no-print { display:none !important; }
      .sidebar,.top-navbar { display:none !important; }
      .main-content { margin:0 !important; padding:0 !important; }
      .app-wrapper { padding:0 !important; }
      .signatories { margin-top: 2.5rem; page-break-inside: avoid; }
      .report-header-logo { max-height: 90px !important; display: inline-block !important; vertical-align: top !important; }
      body { background: #fff !important; font-size: 11pt; }
      .card { border: none !important; box-shadow: none !important; }
      .card-body { padding: 0 !important; }
      .table-bordered th, .table-bordered td { border: 1px solid #111 !important; color: #000 !important; }
    }
    .report-header { text-align: center; margin-bottom: 1.5rem; border-bottom: 2px solid var(--secondary); padding-bottom: 1rem; }
    .report-header h3 { font-weight: 800; color: #000; letter-spacing: 0.5px; }
    .report-header-logo { height: 90px; width: auto; object-fit: contain; flex-shrink: 0; }
    .signatories { margin-top: 2.5rem; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
    .signatory-block { text-align: center; }
    .signatory-block .sig-label { font-size: .72rem; color: var(--gray-600); text-transform: uppercase; letter-spacing: .5px; margin-bottom: .25rem; }
    .signatory-block .sig-name { font-weight: 700; font-size: .85rem; border-top: 1.5px solid var(--dark,#222); padding-top: .35rem; margin-top: 2rem; }
    .signatory-block .sig-position { font-size: .75rem; color: var(--gray-600); }

    /* SF10 Official Info Layout */
    .sf10-infobox {
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 6px;
      padding: 0.85rem 1rem;
      margin-bottom: 1.25rem;
    }
    .sf10-label {
      font-size: .68rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .5px;
      display: block;
      margin-bottom: 2px;
    }
    .sf10-val {
      font-size: .88rem;
      font-weight: 600;
      color: #0f172a;
    }
    .sf10-table th {
      background: #f1f5f9 !important;
      color: #0f172a;
      font-size: .82rem;
      text-transform: uppercase;
      letter-spacing: .3px;
      padding: 6px 8px;
    }
    .sf10-table td {
      font-size: .86rem;
      padding: 6px 8px;
    }
    .report-mode-btn {
      border-radius: 20px;
      padding: 6px 18px;
      font-weight: 600;
      font-size: .86rem;
    }

    /* Real-Time Student Search Autocomplete */
    .realtime-search-wrap {
      position: relative;
    }
    .search-input-group {
      position: relative;
    }
    .search-input-group .search-icon-left {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: .95rem;
      pointer-events: none;
      z-index: 4;
    }
    .search-input-group .clear-search-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      padding: 4px;
      display: none;
      z-index: 4;
      font-size: .85rem;
    }
    .search-input-group .clear-search-btn:hover {
      color: #334155;
    }
    .realtime-search-input {
      padding-left: 38px !important;
      padding-right: 34px !important;
      height: 42px;
      font-size: .92rem;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      background: #fff;
      transition: all .2s ease;
    }
    .realtime-search-input:focus {
      border-color: #34a853;
      box-shadow: 0 0 0 3.5px rgba(52, 168, 83, 0.15);
      outline: none;
    }
    .realtime-results-panel {
      position: absolute;
      top: calc(100% + 4px);
      left: 0;
      right: 0;
      background: #ffffff;
      border: 1.5px solid #cbd5e1;
      border-radius: 10px;
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      max-height: 320px;
      overflow-y: auto;
      z-index: 1050;
      display: none;
    }
    .realtime-item {
      padding: 10px 14px;
      cursor: pointer;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: background .15s ease;
    }
    .realtime-item:last-child {
      border-bottom: none;
    }
    .realtime-item:hover, .realtime-item.active-item {
      background: #f0fdf4;
    }
    .realtime-item:hover .item-title, .realtime-item.active-item .item-title {
      color: #16a34a;
    }
    .realtime-item .item-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #e2e8f0;
      color: #475569;
      font-weight: 700;
      font-size: .82rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-right: 12px;
    }
    .realtime-item:hover .item-avatar, .realtime-item.active-item .item-avatar {
      background: #dcfce7;
      color: #16a34a;
    }
    .realtime-item .item-info {
      flex: 1;
      min-width: 0;
    }
    .realtime-item .item-title {
      font-weight: 700;
      font-size: .88rem;
      color: #0f172a;
      margin-bottom: 2px;
    }
    .realtime-item .item-subtitle {
      font-size: .76rem;
      color: #64748b;
    }
    .realtime-item .item-badge {
      font-size: .72rem;
      padding: 3px 8px;
      border-radius: 12px;
      background: #f1f5f9;
      color: #334155;
      font-weight: 600;
      white-space: nowrap;
    }
    .realtime-empty {
      padding: 24px;
      text-align: center;
      color: #94a3b8;
      font-size: .88rem;
    }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar no-print">
      <div><div class="page-title">Reports</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Reports</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Teacher</div></div></div></div>
    </nav>

    <div class="page-header no-print d-flex flex-column gap-2 mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h3 class="mb-1"><?= $reportType === 'sf9' ? 'Learner’s Progress Report Card (SF9)' : ($reportType === 'sf10' ? 'Individual Learner SF10 Report' : 'Class Grade Summary Report') ?></h3>
          <p class="mb-0 text-muted">Generate and print academic records, SF9 report cards, and SF10 for your advisory class</p>
        </div>

        <!-- Mode Toggle: Class Summary vs Individual SF10 vs Individual SF9 -->
        <div class="d-flex align-items-center gap-2 bg-white p-1 rounded-pill border shadow-sm">
          <a href="?type=summary&class_idx=<?= $selectedClassIdx ?>&sy=<?= urlencode($sy) ?>" class="btn report-mode-btn <?= $reportType === 'summary' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark' ?>">
            <i class="fas fa-list-alt me-1"></i>Class Summary
          </a>
          <a href="?type=sf10<?= $selectedStudentId ? '&student_id='.$selectedStudentId : '' ?>&sy=<?= urlencode($sy) ?>" class="btn report-mode-btn <?= $reportType === 'sf10' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark' ?>">
            <i class="fas fa-id-card me-1"></i>Individual SF10
          </a>
          <a href="?type=sf9<?= $selectedStudentId ? '&student_id='.$selectedStudentId : '' ?>&sy=<?= urlencode($sy) ?>" class="btn report-mode-btn <?= $reportType === 'sf9' ? 'btn-success text-white shadow-sm' : 'btn-light text-dark' ?>">
            <i class="fas fa-file-invoice me-1"></i>Individual SF9
          </a>
        </div>
      </div>

      <!-- Multiple Advisory Classes Quick-Select Shortcuts (Visible in summary mode) -->
      <?php if ($reportType === 'summary' && count($myClasses) > 1): ?>
        <div class="p-2 bg-white rounded-3 border shadow-sm mt-1">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold small me-1 ps-1"><i class="fas fa-chalkboard me-1 text-success"></i>My Advisory Classes:</span>
            <?php foreach ($myClasses as $idx => $cls):
              $isCurrent = ($selectedClassIdx === $idx);
            ?>
              <a href="?type=summary&class_idx=<?= $idx ?>&sy=<?= urlencode($sy) ?>" class="btn btn-sm <?= $isCurrent ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light border text-dark' ?>" style="border-radius: 20px; font-size: .84rem; padding: 4px 14px;">
                <i class="fas fa-layer-group me-1 <?= $isCurrent ? 'text-white' : 'text-success' ?>"></i>
                <?= htmlspecialchars($cls['grade_level']) ?> — Section <?= htmlspecialchars($cls['section']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$hasAdvisory): ?>
      <div class="alert alert-warning d-flex align-items-center mb-4 no-print shadow-sm" style="border-radius: 10px;">
        <i class="fas fa-exclamation-triangle fa-2x text-warning me-3"></i>
        <div>
          <h5 class="alert-heading fw-bold mb-1">No Advisory Class Assigned</h5>
          <p class="mb-0">You are currently not assigned as an adviser to any class. Teachers can only generate and print reports for their own advisory class. Please contact the administrator to assign your advisory class.</p>
        </div>
      </div>
    <?php else: ?>

      <!-- FILTER CONTROLS (NO-PRINT) -->
      <?php if ($reportType === 'summary'): ?>
        <!-- Class Summary Filter Form -->
        <form method="get" class="card mb-3 no-print border-0 shadow-sm">
          <input type="hidden" name="type" value="summary">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <?php if (count($myClasses) > 1): ?>
                <div class="col-md-5">
                  <label class="form-label mb-1 fw-semibold"><i class="fas fa-chalkboard-teacher text-success me-1"></i>Advisory Class</label>
                  <select name="class_idx" id="rep-class" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($myClasses as $idx => $cls): ?>
                    <option value="<?= $idx ?>" <?= $selectedClassIdx === $idx ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cls['grade_level']) ?> — Section <?= htmlspecialchars($cls['section']) ?> (Advisory Class)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              <?php else: ?>
                <div class="col-md-5">
                  <label class="form-label mb-1 fw-semibold"><i class="fas fa-chalkboard-teacher text-success me-1"></i>Advisory Class</label>
                  <div class="input-group">
                    <span class="input-group-text bg-success bg-opacity-10 text-success border-end-0"><i class="fas fa-check-circle"></i></span>
                    <input type="text" class="form-control bg-light fw-bold border-start-0" value="<?= htmlspecialchars($grade . ' — Section ' . $section) ?> (Your Advisory Class)" readonly>
                  </div>
                  <input type="hidden" name="class_idx" value="0">
                </div>
              <?php endif; ?>

              <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">School Year</label>
                <select name="sy" class="form-select" onchange="this.form.submit()">
                  <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
                  <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= $sy === $syItem['year_label'] ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-primary w-100" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Report</button>
              </div>
            </div>
          </div>
        </form>

        <!-- Class Summary Card -->
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <div class="report-header text-center mb-4" style="border-bottom:2px solid var(--secondary);padding-bottom:1.25rem;">
              <div class="d-inline-flex align-items-start justify-content-center gap-4">
                <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="School Logo" class="report-header-logo" style="margin-top: 4px;">
                <div class="text-center">
                  <div style="font-size:.95rem;color:#222;font-weight:400;margin-bottom:.2rem;">Republic of the Philippines · Department of Education</div>
                  <div style="font-size:1.15rem;color:#000;font-weight:700;margin-bottom:.2rem;"><?= SCHOOL_NAME ?></div>
                  <div style="font-size:.95rem;color:#333;font-weight:400;margin-bottom:1.5rem;"><?= SCHOOL_ADDRESS ?></div>

                  <h3 class="text-center text-uppercase text-dark fw-bold mb-1" style="letter-spacing:0.5px;">CLASS GRADE SUMMARY REPORT</h3>
                  <div style="font-size:1rem;color:#111;font-weight:700;" class="text-center">
                    <?= htmlspecialchars($grade) ?> — Section <?= htmlspecialchars($section) ?> | S.Y. <?= htmlspecialchars($sy) ?>
                  </div>
                  <div style="font-size:.85rem;color:var(--gray-600);" class="text-center mt-1">
                    Class Adviser: <strong><?= htmlspecialchars($user['name']) ?></strong>
                  </div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:40px;" class="text-center">#</th>
                    <th>LRN</th>
                    <th>Full Name</th>
                    <th class="text-center">Sex</th>
                    <th class="text-center">Graded Subjects</th>
                    <th class="text-center">General Average</th>
                    <th class="text-center">Status</th>
                    <th class="text-center no-print" style="width:90px;">SF10</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($summaryStudents)): ?>
                  <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-users-slash me-2"></i>No enrolled students found in your advisory class (<?= htmlspecialchars($grade . ' - ' . $section) ?>) for S.Y. <?= htmlspecialchars($sy) ?>.</td></tr>
                  <?php else: foreach ($summaryStudents as $i => $s):
                    $avg = $s['avg_grade'] !== null ? round($s['avg_grade'],2) : null;
                    $remarks = $avg !== null ? ($avg >= 75 ? 'Passed' : 'Failed') : '—';
                  ?>
                  <tr>
                    <td class="text-center text-muted fw-bold"><?= $i+1 ?></td>
                    <td style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($s['lrn']) ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?></td>
                    <td class="text-center"><?= htmlspecialchars($s['sex']) ?></td>
                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $s['graded_count'] ?: '0' ?></span></td>
                    <td class="text-center fw-bold fs-6"><?= $avg ?? '—' ?></td>
                    <td class="text-center" style="color:<?= $remarks==='Passed'?'#16a34a':($remarks==='Failed'?'#dc2626':'inherit') ?>;font-weight:700;"><?= $remarks ?></td>
                    <td class="text-center no-print">
                      <a href="?type=sf10&student_id=<?= $s['id'] ?>&sy=<?= urlencode($sy) ?>" class="btn btn-xs btn-outline-success py-1 px-2" style="font-size:.78rem;" title="View & Print SF10">
                        <i class="fas fa-file-invoice me-1"></i>SF10
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Signatories -->
            <div class="signatories" id="signatories-block">
              <div class="signatory-block">
                <div class="sig-label">Prepared by</div>
                <div class="sig-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="sig-position">Class Adviser (<?= htmlspecialchars($grade . ' - ' . $section) ?>)</div>
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

            <div style="font-size:.72rem;color:var(--gray-400);text-align:right;margin-top:1.5rem;" class="no-print">
              Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($user['name']) ?> (Class Adviser)
            </div>
          </div>
        </div>

      <?php elseif ($reportType === 'sf10'): ?>
        <!-- Individual Learner SF10 Search & Report Mode -->
        <form method="get" class="card mb-3 no-print border-0 shadow-sm" id="sf10-search-form" autocomplete="off">
          <input type="hidden" name="type" value="sf10">
          <input type="hidden" name="student_id" id="sf10-student-id-input" value="<?= $selectedStudentId ?: '' ?>">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <div class="col-md-6 realtime-search-wrap">
                <label class="form-label mb-1 fw-semibold"><i class="fas fa-user-graduate text-success me-1"></i>Search Student Name or LRN (Realtime)</label>
                <div class="search-input-group">
                  <i class="fas fa-search search-icon-left"></i>
                  <input type="text" id="realtime-search-input" class="form-control realtime-search-input" 
                         placeholder="Type student name or LRN to search..." 
                         value="<?= $sf10Student ? htmlspecialchars($sf10Student['last_name'].', '.$sf10Student['first_name'].' '.($sf10Student['middle_name'] ? $sf10Student['middle_name'].' ' : '').'('.$sf10Student['grade_level'].' - '.$sf10Student['section'].')') : '' ?>"
                         oninput="onRealtimeSearchInput(this.value)" 
                         onfocus="onRealtimeSearchFocus()"
                         onkeydown="onRealtimeSearchKeydown(event)">
                  <button type="button" id="clear-search-btn" class="clear-search-btn" onclick="clearRealtimeSearch()" title="Clear search">
                    <i class="fas fa-times-circle"></i>
                  </button>
                </div>
                <!-- Real-time suggestion / matching results panel -->
                <div id="realtime-results-panel" class="realtime-results-panel"></div>
              </div>

              <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">School Year</label>
                <select name="sy" id="sf10-sy-select" class="form-select" onchange="onSchoolYearChange()">
                  <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
                  <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= $sy === $syItem['year_label'] ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3 d-flex gap-2">
                <button type="button" class="btn btn-primary flex-grow-1" onclick="window.print()" <?= !$sf10Student ? 'disabled' : '' ?>><i class="fas fa-print me-1"></i>Print SF10</button>
                <?php if ($selectedStudentId): ?>
                  <a href="?type=sf10&sy=<?= urlencode($sy) ?>" class="btn btn-light border" title="Reset Search"><i class="fas fa-redo-alt"></i></a>
                <?php endif; ?>
              </div>
            </div>

            <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between text-muted small">
              <div>
                <i class="fas fa-info-circle text-primary me-1"></i>
                Type any part of the student's <strong>First Name</strong>, <strong>Last Name</strong>, or <strong>LRN</strong> to instantly display their SF10.
              </div>
              <div class="fw-semibold">
                <?= count($allAdvisoryStudents) ?> students in your advisory class
              </div>
            </div>
          </div>
        </form>

        <?php if (!$selectedStudentId): ?>
          <div class="card border-0 shadow-sm text-center py-5 no-print">
            <div class="card-body">
              <div style="width: 70px; height: 70px; background: rgba(52,168,83,0.1); color: #34a853; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem;">
                <i class="fas fa-search"></i>
              </div>
              <h5 class="fw-bold mb-1">Select an Advisory Student to View & Print SF10</h5>
              <p class="text-muted mb-0" style="max-width: 500px; margin: 0 auto;">Choose any student from your advisory class above or use the search filter to display their official School Form 10 (Learner's Permanent Academic Record).</p>
            </div>
          </div>
        <?php elseif (!$sf10Student): ?>
          <div class="alert alert-danger shadow-sm border-0 no-print" style="border-radius: 10px;">
            <i class="fas fa-shield-alt fa-2x float-start me-3 text-danger"></i>
            <div>
              <h5 class="alert-heading fw-bold mb-1">Access Restricted</h5>
              <p class="mb-0">The requested student does not belong to any of your assigned advisory classes. Teachers may only view and print School Form 10 for their own advisory students.</p>
            </div>
          </div>
        <?php else: ?>

          <!-- OFFICIAL SF10 PRINTABLE RECORD -->
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <!-- Official DepEd Header -->
              <div class="report-header text-center mb-4" style="border-bottom:2px solid var(--secondary);padding-bottom:1.25rem;">
                <div class="d-inline-flex align-items-start justify-content-center gap-4">
                  <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="School Logo" class="report-header-logo" style="margin-top: 4px;">
                  <div class="text-center">
                    <div style="font-size:.95rem;color:#222;font-weight:400;margin-bottom:.2rem;">Republic of the Philippines · Department of Education</div>
                    <div style="font-size:1.2rem;color:#000;font-weight:800;margin-bottom:.2rem;"><?= SCHOOL_NAME ?></div>
                    <div style="font-size:.92rem;color:#333;font-weight:400;margin-bottom:1.25rem;"><?= SCHOOL_ADDRESS ?></div>

                    <h3 class="text-center text-uppercase text-dark fw-bold mb-1" style="letter-spacing:0.5px;">SCHOOL FORM 10 (SF10)</h3>
                    <div style="font-size:1rem;color:#111;font-weight:700;" class="text-center text-uppercase">
                      LEARNER'S PERMANENT ACADEMIC RECORD
                    </div>
                    <div style="font-size:.85rem;color:var(--gray-600);" class="text-center mt-1">
                      School Year: <strong><?= htmlspecialchars($sy) ?></strong>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Learner Information Box -->
              <div class="sf10-infobox">
                <div class="row g-2">
                  <div class="col-md-5">
                    <span class="sf10-label">Learner's Full Name</span>
                    <span class="sf10-val text-uppercase"><?= htmlspecialchars($sf10Student['last_name'].', '.$sf10Student['first_name'].' '.($sf10Student['middle_name'] ?? '')) ?></span>
                  </div>
                  <div class="col-md-3">
                    <span class="sf10-label">Learner Reference No. (LRN)</span>
                    <span class="sf10-val" style="font-family:monospace;letter-spacing:1px;"><?= htmlspecialchars($sf10Student['lrn']) ?></span>
                  </div>
                  <div class="col-md-2">
                    <span class="sf10-label">Grade & Section</span>
                    <span class="sf10-val"><?= htmlspecialchars($sf10Student['grade_level'].' - '.$sf10Student['section']) ?></span>
                  </div>
                  <div class="col-md-2">
                    <span class="sf10-label">Sex</span>
                    <span class="sf10-val"><?= htmlspecialchars($sf10Student['sex']) ?></span>
                  </div>

                  <div class="col-md-3 mt-2">
                    <span class="sf10-label">Birthdate</span>
                    <span class="sf10-val"><?= !empty($sf10Student['birthdate']) ? date('F j, Y', strtotime($sf10Student['birthdate'])) : '—' ?></span>
                  </div>
                  <div class="col-md-2 mt-2">
                    <span class="sf10-label">Age</span>
                    <span class="sf10-val"><?= htmlspecialchars($sf10Student['age'] ?? '—') ?></span>
                  </div>
                  <div class="col-md-4 mt-2">
                    <span class="sf10-label">Class Adviser</span>
                    <span class="sf10-val"><?= htmlspecialchars($sf10AdviserName) ?></span>
                  </div>
                  <div class="col-md-3 mt-2">
                    <span class="sf10-label">Status</span>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1"><?= ucfirst(htmlspecialchars($sf10Student['status'] ?? 'active')) ?></span>
                  </div>
                </div>
              </div>

              <!-- Academic Record Table -->
              <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle sf10-table mb-0">
                  <thead>
                    <tr>
                      <th style="min-width: 260px;">Learning Areas / Subjects</th>
                      <th class="text-center" style="width: 85px;">Term 1</th>
                      <th class="text-center" style="width: 85px;">Term 2</th>
                      <th class="text-center" style="width: 85px;">Term 3</th>
                      <th class="text-center" style="width: 105px; background: #e2e8f0 !important;">Final Grade</th>
                      <th class="text-center" style="width: 110px; background: #e2e8f0 !important;">Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($sf10Subjects)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No subjects registered for <?= htmlspecialchars($sf10Student['grade_level']) ?>.</td></tr>
                    <?php else: foreach ($sf10Subjects as $subj):
                      $rec = $sf10Grades[$subj] ?? ['t1'=>null,'t2'=>null,'t3'=>null,'q1'=>null,'q2'=>null,'q3'=>null,'final_grade'=>null,'remarks'=>''];
                      $t1Val = $rec['t1'] ?? $rec['q1'] ?? null;
                      $t2Val = $rec['t2'] ?? $rec['q2'] ?? null;
                      $t3Val = $rec['t3'] ?? $rec['q3'] ?? null;
                      $fg = $rec['final_grade'] !== null ? number_format($rec['final_grade'], 0) : '—';
                      $rem = $rec['remarks'] ?: '—';
                      $remColor = ($rem === 'Passed') ? '#16a34a' : (($rem === 'Failed') ? '#dc2626' : 'inherit');
                    ?>
                    <tr>
                      <td class="fw-semibold text-dark"><?= htmlspecialchars($subj) ?></td>
                      <td class="text-center"><?= $t1Val !== null ? number_format($t1Val,0) : '—' ?></td>
                      <td class="text-center"><?= $t2Val !== null ? number_format($t2Val,0) : '—' ?></td>
                      <td class="text-center"><?= $t3Val !== null ? number_format($t3Val,0) : '—' ?></td>
                      <td class="text-center fw-bold fs-6" style="background:#f8fafc;"><?= $fg ?></td>
                      <td class="text-center fw-bold" style="color:<?= $remColor ?>;"><?= $rem ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                  </tbody>
                  <tfoot>
                    <tr style="background:#f1f5f9; font-size:.92rem;">
                      <th class="fw-bold text-dark text-uppercase">General Average</th>
                      <th colspan="3"></th>
                      <th class="text-center fw-bold fs-6 text-dark"><?= $sf10GeneralAverage !== null ? number_format($sf10GeneralAverage, 2) : '—' ?></th>
                      <th class="text-center fw-bold" style="color:<?= $sf10GeneralRemarks==='Passed'?'#16a34a':($sf10GeneralRemarks==='Failed'?'#dc2626':'inherit') ?>;"><?= $sf10GeneralRemarks ?></th>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Grading Scale DepEd Guide (Standard SF10 Footer) -->
              <div class="row g-2 mb-4 p-2 bg-light rounded border text-muted small" style="font-size: .75rem;">
                <div class="col-md-3"><strong>Descriptors:</strong> Outstanding (90-100)</div>
                <div class="col-md-3">Very Satisfactory (85-89)</div>
                <div class="col-md-3">Satisfactory (80-84)</div>
                <div class="col-md-3">Fairly Satisfactory (75-79) / Did Not Meet (Below 75)</div>
              </div>

              <!-- Signatories -->
              <div class="signatories" id="signatories-block">
                <div class="signatory-block">
                  <div class="sig-label">Prepared by (Class Adviser)</div>
                  <div class="sig-name"><?= htmlspecialchars($user['name']) ?></div>
                  <div class="sig-position">Class Adviser (<?= htmlspecialchars($sf10Student['grade_level'] . ' - ' . $sf10Student['section']) ?>)</div>
                </div>
                <div class="signatory-block">
                  <div class="sig-label">Certified True & Correct (School Head)</div>
                  <div class="sig-name"><?= htmlspecialchars($sigData['noted_by_name'] ?: ' ') ?></div>
                  <div class="sig-position"><?= htmlspecialchars($sigData['noted_by_title']) ?></div>
                </div>
                <div class="signatory-block">
                  <div class="sig-label">Date Issued</div>
                  <div class="sig-name"><?= date('F j, Y') ?></div>
                  <div class="sig-position"><?= date('g:i A') ?></div>
                </div>
              </div>

              <div style="font-size:.72rem;color:var(--gray-400);text-align:right;margin-top:1.5rem;" class="no-print">
                SF10 Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($user['name']) ?> (Class Adviser)
              </div>
            </div>
          </div>
        <?php endif; ?>

      <?php elseif ($reportType === 'sf9'): ?>
        <!-- Teacher SF9 Learner Progress Report Card Mode -->
        <div class="card mb-3 no-print border-0 shadow-sm">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <div class="col-md-5">
                <label class="form-label mb-1 fw-semibold"><i class="fas fa-user-graduate text-success me-1"></i>Select Advisory Learner <span class="text-danger">*</span></label>
                <select id="teacher-sf9-student" class="form-select form-select-sm" onchange="loadTeacherSf9Report()">
                  <option value="">— Choose an advisory learner —</option>
                  <?php foreach ($allAdvisoryStudents as $stu): ?>
                  <option value="<?= $stu['id'] ?>" <?= $selectedStudentId === (int)$stu['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($stu['last_name'].', '.$stu['first_name'].' '.($stu['middle_name']??'')) ?> (<?= $stu['grade_level'] ?> - <?= $stu['section'] ?>)
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Grading Term</label>
                <select id="teacher-sf9-period" class="form-select form-select-sm" onchange="loadTeacherSf9Report()">
                  <option value="0">All Terms</option>
                  <option value="1">1st Term</option>
                  <option value="2">2nd Term</option>
                  <option value="3">3rd Term</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label mb-1 fw-semibold">School Year</label>
                <select id="teacher-sf9-sy" class="form-select form-select-sm" onchange="loadTeacherSf9Report()">
                  <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
                  <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['is_active'] || $syItem['year_label'] === $sy) ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100" onclick="loadTeacherSf9Report()"><i class="fas fa-sync me-1"></i>Load SF9</button>
              </div>
            </div>

            <!-- Signatories Live-Sync Bar -->
            <div class="row g-2 align-items-end mt-2 pt-2 border-top">
              <div class="col-md-6">
                <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600" for="teacherSf9AdviserInput">
                  <i class="fas fa-chalkboard-teacher me-1 text-success"></i>Class Adviser (Prepared by)
                </label>
                <input type="text" id="teacherSf9AdviserInput" class="form-control form-control-sm" value="<?= htmlspecialchars($user['name']) ?>" oninput="updateTeacherSf9SignatoriesLive()">
              </div>
              <div class="col-md-6">
                <label class="form-label mb-1" style="font-size:0.8rem;font-weight:600" for="teacherSf9PrincipalInput">
                  <i class="fas fa-user-tie me-1 text-primary"></i>School Head / Principal (Approved by)
                </label>
                <input type="text" id="teacherSf9PrincipalInput" class="form-control form-control-sm" placeholder="School Head / Principal Name" oninput="updateTeacherSf9SignatoriesLive()">
              </div>
            </div>
          </div>
        </div>

        <div id="teacher-sf9-report-content">
          <div class="card p-5 text-center text-muted no-print">
            <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
            <h5>Select an Advisory Learner to Preview SF9</h5>
            <p class="small mb-0">Choose a student above to view and print their official DepEd Form 9 Progress Report Card.</p>
          </div>
        </div>

      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script src="<?= BASE_URL ?>/assets/js/sf9-renderer.js"></script>
<script>
showDesktopOnlyWarning();

// Advisory student dataset for instant realtime search
const ADVISORY_STUDENTS = <?= json_encode(array_map(function($st) {
    return [
        'id'          => (int)$st['id'],
        'lrn'         => (string)$st['lrn'],
        'first_name'  => (string)$st['first_name'],
        'middle_name' => (string)($st['middle_name'] ?? ''),
        'last_name'   => (string)$st['last_name'],
        'full_name'   => trim($st['last_name'] . ', ' . $st['first_name'] . ' ' . ($st['middle_name'] ?? '')),
        'grade_level' => (string)$st['grade_level'],
        'section'     => (string)$st['section'],
        'sex'         => (string)($st['sex'] ?? '')
    ];
}, $allAdvisoryStudents)) ?>;

let activeFocusIndex = -1;

function onRealtimeSearchFocus() {
  const input = document.getElementById('realtime-search-input');
  if (input) {
    onRealtimeSearchInput(input.value);
  }
}

function onRealtimeSearchInput(query) {
  const panel = document.getElementById('realtime-results-panel');
  const clearBtn = document.getElementById('clear-search-btn');
  if (!panel) return;

  const q = (query || '').trim().toLowerCase();
  
  if (clearBtn) {
    clearBtn.style.display = q ? 'block' : 'none';
  }

  activeFocusIndex = -1;

  if (!q) {
    // If input is empty, show all advisory students (sorted)
    renderResults(ADVISORY_STUDENTS);
    panel.style.display = ADVISORY_STUDENTS.length ? 'block' : 'none';
    return;
  }

  // Real-time filter against first name, last name, middle name, full name, or LRN
  const matches = ADVISORY_STUDENTS.filter(s => {
    return s.full_name.toLowerCase().includes(q) ||
           s.first_name.toLowerCase().includes(q) ||
           s.last_name.toLowerCase().includes(q) ||
           s.lrn.toLowerCase().includes(q) ||
           `${s.grade_level} ${s.section}`.toLowerCase().includes(q);
  });

  renderResults(matches, q);
  panel.style.display = 'block';
}

function renderResults(list, query = '') {
  const panel = document.getElementById('realtime-results-panel');
  if (!panel) return;

  if (!list.length) {
    panel.innerHTML = `
      <div class="realtime-empty">
        <i class="fas fa-search me-1 text-muted"></i> No matching students found in your advisory classes.
      </div>`;
    return;
  }

  const highlight = (text, needle) => {
    if (!needle) return escapeHtml(text);
    const escapedNeedle = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp('(' + escapedNeedle + ')', 'gi');
    return escapeHtml(text).replace(regex, '<mark class="p-0 bg-warning text-dark">$1</mark>');
  };

  panel.innerHTML = list.map((s, idx) => {
    const initial = s.first_name ? s.first_name.charAt(0).toUpperCase() : 'S';
    return `
      <div class="realtime-item" data-id="${s.id}" data-idx="${idx}" onclick="selectStudentForSF10(${s.id})">
        <div class="d-flex align-items-center" style="min-width:0;">
          <div class="item-avatar">${initial}</div>
          <div class="item-info">
            <div class="item-title">${highlight(s.full_name, query)}</div>
            <div class="item-subtitle">
              LRN: <span style="font-family:monospace;font-weight:600;">${highlight(s.lrn, query)}</span> · ${escapeHtml(s.grade_level)} — ${escapeHtml(s.section)}
            </div>
          </div>
        </div>
        <div class="item-badge ms-2">
          ${escapeHtml(s.grade_level)} · ${escapeHtml(s.section)}
        </div>
      </div>
    `;
  }).join('');
}

function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[m]);
}

function selectStudentForSF10(studentId) {
  const stu = ADVISORY_STUDENTS.find(s => s.id === studentId);
  if (!stu) return;

  const input = document.getElementById('realtime-search-input');
  const idInput = document.getElementById('sf10-student-id-input');
  const panel = document.getElementById('realtime-results-panel');
  const sySelect = document.getElementById('sf10-sy-select');

  if (input) {
    input.value = `${stu.last_name}, ${stu.first_name} ${stu.middle_name ? stu.middle_name + ' ' : ''}(${stu.grade_level} - ${stu.section})`;
  }
  if (idInput) {
    idInput.value = stu.id;
  }
  if (panel) {
    panel.style.display = 'none';
  }

  // Submit form to render official DepEd SF10
  const sy = sySelect ? sySelect.value : '';
  window.location.href = `?type=sf10&student_id=${stu.id}&sy=${encodeURIComponent(sy)}`;
}

function clearRealtimeSearch() {
  const input = document.getElementById('realtime-search-input');
  const idInput = document.getElementById('sf10-student-id-input');
  const panel = document.getElementById('realtime-results-panel');
  const clearBtn = document.getElementById('clear-search-btn');

  if (input) {
    input.value = '';
    input.focus();
  }
  if (idInput) {
    idInput.value = '';
  }
  if (clearBtn) {
    clearBtn.style.display = 'none';
  }
  if (panel) {
    renderResults(ADVISORY_STUDENTS);
    panel.style.display = 'block';
  }
}

function onSchoolYearChange() {
  const idInput = document.getElementById('sf10-student-id-input');
  const sySelect = document.getElementById('sf10-sy-select');
  const studentId = idInput ? idInput.value : '';
  const sy = sySelect ? sySelect.value : '';

  if (studentId) {
    window.location.href = `?type=sf10&student_id=${studentId}&sy=${encodeURIComponent(sy)}`;
  } else {
    window.location.href = `?type=sf10&sy=${encodeURIComponent(sy)}`;
  }
}

function onRealtimeSearchKeydown(e) {
  const panel = document.getElementById('realtime-results-panel');
  if (!panel || panel.style.display === 'none') return;

  const items = panel.querySelectorAll('.realtime-item');
  if (!items.length) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    activeFocusIndex = (activeFocusIndex + 1) % items.length;
    updateActiveItem(items);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    activeFocusIndex = (activeFocusIndex - 1 + items.length) % items.length;
    updateActiveItem(items);
  } else if (e.key === 'Enter') {
    e.preventDefault();
    if (activeFocusIndex >= 0 && items[activeFocusIndex]) {
      items[activeFocusIndex].click();
    } else if (items.length > 0) {
      items[0].click();
    }
  } else if (e.key === 'Escape') {
    panel.style.display = 'none';
  }
}

function updateActiveItem(items) {
  items.forEach((item, idx) => {
    if (idx === activeFocusIndex) {
      item.classList.add('active-item');
      item.scrollIntoView({ block: 'nearest' });
    } else {
      item.classList.remove('active-item');
    }
  });
}

// Close suggestion panel when clicking outside
document.addEventListener('click', function(e) {
  const wrap = document.querySelector('.realtime-search-wrap');
  const panel = document.getElementById('realtime-results-panel');
  if (panel && wrap && !wrap.contains(e.target)) {
    panel.style.display = 'none';
  }
});

// Teacher SF9 Report Card Controller
function updateTeacherSf9SignatoriesLive() {
  const advVal = document.getElementById('teacherSf9AdviserInput')?.value || '';
  const prinVal = document.getElementById('teacherSf9PrincipalInput')?.value || '';
  const advEl = document.getElementById('sf9AdviserName');
  const prinEl = document.getElementById('sf9SchoolHeadName');
  if (advEl) advEl.textContent = advVal || '—';
  if (prinEl) prinEl.textContent = prinVal || '—';

  const stuId = document.getElementById('teacher-sf9-student')?.value;
  if (stuId) {
    if (advVal) localStorage.setItem('spsmis_sf9_adv_' + stuId, advVal);
    if (prinVal) localStorage.setItem('spsmis_sf9_prin_' + stuId, prinVal);
  }
}

async function loadTeacherSf9Report() {
  const stuId = document.getElementById('teacher-sf9-student')?.value;
  const sy = document.getElementById('teacher-sf9-sy')?.value || '<?= $sy ?>';
  const period = parseInt(document.getElementById('teacher-sf9-period')?.value || '0');
  const container = document.getElementById('teacher-sf9-report-content');

  if (!stuId) {
    container.innerHTML = `
      <div class="card p-5 text-center text-muted no-print">
        <i class="fas fa-file-invoice fa-3x mb-3 text-secondary opacity-50"></i>
        <h5>Please Select an Advisory Learner</h5>
        <p class="small mb-0">Choose a student above to generate their SF9 Learner's Progress Report Card.</p>
      </div>`;
    return;
  }

  showLoading('Generating SF9 Report Card...', 'Loading advisory learner grades & Form 9 layout...');
  try {
    const res = await fetch(`${BASE}/api/grades/student.php?student_id=${stuId}&school_year=${sy}`);
    const data = await res.json();
    hideLoading();

    if (!data.ok) {
      showToast(data.message || 'Failed to load student record', 'error');
      return;
    }

    const savedAdv = localStorage.getItem('spsmis_sf9_adv_' + stuId) || <?= json_encode($user['name']) ?>;
    const savedPrin = localStorage.getItem('spsmis_sf9_prin_' + stuId) || 'School Principal / Head';

    const advInput = document.getElementById('teacherSf9AdviserInput');
    const prinInput = document.getElementById('teacherSf9PrincipalInput');
    if (advInput) advInput.value = savedAdv;
    if (prinInput) prinInput.value = savedPrin;

    container.innerHTML = renderSf9ReportCard(data, {
      period: period,
      adviser: savedAdv,
      schoolHead: savedPrin,
      baseUrl: BASE
    });

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
}

<?php if ($reportType === 'sf9' && $selectedStudentId > 0): ?>
window.addEventListener('DOMContentLoaded', () => {
  loadTeacherSf9Report();
});
<?php endif; ?>
</script>
</body>
</html>


