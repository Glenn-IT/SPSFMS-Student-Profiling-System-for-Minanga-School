<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'settings';
$secQuestions = [
  "What is the name of your first pet?",
  "What is your mother's maiden name?",
  "What city were you born in?",
  "What is the name of your elementary school?",
];
$currentQ = $dbUser['sec_question'] ?? '';
if ($currentQ && !in_array($currentQ, $secQuestions)) {
  $secQuestions[] = $currentQ;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Settings — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Settings</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Settings</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role"><?= htmlspecialchars($user['position']??'Teacher') ?></div></div></div></div>
    </nav>

    <div class="page-header"><h3>Account Settings</h3><p>Manage your profile and security settings</p></div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center py-4">
            <div style="width:72px;height:72px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:var(--secondary);margin:0 auto 1rem;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <h6 class="fw-bold" id="display-name"><?= htmlspecialchars($user['name']) ?></h6>
            <div style="font-size:.8rem;color:var(--gray-600);" id="display-email"><?= htmlspecialchars($user['email']) ?></div>
            <span class="badge bg-success bg-opacity-15 text-success mt-2"><?= htmlspecialchars($user['position']??'Teacher') ?></span>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card mb-3">
          <div class="card-header" style="color:var(--secondary);"><i class="fas fa-user me-2"></i>Profile Information</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" id="p-name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>"></div>
              <div class="col-md-6"><label class="form-label">Email</label><input type="email" id="p-email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"></div>
              <div class="col-12"><button class="btn btn-sm" style="background:var(--secondary);color:#fff;" onclick="saveProfile()"><i class="fas fa-save me-2"></i>Save Profile</button></div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header" style="color:var(--secondary);"><i class="fas fa-lock me-2"></i>Change Password</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" id="pw-old" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">New Password</label><input type="password" id="pw-new" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">Confirm New</label><input type="password" id="pw-confirm" class="form-control"></div>
              <div class="col-12"><button class="btn btn-sm" style="background:var(--secondary);color:#fff;" onclick="changePassword()"><i class="fas fa-key me-2"></i>Change Password</button></div>
            </div>
          </div>
        </div>

        <div class="card mb-3">
          <div class="card-header" style="color:var(--secondary);"><i class="fas fa-shield-alt me-2"></i>Security Question</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-7">
                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                  <span>Security Question</span>
                  <small class="text-muted fw-normal" style="font-size:0.78rem;">Preset or custom question</small>
                </label>
                <div class="input-group">
                  <select id="sec-q" class="form-select">
                    <option value="">Select a security question</option>
                    <?php 
                    foreach ($secQuestions as $q): 
                      $selected = ($q === $currentQ) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($q) ?>" <?= $selected ?>><?= htmlspecialchars($q) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input type="text" id="sec-q-custom" class="form-control d-none" placeholder="Type or edit security question..." value="<?= htmlspecialchars($currentQ) ?>">
                  <button type="button" class="btn btn-outline-secondary" id="btn-toggle-sec-edit" onclick="toggleEditSecQuestion()" title="Edit security question text">
                    <i class="fas fa-edit me-1"></i><span>Edit</span>
                  </button>
                </div>
                <div class="form-text small" id="sec-q-help">Click <strong>Edit</strong> to customize or write your own question.</div>
              </div>
              <div class="col-md-5"><label class="form-label">Answer</label><input type="text" id="sec-a" class="form-control"></div>
              <div class="col-12"><button class="btn btn-sm" style="background:var(--secondary);color:#fff;" onclick="saveSecQuestion()"><i class="fas fa-save me-2"></i>Save</button></div>
            </div>
          </div>
        </div>

        <div class="card border-danger">
          <div class="card-header text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</div>
          <div class="card-body">
            <a href="<?= BASE_URL ?>/api/auth/logout.php" class="btn btn-danger btn-sm" onclick="return confirmLogout(this)"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
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
  const res = await fetch(BASE+'/api/accounts/update-profile.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ name:document.getElementById('p-name').value, email:document.getElementById('p-email').value }) });
  const d = await res.json();
  if (d.ok) { document.getElementById('display-name').textContent=d.name; document.getElementById('display-email').textContent=d.email; showToast('Profile updated!','success'); } else showToast(d.message,'error');
}
async function changePassword() {
  const res = await fetch(BASE+'/api/auth/change-password.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ old_password:document.getElementById('pw-old').value, new_password:document.getElementById('pw-new').value, confirm_password:document.getElementById('pw-confirm').value }) });
  const d = await res.json();
  if (d.ok) { showToast('Password changed!','success'); ['pw-old','pw-new','pw-confirm'].forEach(id=>document.getElementById(id).value=''); } else showToast(d.message,'error');
}
function toggleEditSecQuestion() {
  const select = document.getElementById('sec-q');
  const custom = document.getElementById('sec-q-custom');
  const btn = document.getElementById('btn-toggle-sec-edit');
  const help = document.getElementById('sec-q-help');

  if (custom.classList.contains('d-none')) {
    if (select.value) {
      custom.value = select.value;
    }
    select.classList.add('d-none');
    custom.classList.remove('d-none');
    custom.focus();
    btn.innerHTML = '<i class="fas fa-list me-1"></i><span>List</span>';
    btn.className = 'btn btn-outline-secondary';
    if (help) help.innerHTML = 'Editing question text. Click <strong>List</strong> to pick from preset questions.';
  } else {
    custom.classList.add('d-none');
    select.classList.remove('d-none');
    btn.innerHTML = '<i class="fas fa-edit me-1"></i><span>Edit</span>';
    btn.className = 'btn btn-outline-secondary';
    if (help) help.innerHTML = 'Click <strong>Edit</strong> to customize or write your own question.';
  }
}

async function saveSecQuestion() {
  const select = document.getElementById('sec-q');
  const custom = document.getElementById('sec-q-custom');
  const isCustomMode = !custom.classList.contains('d-none');

  const question = isCustomMode ? custom.value.trim() : select.value.trim();
  const answer = document.getElementById('sec-a').value.trim();

  if (!question || !answer) {
    showToast('Please select or enter a security question and an answer.', 'error');
    return;
  }

  try {
    const res = await fetch(BASE + '/api/accounts/update-security.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ question, answer })
    });
    const d = await res.json();
    if (d.ok) {
      triggerPageRefresh('Security question saved successfully!', 'security');
    } else {
      showToast(d.message || 'Failed to save security question.', 'error');
    }
  } catch (err) {
    showToast('An error occurred while saving security question.', 'error');
  }
}
</script>
</body>
</html>
