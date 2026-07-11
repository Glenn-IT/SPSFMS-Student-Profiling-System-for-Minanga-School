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
    <div class="sidebar-logo" style="background:rgba(52,168,83,.2);color:#34a853;"><i class="fas fa-chalkboard-teacher"></i></div>
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
    <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;">
      <div style="width:34px;height:34px;background:rgba(52,168,83,.25);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;"><?= htmlspecialchars($initial) ?></div>
      <div style="overflow:hidden;">
        <div style="font-size:.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($user['name'] ?? 'Teacher') ?></div>
        <div style="font-size:.68rem;opacity:.6;"><?= htmlspecialchars($user['position'] ?? 'Teacher') ?></div>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/api/auth/logout.php" class="sidebar-link" style="color:#ff7b7b;" onclick="return confirm('Logout?')">
      <i class="fas fa-sign-out-alt sidebar-icon"></i><span>Logout</span>
    </a>
  </div>
</aside>
