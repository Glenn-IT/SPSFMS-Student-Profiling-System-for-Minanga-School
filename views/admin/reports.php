<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'reports';

$sigData = getSignatories($pdo, $user);

$type      = $_GET['type']       ?? '';
$grade     = $_GET['grade']      ?? '';
$section   = $_GET['section']    ?? '';
$sy        = $_GET['sy']         ?? SCHOOL_YEAR;
$search    = $_GET['search']     ?? '';
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$period    = isset($_GET['period']) ? (int)$_GET['period'] : 0;

$students = [];
if ($type && $type !== 'sf9') {
    $where = ['status="active"'];
    $params = [];
    if ($grade)   { $where[] = 'grade_level=?'; $params[] = $grade; }
    if ($section) { $where[] = 'section=?';     $params[] = $section; }
    if ($sy)      { $where[] = 'school_year=?'; $params[] = $sy; }
    if ($search)  { $where[] = '(first_name LIKE ? OR last_name LIKE ? OR lrn LIKE ?)'; array_push($params, "%$search%", "%$search%", "%$search%"); }
    $stmt = $pdo->prepare('SELECT * FROM students WHERE '.implode(' AND ',$where).' ORDER BY grade_level,last_name,first_name');
    $stmt->execute($params);
    $students = $stmt->fetchAll();
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
  <?php $pageTitle = 'Reports — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sf9.css">
  <style>
    @media print {
      .no-print { display:none !important; }
      .sidebar,.top-navbar { display:none !important; }
      .main-content { margin:0 !important; }
      .signatories { margin-top: 3rem; }
      .report-header-logo { max-height: 95px !important; display: inline-block !important; vertical-align: top !important; }
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
      <div><div class="page-title">Reports</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Reports</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header no-print"><h3>Reports</h3><p>Generate and print student enrollment reports</p></div>

    <!-- Report type selector -->
    <?php if (!$type): ?>
    <div class="row g-3 no-print">
      <?php foreach ([
        ['masterlist','fa-id-card','Student Masterlist','Complete student records with all fields','primary'],
        ['gender','fa-venus-mars','Gender Summary','Gender breakdown by grade level','warning'],
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
              <option value="2025-2026" <?= $sy==='2025-2026'?'selected':'' ?>>2025–2026</option>
              <option value="2024-2025" <?= $sy==='2024-2025'?'selected':'' ?>>2024–2025</option>
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
            <a href="<?= BASE_URL ?>/views/admin/signatories.php" class="btn btn-outline-secondary w-100" title="Configure Report Signatories">
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
                <?= $type === 'gender' ? 'GENDER SUMMARY' : 'STUDENT MASTERLIST' ?>
              </h3>
              <div style="font-size:.95rem;color:#333;" class="text-center" id="report-subtitle">
                School Year <span id="report-sy"><?= htmlspecialchars($sy) ?></span><?= $grade ? ' · '.$grade : '' ?>
              </div>
            </div>
          </div>
        </div>

        <?php if ($type === 'gender'): ?>
        <?php
          $gStmt = $pdo->prepare("SELECT grade_level, sex, COUNT(*) as cnt FROM students WHERE status='active' AND school_year=? GROUP BY grade_level, sex ORDER BY grade_level");
          $gStmt->execute([$sy]);
          $gRows = $gStmt->fetchAll();
          $gMap = [];
          foreach ($gRows as $r) $gMap[$r['grade_level']][$r['sex']] = $r['cnt'];
        ?>
        <table class="table table-bordered table-sm">
          <thead><tr><th>Grade Level</th><th>Male</th><th>Female</th><th>Total</th></tr></thead>
          <tbody>
            <?php $totalM=$totalF=0; foreach (GRADE_LEVELS as $gl): $m=$gMap[$gl]['Male']??0; $f=$gMap[$gl]['Female']??0; $totalM+=$m; $totalF+=$f; ?>
            <tr><td><?= $gl ?></td><td><?= $m ?></td><td><?= $f ?></td><td><strong><?= $m+$f ?></strong></td></tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr class="fw-bold"><td>TOTAL</td><td><?= $totalM ?></td><td><?= $totalF ?></td><td><?= $totalM+$totalF ?></td></tr></tfoot>
        </table>

        <?php else: ?>

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
              <tr><td colspan="9" class="text-center text-muted py-3">No records found.</td></tr>
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

        <?php endif; ?>

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

    try {
      const res  = await fetch(`${BASE_URL}/api/students/index.php?${params}`);
      const data = await res.json();

      if (!data.ok) throw new Error(data.message);

      const students = data.students;

      if (students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">No records found.</td></tr>';
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
