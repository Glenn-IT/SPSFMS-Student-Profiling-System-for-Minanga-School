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

<?php
$activeNav = 'settings';
$navTitle = 'Settings';
$showBack = true;
include __DIR__ . '/../../includes/student-navbar.php';
?>

<div class="student-app">

  <!-- Desktop Header / Breadcrumb -->
  <div class="student-hero-banner d-none d-md-flex">
    <div class="hero-text">
      <h4><i class="fas fa-user-cog me-2"></i>Account & Security Settings</h4>
      <p class="mb-0">Manage your student credentials, account access security, and school system information.</p>
    </div>
    <div class="hero-avatar-wrap">
      <?= $initial ?>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: User Profile Overview & System Info -->
    <div class="col-12 col-lg-5">
      
      <!-- Account Identity Card -->
      <div class="desktop-card">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="profile-avatar"><?= $initial ?></div>
          <div>
            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['name']) ?></h6>
            <div class="text-muted small"><?= $student ? htmlspecialchars($student['grade_level'] . ' | Section ' . $student['section']) : 'Enrolled Student' ?></div>
            <div class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1">Student Account</div>
          </div>
        </div>
        <div class="border-top pt-2 mt-2">
          <div class="d-flex justify-content-between py-2 border-bottom small">
            <span class="text-muted">Username:</span>
            <span class="fw-bold font-monospace text-dark"><?= htmlspecialchars($user['username'] ?? '') ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom small">
            <span class="text-muted">LRN:</span>
            <span class="fw-bold font-monospace text-dark"><?= htmlspecialchars($user['lrn'] ?? ($student['lrn'] ?? '—')) ?></span>
          </div>
          <div class="d-flex justify-content-between py-2 small">
            <span class="text-muted">Account Status:</span>
            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
          </div>
        </div>
      </div>

      <!-- System & School Information Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-info-circle text-primary"></i> About System</h6>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">System Version:</span>
          <span class="fw-semibold text-dark">SPSMIS v2.0 (Full-Stack)</span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">School Year:</span>
          <span class="fw-semibold text-dark">S.Y. <?= SCHOOL_YEAR ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">School Name:</span>
          <span class="fw-semibold text-dark text-truncate ms-2"><?= SCHOOL_NAME ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 small">
          <span class="text-muted">School Address:</span>
          <span class="fw-semibold text-dark text-truncate ms-2"><?= SCHOOL_ADDRESS ?></span>
        </div>
      </div>

      <!-- System Developers Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-laptop-code text-primary"></i> System Developers</h6>
          <span class="badge bg-primary bg-opacity-10 text-primary">BSIT 4th Year</span>
        </div>
        <div class="d-flex flex-column gap-3">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= BASE_URL ?>/img/Bea.jpg" alt="Bea O. Eneres" class="rounded-circle shadow-sm" style="width:48px;height:48px;object-fit:cover;border:2px solid var(--primary-light,#e8f0fe);">
            <div class="overflow-hidden">
              <div class="fw-bold text-dark small">Bea O. Eneres</div>
              <div class="text-muted" style="font-size:.72rem;">BSIT 4th Year · 09974996082</div>
              <div class="text-truncate" style="font-size:.72rem;"><a href="mailto:beaeneres2704@gmail.com" class="text-secondary text-decoration-none">beaeneres2704@gmail.com</a></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 border-top pt-2">
            <img src="<?= BASE_URL ?>/img/Ritchilyn.jpg" alt="Ritchilyn A. Orpilla" class="rounded-circle shadow-sm" style="width:48px;height:48px;object-fit:cover;border:2px solid var(--primary-light,#e8f0fe);">
            <div class="overflow-hidden">
              <div class="fw-bold text-dark small">Ritchilyn A. Orpilla</div>
              <div class="text-muted" style="font-size:.72rem;">BSIT 4th Year · 09531124253</div>
              <div class="text-truncate" style="font-size:.72rem;"><a href="mailto:ritchilynorpilla@gmail.com" class="text-secondary text-decoration-none">ritchilynorpilla@gmail.com</a></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 border-top pt-2">
            <img src="<?= BASE_URL ?>/img/Roque.jpg" alt="Christian Roque" class="rounded-circle shadow-sm" style="width:48px;height:48px;object-fit:cover;border:2px solid var(--primary-light,#e8f0fe);">
            <div class="overflow-hidden">
              <div class="fw-bold text-dark small">Christian Roque</div>
              <div class="text-muted" style="font-size:.72rem;">BSIT 4th Year · CSU Piat Campus</div>
            </div>
          </div>
        </div>
        <button class="btn btn-outline-primary btn-sm w-100 mt-3" onclick="openDevelopersModal(event)">
          <i class="fas fa-id-card me-1"></i> View Full Developer Profiles
        </button>
      </div>

    </div>

    <!-- Right Column: Password Management & Session -->
    <div class="col-12 col-lg-7">

      <!-- Change Password Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-lock text-primary"></i> Change Account Password</h6>
        </div>
        <p class="text-muted small mb-3">Ensure your new password contains at least 6 characters. Do not share your login credentials with others.</p>
        
        <form onsubmit="event.preventDefault(); changePassword();">
          <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Current Password</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="fas fa-key"></i></span>
              <input type="password" id="s-old" class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
              <span class="input-group-text bg-white toggle-pw" onclick="togglePw('s-old','s-old-eye')" title="Toggle password"><i class="fas fa-eye" id="s-old-eye"></i></span>
            </div>
          </div>
          
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold small text-secondary">New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-shield-alt"></i></span>
                <input type="password" id="s-new" class="form-control" placeholder="At least 6 characters" required autocomplete="new-password">
                <span class="input-group-text bg-white toggle-pw" onclick="togglePw('s-new','s-new-eye')" title="Toggle password"><i class="fas fa-eye" id="s-new-eye"></i></span>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold small text-secondary">Confirm New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-check-double"></i></span>
                <input type="password" id="s-confirm" class="form-control" placeholder="Re-enter new password" required autocomplete="new-password">
                <span class="input-group-text bg-white toggle-pw" onclick="togglePw('s-confirm','s-confirm-eye')" title="Toggle password"><i class="fas fa-eye" id="s-confirm-eye"></i></span>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" id="save-pwd-btn" class="btn btn-primary px-4 fw-bold">
              <i class="fas fa-save me-1"></i> Update Password
            </button>
          </div>
        </form>
      </div>

      <!-- Sign Out / Session Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title text-danger"><i class="fas fa-sign-out-alt"></i> Account Session</h6>
        </div>
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="fw-semibold text-dark">Sign Out of Student Portal</div>
            <div class="text-muted small">Remember to sign out when using shared or school computer laboratory devices.</div>
          </div>
          <button class="btn btn-outline-danger fw-bold px-3 py-2" onclick="doLogout()">
            <i class="fas fa-power-off me-1"></i> Logout
          </button>
        </div>
      </div>

    </div>
  </div>

</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
async function changePassword() {
  const oldPwd = document.getElementById('s-old').value;
  const newPwd = document.getElementById('s-new').value;
  const confirmPwd = document.getElementById('s-confirm').value;

  if (!oldPwd || !newPwd || !confirmPwd) {
    showToast('Please fill in all password fields.', 'error');
    return;
  }

  if (newPwd.length < 6) {
    showToast('New password must be at least 6 characters.', 'error');
    return;
  }

  if (newPwd !== confirmPwd) {
    showToast('New password and confirmation do not match.', 'error');
    return;
  }

  const btn = document.getElementById('save-pwd-btn');
  const origText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';

  try {
    const payload = {
      old_password:     oldPwd,
      new_password:     newPwd,
      confirm_password: confirmPwd,
    };
    const res = await fetch(BASE + '/api/auth/change-password.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.ok) {
      showToast('Password changed successfully!', 'success');
      ['s-old','s-new','s-confirm'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; el.type = 'password'; }
      });
      ['s-old-eye','s-new-eye','s-confirm-eye'].forEach(id => {
        const eye = document.getElementById(id);
        if (eye) eye.className = 'fas fa-eye';
      });
    } else {
      showToast(data.message || 'Failed to change password.', 'error');
    }
  } catch (e) {
    showToast('A network error occurred while updating password.', 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = origText;
  }
}

function doLogout() {
  confirmModal('Logout', 'Are you sure you want to log out of your student portal?', () => {
    window.location.href = BASE + '/api/auth/logout.php';
  });
}
</script>
<?php include __DIR__ . '/../../includes/developers-modal.php'; ?>
</body>
</html>
