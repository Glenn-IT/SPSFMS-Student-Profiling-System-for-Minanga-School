<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

// Already logged in — redirect
if (!empty($_SESSION['user'])) {
    $map = ['admin'=>BASE_URL.'/views/admin/dashboard.php','teacher'=>BASE_URL.'/views/teacher/dashboard.php','student'=>BASE_URL.'/views/student/dashboard.php'];
    header('Location: '.($map[$_SESSION['user']['role']] ?? BASE_URL.'/views/auth/login.php')); exit;
}

$role = $_GET['role'] ?? 'student';
if (!in_array($role, ['teacher', 'student'])) $role = 'student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = ucfirst($role) . ' Registration'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1.5rem 1rem; }
    .register-card { background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); padding:2.5rem 2rem; width:100%; max-width:440px; animation:fadeIn .35s ease; }
    .role-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .9rem; border-radius:20px; font-size:.78rem; font-weight:600; margin-bottom:1.25rem; }
    .role-badge.teacher { background:var(--secondary-light); color:var(--secondary); }
    .role-badge.student { background:var(--warning-light); color:#b06a00; }
    .login-header { text-align:center; margin-bottom:1.75rem; }
    .login-icon { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin:0 auto .75rem; }
    .login-icon.teacher { background:var(--secondary-light); color:var(--secondary); }
    .login-icon.student { background:var(--warning-light); color:#b06a00; }
    .login-header h4 { font-weight:700; color:var(--dark); font-size:1.15rem; }
    .login-header p  { font-size:.8rem; color:var(--gray-600); }
    .toggle-pw { cursor:pointer; color:var(--gray-400); font-size:.85rem; }
    .toggle-pw:hover { color:var(--gray-600); }
    input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display:none; }
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-strong-password-auto-fill-button { display:none !important; visibility:hidden; }
    .btn-login { width:100%; padding:.65rem; font-size:.95rem; font-weight:600; border-radius:8px; }
    .btn-login.teacher-btn { background:var(--secondary); border-color:var(--secondary); }
    .btn-login.student-btn { background:#b06a00; border-color:#b06a00; }
    .back-link { display:block; text-align:center; margin-top:1rem; font-size:.82rem; color:var(--gray-600); text-decoration:none; }
    .back-link:hover { color:var(--primary); }
    .error-box { background:#fce8e6; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--danger); display:none; margin-bottom:1rem; }
    .success-box { background:#e6f4ea; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--secondary); display:none; margin-bottom:1rem; }
    .role-toggle { display:flex; gap:.5rem; margin-bottom:1.5rem; }
    .role-toggle a { flex:1; text-align:center; padding:.45rem; border-radius:8px; font-size:.82rem; font-weight:600; text-decoration:none; border:1px solid var(--gray-200); color:var(--gray-600); }
    .role-toggle a.active.teacher { background:var(--secondary-light); color:var(--secondary); border-color:var(--secondary); }
    .role-toggle a.active.student { background:var(--warning-light); color:#b06a00; border-color:#b06a00; }
  </style>
</head>
<body>
<?php
$cfg = [
  'teacher' => ['label'=>'Teacher', 'icon'=>'fa-chalkboard-teacher'],
  'student' => ['label'=>'Student', 'icon'=>'fa-user-graduate'],
][$role];
?>
<div class="register-card">
  <div class="login-header">
    <div class="login-icon <?= $role ?>"><i class="fas <?= $cfg['icon'] ?>"></i></div>
    <span class="role-badge <?= $role ?>"><i class="fas fa-circle" style="font-size:.4rem;"></i> <?= $cfg['label'] ?></span>
    <h4>Create Account</h4>
    <p><?= SCHOOL_NAME ?> — SPSMIS</p>
  </div>

  <div class="role-toggle">
    <a href="?role=teacher" class="<?= $role === 'teacher' ? 'active teacher' : '' ?>">Teacher</a>
    <a href="?role=student" class="<?= $role === 'student' ? 'active student' : '' ?>">Student</a>
  </div>

  <div class="error-box" id="error-box">
    <i class="fas fa-exclamation-circle me-1"></i> <span id="error-msg">Something went wrong.</span>
  </div>
  <div class="success-box" id="success-box">
    <i class="fas fa-check-circle me-1"></i> <span id="success-msg">Account created.</span>
  </div>

  <form id="register-form" novalidate>
    <?php if ($role === 'teacher'): ?>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-user" style="color:var(--gray-400);font-size:.85rem;"></i></span>
          <input type="text" id="name" class="form-control" placeholder="Juan Dela Cruz" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Position</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-briefcase" style="color:var(--gray-400);font-size:.85rem;"></i></span>
          <input type="text" id="position" class="form-control" placeholder="e.g. Grade 5 Adviser" required>
        </div>
      </div>
    <?php else: ?>
      <div class="mb-3">
        <label class="form-label">LRN (Learner Reference Number)</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-id-card" style="color:var(--gray-400);font-size:.85rem;"></i></span>
          <input type="text" id="lrn" class="form-control" placeholder="12-digit LRN" required>
        </div>
        <div class="form-text" style="font-size:.75rem;">Must match a student record already on file with the Admin.</div>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-envelope" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="email" id="email" class="form-control" placeholder="you@example.com" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-user-tag" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="text" id="username" class="form-control" placeholder="Choose a username" required autocomplete="username">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="password" class="form-control" placeholder="At least 6 characters" required autocomplete="new-password">
        <span class="input-group-text bg-white toggle-pw" onclick="togglePw('password','pw-eye')"><i class="fas fa-eye" id="pw-eye"></i></span>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="confirm_password" class="form-control" placeholder="Re-enter password" required autocomplete="new-password">
        <span class="input-group-text bg-white toggle-pw" onclick="togglePw('confirm_password','pw-eye2')"><i class="fas fa-eye" id="pw-eye2"></i></span>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Security Question <span class="text-muted" style="font-weight:400;">(for password recovery)</span></label>
      <select id="sec_question" class="form-select" required>
        <option value="">— Choose a question —</option>
        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
        <option value="What city were you born in?">What city were you born in?</option>
        <option value="What is the name of your elementary school?">What is the name of your elementary school?</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Answer</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-key" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="text" id="sec_answer" class="form-control" placeholder="Your answer" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-login <?= $role ?>-btn">
      <span id="btn-text"><i class="fas fa-user-plus me-2"></i>Create Account</span>
      <span id="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin me-2"></i>Creating...</span>
    </button>
  </form>

  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    Already have an account? <a href="login.php" style="color:var(--primary);text-decoration:none;font-weight:500;">Log in</a>
  </div>
</div>

<script>
  const BASE = '<?= BASE_URL ?>';
  const ROLE = '<?= $role ?>';

  function togglePw(inputId, eyeId) {
    const pw  = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (pw.type === 'password') { pw.type = 'text'; eye.className = 'fas fa-eye-slash'; }
    else { pw.type = 'password'; eye.className = 'fas fa-eye'; }
  }

  function setLoading(loading) {
    document.getElementById('btn-text').style.display    = loading ? 'none'   : 'inline';
    document.getElementById('btn-spinner').style.display = loading ? 'inline' : 'none';
  }

  function showError(msg) {
    document.getElementById('success-box').style.display = 'none';
    const box = document.getElementById('error-box');
    document.getElementById('error-msg').textContent = msg;
    box.style.display = 'block';
  }

  function showSuccess(msg) {
    document.getElementById('error-box').style.display = 'none';
    const box = document.getElementById('success-box');
    document.getElementById('success-msg').textContent = msg;
    box.style.display = 'block';
  }

  document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const payload = {
      role: ROLE,
      email: document.getElementById('email').value.trim(),
      username: document.getElementById('username').value.trim(),
      password: document.getElementById('password').value,
      confirm_password: document.getElementById('confirm_password').value,
      sec_question: document.getElementById('sec_question').value,
      sec_answer: document.getElementById('sec_answer').value.trim(),
    };

    if (ROLE === 'teacher') {
      payload.name = document.getElementById('name').value.trim();
      payload.position = document.getElementById('position').value.trim();
    } else {
      payload.lrn = document.getElementById('lrn').value.trim();
    }

    document.getElementById('error-box').style.display = 'none';
    document.getElementById('success-box').style.display = 'none';
    setLoading(true);

    try {
      const res  = await fetch(BASE + '/api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      setLoading(false);

      if (data.ok) {
        showSuccess(data.message + ' Redirecting to login...');
        document.getElementById('register-form').reset();
        setTimeout(() => { window.location.href = BASE + '/views/auth/login.php?role=' + ROLE; }, 1500);
      } else {
        showError(data.message);
      }
    } catch (err) {
      setLoading(false);
      showError('Network error. Please try again.');
    }
  });
</script>
</body>
</html>
