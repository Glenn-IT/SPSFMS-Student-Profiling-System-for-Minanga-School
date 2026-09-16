<?php
$activePage = $activePage ?? '';
$user = $_SESSION['user'] ?? [];
$initial = $user ? strtoupper(substr($user['name'], 0, 1)) : 'T';

$navItems = [
  ['page'=>'dashboard',        'icon'=>'fa-tachometer-alt',  'label'=>'Dashboard',          'href'=>BASE_URL.'/views/teacher/dashboard.php'],
  ['page'=>'student-profiles', 'icon'=>'fa-users',           'label'=>'Student Profiles',   'href'=>BASE_URL.'/views/teacher/student-profiles.php'],
  ['page'=>'grades',           'icon'=>'fa-clipboard-list',  'label'=>'Grade Management',   'href'=>BASE_URL.'/views/teacher/grades.php'],
  ['page'=>'reports',          'icon'=>'fa-file-alt',        'label'=>'Reports',            'href'=>BASE_URL.'/views/teacher/reports.php'],
  ['page'=>'settings',         'icon'=>'fa-cog',             'label'=>'Settings',           'href'=>BASE_URL.'/views/teacher/settings.php'],
];
?>
<aside class="sidebar" style="background:#1e3a2f;">
  <div class="sidebar-brand">
    <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="Logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid rgba(255,255,255,.2);" onerror="this.style.display='none'">
    <div>
      <div style="font-weight:700;font-size:.9rem;line-height:1.1;">Minanga IS</div>
      <div style="font-size:.68rem;opacity:.6;">Teacher Portal</div>
    </div>
  </div>

  <div class="sidebar-section">MAIN MENU</div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
    <a href="<?= $item['href'] ?>" class="sidebar-link<?= $activePage === $item['page'] ? ' active' : '' ?>" style="<?= $activePage === $item['page'] ? 'background:#34a853;border-right-color:#fff;' : '' ?>">
      <i class="fas <?= $item['icon'] ?> sidebar-icon"></i>
      <span><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div style="margin-top:auto;padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);">
    <a href="<?= BASE_URL ?>/views/teacher/settings.php?tab=developers" class="sidebar-link" style="color:rgba(255,255,255,.75);margin-bottom:.25rem;">
      <i class="fas fa-laptop-code sidebar-icon"></i><span>Developers</span>
    </a>
    <a href="<?= BASE_URL ?>/api/auth/logout.php" class="sidebar-link" style="color:#ff7b7b;" onclick="return confirmLogout(this)">
      <i class="fas fa-sign-out-alt sidebar-icon"></i><span>Logout</span>
    </a>
  </div>
</aside>
