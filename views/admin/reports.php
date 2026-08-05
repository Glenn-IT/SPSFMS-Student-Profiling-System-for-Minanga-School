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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Reports — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    @media print { .no-print { display:none !important; } .sidebar,.top-navbar { display:none !important; } .main-content { margin:0 !important; } }
    .report-header { text-align:center; margin-bottom:1.5rem; }
    .report-header h4 { font-weight:700; color:#1a1a2e; }
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
        ['enrollment','fa-list','Enrollment List','List of enrolled students with basic info','primary'],
        ['masterlist','fa-id-card','Student Masterlist','Complete student records with all fields','secondary'],
        ['gender','fa-venus-mars','Gender Summary','Gender breakdown by grade level','warning'],
      ] as [$t,$icon,$label,$desc,$color]): ?>
      <div class="col-md-4">
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
    <form method="get" class="card mb-3 no-print">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
          <div class="col-md-3"><label class="form-label mb-1">Grade Level</label>
            <select name="grade" class="form-select">
              <option value="">All Grades</option>
              <?php foreach (GRADE_LEVELS as $gl): ?>
              <option value="<?= $gl ?>" <?= $grade===$gl?'selected':'' ?>><?= $gl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2"><label class="form-label mb-1">School Year</label>
            <select name="sy" class="form-select">
              <option value="2025-2026" <?= $sy==='2025-2026'?'selected':'' ?>>2025–2026</option>
              <option value="2024-2025" <?= $sy==='2024-2025'?'selected':'' ?>>2024–2025</option>
            </select>
          </div>
          <div class="col-md-3"><label class="form-label mb-1">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or LRN..." value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
          <div class="col-md-1"><button class="btn btn-light w-100" onclick="window.print()"><i class="fas fa-print"></i></button></div>
          <div class="col-md-1"><a href="reports.php" class="btn btn-light w-100"><i class="fas fa-times"></i></a></div>
        </div>
      </div>
    </form>

    <!-- Report Output -->
    <div class="card">
      <div class="card-body">
        <div class="report-header">
          <div style="font-size:.8rem;color:var(--gray-600);">Republic of the Philippines · Department of Education</div>
          <h5 class="fw-bold mt-1"><?= SCHOOL_NAME ?></h5>
          <div style="font-size:.85rem;color:var(--gray-600);">Minanga, Cagayan de Oro City</div>
          <h4 class="mt-2">
            <?= $type === 'enrollment' ? 'ENROLLMENT LIST' : ($type === 'masterlist' ? 'STUDENT MASTERLIST' : 'GENDER SUMMARY') ?>
          </h4>
          <div style="font-size:.82rem;">School Year <?= htmlspecialchars($sy) ?> <?= $grade ? '· '.$grade : '' ?></div>
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
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>#</th><th>LRN</th><th>Full Name</th><th>Grade</th><th>Section</th><th>Sex</th><th>Age</th>
              <?php if ($type === 'masterlist'): ?><th>Contact</th><th>Guardian</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
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
              <?php if ($type === 'masterlist'): ?>
              <td><?= htmlspecialchars($s['contact']??'—') ?></td>
              <td><?= htmlspecialchars($s['guardian_name']??'—') ?></td>
              <?php endif; ?>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($students)): ?>
          <tfoot><tr class="fw-bold"><td colspan="9">Total: <?= count($students) ?> student(s)</td></tr></tfoot>
          <?php endif; ?>
        </table>
        <?php endif; ?>

        <div style="font-size:.75rem;color:var(--gray-400);text-align:right;margin-top:1rem;">
          Generated: <?= date('F j, Y \a\t g:i A') ?> · <?= htmlspecialchars($user['name']) ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>showDesktopOnlyWarning();</script>
</body>
</html>
