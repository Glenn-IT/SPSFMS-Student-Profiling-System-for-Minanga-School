<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'settings';
$secQuestions = [
  "What is the name of your first pet?",
  "What is your mother's maiden name?",
  "What city were you born in?",
  "What is the name of your elementary school?",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Settings — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Settings</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Settings</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header"><h3>Account Settings</h3><p>Manage your profile and security settings</p></div>

    <div class="row g-4">
      <!-- Profile -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center py-4">
            <div style="width:72px;height:72px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:var(--primary);margin:0 auto 1rem;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <h6 class="fw-bold" id="display-name"><?= htmlspecialchars($user['name']) ?></h6>
            <div style="font-size:.8rem;color:var(--gray-600);" id="display-email"><?= htmlspecialchars($user['email']) ?></div>
            <span class="badge bg-primary bg-opacity-15 text-primary mt-2">Administrator</span>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <!-- Profile Edit -->
        <div class="card mb-3">
          <div class="card-header"><i class="fas fa-user me-2" style="color:var(--primary);"></i>Profile Information</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" id="p-name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>"></div>
              <div class="col-md-6"><label class="form-label">Email</label><input type="email" id="p-email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"></div>
              <div class="col-12"><button class="btn btn-primary btn-sm" onclick="saveProfile()"><i class="fas fa-save me-2"></i>Save Profile</button></div>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="card mb-3">
          <div class="card-header"><i class="fas fa-lock me-2" style="color:var(--primary);"></i>Change Password</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" id="pw-old" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">New Password</label><input type="password" id="pw-new" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">Confirm New</label><input type="password" id="pw-confirm" class="form-control"></div>
              <div class="col-12"><button class="btn btn-primary btn-sm" onclick="changePassword()"><i class="fas fa-key me-2"></i>Change Password</button></div>
            </div>
          </div>
        </div>

        <!-- Security Q -->
        <div class="card mb-3">
          <div class="card-header"><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>Security Question</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-7"><label class="form-label">Question</label>
                <select id="sec-q" class="form-select">
                  <?php foreach ($secQuestions as $q): ?>
                  <option value="<?= htmlspecialchars($q) ?>"><?= htmlspecialchars($q) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5"><label class="form-label">Answer</label><input type="text" id="sec-a" class="form-control" placeholder="Your answer"></div>
              <div class="col-12"><button class="btn btn-primary btn-sm" onclick="saveSecQuestion()"><i class="fas fa-save me-2"></i>Save Security Question</button></div>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger">
          <div class="card-header text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</div>
          <div class="card-body">
            <a href="<?= BASE_URL ?>/api/auth/logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Logout?')"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
showDesktopOnlyWarning();

async function saveProfile() {
  const res = await fetch(BASE+'/api/accounts/update-profile.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ name: document.getElementById('p-name').value, email: document.getElementById('p-email').value })
  });
  const d = await res.json();
  if (d.ok) {
    document.getElementById('display-name').textContent = d.name;
    document.getElementById('display-email').textContent = d.email;
    showToast('Profile updated!', 'success');
  } else showToast(d.message, 'error');
}

async function changePassword() {
  const res = await fetch(BASE+'/api/auth/change-password.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ old_password: document.getElementById('pw-old').value, new_password: document.getElementById('pw-new').value, confirm_password: document.getElementById('pw-confirm').value })
  });
  const d = await res.json();
  if (d.ok) { showToast('Password changed!', 'success'); ['pw-old','pw-new','pw-confirm'].forEach(id => document.getElementById(id).value=''); }
  else showToast(d.message, 'error');
}

async function saveSecQuestion() {
  const res = await fetch(BASE+'/api/accounts/update-security.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ question: document.getElementById('sec-q').value, answer: document.getElementById('sec-a').value })
  });
  const d = await res.json();
  if (d.ok) showToast('Security question saved!', 'success'); else showToast(d.message, 'error');
}
</script>
</body>
</html>
