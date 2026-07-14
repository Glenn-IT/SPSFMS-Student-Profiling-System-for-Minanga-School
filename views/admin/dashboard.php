<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'dashboard';

// Server-side stats
$total  = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn();
$elem   = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active' AND grade_level IN ('Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6')")->fetchColumn();
$jhs    = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active' AND grade_level IN ('Grade 7','Grade 8','Grade 9','Grade 10')")->fetchColumn();
$shs    = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active' AND grade_level IN ('Grade 11','Grade 12')")->fetchColumn();
$male   = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active' AND sex='Male'")->fetchColumn();
$female = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status='active' AND sex='Female'")->fetchColumn();

$gradeCounts = [];
foreach (GRADE_LEVELS as $gl) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM students WHERE status='active' AND grade_level=?");
    $s->execute([$gl]);
    $gradeCounts[] = (int)$s->fetchColumn();
}

$recentStmt = $pdo->query("SELECT * FROM students WHERE status='active' ORDER BY created_at DESC LIMIT 7");
$recent = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Dashboard — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+) to access this portal.</p></div>

<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Dashboard</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Dashboard</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span style="font-size:.78rem;color:var(--gray-600);">S.Y. <?= SCHOOL_YEAR ?></span>
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header">
      <h3>Welcome back, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>!</h3>
      <p>Here's an overview of <?= SCHOOL_NAME ?> — S.Y. <?= SCHOOL_YEAR ?></p>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div>
          <div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Students</div></div></div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card green"><div class="stat-icon"><i class="fas fa-child"></i></div>
          <div><div class="stat-value"><?= $elem ?></div><div class="stat-label">Elementary (Gr. 1–6)</div></div></div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-user-friends"></i></div>
          <div><div class="stat-value"><?= $jhs ?></div><div class="stat-label">Junior High (Gr. 7–10)</div></div></div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="stat-card red"><div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
          <div><div class="stat-value"><?= $shs ?></div><div class="stat-label">Senior High (Gr. 11–12)</div></div></div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mb-4">
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><i class="fas fa-chart-pie me-2" style="color:var(--primary);"></i>Enrollment Distribution</div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <canvas id="enrollmentChart" style="max-height:220px;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><i class="fas fa-venus-mars me-2" style="color:var(--primary);"></i>Gender Distribution</div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <canvas id="genderChart" style="max-height:220px;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><i class="fas fa-bar-chart me-2" style="color:var(--primary);"></i>Students Per Grade Level</div>
          <div class="card-body"><canvas id="gradeChart" style="max-height:220px;"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Recent Students -->
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-clock me-2" style="color:var(--primary);"></i>Recently Enrolled Students</span>
        <a href="students.php" class="btn btn-sm btn-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Grade Level</th><th>Section</th><th>Sex</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($recent)): ?>
              <tr><td colspan="7" class="text-center py-4 text-muted">No students found.</td></tr>
              <?php else: foreach ($recent as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($s['lrn']) ?></span></td>
                <td><strong><?= htmlspecialchars($s['last_name']) ?></strong>, <?= htmlspecialchars($s['first_name'].' '.$s['middle_name']) ?></td>
                <td><?= htmlspecialchars($s['grade_level']) ?></td>
                <td><?= htmlspecialchars($s['section']) ?></td>
                <td><?= htmlspecialchars($s['sex']) ?></td>
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
<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
showDesktopOnlyWarning();

const ELEM=<?= $elem ?>, JHS=<?= $jhs ?>, SHS=<?= $shs ?>, MALE=<?= $male ?>, FEMALE=<?= $female ?>;
const GRADE_LABELS = <?= json_encode(array_map(fn($g)=>str_replace('Grade ','Gr.',$g), GRADE_LEVELS)) ?>;
const GRADE_COUNTS = <?= json_encode($gradeCounts) ?>;

new Chart(document.getElementById('enrollmentChart'), {
  type:'doughnut',
  data:{ labels:['Elementary','Junior High','Senior High'], datasets:[{ data:[ELEM,JHS,SHS], backgroundColor:['#34a853','#1a73e8','#ea4335'], borderWidth:0 }] },
  options:{ plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } }, cutout:'65%' }
});

new Chart(document.getElementById('genderChart'), {
  type:'doughnut',
  data:{ labels:['Male','Female'], datasets:[{ data:[MALE,FEMALE], backgroundColor:['#1a73e8','#ea4335'], borderWidth:0 }] },
  options:{ plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } }, cutout:'65%' }
});

new Chart(document.getElementById('gradeChart'), {
  type:'bar',
  data:{ labels:GRADE_LABELS, datasets:[{ label:'Students', data:GRADE_COUNTS, backgroundColor:'#1a73e8', borderRadius:4 }] },
  options:{ plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, ticks:{ precision:0 } } } }
});
</script>
</body>
</html>
