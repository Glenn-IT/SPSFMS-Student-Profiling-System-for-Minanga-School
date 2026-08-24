<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('student');
$sStmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
$sStmt->execute([$user['lrn']]);
$student = $sStmt->fetch();
$initial = strtoupper(substr($user['name'],0,1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Settings — Student'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student-mobile.css">
</head>
<body>
<div class="student-app">
  <div class="student-header">
    <div class="header-row">
      <div><h6><?= SCHOOL_NAME ?></h6><h5><i class="fas fa-arrow-left me-2" onclick="history.back()" style="cursor:pointer;"></i>Settings</h5></div>
      <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;"><?= $initial ?></div>
    </div>
  </div>

  <div style="background:#fff;margin:.75rem;border-radius:12px;padding:1rem;box-shadow:0 1px 6px rgba(0,0,0,.08);display:flex;align-items:center;gap:.75rem;">
    <div style="width:48px;height:48px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:var(--primary);"><?= $initial ?></div>
    <div>
      <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
      <div style="font-size:.78rem;color:var(--gray-600);"><?= $student ? htmlspecialchars($student['grade_level'].' | Section '.$student['section']) : 'Student' ?></div>
    </div>
  </div>

  <div class="settings-group">
    <div class="mobile-section-title">Security</div>
    <div style="background:#fff;border-radius:12px;padding:1rem;box-shadow:0 1px 6px rgba(0,0,0,.07);margin-bottom:.6rem;">
      <div style="font-size:.85rem;font-weight:600;margin-bottom:.75rem;"><i class="fas fa-lock me-2" style="color:var(--primary);"></i>Change Password</div>
      <div class="mb-2"><label class="form-label" style="font-size:.8rem;">Current Password</label><input type="password" id="s-old" class="form-control form-control-sm"></div>
      <div class="mb-2"><label class="form-label" style="font-size:.8rem;">New Password</label><input type="password" id="s-new" class="form-control form-control-sm"></div>
      <div class="mb-3"><label class="form-label" style="font-size:.8rem;">Confirm New Password</label><input type="password" id="s-confirm" class="form-control form-control-sm"></div>
      <button class="btn btn-primary btn-sm w-100" onclick="changePassword()"><i class="fas fa-key me-2"></i>Change Password</button>
    </div>
  </div>

  <div class="settings-group">
    <div class="mobile-section-title">About</div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">System</span><span class="card-value" style="font-size:.82rem;">SPSMIS v2.0</span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">School Year</span><span class="card-value"><?= SCHOOL_YEAR ?></span></div></div>
    <div class="mobile-card"><div class="card-row"><span class="card-label">School</span><span class="card-value" style="font-size:.8rem;"><?= SCHOOL_NAME ?></span></div></div>
  </div>

  <div class="settings-group">
    <a class="settings-item danger" onclick="doLogout()" style="cursor:pointer;">
      <div class="si-icon"><i class="fas fa-sign-out-alt"></i></div>
      <div class="si-label">Logout</div>
      <i class="fas fa-chevron-right si-arrow"></i>
    </a>
  </div>

  <div style="height:.5rem;"></div>
  <nav class="bottom-nav">
    <a href="dashboard.php" class="bottom-nav-item"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="dashboard.php#grades-section" class="bottom-nav-item"><i class="fas fa-chart-bar"></i><span>Grades</span></a>
    <a href="profile.php" class="bottom-nav-item"><i class="fas fa-user"></i><span>Profile</span></a>
    <a href="settings.php" class="bottom-nav-item active"><i class="fas fa-cog"></i><span>Settings</span></a>
  </nav>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
async function changePassword() {
  const payload = {
    old_password:     document.getElementById('s-old').value,
    new_password:     document.getElementById('s-new').value,
    confirm_password: document.getElementById('s-confirm').value,
  };
  const res = await fetch(BASE + '/api/auth/change-password.php', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
  });
  const data = await res.json();
  if (data.ok) {
    showToast('Password changed successfully!', 'success');
    ['s-old','s-new','s-confirm'].forEach(id => document.getElementById(id).value = '');
  } else {
    showToast(data.message, 'error');
  }
}
function doLogout() {
  confirmModal('Logout', 'Are you sure you want to logout?', () => {
    window.location.href = BASE + '/api/auth/logout.php';
  });
}
</script>
</body>
</html>
