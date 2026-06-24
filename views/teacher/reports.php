<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'reports';

$grade   = $_GET['grade']   ?? 'Grade 7';
$section = $_GET['section'] ?? 'Rizal';
$sy      = $_GET['sy']      ?? '2025-2026';

$stmt = $pdo->prepare("SELECT s.id, s.last_name, s.first_name, s.middle_name, s.lrn, s.sex,
    AVG(g.final_grade) as avg_grade,
    COUNT(g.id) as graded_count
    FROM students s
    LEFT JOIN grades g ON g.student_id=s.id AND g.school_year=?
    WHERE s.grade_level=? AND s.section=? AND s.status='active'
    GROUP BY s.id ORDER BY s.last_name, s.first_name");
$stmt->execute([$sy, $grade, $section]);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Reports — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    @media print { .no-print { display:none !important; } .sidebar,.top-navbar { display:none !important; } .main-content { margin:0 !important; } }
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

    <div class="page-header no-print"><h3>Grade Summary Report</h3><p>Print class grade summary</p></div>

    <form method="get" class="card mb-3 no-print">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-3"><label class="form-label mb-1">Grade Level</label>
            <select name="grade" class="form-select">
              <?php foreach (GRADE_LEVELS as $gl): ?>
              <option value="<?= $gl ?>" <?= $grade===$gl?'selected':'' ?>><?= $gl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3"><label class="form-label mb-1">School Year</label>
            <select name="sy" class="form-select">
              <option value="2025-2026" <?= $sy==='2025-2026'?'selected':'' ?>>2025–2026</option>
              <option value="2024-2025" <?= $sy==='2024-2025'?'selected':'' ?>>2024–2025</option>
            </select>
          </div>
          <div class="col-md-3"><button class="btn btn-secondary w-100">Generate</button></div>
          <div class="col-md-3"><button type="button" class="btn btn-primary w-100" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button></div>
        </div>
      </div>
    </form>

    <div class="card">
      <div class="card-body">
        <div style="text-align:center;margin-bottom:1.25rem;border-bottom:2px solid var(--secondary);padding-bottom:.75rem;">
          <div style="font-size:.75rem;color:var(--gray-600);">Republic of the Philippines · Department of Education</div>
          <h5 class="fw-bold mt-1"><?= SCHOOL_NAME ?></h5>
          <h4 class="mt-1">CLASS GRADE SUMMARY REPORT</h4>
          <div style="font-size:.82rem;"><?= htmlspecialchars($grade) ?> | S.Y. <?= htmlspecialchars($sy) ?></div>
          <div style="font-size:.78rem;color:var(--gray-600);">Prepared by: <?= htmlspecialchars($user['name']) ?></div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Sex</th><th>Graded Subjects</th><th>General Average</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($students)): ?>
              <tr><td colspan="7" class="text-center text-muted py-3">No students found for this class.</td></tr>
              <?php else: foreach ($students as $i => $s):
                $avg = $s['avg_grade'] !== null ? round($s['avg_grade'],2) : null;
                $remarks = $avg !== null ? ($avg >= 75 ? 'Passed' : 'Failed') : '—';
              ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td style="font-family:monospace;font-size:.78rem;"><?= htmlspecialchars($s['lrn']) ?></td>
                <td><?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?></td>
                <td><?= htmlspecialchars($s['sex']) ?></td>
                <td><?= $s['graded_count'] ?: '0' ?></td>
                <td class="fw-bold"><?= $avg ?? '—' ?></td>
                <td style="color:<?= $remarks==='Passed'?'green':($remarks==='Failed'?'red':'inherit') ?>;font-weight:600;"><?= $remarks ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <div style="font-size:.72rem;color:var(--gray-400);text-align:right;margin-top:.75rem;">
          Generated: <?= date('F j, Y \a\t g:i A') ?>
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
