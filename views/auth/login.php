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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Login'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1rem; }
    .login-card { background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); padding:2.5rem 2rem; width:100%; max-width:400px; animation:fadeIn .35s ease; }
    .login-header { text-align:center; margin-bottom:1.75rem; }
    .login-icon { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin:0 auto .75rem; background:var(--primary-light); color:var(--primary); }
    .login-header h4 { font-weight:700; color:var(--dark); font-size:1.15rem; }
    .login-header p  { font-size:.8rem; color:var(--gray-600); }
    .toggle-pw { cursor:pointer; color:var(--gray-400); font-size:.85rem; }
    .toggle-pw:hover { color:var(--gray-600); }
    /* Hide native browser password-reveal icons so they don't overlap our custom toggle-pw button */
    input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display:none; }
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-strong-password-auto-fill-button { display:none !important; visibility:hidden; }
    .btn-login { width:100%; padding:.65rem; font-size:.95rem; font-weight:600; border-radius:8px; background:var(--primary); border-color:var(--primary); }
    .back-link { display:block; text-align:center; margin-top:1rem; font-size:.82rem; color:var(--gray-600); text-decoration:none; }
    .back-link:hover { color:var(--primary); }
    .demo-hint { background:var(--gray-100); border-radius:8px; padding:.6rem .9rem; font-size:.78rem; color:var(--gray-600); margin-top:1rem; }
    .error-box { background:#fce8e6; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--danger); display:none; margin-bottom:1rem; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="login-header">
    <div class="login-icon"><i class="fas fa-user-shield"></i></div>
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
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
        <span class="input-group-text bg-white toggle-pw" onclick="togglePw()"><i class="fas fa-eye" id="pw-eye"></i></span>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-login">
      <span id="btn-text"><i class="fas fa-sign-in-alt me-2"></i>Login</span>
      <span id="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin me-2"></i>Signing in...</span>
    </button>
  </form>

  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    <a href="forgot-password.php" style="color:var(--primary);text-decoration:none;font-weight:500;">Forgot Password?</a>
  </div>

  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    Don't have an account?
    <a href="register.php?role=teacher" style="color:var(--primary);text-decoration:none;font-weight:500;">Register as Teacher</a>
    ·
    <a href="register.php?role=student" style="color:var(--primary);text-decoration:none;font-weight:500;">Register as Student</a>
  </div>

</div>

<script>
  const BASE = '<?= BASE_URL ?>';
  const ROLE = 'any';
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
    const username    = document.getElementById('username');
    const password    = document.getElementById('password');

    username.value = '';
    password.value = '';
    username.disabled = true;
    password.disabled = true;
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
        username.disabled = false;
        password.disabled = false;
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
