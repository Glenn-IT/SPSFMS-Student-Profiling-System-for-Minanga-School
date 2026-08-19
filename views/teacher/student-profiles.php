<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'student-profiles';

// Fetch fresh teacher profile details to ensure up-to-date advisory assignment
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$freshUser = $uStmt->fetch() ?: $user;

$advisoryGrade   = $freshUser['advisory_grade']   ?? '';
$advisorySection = $freshUser['advisory_subject'] ?? '';

// Fallback parsing from position string if advisory columns are empty
if (!$advisoryGrade && !empty($freshUser['position'])) {
    if (preg_match('/(Grade\s+\d+)\s*-\s*Section\s*(.+)/i', $freshUser['position'], $m)) {
        $advisoryGrade   = trim($m[1]);
        $advisorySection = trim($m[2]);
    } elseif (preg_match('/(Grade\s+\d+)\s*(.+)/i', $freshUser['position'], $m)) {
        $advisoryGrade   = trim($m[1]);
        $advisorySection = trim($m[2]);
    }
}

$search = $_GET['search'] ?? '';
$where  = ['s.status = "active"'];
$params = [];

if ($advisoryGrade) {
    $where[] = 's.grade_level = ?';
    $params[] = $advisoryGrade;
}
if ($advisorySection) {
    $where[] = 's.section = ?';
    $params[] = $advisorySection;
}
if (!$advisoryGrade && !$advisorySection) {
    $where[] = '1 = 0';
}

if ($search) {
    $where[] = '(s.first_name LIKE ? OR s.last_name LIKE ? OR s.lrn LIKE ?)';
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$stmt = $pdo->prepare('SELECT * FROM students s WHERE '.implode(' AND ',$where).' ORDER BY s.last_name, s.first_name');
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Student Profiles — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Student Profiles</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Student Profiles</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Teacher</div></div></div></div>
    </nav>

    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h3>Advisory Student Profiles</h3>
        <p class="mb-0">Read-only student records for 
          <?php if ($advisoryGrade && $advisorySection): ?>
            <strong><?= htmlspecialchars($advisoryGrade) ?> — Section <?= htmlspecialchars($advisorySection) ?></strong>
          <?php else: ?>
            <span class="text-danger">No Advisory Class Assigned</span>
          <?php endif; ?>
        </p>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
          <div class="col-md-9">
            <label class="form-label mb-1">Search Advisory Students</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" name="search" class="form-control" placeholder="Search name or LRN..." value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          <div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div>
          <div class="col-md-1"><a href="student-profiles.php" class="btn btn-light w-100"><i class="fas fa-times"></i></a></div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><i class="fas fa-users me-2" style="color:var(--secondary);"></i>Students (<?= count($students) ?> records)</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Grade</th><th>Section</th><th>Sex</th><th>Contact No.</th><th class="text-center">View</th></tr></thead>
            <tbody>
              <?php if (empty($students)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No students found.</td></tr>
              <?php else: foreach ($students as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($s['lrn']) ?></span></td>
                <td><strong><?= htmlspecialchars($s['last_name']) ?></strong>, <?= htmlspecialchars($s['first_name'].' '.($s['middle_name']??'')) ?></td>
                <td><?= htmlspecialchars($s['grade_level']) ?></td>
                <td><?= htmlspecialchars($s['section']) ?></td>
                <td><?= htmlspecialchars($s['sex']) ?></td>
                <td><?= htmlspecialchars($s['contact']??'—') ?></td>
                <td class="text-center"><button class="btn btn-sm btn-outline-secondary" onclick='viewStudent(<?= htmlspecialchars(json_encode($s)) ?>)'><i class="fas fa-eye"></i></button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--secondary);color:#fff;">
        <h5 class="modal-title">Student Profile</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="view-modal-body"></div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
showDesktopOnlyWarning();
const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
function viewStudent(s) {
  const field = (label, val) => `<div class="col-md-4"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">${label}</div><div class="fw-semibold">${val||'—'}</div></div></div>`;
  document.getElementById('view-modal-body').innerHTML = `
    <div class="row g-3">
      <div class="col-12 text-center mb-2">
        <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;color:var(--secondary);margin:0 auto .5rem;">${s.first_name.charAt(0)}</div>
        <h5 class="fw-bold mb-0">${s.last_name}, ${s.first_name} ${s.middle_name||''}</h5>
      </div>
      ${field('LRN','<span style="font-family:monospace">'+s.lrn+'</span>')}
      ${field('Grade Level',s.grade_level)}
      ${field('Section',s.section)}
      ${field('Sex',s.sex)} ${field('Age',s.age+' years old')}
      ${field('Religion',s.religion)}
      <div class="col-12"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">Address</div><div class="fw-semibold">${s.address||'—'}</div></div></div>
      ${field('Mother',s.mother_name)} ${field('Father',s.father_name)}
      ${field('Guardian',s.guardian_name?(s.guardian_name+' ('+s.guardian_relation+')'):'—')}
      ${field('Contact No.',s.contact)} ${field('Email','<span style="font-size:.82rem">'+s.email+'</span>')}
    </div>`;
  viewModal.show();
}
</script>
</body>
</html>
