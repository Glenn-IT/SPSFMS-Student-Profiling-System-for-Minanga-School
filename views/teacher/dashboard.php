<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'dashboard';

// Fetch fresh user profile details to ensure up-to-date advisory assignment
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$freshUser = $uStmt->fetch() ?: $user;

// Get all advisory classes assigned to teacher
$myClasses = getTeacherAdvisoryClasses($pdo, $freshUser['id']);
if (empty($myClasses)) {
    $myClasses = [['grade_level' => 'Grade 1', 'section' => 'Mabini']];
}

// Determine active advisory class from GET param (?class=index or ?grade=...&section=...)
$selectedClassIndex = 0;
if (isset($_GET['class']) && is_numeric($_GET['class'])) {
    $cIdx = (int)$_GET['class'];
    if (isset($myClasses[$cIdx])) {
        $selectedClassIndex = $cIdx;
    }
} elseif (!empty($_GET['grade']) && !empty($_GET['section'])) {
    foreach ($myClasses as $idx => $cls) {
        if ($cls['grade_level'] === $_GET['grade'] && $cls['section'] === $_GET['section']) {
            $selectedClassIndex = $idx;
            break;
        }
    }
}

$activeClass     = $myClasses[$selectedClassIndex] ?? $myClasses[0];
$advisoryGrade   = $activeClass['grade_level'];
$advisorySection = $activeClass['section'];

$sy = SCHOOL_YEAR;

$classStmt = $pdo->prepare("SELECT s.*, COUNT(g.id) as graded_subjects FROM students s LEFT JOIN grades g ON g.student_id=s.id AND g.school_year=? WHERE s.grade_level=? AND s.section=? AND s.status='active' GROUP BY s.id ORDER BY s.last_name");
$classStmt->execute([$sy, $advisoryGrade, $advisorySection]);
$classStudents = $classStmt->fetchAll();

$totalStudents = count($classStudents);
$graded = count(array_filter($classStudents, fn($s) => $s['graded_subjects'] > 0));
$pending = $totalStudents - $graded;

