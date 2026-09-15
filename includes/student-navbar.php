<?php
// Shared Student Navigation Component (Responsive: Desktop Topbar + Mobile Top/Bottom Nav)
$activeNav = $activeNav ?? 'dashboard';
$user = $user ?? ($_SESSION['user'] ?? []);
$userName = $user['name'] ?? 'Student';
$initial = $initial ?? (strtoupper(substr($userName, 0, 1)) ?: 'S');
$gradeInfo = '';
if (isset($student) && $student) {
    $gradeInfo = htmlspecialchars($student['grade_level'] . ' - Section ' . $student['section']);
} elseif (!empty($user['grade_level'])) {
    $gradeInfo = htmlspecialchars($user['grade_level'] . (!empty($user['section']) ? ' - Section ' . $user['section'] : ''));
}
$navTitle = $navTitle ?? 'Dashboard';
$showBack = $showBack ?? false;
?>

<!-- ── Desktop Top Navigation Bar (>= 768px) ── -->
<header class="student-desktop-navbar">
  <div class="desktop-navbar-inner">
    <a href="<?= BASE_URL ?>/views/student/dashboard.php" class="desktop-brand">
      <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="Logo" class="student-logo" onerror="this.style.display='none'">
      <div>
        <div class="desktop-brand-title"><?= SCHOOL_NAME ?></div>
        <div class="desktop-brand-sub">
          <span class="portal-badge">Student Portal</span>
          <span class="text-muted ms-1">S.Y. <?= SCHOOL_YEAR ?></span>
        </div>
      </div>
    </a>

    <nav class="desktop-nav-menu">
      <a href="<?= BASE_URL ?>/views/student/dashboard.php" class="desktop-nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-home me-1"></i> Dashboard
      </a>
      <a href="<?= BASE_URL ?>/views/student/dashboard.php#grades-section" class="desktop-nav-link <?= $activeNav === 'grades' ? 'active' : '' ?>">
        <i class="fas fa-chart-bar me-1"></i> My Grades
      </a>
      <a href="<?= BASE_URL ?>/views/student/profile.php" class="desktop-nav-link <?= $activeNav === 'profile' ? 'active' : '' ?>">
        <i class="fas fa-user me-1"></i> My Profile
      </a>
      <a href="<?= BASE_URL ?>/views/student/settings.php" class="desktop-nav-link <?= $activeNav === 'settings' ? 'active' : '' ?>">
        <i class="fas fa-cog me-1"></i> Settings
      </a>
    </nav>

    <div class="desktop-user-menu">
      <a href="<?= BASE_URL ?>/views/student/profile.php" class="desktop-user-pill" title="View Profile">
        <div class="desktop-user-avatar"><?= $initial ?></div>
        <div class="desktop-user-info">
          <div class="desktop-user-name"><?= htmlspecialchars($userName) ?></div>
          <?php if ($gradeInfo): ?>
            <div class="desktop-user-grade"><?= $gradeInfo ?></div>
          <?php endif; ?>
        </div>
      </a>
      <a href="<?= BASE_URL ?>/api/auth/logout.php" class="desktop-logout-btn" onclick="return confirmLogout(this)" title="Sign Out">
        <i class="fas fa-sign-out-alt me-1"></i> Logout
      </a>
    </div>
  </div>
</header>

<!-- ── Mobile Top Header (< 768px) ── -->
<div class="student-header student-mobile-header">
  <div class="header-row">
    <div class="d-flex align-items-center gap-2">
      <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="Logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid rgba(255,255,255,0.4);" onerror="this.style.display='none'">
      <div>
        <h6><?= SCHOOL_NAME ?></h6>
        <h5>
          <?php if ($showBack): ?>
            <i class="fas fa-arrow-left me-2" onclick="history.back()" style="cursor:pointer;" title="Go Back"></i>
          <?php endif; ?>
          <?= htmlspecialchars($navTitle) ?>
        </h5>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/views/student/profile.php" style="text-decoration:none;color:#fff;">
      <div class="mobile-header-avatar"><?= $initial ?></div>
    </a>
  </div>
</div>

<!-- ── Mobile Bottom Navigation Bar (< 768px) ── -->
<nav class="bottom-nav">
  <a href="<?= BASE_URL ?>/views/student/dashboard.php" class="bottom-nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
    <i class="fas fa-home"></i><span>Home</span>
  </a>
  <a href="<?= BASE_URL ?>/views/student/dashboard.php#grades-section" class="bottom-nav-item <?= $activeNav === 'grades' ? 'active' : '' ?>">
    <i class="fas fa-chart-bar"></i><span>Grades</span>
  </a>
  <a href="<?= BASE_URL ?>/views/student/profile.php" class="bottom-nav-item <?= $activeNav === 'profile' ? 'active' : '' ?>">
    <i class="fas fa-user"></i><span>Profile</span>
  </a>
  <a href="<?= BASE_URL ?>/views/student/settings.php" class="bottom-nav-item <?= $activeNav === 'settings' ? 'active' : '' ?>">
    <i class="fas fa-cog"></i><span>Settings</span>
  </a>
</nav>
