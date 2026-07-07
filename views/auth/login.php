<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

// Already logged in — redirect
if (!empty($_SESSION['user'])) {
    redirectByRole($_SESSION['user']['role']);
}
function redirectByRole(string $role): void {
    $map = ['admin'=>BASE_URL.'/views/admin/dashboard.php','teacher'=>BASE_URL.'/views/teacher/dashboard.php','student'=>BASE_URL.'/views/student/dashboard.php'];
    header('Location: '.($map[$role] ?? BASE_URL.'/views/auth/login.php')); exit;
}

$role = $_GET['role'] ?? 'admin';
if (!in_array($role, ['admin','teacher','student'])) $role = 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = ucfirst($role) . ' Login'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1rem; }
    .login-card { background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); padding:2.5rem 2rem; width:100%; max-width:400px; animation:fadeIn .35s ease; }
    .role-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .9rem; border-radius:20px; font-size:.78rem; font-weight:600; margin-bottom:1.25rem; }
    .role-badge.admin   { background:var(--primary-light); color:var(--primary); }
    .role-badge.teacher { background:var(--secondary-light); color:var(--secondary); }
    .role-badge.student { background:var(--warning-light); color:#b06a00; }
    .login-header { text-align:center; margin-bottom:1.75rem; }
    .login-icon { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin:0 auto .75rem; }
    .login-icon.admin   { background:var(--primary-light); color:var(--primary); }
    .login-icon.teacher { background:var(--secondary-light); color:var(--secondary); }
    .login-icon.student { background:var(--warning-light); color:#b06a00; }
    .login-header h4 { font-weight:700; color:var(--dark); font-size:1.15rem; }
    .login-header p  { font-size:.8rem; color:var(--gray-600); }
    .password-wrapper { position:relative; }
    .password-wrapper .toggle-pw { position:absolute; right:.75rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--gray-400); cursor:pointer; padding:0; font-size:.9rem; }
    .btn-login { width:100%; padding:.65rem; font-size:.95rem; font-weight:600; border-radius:8px; }
    .btn-login.admin-btn   { background:var(--primary); border-color:var(--primary); }
    .btn-login.teacher-btn { background:var(--secondary); border-color:var(--secondary); }
    .btn-login.student-btn { background:#b06a00; border-color:#b06a00; }
    .back-link { display:block; text-align:center; margin-top:1rem; font-size:.82rem; color:var(--gray-600); text-decoration:none; }
    .back-link:hover { color:var(--primary); }
    .demo-hint { background:var(--gray-100); border-radius:8px; padding:.6rem .9rem; font-size:.78rem; color:var(--gray-600); margin-top:1rem; }
    .error-box { background:#fce8e6; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--danger); display:none; margin-bottom:1rem; }
  </style>
</head>
<body>
<?php
$cfg = [
  'admin'   => ['label'=>'Administrator','icon'=>'fa-user-shield'],
  'teacher' => ['label'=>'Teacher',      'icon'=>'fa-chalkboard-teacher'],
  'student' => ['label'=>'Student',      'icon'=>'fa-user-graduate'],
][$role];
?>
<div class="login-card">
  <div class="login-header">
    <div class="login-icon <?= $role ?>"><i class="fas <?= $cfg['icon'] ?>"></i></div>
    <span class="role-badge <?= $role ?>"><i class="fas fa-circle" style="font-size:.4rem;"></i> <?= $cfg['label'] ?></span>
    <h4>Welcome Back</h4>
    <p><?= SCHOOL_NAME ?> — SPSMIS</p>
  </div>

  <div class="error-box" id="error-box">
    <i class="fas fa-exclamation-circle me-1"></i> <span id="error-msg">Invalid username or password.</span>
  </div>
  <div class="error-box" id="lockout-box" style="background:#fff3cd;color:#856404;">
    <i class="fas fa-clock me-1"></i> Too many failed attempts. Try again in <strong id="lockout-countdown">15</strong>s.
  </div>

  <form id="login-form" novalidate>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-user" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="text" id="username" class="form-control" placeholder="Enter username" required autocomplete="username">
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <div class="input-group password-wrapper">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
        <button type="button" class="toggle-pw" onclick="togglePw()"><i class="fas fa-eye" id="pw-eye"></i></button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-login <?= $role ?>-btn">
      <span id="btn-text"><i class="fas fa-sign-in-alt me-2"></i>Login</span>
      <span id="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin me-2"></i>Signing in...</span>
    </button>
  </form>

  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    <a href="forgot-password.php?role=<?= $role ?>" style="color:var(--primary);text-decoration:none;font-weight:500;">Forgot Password?</a>
  </div>

  <?php if ($role !== 'admin'): ?>
  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    Don't have an account? <a href="register.php?role=<?= $role ?>" style="color:var(--primary);text-decoration:none;font-weight:500;">Register</a>
  </div>
  <?php endif; ?>

  <a href="<?= BASE_URL ?>/index.php" class="back-link"><i class="fas fa-arrow-left me-1"></i>Back to portal selection</a>
</div>

<script>
  const BASE = '<?= BASE_URL ?>';
  const ROLE = '<?= $role ?>';
  const MAX_ATTEMPTS = 3;
  const LOCKOUT_SECS = 15;

  let failCount = 0;
  let lockoutTimer = null;

  function togglePw() {
    const pw  = document.getElementById('password');
    const eye = document.getElementById('pw-eye');
    if (pw.type === 'password') { pw.type = 'text'; eye.className = 'fas fa-eye-slash'; }
    else { pw.type = 'password'; eye.className = 'fas fa-eye'; }
  }

  function startLockout() {
    const btn         = document.querySelector('button[type="submit"]');
    const errorBox    = document.getElementById('error-box');
    const lockoutBox  = document.getElementById('lockout-box');
    const countdown   = document.getElementById('lockout-countdown');

    btn.disabled = true;
    errorBox.style.display  = 'none';
    lockoutBox.style.display = 'block';
    let secs = LOCKOUT_SECS;
    countdown.textContent = secs;

    lockoutTimer = setInterval(() => {
      secs--;
      countdown.textContent = secs;
      if (secs <= 0) {
        clearInterval(lockoutTimer);
        lockoutTimer = null;
        failCount = 0;
        btn.disabled = false;
        lockoutBox.style.display = 'none';
      }
    }, 1000);
  }

  function setLoading(loading) {
    document.getElementById('btn-text').style.display    = loading ? 'none'   : 'inline';
    document.getElementById('btn-spinner').style.display = loading ? 'inline' : 'none';
  }

  function showError(msg) {
    const box = document.getElementById('error-box');
    document.getElementById('error-msg').textContent = msg;
    box.style.display = 'block';
  }

  document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (lockoutTimer) return;

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    document.getElementById('error-box').style.display = 'none';
    setLoading(true);

    try {
      const res  = await fetch(BASE + '/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password, role: ROLE })
      });
      const data = await res.json();

      if (data.ok) {
        const dest = { admin: BASE+'/views/admin/dashboard.php', teacher: BASE+'/views/teacher/dashboard.php', student: BASE+'/views/student/dashboard.php' };
        window.location.replace(dest[data.role] || BASE+'/index.php');
      } else {
        failCount++;
        setLoading(false);
        if (failCount >= MAX_ATTEMPTS) {
          startLockout();
        } else {
          showError(data.message + (failCount > 0 ? ` (${MAX_ATTEMPTS - failCount} attempt${MAX_ATTEMPTS - failCount > 1 ? 's' : ''} left)` : ''));
        }
      }
    } catch (err) {
      setLoading(false);
      showError('Network error. Please try again.');
    }
  });
</script>
</body>
</html>
