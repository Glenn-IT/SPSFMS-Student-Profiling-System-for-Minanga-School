<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
$role = $_GET['role'] ?? 'admin';
if (!in_array($role, ['admin','teacher','student'])) $role = 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Forgot Password'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1rem; }
    .fp-card { background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); padding:2.5rem 2rem; width:100%; max-width:420px; }
    .step { display:none; }
    .step.active { display:block; }
    .step-dots { display:flex; justify-content:center; gap:.5rem; margin-bottom:1.5rem; }
    .dot { width:10px; height:10px; border-radius:50%; background:var(--gray-200); transition:.3s; }
    .dot.active { background:var(--primary); }
    .dot.done { background:var(--secondary); }
    .temp-pw-box { background:var(--primary-light); border-radius:8px; padding:1rem; text-align:center; font-family:monospace; font-size:1.3rem; font-weight:700; color:var(--primary); letter-spacing:2px; margin:1rem 0; }
  </style>
</head>
<body>
<div class="fp-card">
  <div class="step-dots">
    <div class="dot active" id="dot1"></div>
    <div class="dot" id="dot2"></div>
    <div class="dot" id="dot3"></div>
  </div>

  <!-- Step 1: Username -->
  <div class="step active" id="step1">
    <h5 class="fw-bold mb-1">Forgot Password</h5>
    <p class="text-muted mb-3" style="font-size:.85rem;">Enter your username to continue.</p>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" id="fp-username" class="form-control" placeholder="Enter your username">
    </div>
    <div id="s1-error" class="text-danger" style="font-size:.82rem;display:none;margin-bottom:.5rem;"></div>
    <button class="btn btn-primary w-100" onclick="checkUsername()">Next <i class="fas fa-arrow-right ms-1"></i></button>
    <a href="login.php?role=<?= $role ?>" class="d-block text-center mt-3" style="font-size:.82rem;color:var(--gray-600);">Back to Login</a>
  </div>

  <!-- Step 2: Security Question -->
  <div class="step" id="step2">
    <h5 class="fw-bold mb-1">Security Question</h5>
    <p class="text-muted mb-3" style="font-size:.85rem;">Select the security question you set, then enter your answer.</p>
    <div class="mb-3">
      <label class="form-label">Select Your Question</label>
      <select id="fp-question" class="form-select">
        <option value="">— Choose a question —</option>
        <option value="What is the name of your first pet?">What is the name of your first pet?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
        <option value="What city were you born in?">What city were you born in?</option>
        <option value="What is the name of your elementary school?">What is the name of your elementary school?</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Your Answer</label>
      <input type="text" id="fp-answer" class="form-control" placeholder="Enter your answer">
    </div>
    <div id="s2-error" class="text-danger" style="font-size:.82rem;display:none;margin-bottom:.5rem;"></div>
    <button class="btn btn-primary w-100" onclick="checkAnswer()">Verify <i class="fas fa-check ms-1"></i></button>
  </div>

  <!-- Step 3: Temp Password -->
  <div class="step" id="step3">
    <h5 class="fw-bold mb-1">Temporary Password</h5>
    <p class="text-muted mb-3" style="font-size:.85rem;">Use this temporary password to log in, then change it in Settings.</p>
    <div class="temp-pw-box" id="temp-pw-display">—</div>
    <a href="login.php?role=<?= $role ?>" class="btn btn-primary w-100 mt-2"><i class="fas fa-sign-in-alt me-2"></i>Go to Login</a>
  </div>
</div>

<script>
  const BASE = '<?= BASE_URL ?>';
  let foundUserId = null;

  async function checkUsername() {
    const username = document.getElementById('fp-username').value.trim();
    const err = document.getElementById('s1-error');
    if (!username) { err.textContent = 'Please enter your username.'; err.style.display='block'; return; }

    const res = await fetch(BASE + '/api/auth/forgot-step1.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ username })
    });
    const data = await res.json();
    if (!data.ok) { err.textContent = data.message; err.style.display='block'; return; }
    err.style.display = 'none';
    foundUserId = data.user_id;
    document.getElementById('fp-question').value = '';
    document.getElementById('fp-answer').value = '';
    goStep(2);
  }

  async function checkAnswer() {
    const question = document.getElementById('fp-question').value;
    const answer = document.getElementById('fp-answer').value.trim();
    const err = document.getElementById('s2-error');
    if (!question) { err.textContent = 'Please select your security question.'; err.style.display='block'; return; }
    if (!answer) { err.textContent = 'Please enter your answer.'; err.style.display='block'; return; }

    const res = await fetch(BASE + '/api/auth/forgot-step2.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ user_id: foundUserId, question, answer })
    });
    const data = await res.json();
    if (!data.ok) { err.textContent = data.message; err.style.display='block'; return; }
    err.style.display = 'none';
    document.getElementById('temp-pw-display').textContent = data.temp_password;
    goStep(3);
  }

  function goStep(n) {
    document.querySelectorAll('.step').forEach((s,i) => {
      s.classList.toggle('active', i+1 === n);
    });
    [1,2,3].forEach(i => {
      const dot = document.getElementById('dot'+i);
      dot.classList.toggle('active', i === n);
      dot.classList.toggle('done', i < n);
    });
  }
</script>
</body>
</html>
