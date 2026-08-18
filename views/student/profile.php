<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('student');
$sStmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
$sStmt->execute([$user['lrn']]);
$student = $sStmt->fetch();
$initial = $student ? strtoupper(substr($student['first_name'],0,1)) : 'S';
function fd(string $dateStr): string {
    if (!$dateStr) return '—';
    return date('F j, Y', strtotime($dateStr));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'My Profile — Student'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student-mobile.css">
</head>
<body>
<div class="student-app">
  <div class="student-header">
    <div class="header-row">
      <div><h6><?= SCHOOL_NAME ?></h6><h5><i class="fas fa-arrow-left me-2" onclick="history.back()" style="cursor:pointer;"></i>My Profile</h5></div>
      <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;"><?= $initial ?></div>
    </div>
  </div>

  <?php if ($student): ?>
  <div class="profile-summary-card" style="flex-direction:column;text-align:center;">
    <div class="profile-avatar" style="width:72px;height:72px;font-size:1.8rem;margin:0 auto .75rem;"><?= $initial ?></div>
    <div class="fw-bold" style="font-size:1.05rem;"><?= htmlspecialchars($student['last_name'].', '.$student['first_name'].' '.($student['middle_name']??'')) ?></div>
    <div style="font-size:.8rem;color:var(--gray-600);margin-top:2px;"><?= htmlspecialchars($student['grade_level'].' | Section '.$student['section']) ?></div>
    <div style="font-family:monospace;font-size:.78rem;color:var(--gray-400);margin-top:2px;">LRN: <?= htmlspecialchars($student['lrn']) ?></div>
  </div>

  <div class="mobile-section">
    <div class="mobile-section-title">Personal Information</div>
    <div class="mobile-card"><div class="card-row mb-1"><span class="card-label">Full Name</span></div><div class="card-value"><?= htmlspecialchars($student['first_name'].' '.($student['middle_name']??'').' '.$student['last_name']) ?></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Sex</span><span class="card-value"><?= htmlspecialchars($student['sex']) ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Birthdate</span><span class="card-value"><?= fd($student['birthdate']) ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Age</span><span class="card-value"><?= $student['age'] ?> years old</span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Mother Tongue</span><span class="card-value"><?= htmlspecialchars($student['mother_tongue']??'—') ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Religion</span><span class="card-value"><?= htmlspecialchars($student['religion']??'—') ?></span></div></div>
    <div class="mobile-card"><div class="card-row mb-1"><span class="card-label">Address</span></div><div class="card-value" style="font-size:.85rem;"><?= htmlspecialchars($student['address']??'—') ?></div></div>
  </div>

  <div class="mobile-section">
    <div class="mobile-section-title">Family Information</div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Mother</span><span class="card-value"><?= htmlspecialchars($student['mother_name']??'—') ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Father</span><span class="card-value"><?= htmlspecialchars($student['father_name']??'—') ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Guardian</span><span class="card-value"><?= htmlspecialchars(($student['guardian_name']??'—').($student['guardian_relation']?' ('.$student['guardian_relation'].')':'')) ?></span></div></div>
  </div>

  <div class="mobile-section">
    <div class="mobile-section-title">Contact Information</div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Contact No.</span><span class="card-value"><?= htmlspecialchars($student['contact']??'—') ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">Email</span><span class="card-value" style="font-size:.82rem;"><?= htmlspecialchars($student['email']??'—') ?></span></div></div>
  </div>
  <?php else: ?>
  <div class="mobile-section text-center py-4 text-muted">Student record not found.</div>
  <?php endif; ?>

  <div style="height:.5rem;"></div>
  <nav class="bottom-nav">
    <a href="dashboard.php" class="bottom-nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="dashboard.php#grades-section" class="bottom-nav-item"><i class="fas fa-chart-bar"></i><span>Grades</span></a>
    <a href="profile.php" class="bottom-nav-item active"><i class="fas fa-user"></i><span>Profile</span></a>
    <a href="settings.php" class="bottom-nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
  </nav>
</div>
<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
</body>
</html>
