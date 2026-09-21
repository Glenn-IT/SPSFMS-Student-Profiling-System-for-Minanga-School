<?php
// $activePage must be set before including this file (e.g. 'dashboard', 'students', 'reports', 'accounts', 'analytics', 'settings')
$activePage = $activePage ?? '';
$user = $_SESSION['user'] ?? [];
$initial = $user ? strtoupper(substr($user['name'], 0, 1)) : 'A';

$navItems = [
  ['page'=>'dashboard',     'icon'=>'fa-tachometer-alt',       'label'=>'Dashboard',            'href'=>BASE_URL.'/views/admin/dashboard.php'],
  ['page'=>'students',      'icon'=>'fa-users',                'label'=>'Student Management',   'href'=>BASE_URL.'/views/admin/students.php'],
  ['page'=>'teachers',      'icon'=>'fa-chalkboard-teacher',   'label'=>'Teacher Management',   'href'=>BASE_URL.'/views/admin/teachers.php'],
  ['page'=>'sections',      'icon'=>'fa-th-large',             'label'=>'Section Management',   'href'=>BASE_URL.'/views/admin/sections.php'],
  ['page'=>'subjects',      'icon'=>'fa-book',                 'label'=>'Subject Management',   'href'=>BASE_URL.'/views/admin/subjects.php'],
  ['page'=>'reports',       'icon'=>'fa-file-alt',             'label'=>'Reports',              'href'=>BASE_URL.'/views/admin/reports.php'],
  ['page'=>'signatories',   'icon'=>'fa-file-signature',        'label'=>'Report Signatories',   'href'=>BASE_URL.'/views/admin/signatories.php'],
  ['page'=>'accounts',      'icon'=>'fa-user-cog',             'label'=>'Account Management',   'href'=>BASE_URL.'/views/admin/accounts.php'],
  ['page'=>'analytics',       'icon'=>'fa-chart-line',           'label'=>'Analytics',            'href'=>BASE_URL.'/views/admin/analytics.php'],
  ['page'=>'announcements',   'icon'=>'fa-bullhorn',             'label'=>'Announcements',        'href'=>BASE_URL.'/views/admin/announcements.php'],
  ['page'=>'settings',        'icon'=>'fa-cog',                  'label'=>'Settings',             'href'=>BASE_URL.'/views/admin/settings.php'],
  ['page'=>'sec_questions', 'icon'=>'fa-shield-alt',           'label'=>'Manage Security QT',   'href'=>BASE_URL.'/views/admin/security-questions.php'],
  ['page'=>'school_years',  'icon'=>'fa-calendar-alt',         'label'=>'Manage School Year',   'href'=>BASE_URL.'/views/admin/school-years.php'],
];

?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="Logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid rgba(255,255,255,.2);" onerror="this.style.display='none'">
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

  <div style="margin-top:auto;padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);">
    <a href="<?= BASE_URL ?>/views/admin/settings.php?tab=developers" class="sidebar-link" style="color:rgba(255,255,255,.75);margin-bottom:.25rem;">
      <i class="fas fa-laptop-code sidebar-icon"></i><span>Developers</span>
    </a>
    <a href="<?= BASE_URL ?>/api/auth/logout.php" class="sidebar-link" style="color:#ff7b7b;" onclick="return confirmLogout(this)">
      <i class="fas fa-sign-out-alt sidebar-icon"></i><span>Logout</span>
    </a>
  </div>
</aside>
