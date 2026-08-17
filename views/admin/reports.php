<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'reports';

$type    = $_GET['type']    ?? '';
$grade   = $_GET['grade']   ?? '';
$section = $_GET['section'] ?? '';
$sy      = $_GET['sy']      ?? '2025-2026';
$search  = $_GET['search']  ?? '';

$students = [];
if ($type) {
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

// Encode SECTION_MAP for JS
$sectionMapJson = json_encode(SECTION_MAP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Reports — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    @media print {
      .no-print { display:none !important; }
      .sidebar,.top-navbar { display:none !important; }
      .main-content { margin:0 !important; }
      .signatories { margin-top: 3rem; }
    }
    .report-header { text-align:center; margin-bottom:1.5rem; }
    .report-header h4 { font-weight:700; color:#1a1a2e; }

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
      ] as [$t,$icon,$label,$desc,$color]): ?>
      <div class="col-md-6">
        <a href="?type=<?= $t ?>&sy=2025-2026" style="text-decoration:none;">
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

    <?php else: ?>

    <!-- Filters -->
    <div class="card mb-3 no-print">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <!-- Grade Level -->
          <div class="col-md-3">
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
          <div class="col-md-3">
            <label class="form-label mb-1">
              Search
              <span id="search-spinner" class="ms-1">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
              </span>
            </label>
            <input type="text" id="live-search" class="form-control" placeholder="Name or LRN — results update live..." value="<?= htmlspecialchars($search) ?>">
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
        <div class="report-header">
          <div style="font-size:.8rem;color:var(--gray-600);">Republic of the Philippines · Department of Education</div>
          <h5 class="fw-bold mt-1"><?= SCHOOL_NAME ?></h5>
          <div style="font-size:.85rem;color:var(--gray-600);"><?= SCHOOL_ADDRESS ?></div>
          <h4 class="mt-2" id="report-title">
            <?= $type === 'gender' ? 'GENDER SUMMARY' : 'STUDENT MASTERLIST' ?>
          </h4>
          <div style="font-size:.82rem;" id="report-subtitle">
            School Year <span id="report-sy"><?= htmlspecialchars($sy) ?></span>
            <?= $grade ? '· '.$grade : '' ?>
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
                <th>Contact</th><th>Guardian</th>
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

        <!-- Signatories -->
        <div class="signatories" id="signatories-block">
          <div class="signatory-block">
            <div class="sig-label">Prepared by</div>
            <div class="sig-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="sig-position"><?= htmlspecialchars($user['position'] ?? 'Administrator') ?></div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Noted by</div>
            <div class="sig-name">&nbsp;</div>
            <div class="sig-position">School Head / Principal</div>
          </div>
          <div class="signatory-block">
            <div class="sig-label">Date Generated</div>
            <div class="sig-name"><?= date('F j, Y') ?></div>
            <div class="sig-position"><?= date('g:i A') ?></div>
          </div>
        </div>

        <?php endif; ?>

        <div style="font-size:.75rem;color:var(--gray-400);text-align:right;margin-top:1rem;" class="no-print">
          Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($user['name']) ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
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
</script>
</body>
</html>
