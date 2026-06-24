<?php
// $activePage must be set before including this file (e.g. 'dashboard', 'students', 'reports', 'accounts', 'analytics', 'settings')
$activePage = $activePage ?? '';
$user = $_SESSION['user'] ?? [];
$initial = $user ? strtoupper(substr($user['name'], 0, 1)) : 'A';

$navItems = [
  ['page'=>'dashboard', 'icon'=>'fa-tachometer-alt',  'label'=>'Dashboard',           'href'=>BASE_URL.'/views/admin/dashboard.php'],
  ['page'=>'students',  'icon'=>'fa-users',            'label'=>'Student Management',  'href'=>BASE_URL.'/views/admin/students.php'],
  ['page'=>'reports',   'icon'=>'fa-file-alt',         'label'=>'Reports',             'href'=>BASE_URL.'/views/admin/reports.php'],
  ['page'=>'accounts',  'icon'=>'fa-user-cog',         'label'=>'Account Management',  'href'=>BASE_URL.'/views/admin/accounts.php'],
  ['page'=>'analytics', 'icon'=>'fa-chart-line',       'label'=>'Analytics',           'href'=>BASE_URL.'/views/admin/analytics.php'],
  ['page'=>'settings',  'icon'=>'fa-cog',              'label'=>'Settings',            'href'=>BASE_URL.'/views/admin/settings.php'],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo"><i class="fas fa-graduation-cap"></i></div>
    <div>
      <div style="font-weight:700;font-size:.9rem;line-height:1.1;">Minanga IS</div>
      <div style="font-size:.68rem;opacity:.6;">Student Profiling System</div>
    </div>
  </div>

  <div class="sidebar-section">MAIN MENU</div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
    <a href="<?= $item['href'] ?>" class="sidebar-link<?= $activePage === $item['page'] ? ' active' : '' ?>">
      <i class="fas <?= $item['icon'] ?> sidebar-icon"></i>
      <span><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-section" style="margin-top:auto;">SESSION</div>
  <div style="padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);">
    <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;">
      <div style="width:34px;height:34px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;"><?= htmlspecialchars($initial) ?></div>
      <div style="overflow:hidden;">
        <div style="font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
        <div style="font-size:.68rem;opacity:.6;">Administrator</div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/api/auth/logout.php" class="sidebar-link" style="color:#ff7b7b;" onclick="return confirm('Logout?')">
      <i class="fas fa-sign-out-alt sidebar-icon"></i><span>Logout</span>
    </a>
  </div>
</aside>