// Fetch announcements visible to teachers ('all' and 'teacher')
$annStmt = $pdo->query("SELECT * FROM announcements WHERE audience IN ('all','teacher') ORDER BY posted_at DESC LIMIT 5");
$announcements = $annStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Dashboard — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Dashboard</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Dashboard</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span style="font-size:.78rem;color:var(--gray-600);">S.Y. <?= SCHOOL_YEAR ?></span>
        <div class="user-menu">
          <div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role"><?= htmlspecialchars($user['position'] ?? 'Teacher') ?></div></div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex flex-column gap-2">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="<?= SCHOOL_NAME ?>" style="width:58px;height:58px;border-radius:12px;object-fit:cover;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,.08);flex-shrink:0;" onerror="this.style.display='none'">
          <div>
            <h3 class="mb-1">Welcome, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>!</h3>
            <p class="mb-0 text-muted">
              Currently viewing: <strong class="text-dark"><?= htmlspecialchars($advisoryGrade) ?> — Section <?= htmlspecialchars($advisorySection) ?></strong> · S.Y. <?= SCHOOL_YEAR ?>
            </p>
          </div>
        </div>
        <div>
          <span class="badge bg-secondary bg-opacity-10 text-dark border px-3 py-2 fw-semibold" style="font-size:.82rem;">
            <i class="fas fa-chalkboard-teacher me-1" style="color:var(--secondary);"></i>
            <?= count($myClasses) ?> Advisory <?= count($myClasses) === 1 ? 'Class' : 'Classes' ?> Assigned
          </span>
        </div>
      </div>

      <!-- Multiple Advisory Classes Switcher Tabs / Buttons -->
      <?php if (count($myClasses) > 1): ?>
        <div class="p-2 bg-white rounded-3 border shadow-sm mt-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold small me-1 ps-1"><i class="fas fa-exchange-alt me-1 text-success"></i>Switch Advisory Class:</span>
            <?php foreach ($myClasses as $idx => $cls):
              $isActive = ($idx === $selectedClassIndex);
            ?>
              <a href="?class=<?= $idx ?>" class="btn btn-sm <?= $isActive ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light border text-dark' ?>" style="border-radius: 20px; font-size: .84rem; padding: 5px 14px; transition: all 0.2s;">
                <i class="fas fa-chalkboard me-1 <?= $isActive ? 'text-white' : 'text-success' ?>"></i>
                <?= htmlspecialchars($cls['grade_level']) ?> — Section <?= htmlspecialchars($cls['section']) ?>
                <?php if ($isActive): ?>
                  <span class="badge bg-white text-success ms-1" style="font-size: .68rem;">Active</span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div>
          <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Students (<?= htmlspecialchars($advisorySection) ?>)</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div><div class="stat-value"><?= $graded ?></div><div class="stat-label">Graded</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div><div class="stat-value"><?= $pending ?></div><div class="stat-label">Pending Grades</div></div></div>
      </div>
      <div class="col-md-3">
        <div class="stat-card purple"><div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
          <div><div class="stat-value"><?= count($myClasses) ?></div><div class="stat-label">Advisory <?= count($myClasses) === 1 ? 'Class' : 'Classes' ?></div></div></div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left: Advisory Class Roster -->
      <div class="col-xl-8 col-lg-7">
        <div class="card h-100 mb-0">
          <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <span><i class="fas fa-chalkboard me-2" style="color:var(--secondary);"></i>Advisory Class Roster — <strong><?= htmlspecialchars($advisoryGrade) ?> <?= htmlspecialchars($advisorySection) ?></strong></span>
              <span class="badge bg-success bg-opacity-15 text-success fw-semibold" style="font-size:.75rem;"><?= $totalStudents ?> Students</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <div class="input-group input-group-sm" style="width:200px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="advisorySearch" class="form-control" placeholder="Search student...">
              </div>
              <a href="grades.php" class="btn btn-sm" style="background:var(--secondary);color:#fff;">Manage Grades</a>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-modern mb-0" id="advisoryTable">
                <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Sex</th><th>Age</th><th>Graded Subjects</th><th class="text-center">Action</th></tr></thead>
                <tbody>
                  <?php if (empty($classStudents)): ?>
                  <tr><td colspan="7" class="text-center py-4 text-muted">No students enrolled in <?= htmlspecialchars($advisoryGrade) ?> - Section <?= htmlspecialchars($advisorySection) ?>.</td></tr>
                  <?php else: foreach ($classStudents as $i => $s): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($s['lrn']) ?></span></td>
                    <td><strong><?= htmlspecialchars($s['last_name']) ?></strong>, <?= htmlspecialchars($s['first_name'].' '.($s['middle_name']??'')) ?></td>
                    <td><?= htmlspecialchars($s['sex']) ?></td>
                    <td><?= $s['age'] ?></td>
                    <td>
                      <?php if ($s['graded_subjects'] > 0): ?>
                      <span class="badge bg-success bg-opacity-15 text-success fw-semibold"><?= $s['graded_subjects'] ?> subjects</span>
                      <?php else: ?>
                      <span class="badge bg-warning bg-opacity-15 text-warning fw-semibold">No grades yet</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <a href="grades.php?student_id=<?= $s['id'] ?>" class="btn btn-sm" style="background:var(--secondary);color:#fff;font-size:.78rem;padding:4px 10px;border-radius:6px;white-space:nowrap;">
                        <i class="fas fa-edit me-1"></i>Manage Grade
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; endif; ?>
                  <tr id="advisoryNoMatch" class="d-none"><td colspan="7" class="text-center py-4 text-muted">No matching students found.</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Announcements & Quick Actions -->
      <div class="col-xl-4 col-lg-5 d-flex flex-column gap-3">
        <!-- Announcements Feed Card -->
        <div class="card shadow-sm">
          <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><i class="fas fa-bullhorn me-2" style="color:var(--secondary);"></i>School Announcements</span>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size:.75rem;"><?= count($announcements) ?></span>
          </div>
          <div class="card-body p-3">
            <?php if (empty($announcements)): ?>
              <div class="text-center py-4 text-muted small">
                <i class="fas fa-bell-slash d-block mb-2 text-muted opacity-50" style="font-size: 1.5rem;"></i>
                No announcements posted for teachers at this time.
              </div>
            <?php else: ?>
              <div class="d-flex flex-column gap-2" style="max-height: 380px; overflow-y: auto;">
                <?php foreach ($announcements as $ann): ?>
                  <div class="p-3 rounded-3" style="background:#f8fafc; border-left:4px solid var(--secondary); box-shadow: 0 1px 3px rgba(0,0,0,.03);">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                      <h6 class="fw-bold mb-0 text-dark" style="font-size:.86rem;"><?= htmlspecialchars($ann['title']) ?></h6>
                      <span class="badge <?= $ann['audience'] === 'teacher' ? 'bg-warning bg-opacity-15 text-warning text-dark' : 'bg-primary bg-opacity-10 text-primary' ?> border" style="font-size:.65rem; white-space:nowrap;">
                        <?= $ann['audience'] === 'teacher' ? 'Teachers' : 'Everyone' ?>
                      </span>
                    </div>
                    <div class="text-muted small mb-2" style="line-height:1.4; font-size:.8rem; white-space:pre-line;"><?= htmlspecialchars($ann['body']) ?></div>
                    <div class="d-flex align-items-center text-muted" style="font-size:.7rem;">
                      <i class="far fa-clock me-1" style="color:var(--secondary);"></i>
                      <span><?= date('F j, Y · g:i A', strtotime($ann['posted_at'])) ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Teacher Quick Actions Card -->
        <div class="card shadow-sm">
          <div class="card-header">
            <span class="fw-semibold"><i class="fas fa-bolt me-2" style="color:var(--secondary);"></i>Quick Actions</span>
          </div>
          <div class="card-body p-3">
            <div class="d-grid gap-2">
              <a href="grades.php" class="btn btn-outline-success btn-sm text-start d-flex align-items-center justify-content-between">
                <span><i class="fas fa-clipboard-list me-2"></i>Enter Class Grades (SF10)</span>
                <i class="fas fa-chevron-right small opacity-50"></i>
              </a>
              <a href="reports.php" class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between">
                <span><i class="fas fa-file-alt me-2"></i>Generate SF9 &amp; SF10 Reports</span>
                <i class="fas fa-chevron-right small opacity-50"></i>
              </a>
              <a href="student-profiles.php" class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between">
                <span><i class="fas fa-users me-2"></i>Browse Student Profiles</span>
                <i class="fas fa-chevron-right small opacity-50"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
showDesktopOnlyWarning();

const advisorySearch = document.getElementById('advisorySearch');
if (advisorySearch) {
  const table = document.getElementById('advisoryTable');
  const noMatchRow = document.getElementById('advisoryNoMatch');
  const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row !== noMatchRow);
  advisorySearch.addEventListener('input', function () {
    const term = this.value.trim().toLowerCase();
    let visibleCount = 0;
    rows.forEach(row => {
      const match = row.textContent.toLowerCase().includes(term);
      row.classList.toggle('d-none', !match);
      if (match) visibleCount++;
    });
    if (noMatchRow) noMatchRow.classList.toggle('d-none', visibleCount !== 0 || rows.length === 0);
  });
}
</script>
</body>
</html>

