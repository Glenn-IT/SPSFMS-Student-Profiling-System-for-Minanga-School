<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

// Already logged in — redirect to portal
if (!empty($_SESSION['user'])) {
    $map = ['admin'=>BASE_URL.'/views/admin/dashboard.php','teacher'=>BASE_URL.'/views/teacher/dashboard.php','student'=>BASE_URL.'/views/student/dashboard.php'];
    $dest = $map[$_SESSION['user']['role']] ?? BASE_URL.'/views/auth/login.php';
    header('Location: '.$dest);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Welcome'; include __DIR__ . '/includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1.5rem; }
    .portal-container { width:100%; max-width:860px; animation:fadeIn .4s ease; }
    .portal-header { text-align:center; color:#fff; margin-bottom:2.5rem; }
    .portal-header h1 { font-size:2rem; font-weight:800; margin-bottom:.25rem; }
    .portal-header p  { opacity:.8; font-size:.95rem; }
    .portal-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:1.5rem; }
    .portal-card { background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,.2); padding:2rem 1.5rem; text-align:center; text-decoration:none; color:inherit; transition:.25s; }
    .portal-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(0,0,0,.3); }
    .portal-icon { width:72px; height:72px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.8rem; margin:0 auto 1rem; }
    .portal-icon.admin   { background:#e8f0fe; color:#1a73e8; }
    .portal-icon.teacher { background:#e6f4ea; color:#34a853; }
    .portal-icon.student { background:#fef3e2; color:#f59e0b; }
    .portal-card h5 { font-weight:700; font-size:1.05rem; margin-bottom:.25rem; }
    .portal-card p  { font-size:.82rem; color:#5f6368; margin:0; }
    .portal-card .portal-arrow { display:block; margin-top:1rem; font-size:.82rem; font-weight:600; color:#1a73e8; text-decoration:none; cursor:pointer; }
    .portal-card .portal-register { display:block; margin-top:.4rem; font-size:.75rem; text-decoration:underline; }
    .portal-footer { text-align:center; color:rgba(255,255,255,.6); font-size:.78rem; margin-top:2rem; }
  </style>
</head>
<body>
<div class="portal-container">
  <div class="portal-header">
    <div style="font-size:2.5rem;margin-bottom:.5rem;">🎓</div>
    <h1>Student Profiling System</h1>
    <p><?= SCHOOL_NAME ?> · S.Y. <?= SCHOOL_YEAR ?></p>
  </div>

  <div class="portal-cards">
    <div class="portal-card">
      <div class="portal-icon admin"><i class="fas fa-user-shield"></i></div>
      <h5>Administrator</h5>
      <p>Manage students, reports, accounts, and school analytics</p>
      <a href="<?= BASE_URL ?>/views/auth/login.php?role=admin" class="portal-arrow">Enter Portal <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="portal-card">
      <div class="portal-icon teacher"><i class="fas fa-chalkboard-teacher"></i></div>
      <h5>Teacher</h5>
      <p>View student profiles and manage quarterly grades (SF10)</p>
      <a href="<?= BASE_URL ?>/views/auth/login.php?role=teacher" class="portal-arrow" style="color:var(--secondary);">Enter Portal <i class="fas fa-arrow-right ms-1"></i></a>
      <a href="<?= BASE_URL ?>/views/auth/register.php?role=teacher" class="portal-register" style="color:var(--secondary);">Register</a>
    </div>

    <div class="portal-card">
      <div class="portal-icon student"><i class="fas fa-user-graduate"></i></div>
      <h5>Student</h5>
      <p>View your profile, grades, and school announcements</p>
      <a href="<?= BASE_URL ?>/views/auth/login.php?role=student" class="portal-arrow" style="color:#f59e0b;">Enter Portal <i class="fas fa-arrow-right ms-1"></i></a>
      <a href="<?= BASE_URL ?>/views/auth/register.php?role=student" class="portal-register" style="color:#f59e0b;">Register</a>
    </div>
  </div>

  <div class="portal-footer">
    <p>SPSMIS v2.0 · PHP + MySQL · <?= SCHOOL_NAME ?></p>
  </div>
</div>
</body>
</html>
