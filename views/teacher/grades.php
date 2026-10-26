<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'grades';

// Fetch fresh teacher profile details to ensure up-to-date advisory assignment
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$freshUser = $uStmt->fetch() ?: $user;

$myClasses = getTeacherAdvisoryClasses($pdo, $freshUser['id']);
$hasAdvisory = !empty($myClasses);

$classSubjects = [];
foreach ($myClasses as $cls) {
    if (!isset($classSubjects[$cls['grade_level']])) {
        $classSubjects[$cls['grade_level']] = getSubjectsForGrade($cls['grade_level'], $pdo);
    }
}

$studentList = [];
if ($hasAdvisory) {
    $advisoryClauses = [];
    $advisoryParams  = [];
    foreach ($myClasses as $cls) {
        $advisoryClauses[] = "(grade_level = ? AND section = ?)";
        $advisoryParams[]  = $cls['grade_level'];
        $advisoryParams[]  = $cls['section'];
    }
    $sql = "SELECT id, last_name, first_name, middle_name, lrn, sex, grade_level, section 
            FROM students 
            WHERE status = 'active' AND (" . implode(' OR ', $advisoryClauses) . ") 
            ORDER BY grade_level, section, last_name, first_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($advisoryParams);
    $studentList = $stmt->fetchAll();
}
$preselectedStudentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Grade Management — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    .nav-tabs .nav-link { font-weight:600; color:var(--gray-600); border:none; border-bottom:3px solid transparent; padding:.75rem 1.25rem; border-radius:0; }
    .nav-tabs .nav-link:hover { color:var(--primary); }
    .nav-tabs .nav-link.active { color:var(--primary); background:transparent; border-bottom:3px solid var(--primary); }
    .subject-pill-btn { border:1px solid #c7d2fe; background:#f8fafc; color:#1e3a8a; border-radius:20px; padding:6px 16px; font-weight:500; font-size:.85rem; transition:all .2s ease; cursor:pointer; }
    .subject-pill-btn:hover { background:#e0e7ff; color:#1e40af; border-color:#93c5fd; }
    .subject-pill-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); font-weight:700; box-shadow:0 2px 4px rgba(26,115,232,.3); }
    .sf10-header { background:#f0f4ff; border:1px solid #c7d2fe; border-radius:8px; padding:1rem; margin-bottom:1rem; }
    .sf10-table th, .subject-table th { background:#e0e7ff; font-size:.78rem; text-align:center; vertical-align:middle; }
    .sf10-table td, .subject-table td { text-align:center; font-size:.85rem; vertical-align:middle; }
    .sf10-table td:first-child, .subject-table td.student-name-cell { text-align:left; }
    .grade-input { width:65px; text-align:center; border:1px solid #d1d5db; border-radius:4px; padding:4px 6px; font-size:.88rem; font-weight:600; }
    .grade-input:focus { outline:none; border-color:var(--secondary); box-shadow:0 0 0 2px rgba(16,185,129,.2); }
    .grade-input.is-modified { background-color:#fef3c7; border-color:#f59e0b; }
    .remarks-passed { color:var(--secondary); font-weight:700; }
    .remarks-failed { color:var(--danger); font-weight:700; }
    .sticky-actions-bar { position:sticky; top:70px; z-index:10; background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Grade Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Grades</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Teacher</div></div></div></div>
    </nav>

    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h3>Grade Management</h3>
        <p>Input academic term grades (Term 1 – Term 3) by subject section roster (A–Z) or individual SF10 record</p>
      </div>
      <ul class="nav nav-tabs border-bottom-0" id="grade-mode-tabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $preselectedStudentId ? '' : 'active' ?>" id="mode-subject-tab" data-bs-toggle="tab" data-bs-target="#mode-subject-pane" type="button" role="tab">
            <i class="fas fa-list-ol me-2"></i>Class Grading by Subject (A–Z)
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $preselectedStudentId ? 'active' : '' ?>" id="mode-student-tab" data-bs-toggle="tab" data-bs-target="#mode-student-pane" type="button" role="tab">
            <i class="fas fa-id-card me-2"></i>Individual Student Record (SF10)
          </button>
        </li>
      </ul>
    </div>

    <?php if (!$hasAdvisory): ?>
    <div class="card p-5 text-center text-muted" id="no-advisory-msg">
      <i class="fas fa-chalkboard-teacher fa-3x mb-3 text-secondary opacity-50"></i>
      <h5>No Advisory Class Assigned</h5>
      <p class="small mb-0">You currently do not have an advisory class assigned. Please contact the administrator.</p>
    </div>
    <?php else: ?>

    <div class="tab-content" id="gradeTabsContent">

      <!-- ========================================== -->
      <!-- TAB 1: CLASS GRADING BY SUBJECT (A - Z)    -->
      <!-- ========================================== -->
      <div class="tab-pane fade <?= $preselectedStudentId ? '' : 'show active' ?>" id="mode-subject-pane" role="tabpanel">

        <!-- Controls: Class, School Year, Actions -->
        <div class="card mb-3">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label fw-bold">Select Section / Class</label>
                <select id="subj-class-select" class="form-select" onchange="onSubjectClassChange()">
                  <?php foreach ($myClasses as $idx => $cls): ?>
                  <option value="<?= htmlspecialchars($cls['grade_level'].'|'.$cls['section']) ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cls['grade_level'].' — '.$cls['section']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold">School Year</label>
                <select id="subj-sy-select" class="form-select" onchange="loadSubjectGrades()">
                  <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
                  <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['is_active'] || $syItem['year_label'] === SCHOOL_YEAR) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label text-muted small">Filter Students</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                  <input type="text" id="subj-search-input" class="form-control" placeholder="Search learner name..." onkeyup="filterSubjectRoster()">
                </div>
              </div>

              <div class="col-md-2">
                <button class="btn btn-primary w-100 fw-bold py-2" id="btn-save-subject-grades" onclick="saveSubjectGrades()">
                  <i class="fas fa-save me-1"></i>Save Grades
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Subject Selector Pills -->
        <div class="card mb-3 border-0 shadow-sm" style="background:#f8fafc;">
          <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <div class="fw-bold text-dark small">
                <i class="fas fa-book-reader text-primary me-1"></i>CLICK A SUBJECT TO INPUT OR EDIT GRADES:
              </div>
              <span id="subj-active-badge" class="badge bg-primary px-3 py-2" style="font-size:.85rem;">
                Subject: Filipino
              </span>
            </div>
            <div id="subject-pills-container" class="d-flex flex-wrap gap-2">
              <!-- Dynamically rendered subject pills -->
            </div>
          </div>
        </div>

        <!-- Kindergarten Performance Standards Bar (Shown for Kindergarten) -->
        <div id="subj-kinder-legend" class="alert alert-info py-2 px-3 mb-3 d-none" style="font-size:.84rem;">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <strong><i class="fas fa-shapes me-1"></i>Kindergarten Standards:</strong>
              <span class="badge bg-success ms-1">C (85-100) Consistent</span>
              <span class="badge bg-primary ms-1">D (75-84) Developing</span>
              <span class="badge bg-warning text-dark ms-1">B (&lt;75) Beginning</span>
            </div>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-success btn-sm py-0" onclick="quickFillSubjectRoster(88)">Set Blank to Consistent (88)</button>
              <button type="button" class="btn btn-outline-primary btn-sm py-0" onclick="quickFillSubjectRoster(80)">Set Blank to Developing (80)</button>
            </div>
          </div>
        </div>

        <!-- Subject Roster Card (Sorted A - Z) -->
        <div class="card shadow-sm">
          <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <h5 class="mb-0 fw-bold text-primary" id="roster-card-title">
                <i class="fas fa-users me-2"></i>Class Grade Sheet — Filipino
              </h5>
              <div class="text-muted small" id="roster-card-subtitle">
                Section Roster sorted alphabetically (A to Z) &bull; DepEd SF10 Term Grading (T1 – T3)
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-light text-dark border px-2 py-1" id="roster-count-badge">0 Students</span>
              <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" id="roster-status-badge">
                <i class="fas fa-check me-1"></i>Up to date
              </span>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table subject-table table-bordered table-hover mb-0" id="subject-grades-table">
              <thead>
                <tr>
                  <th style="width:45px;">#</th>
                  <th style="width:130px;">LRN</th>
                  <th style="text-align:left;min-width:240px;">Learner's Name (Last, First Middle)</th>
                  <th style="width:65px;">Sex</th>
                  <th style="width:95px;">Term 1</th>
                  <th style="width:95px;">Term 2</th>
                  <th style="width:95px;">Term 3</th>
                  <th style="width:110px;background:#d1fae5;">Final Grade</th>
                  <th style="width:110px;background:#d1fae5;">Remarks</th>
                  <th style="width:70px;">Status</th>
                </tr>
              </thead>
              <tbody id="subject-grades-tbody">
                <tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Loading class grades...</td></tr>
              </tbody>
              <tfoot>
                <tr style="background:#f8fafc;" class="fw-bold">
                  <td colspan="4" class="text-start ps-3">
                    Class Summary &bull; <span id="summary-students-count">0</span> learners
                  </td>
                  <td id="avg-t1" class="text-center">—</td>
                  <td id="avg-t2" class="text-center">—</td>
                  <td id="avg-t3" class="text-center">—</td>
                  <td id="summary-general-avg" class="text-center text-success" style="background:#ecfdf5;font-size:1rem;">—</td>
                  <td id="summary-pass-rate" class="text-center" style="background:#ecfdf5;">—</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div class="card-footer bg-light d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
            <small class="text-muted">
              <i class="fas fa-keyboard me-1"></i>Tip: Use <kbd>Enter</kbd> or <kbd>&darr;</kbd> / <kbd>&uarr;</kbd> arrow keys to quickly navigate between student inputs.
            </small>
            <button class="btn btn-sm btn-primary fw-bold px-3" onclick="saveSubjectGrades()">
              <i class="fas fa-save me-1"></i>Save All Changes
            </button>
          </div>
        </div>

      </div>

      <!-- ========================================== -->
      <!-- TAB 2: INDIVIDUAL STUDENT RECORD (SF10)   -->
      <!-- ========================================== -->
      <div class="tab-pane fade <?= $preselectedStudentId ? 'show active' : '' ?>" id="mode-student-pane" role="tabpanel">

        <!-- Student & Class Selector -->
        <div class="card mb-3">
          <div class="card-body">
            <div class="row g-2 align-items-end">
              <?php if (count($myClasses) > 1): ?>
              <div class="col-md-3">
                <label class="form-label fw-bold">Filter by Class</label>
                <select id="class-filter" class="form-select" onchange="filterStudentDropdown()">
                  <option value="">All My Classes</option>
                  <?php foreach ($myClasses as $idx => $cls): ?>
                  <option value="<?= htmlspecialchars($cls['grade_level'].'|'.$cls['section']) ?>"><?= htmlspecialchars($cls['grade_level'].' — '.$cls['section']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
              <?php else: ?>
              <div class="col-md-6">
              <?php endif; ?>
                <label class="form-label fw-bold">Select Student</label>
                <select id="student-select" class="form-select" onchange="loadGrades()" <?= empty($studentList) ? 'disabled' : '' ?>>
                  <?php if (empty($studentList)): ?>
                  <option value="">— No enrolled students in your advisory class —</option>
                  <?php else: ?>
                  <option value="">— Choose a student (<?= count($studentList) ?> available) —</option>
                  <?php foreach ($studentList as $s): ?>
                  <option value="<?= $s['id'] ?>" data-class="<?= htmlspecialchars($s['grade_level'].'|'.$s['section']) ?>" <?= $preselectedStudentId === (int)$s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?> (<?= $s['grade_level'] ?> - <?= $s['section'] ?>)
                  </option>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">School Year</label>
                <select id="sy-select" class="form-select" onchange="loadGrades()">
                  <?php foreach (getSchoolYearsList($pdo) as $syItem): ?>
                  <option value="<?= htmlspecialchars($syItem['year_label']) ?>" <?= ($syItem['is_active'] || $syItem['year_label'] === SCHOOL_YEAR) ? 'selected' : '' ?>><?= htmlspecialchars($syItem['year_label']) ?><?= $syItem['is_active'] ? ' (Active)' : '' ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <button class="btn w-100" style="background:var(--secondary);color:#fff;" onclick="saveAllGrades()" <?= empty($studentList) ? 'disabled' : '' ?>><i class="fas fa-save me-2"></i>Save All Grades</button>
              </div>
            </div>
          </div>
        </div>

        <!-- SF10 Card -->
        <div id="sf10-container" style="display:none;">
          <div class="card">
            <div class="card-body">
              <!-- Republic of Philippines Header -->
              <div style="text-align:center;margin-bottom:1rem;border-bottom:2px solid #1a73e8;padding-bottom:.75rem;">
                <div style="font-size:.7rem;color:var(--gray-600);">Republic of the Philippines · Department of Education</div>
                <div style="font-weight:700;font-size:1rem;color:var(--primary);" id="sf10-doc-title">SCHOOL FORM 10 (SF10) — LEARNER's PERMANENT ACADEMIC RECORD</div>
                <div style="font-size:.8rem;color:var(--gray-600);"><?= SCHOOL_NAME ?></div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                  <span class="text-muted small"><i class="fas fa-id-card me-1"></i>Learner Academic Record</span>
                  <span id="kinder-badge" class="badge bg-info text-dark ms-2 d-none"><i class="fas fa-shapes me-1"></i>Kindergarten / K-3 Grade Sheet</span>
                </div>
                <a id="btn-print-sf9" href="#" target="_blank" class="btn btn-sm btn-outline-success fw-bold">
                  <i class="fas fa-print me-1"></i>Print SF9 Report Card
                </a>
              </div>

              <div id="kinder-legend-box" class="alert alert-info py-2 px-3 mb-3 d-none" style="font-size:.82rem;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div>
                    <strong><i class="fas fa-info-circle me-1"></i>Kindergarten (K-3) Performance Standards:</strong>
                    <span class="ms-2 badge bg-success">C (85-100) Consistent</span>
                    <span class="ms-1 badge bg-primary">D (75-84) Developing</span>
                    <span class="ms-1 badge bg-warning text-dark">B (&lt;75) Beginning</span>
                  </div>
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-success btn-sm py-0" onclick="quickFillKinder(88)">Set All Consistent (88)</button>
                    <button type="button" class="btn btn-outline-primary btn-sm py-0" onclick="quickFillKinder(80)">Set All Developing (80)</button>
                  </div>
                </div>
              </div>

              <div class="sf10-header row g-2 mb-3">
                <div class="col-md-4"><span style="font-size:.75rem;color:var(--gray-600);">LEARNER'S NAME</span><div class="fw-bold" id="sf10-name">—</div></div>
                <div class="col-md-3"><span style="font-size:.75rem;color:var(--gray-600);">LRN</span><div class="fw-bold" style="font-family:monospace;" id="sf10-lrn">—</div></div>
                <div class="col-md-2"><span style="font-size:.75rem;color:var(--gray-600);">GRADE LEVEL</span><div class="fw-bold" id="sf10-grade">—</div></div>
                <div class="col-md-2"><span style="font-size:.75rem;color:var(--gray-600);">SECTION</span><div class="fw-bold" id="sf10-section">—</div></div>
                <div class="col-md-1"><span style="font-size:.75rem;color:var(--gray-600);">S.Y.</span><div class="fw-bold" id="sf10-sy">—</div></div>
              </div>

              <div class="table-responsive">
                <table class="table sf10-table table-bordered mb-0" id="grades-table">
                  <thead>
                    <tr>
                      <th style="text-align:left;min-width:200px;">Learning Area / Subject</th>
                      <th class="text-center" style="width:110px;">Term 1</th>
                      <th class="text-center" style="width:110px;">Term 2</th>
                      <th class="text-center" style="width:110px;">Term 3</th>
                      <th class="text-center" style="background:#d1fae5;width:120px;">Final Grade</th>
                      <th class="text-center" style="background:#d1fae5;width:110px;">Remarks</th>
                    </tr>
                  </thead>
                  <tbody id="grades-tbody">
                    <tr><td colspan="6" class="text-center text-muted py-4">Select a student above to load grades.</td></tr>
                  </tbody>
                  <tfoot>
                    <tr style="background:#f0fdf4;">
                      <td class="fw-bold">General Average</td>
                      <td colspan="3"></td>
                      <td class="fw-bold text-center" id="general-average">—</td>
                      <td class="fw-bold text-center" id="general-remarks">—</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div id="no-student-msg" class="text-center text-muted py-5">
          <i class="fas fa-clipboard-list fa-3x mb-3" style="color:var(--gray-300);"></i>
          <p>Select a student from the dropdown above to view their SF10 grade card.</p>
        </div>

      </div>

    </div>
    <?php endif; ?>

  </div>
</div>

<script src="<?= BASE_URL ?>/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
const CLASS_SUBJECTS = <?= json_encode($classSubjects) ?>;
showDesktopOnlyWarning();

// State for Subject-Based Roster Grading
let currentSubject = '';
let currentSubjectRoster = [];
let hasUnsavedSubjectChanges = false;

// State for Individual SF10 Grading
let currentStudent = null;
let currentGrades  = {};

/* ==============================================================
   1. SUBJECT-BASED CLASS GRADING ENGINE (A - Z ROSTER)
   ============================================================== */

function getActiveSubjectClass() {
  const sel = document.getElementById('subj-class-select');
  if (!sel || !sel.value) return { grade_level: '', section: '' };
  const parts = sel.value.split('|');
  return { grade_level: parts[0] || '', section: parts[1] || '' };
}

function renderSubjectPills(subjects) {
  const container = document.getElementById('subject-pills-container');
  if (!container) return;
  container.innerHTML = '';

  if (!subjects || !subjects.length) {
    container.innerHTML = '<span class="text-muted small">No subjects configured for this grade level.</span>';
    return;
  }

  if (!currentSubject || !subjects.includes(currentSubject)) {
    currentSubject = subjects[0];
  }

  subjects.forEach(subj => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'subject-pill-btn' + (subj === currentSubject ? ' active' : '');
    btn.innerHTML = `<i class="fas fa-book me-1"></i>${subj}`;
    btn.onclick = () => {
      if (subj === currentSubject) return;
      if (hasUnsavedSubjectChanges) {
        if (!confirm('You have unsaved changes in ' + currentSubject + '. Switch anyway?')) return;
      }
      currentSubject = subj;
      updateActiveSubjectPill();
      loadSubjectGrades();
    };
    container.appendChild(btn);
  });

  updateActiveSubjectPill();
}

function updateActiveSubjectPill() {
  document.querySelectorAll('.subject-pill-btn').forEach(btn => {
    if (btn.textContent.trim() === currentSubject || btn.innerText.includes(currentSubject)) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });

  const badge = document.getElementById('subj-active-badge');
  if (badge) badge.textContent = `Subject: ${currentSubject}`;

  const title = document.getElementById('roster-card-title');
  if (title) title.innerHTML = `<i class="fas fa-users me-2"></i>Class Grade Sheet — <span class="text-primary">${currentSubject}</span>`;
}

function onSubjectClassChange() {
  const { grade_level } = getActiveSubjectClass();
  const subjects = CLASS_SUBJECTS[grade_level] || [];
  renderSubjectPills(subjects);

  const kinderLegend = document.getElementById('subj-kinder-legend');
  if (kinderLegend) {
    if (/kinder/i.test(grade_level)) {
      kinderLegend.classList.remove('d-none');
    } else {
      kinderLegend.classList.add('d-none');
    }
  }

  loadSubjectGrades();
}

async function loadSubjectGrades() {
  const { grade_level, section } = getActiveSubjectClass();
  const sy = document.getElementById('subj-sy-select') ? document.getElementById('subj-sy-select').value : '';
  const tbody = document.getElementById('subject-grades-tbody');
  if (!grade_level || !section || !currentSubject) return;

  tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin me-2"></i>Loading student roster (A–Z) for ${currentSubject}...</td></tr>`;
  hasUnsavedSubjectChanges = false;
  updateSubjectStatusBadge(false);

  try {
    const url = `${BASE}/api/grades/index.php?school_year=${encodeURIComponent(sy)}&grade_level=${encodeURIComponent(grade_level)}&section=${encodeURIComponent(section)}&subject=${encodeURIComponent(currentSubject)}`;
    const res = await fetch(url);
    const data = await res.json();

    if (!data.ok) {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">${data.message || 'Error loading grades.'}</td></tr>`;
      return;
    }

    if (data.subjects && data.subjects.length && (!CLASS_SUBJECTS[grade_level] || !CLASS_SUBJECTS[grade_level].length)) {
      CLASS_SUBJECTS[grade_level] = data.subjects;
      renderSubjectPills(data.subjects);
    }

    currentSubjectRoster = data.students || [];
    renderSubjectRosterTable(currentSubjectRoster);
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">Failed to fetch grades. Check your network connection.</td></tr>`;
  }
}

function renderSubjectRosterTable(students) {
  const tbody = document.getElementById('subject-grades-tbody');
  const countBadge = document.getElementById('roster-count-badge');
  const summaryCount = document.getElementById('summary-students-count');
  if (countBadge) countBadge.textContent = `${students.length} Students`;
  if (summaryCount) summaryCount.textContent = students.length;

  if (!students.length) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-5"><i class="fas fa-user-slash fa-2x mb-2 text-secondary opacity-50"></i><br>No active students enrolled in this section.</td></tr>`;
    recalculateSubjectSummary();
    return;
  }

  let html = '';
  students.forEach((stu, idx) => {
    const t1 = stu.t1 !== null && stu.t1 !== undefined ? stu.t1 : '';
    const t2 = stu.t2 !== null && stu.t2 !== undefined ? stu.t2 : '';
    const t3 = stu.t3 !== null && stu.t3 !== undefined ? stu.t3 : '';
    const final = stu.final_grade !== null && stu.final_grade !== undefined ? stu.final_grade : '';
    const remarks = stu.remarks || '';
    const sexBadge = stu.sex === 'Female' 
      ? '<span class="badge" style="background:#fce7f3;color:#be185d;">F</span>' 
      : '<span class="badge" style="background:#e0f2fe;color:#0369a1;">M</span>';

    const middle = stu.middle_name ? ` ${stu.middle_name}` : '';
    const fullName = `${stu.last_name}, ${stu.first_name}${middle}`;

    const rmClass = remarks === 'Passed' ? 'remarks-passed' : (remarks === 'Failed' ? 'remarks-failed' : '');

    html += `
      <tr class="roster-row" data-student-id="${stu.student_id}" data-name="${fullName.toLowerCase()}" data-lrn="${stu.lrn}">
        <td class="text-muted small">${idx + 1}</td>
        <td class="font-monospace small">${stu.lrn}</td>
        <td class="student-name-cell fw-bold">
          ${fullName}
        </td>
        <td>${sexBadge}</td>
        <td>
          <input type="number" class="grade-input subj-term-input" data-col="t1" min="0" max="100" step="any"
                 value="${t1}" oninput="recomputeSubjectRow(this)" onkeydown="navigateSubjectInputs(event, this)">
        </td>
        <td>
          <input type="number" class="grade-input subj-term-input" data-col="t2" min="0" max="100" step="any"
                 value="${t2}" oninput="recomputeSubjectRow(this)" onkeydown="navigateSubjectInputs(event, this)">
        </td>
        <td>
          <input type="number" class="grade-input subj-term-input" data-col="t3" min="0" max="100" step="any"
                 value="${t3}" oninput="recomputeSubjectRow(this)" onkeydown="navigateSubjectInputs(event, this)">
        </td>
        <td class="fw-bold final-cell text-center" style="background:#f0fdf4;">${final !== '' ? Number(final).toFixed(2) : '—'}</td>
        <td class="${rmClass} remarks-cell text-center" style="background:#f0fdf4;">${remarks || '—'}</td>
        <td class="status-cell text-muted small"><i class="fas fa-check-circle text-muted"></i></td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
  recalculateSubjectSummary();
}

function recomputeSubjectRow(input) {
  input.classList.add('is-modified');
  const row = input.closest('tr');
  const t1Input = row.querySelector('.subj-term-input[data-col="t1"]');
  const t2Input = row.querySelector('.subj-term-input[data-col="t2"]');
  const t3Input = row.querySelector('.subj-term-input[data-col="t3"]');
  const finalCell = row.querySelector('.final-cell');
  const remarksCell = row.querySelector('.remarks-cell');
  const statusCell = row.querySelector('.status-cell');

  const v1 = t1Input.value !== '' ? parseFloat(t1Input.value) : null;
  const v2 = t2Input.value !== '' ? parseFloat(t2Input.value) : null;
  const v3 = t3Input.value !== '' ? parseFloat(t3Input.value) : null;

  if (v1 !== null && v2 !== null && v3 !== null) {
    const finalVal = Math.round(((v1 + v2 + v3) / 3) * 100) / 100;
    const isPassed = finalVal >= 75;
    finalCell.textContent = finalVal.toFixed(2);
    remarksCell.textContent = isPassed ? 'Passed' : 'Failed';
    remarksCell.className = 'remarks-cell text-center ' + (isPassed ? 'remarks-passed' : 'remarks-failed');
  } else {
    finalCell.textContent = '—';
    remarksCell.textContent = '—';
    remarksCell.className = 'remarks-cell text-center';
  }

  statusCell.innerHTML = '<span class="badge bg-warning text-dark">Edited</span>';
  hasUnsavedSubjectChanges = true;
  updateSubjectStatusBadge(true);
  recalculateSubjectSummary();
}

function recalculateSubjectSummary() {
  const rows = document.querySelectorAll('#subject-grades-tbody tr.roster-row');
  let t1Sum = 0, t1Count = 0;
  let t2Sum = 0, t2Count = 0;
  let t3Sum = 0, t3Count = 0;
  let finalSum = 0, finalCount = 0;
  let passedCount = 0;

  rows.forEach(r => {
    const t1 = parseFloat(r.querySelector('.subj-term-input[data-col="t1"]').value);
    const t2 = parseFloat(r.querySelector('.subj-term-input[data-col="t2"]').value);
    const t3 = parseFloat(r.querySelector('.subj-term-input[data-col="t3"]').value);
    const final = parseFloat(r.querySelector('.final-cell').textContent);

    if (!isNaN(t1)) { t1Sum += t1; t1Count++; }
    if (!isNaN(t2)) { t2Sum += t2; t2Count++; }
    if (!isNaN(t3)) { t3Sum += t3; t3Count++; }
    if (!isNaN(final)) {
      finalSum += final;
      finalCount++;
      if (final >= 75) passedCount++;
    }
  });

  const avgT1 = t1Count ? (t1Sum / t1Count).toFixed(2) : '—';
  const avgT2 = t2Count ? (t2Sum / t2Count).toFixed(2) : '—';
  const avgT3 = t3Count ? (t3Sum / t3Count).toFixed(2) : '—';
  const genAvg = finalCount ? (finalSum / finalCount).toFixed(2) : '—';

  const t1El = document.getElementById('avg-t1');
  const t2El = document.getElementById('avg-t2');
  const t3El = document.getElementById('avg-t3');
  const genEl = document.getElementById('summary-general-avg');
  const passEl = document.getElementById('summary-pass-rate');

  if (t1El) t1El.textContent = avgT1;
  if (t2El) t2El.textContent = avgT2;
  if (t3El) t3El.textContent = avgT3;
  if (genEl) genEl.textContent = genAvg;
  if (passEl) {
    if (finalCount > 0) {
      const pct = Math.round((passedCount / finalCount) * 100);
      passEl.innerHTML = `<span class="fw-bold ${pct >= 75 ? 'text-success' : 'text-danger'}">${passedCount}/${finalCount} Passed (${pct}%)</span>`;
    } else {
      passEl.textContent = '—';
    }
  }
}

function updateSubjectStatusBadge(hasChanges) {
  const badge = document.getElementById('roster-status-badge');
  if (!badge) return;
  if (hasChanges) {
    badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1';
    badge.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Unsaved changes';
  } else {
    badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
    badge.innerHTML = '<i class="fas fa-check me-1"></i>Up to date';
  }
}

function navigateSubjectInputs(event, input) {
  const col = input.dataset.col;
  const row = input.closest('tr');
  if (event.key === 'Enter' || event.key === 'ArrowDown') {
    event.preventDefault();
    const nextRow = row.nextElementSibling;
    if (nextRow && nextRow.classList.contains('roster-row')) {
      const targetInput = nextRow.querySelector(`.subj-term-input[data-col="${col}"]`);
      if (targetInput) { targetInput.focus(); targetInput.select(); }
    }
  } else if (event.key === 'ArrowUp') {
    event.preventDefault();
    const prevRow = row.previousElementSibling;
    if (prevRow && prevRow.classList.contains('roster-row')) {
      const targetInput = prevRow.querySelector(`.subj-term-input[data-col="${col}"]`);
      if (targetInput) { targetInput.focus(); targetInput.select(); }
    }
  }
}

function filterSubjectRoster() {
  const q = (document.getElementById('subj-search-input').value || '').trim().toLowerCase();
  const rows = document.querySelectorAll('#subject-grades-tbody tr.roster-row');
  rows.forEach(r => {
    const name = r.dataset.name || '';
    const lrn  = r.dataset.lrn || '';
    if (!q || name.includes(q) || lrn.includes(q)) {
      r.style.display = '';
    } else {
      r.style.display = 'none';
    }
  });
}

function quickFillSubjectRoster(val) {
  const inputs = document.querySelectorAll('#subject-grades-tbody .subj-term-input');
  let filled = 0;
  inputs.forEach(inp => {
    if (!inp.value) {
      inp.value = val;
      recomputeSubjectRow(inp);
      filled++;
    }
  });
  if (filled > 0) {
    showToast(`Quick-filled ${filled} blank terms with ${val}!`, 'info');
  } else {
    showToast('No blank input fields to fill.', 'warning');
  }
}

async function saveSubjectGrades() {
  const { grade_level, section } = getActiveSubjectClass();
  const sy = document.getElementById('subj-sy-select').value;
  if (!grade_level || !section || !currentSubject) {
    showToast('Please select a valid class and subject.', 'error');
    return;
  }

  const rows = document.querySelectorAll('#subject-grades-tbody tr.roster-row');
  if (!rows.length) {
    showToast('No students to save.', 'warning');
    return;
  }

  const grades = [];
  rows.forEach(r => {
    const student_id = parseInt(r.dataset.studentId);
    const t1 = r.querySelector('.subj-term-input[data-col="t1"]').value;
    const t2 = r.querySelector('.subj-term-input[data-col="t2"]').value;
    const t3 = r.querySelector('.subj-term-input[data-col="t3"]').value;
    grades.push({ student_id, t1, t2, t3 });
  });

  showLoading('Saving Grades...', `Batch saving ${currentSubject} grades for ${grade_level} - ${section}...`);

  try {
    const res = await fetch(`${BASE}/api/grades/index.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        school_year: sy,
        grade_level,
        section,
        subject: currentSubject,
        grades
      })
    });

    const data = await res.json();
    hideLoading();

    if (data.ok) {
      showToast(data.message || 'Grades saved successfully!', 'success');
      hasUnsavedSubjectChanges = false;
      updateSubjectStatusBadge(false);
      document.querySelectorAll('#subject-grades-tbody .subj-term-input').forEach(inp => inp.classList.remove('is-modified'));
      document.querySelectorAll('#subject-grades-tbody .status-cell').forEach(cell => {
        cell.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Saved</span>';
      });
    } else {
      showToast(data.message || 'Failed to save grades.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('Network error while saving grades.', 'error');
  }
}


/* ==============================================================
   2. INDIVIDUAL SF10 PER-STUDENT GRADING ENGINE
   ============================================================== */

function filterStudentDropdown() {
  const filter = document.getElementById('class-filter') ? document.getElementById('class-filter').value : '';
  const sel = document.getElementById('student-select');
  const options = sel.querySelectorAll('option');

  let currentSelectedHidden = false;
  options.forEach(opt => {
    if (!opt.value) return;
    const optClass = opt.dataset.class || '';
    if (!filter || optClass === filter) {
      opt.style.display = '';
      opt.disabled = false;
    } else {
      opt.style.display = 'none';
      opt.disabled = true;
      if (opt.selected) currentSelectedHidden = true;
    }
  });

  if (currentSelectedHidden) {
    sel.value = '';
    loadGrades();
  }
}

async function loadGrades() {
  const studentId = document.getElementById('student-select').value;
  const sy = document.getElementById('sy-select').value;
  if (!studentId) {
    document.getElementById('sf10-container').style.display = 'none';
    const noMsg = document.getElementById('no-student-msg');
    if (noMsg) noMsg.style.display = 'block';
    return;
  }
  showLoading('Loading Student Grades...', 'Fetching academic record from database...');
  try {
    const res = await fetch(`${BASE}/api/grades/student.php?student_id=${studentId}&school_year=${sy}`);
    const data = await res.json();
    hideLoading();
    if (!data.ok) { showToast(data.message, 'error'); return; }

    currentStudent = data.student;
    currentGrades  = data.grades;

    document.getElementById('sf10-name').textContent = `${data.student.last_name}, ${data.student.first_name} ${data.student.middle_name||''}`;
    document.getElementById('sf10-lrn').textContent  = data.student.lrn;
    document.getElementById('sf10-grade').textContent   = data.student.grade_level;
    document.getElementById('sf10-section').textContent = data.student.section;
    document.getElementById('sf10-sy').textContent = sy;

    const sf9Btn = document.getElementById('btn-print-sf9');
    const isKinder = /kinder/i.test(data.student.grade_level);
    const docTitle = document.getElementById('sf10-doc-title');
    const kinderBadge = document.getElementById('kinder-badge');
    const kinderLegend = document.getElementById('kinder-legend-box');
    if (isKinder) {
      if (docTitle) docTitle.innerHTML = 'K-3 REPORT CARD GRADE SHEET &bull; KINDERGARTEN LEARNER PROGRESS RECORD';
      if (kinderBadge) kinderBadge.classList.remove('d-none');
      if (kinderLegend) kinderLegend.classList.remove('d-none');
      if (sf9Btn) {
        sf9Btn.innerHTML = '<i class="fas fa-print me-1"></i>Print K-3 Report Card (SF9-K)';
        sf9Btn.href = `${BASE}/views/teacher/reports.php?type=sf9&student_id=${studentId}&sy=${encodeURIComponent(sy)}`;
      }
    } else {
      if (docTitle) docTitle.innerHTML = 'SCHOOL FORM 10 (SF10) — LEARNER\x27s PERMANENT ACADEMIC RECORD';
      if (kinderBadge) kinderBadge.classList.add('d-none');
      if (kinderLegend) kinderLegend.classList.add('d-none');
      if (sf9Btn) {
        sf9Btn.innerHTML = '<i class="fas fa-print me-1"></i>Print SF9 Report Card';
        sf9Btn.href = `${BASE}/views/teacher/reports.php?type=sf9&student_id=${studentId}&sy=${encodeURIComponent(sy)}`;
      }
    }

    const tbody = document.getElementById('grades-tbody');
    tbody.innerHTML = '';
    let gradeSum = 0, gradeCount = 0;

    data.subjects.forEach(subject => {
      const g = data.grades[subject] || {};
      const t1 = g.t1 ?? g.q1 ?? '';
      const t2 = g.t2 ?? g.q2 ?? '';
      const t3 = g.t3 ?? g.q3 ?? '';
      const final = g.final_grade ?? '';
      const remarks = g.remarks || '';
      if (final !== '' && final !== null) { gradeSum += parseFloat(final); gradeCount++; }
      const rmClass = remarks === 'Passed' ? 'remarks-passed' : (remarks === 'Failed' ? 'remarks-failed' : '');
      tbody.innerHTML += `<tr data-subject="${subject}">
        <td>${subject}</td>
        <td><input type="number" class="grade-input term-input text-center" data-term="t1" min="0" max="100" step="any" value="${t1}" oninput="recomputeRow(this)" onchange="recomputeRow(this)"></td>
        <td><input type="number" class="grade-input term-input text-center" data-term="t2" min="0" max="100" step="any" value="${t2}" oninput="recomputeRow(this)" onchange="recomputeRow(this)"></td>
        <td><input type="number" class="grade-input term-input text-center" data-term="t3" min="0" max="100" step="any" value="${t3}" oninput="recomputeRow(this)" onchange="recomputeRow(this)"></td>
        <td class="fw-bold final-cell text-center">${final !== '' ? final : '—'}</td>
        <td class="${rmClass} remarks-cell text-center">${remarks || '—'}</td>
      </tr>`;
    });

    const avg = gradeCount > 0 ? (gradeSum / gradeCount).toFixed(2) : '—';
    document.getElementById('general-average').textContent = avg;
    document.getElementById('general-remarks').textContent = avg !== '—' ? (parseFloat(avg) >= 75 ? 'Passed' : 'Failed') : '—';
    document.getElementById('sf10-container').style.display = 'block';
    const noMsg = document.getElementById('no-student-msg');
    if (noMsg) noMsg.style.display = 'none';
  } catch (err) {
    hideLoading();
    showToast('An error occurred while loading grades.', 'error');
  }
}

function recomputeRow(input) {
  const row = input.closest('tr');
  const inputs = row.querySelectorAll('.term-input');
  const vals = Array.from(inputs).map(i => i.value !== '' ? parseFloat(i.value) : null);
  if (vals.every(v => v !== null)) {
    const final = Math.round(vals.reduce((a,b) => a+b, 0) / 3 * 100) / 100;
    const remarks = final >= 75 ? 'Passed' : 'Failed';
    row.querySelector('.final-cell').textContent = final.toFixed(2);
    row.querySelector('.remarks-cell').textContent = remarks;
    row.querySelector('.remarks-cell').className = 'remarks-cell text-center ' + (remarks === 'Passed' ? 'remarks-passed' : 'remarks-failed');
  } else {
    row.querySelector('.final-cell').textContent = '—';
    row.querySelector('.remarks-cell').textContent = '—';
    row.querySelector('.remarks-cell').className = 'remarks-cell text-center';
  }
  updateGeneralAverage();
}

function updateGeneralAverage() {
  const finals = Array.from(document.querySelectorAll('#grades-tbody .final-cell'))
    .map(c => parseFloat(c.textContent)).filter(v => !isNaN(v));
  if (!finals.length) { document.getElementById('general-average').textContent = '—'; document.getElementById('general-remarks').textContent = '—'; return; }
  const avg = (finals.reduce((a,b)=>a+b,0)/finals.length).toFixed(2);
  document.getElementById('general-average').textContent = avg;
  document.getElementById('general-remarks').textContent = parseFloat(avg) >= 75 ? 'Passed' : 'Failed';
}

function quickFillKinder(val) {
  document.querySelectorAll('#grades-tbody .term-input').forEach(input => {
    if (!input.value) {
      input.value = val;
      recomputeRow(input);
    }
  });
}

async function saveAllGrades() {
  const studentId = document.getElementById('student-select').value;
  const sy        = document.getElementById('sy-select').value;
  if (!studentId || !currentStudent) { showToast('No student selected.', 'error'); return; }

  showLoading('Saving Grades...', 'Updating student SF10 term grades (Term 1 - Term 3)...');
  try {
    const rows = document.querySelectorAll('#grades-tbody tr');
    let saved = 0;
    for (const row of rows) {
      const subject = row.dataset.subject;
      if (!subject) continue;
      const inputs  = row.querySelectorAll('.term-input');
      const t1 = inputs[0].value, t2 = inputs[1].value, t3 = inputs[2].value;
      const payload = {
        student_id: parseInt(studentId), school_year: sy,
        grade_level: currentStudent.grade_level, section: currentStudent.section,
        subject, t1, t2, t3, q1: t1, q2: t2, q3: t3
      };
      const res = await fetch(BASE + '/api/grades/student.php', {
        method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
      });
      const r = await res.json();
      if (r.ok) saved++;
    }
    hideLoading();
    showToast(`Grades saved (${saved} subjects)!`, 'success');
  } catch (err) {
    hideLoading();
    showToast('Failed to save grades.', 'error');
  }
}

/* ==============================================================
   3. INITIALIZATION
   ============================================================== */
window.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const studentId = urlParams.get('student_id') || urlParams.get('id');

  // Initialize Subject-based grading
  onSubjectClassChange();

  // If a specific student was targeted via URL, activate the SF10 tab
  if (studentId) {
    const sel = document.getElementById('student-select');
    if (sel) {
      sel.value = studentId;
      const selectedOpt = sel.querySelector(`option[value="${studentId}"]`);
      if (selectedOpt && selectedOpt.dataset.class) {
        const classFilter = document.getElementById('class-filter');
        if (classFilter) {
          const classOpt = classFilter.querySelector(`option[value="${selectedOpt.dataset.class}"]`);
          if (classOpt) {
            classFilter.value = selectedOpt.dataset.class;
            filterStudentDropdown();
            sel.value = studentId;
          }
        }
      }
      loadGrades();
    }
  }
});
</script>
</body>
</html>
