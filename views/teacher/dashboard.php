<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'dashboard';

// Advisory class — Grade 7 Rizal for teacher-001, Grade 1 Mabini for teacher-002
$advisoryGrade   = (strpos($user['position'] ?? '', 'Grade 7') !== false) ? 'Grade 7' : 'Grade 1';
$advisorySection = ($advisoryGrade === 'Grade 7') ? 'Rizal' : 'Mabini';

$classStmt = $pdo->prepare("SELECT s.*, COUNT(g.id) as graded_subjects FROM students s LEFT JOIN grades g ON g.student_id=s.id AND g.school_year='2025-2026' WHERE s.grade_level=? AND s.section=? AND s.status='active' GROUP BY s.id ORDER BY s.last_name");
$classStmt->execute([$advisoryGrade, $advisorySection]);
$classStudents = $classStmt->fetchAll();

$totalStudents = count($classStudents);
$graded = count(array_filter($classStudents, fn($s) => $s['graded_subjects'] > 0));
$pending = $totalStudents - $graded;
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

    <div class="page-header">
      <h3>Welcome, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>!</h3>
      <p>Advisory Class: <strong><?= $advisoryGrade ?> — Section <?= $advisorySection ?></strong> · S.Y. <?= SCHOOL_YEAR ?></p>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div>
          <div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">Total Students</div></div></div>
      </div>
      <div class="col-md-4">
        <div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div>
          <div><div class="stat-value"><?= $graded ?></div><div class="stat-label">Graded</div></div></div>
      </div>
      <div class="col-md-4">
        <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-clock"></i></div>
          <div><div class="stat-value"><?= $pending ?></div><div class="stat-label">Pending Grades</div></div></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-chalkboard me-2" style="color:var(--secondary);"></i>Advisory Class — <?= $advisoryGrade ?> <?= $advisorySection ?></span>
        <a href="grades.php" class="btn btn-sm" style="background:var(--secondary);color:#fff;">Manage Grades</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Sex</th><th>Age</th><th>Graded Subjects</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($classStudents)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">No students in this class.</td></tr>
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
                <td><span class="badge-active">Active</span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>showDesktopOnlyWarning();</script>
</body>
</html>
