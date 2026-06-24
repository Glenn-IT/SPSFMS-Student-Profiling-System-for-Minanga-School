<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('student');

// Fetch student record by LRN
$sStmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
$sStmt->execute([$user['lrn']]);
$student = $sStmt->fetch();

// Fetch grades
$grades = [];
if ($student) {
    $gStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id=? AND school_year='2025-2026' ORDER BY subject");
    $gStmt->execute([$student['id']]);
    $grades = $gStmt->fetchAll();
}

// Announcements
$annStmt = $pdo->query("SELECT * FROM announcements WHERE audience IN ('all','student') ORDER BY posted_at DESC LIMIT 3");
$announcements = $annStmt->fetchAll();

$initial = $student ? strtoupper(substr($student['first_name'],0,1)) : 'S';
$firstName = $student ? $student['first_name'] : ($user['name'] ?? 'Student');
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Dashboard — Student'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student-mobile.css">
</head>
<body>
<div class="student-app">
  <div class="student-header">
    <div class="header-row">
      <div>
        <h6><?= SCHOOL_NAME ?></h6>
        <h5><?= $greeting ?>, <?= htmlspecialchars($firstName) ?>!</h5>
      </div>
      <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;"><?= $initial ?></div>
    </div>
  </div>

  <!-- Profile Summary Card -->
  <?php if ($student): ?>
  <div class="profile-summary-card">
    <div class="profile-avatar"><?= $initial ?></div>
    <div class="profile-info">
      <div class="profile-name"><?= htmlspecialchars($student['last_name'].', '.$student['first_name'].' '.($student['middle_name']??'')) ?></div>
      <div class="profile-meta"><?= htmlspecialchars($student['grade_level']) ?> | Section <?= htmlspecialchars($student['section']) ?></div>
      <div class="profile-lrn">LRN: <?= htmlspecialchars($student['lrn']) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Quick Actions -->
  <div class="mobile-section">
    <div class="mobile-section-title">Quick Actions</div>
    <div class="quick-actions">
      <a href="#grades-section" class="quick-action-btn">
        <div class="qa-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="qa-label">View Grades</div>
      </a>
      <a href="profile.php" class="quick-action-btn">
        <div class="qa-icon"><i class="fas fa-user"></i></div>
        <div class="qa-label">My Profile</div>
      </a>
      <a href="settings.php" class="quick-action-btn">
        <div class="qa-icon"><i class="fas fa-cog"></i></div>
        <div class="qa-label">Settings</div>
      </a>
      <a href="<?= BASE_URL ?>/api/auth/logout.php" class="quick-action-btn" onclick="return confirm('Logout?')">
        <div class="qa-icon" style="background:#fce8e6;color:var(--danger);"><i class="fas fa-sign-out-alt"></i></div>
        <div class="qa-label">Logout</div>
      </a>
    </div>
  </div>

  <!-- Announcements -->
  <?php if (!empty($announcements)): ?>
  <div class="mobile-section">
    <div class="mobile-section-title">Announcements</div>
    <?php foreach ($announcements as $ann): ?>
    <div class="mobile-card">
      <div class="fw-semibold" style="font-size:.88rem;"><?= htmlspecialchars($ann['title']) ?></div>
      <div style="font-size:.8rem;color:var(--gray-600);margin-top:.25rem;"><?= htmlspecialchars($ann['body']) ?></div>
      <div style="font-size:.72rem;color:var(--gray-400);margin-top:.35rem;"><?= date('M j, Y', strtotime($ann['posted_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Grades -->
  <div class="mobile-section" id="grades-section">
    <div class="mobile-section-title">My Grades — S.Y. <?= SCHOOL_YEAR ?></div>
    <?php if (empty($grades)): ?>
    <div class="mobile-card text-center text-muted" style="font-size:.85rem;padding:1.5rem;">No grades recorded yet.</div>
    <?php else: ?>
      <?php foreach ($grades as $g): ?>
      <?php
        $final = $g['final_grade'];
        $color = $final >= 90 ? 'var(--secondary)' : ($final >= 75 ? 'var(--primary)' : 'var(--danger)');
      ?>
      <div class="mobile-card">
        <div class="card-row">
          <span class="card-label"><?= htmlspecialchars($g['subject']) ?></span>
          <span class="card-value fw-bold" style="color:<?= $color ?>;"><?= $final ?? '—' ?></span>
        </div>
        <?php if ($g['remarks']): ?>
        <div style="font-size:.72rem;color:<?= $g['remarks']==='Passed'?'var(--secondary)':'var(--danger)' ?>;margin-top:2px;"><?= $g['remarks'] ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php
        $finals = array_filter(array_column($grades,'final_grade'), fn($v) => $v !== null);
        $avg = count($finals) ? round(array_sum($finals)/count($finals),2) : null;
      ?>
      <?php if ($avg !== null): ?>
      <div class="mobile-card" style="background:var(--primary-light);">
        <div class="card-row">
          <span class="card-label fw-bold">General Average</span>
          <span class="card-value fw-bold" style="color:var(--primary);font-size:1.1rem;"><?= $avg ?></span>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <div style="height:.5rem;"></div>

  <nav class="bottom-nav">
    <a href="dashboard.php" class="bottom-nav-item active"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="#grades-section" class="bottom-nav-item"><i class="fas fa-chart-bar"></i><span>Grades</span></a>
    <a href="profile.php" class="bottom-nav-item"><i class="fas fa-user"></i><span>Profile</span></a>
    <a href="settings.php" class="bottom-nav-item"><i class="fas fa-cog"></i><span>Settings</span></a>
  </nav>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
</body>
</html>
