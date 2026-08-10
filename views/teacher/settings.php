<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'settings';
try {
  $sqStmt = $pdo->query("SELECT question FROM security_questions ORDER BY id ASC");
  $secQuestions = $sqStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
  $secQuestions = [
    "What is the name of your first pet?",
    "What is your mother's maiden name?",
    "What city were you born in?",
    "What is the name of your elementary school?",
  ];
}
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
  <style>
    .subsetting-pill {
      font-weight: 600;
      font-size: 0.88rem;
      padding: 0.55rem 1.1rem;
      border-radius: 10px;
      transition: all 0.2s ease;
      color: var(--gray-700);
      background: #fff;
      border: 1px solid var(--gray-200);
      cursor: pointer;
    }
    .subsetting-pill:hover {
      background: var(--gray-100);
      color: var(--secondary);
    }
    .subsetting-pill.active {
      background: var(--secondary) !important;
      color: #fff !important;
      border-color: var(--secondary) !important;
      box-shadow: 0 4px 12px rgba(52, 168, 83, 0.25);
    }
    .subsetting-card {
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>

<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Settings</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Teacher</li>
            <li class="breadcrumb-item active">Settings</li>
          </ol>
        </nav>
      </div>

      <div class="ms-auto">
        <div class="user-menu">
          <div class="user-avatar" style="background:var(--secondary-light);color:var(--secondary);"><?= strtoupper(substr($dbUser['name'],0,1)) ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($dbUser['name']) ?></div>
            <div class="user-role">Teacher</div>
          </div>
        </div>
>>>>>>> 6eb1884 (Enhance student management and security question features)
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role"><?= htmlspecialchars($user['position']??'Teacher') ?></div></div></div></div>
    </nav>

    <div class="page-header"><h3>Account Settings</h3><p>Manage your profile and security settings</p></div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center py-4">
<<<<<<< HEAD
            <div style="width:72px;height:72px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:var(--secondary);margin:0 auto 1rem;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <h6 class="fw-bold" id="display-name"><?= htmlspecialchars($user['name']) ?></h6>
            <div style="font-size:.8rem;color:var(--gray-600);" id="display-email"><?= htmlspecialchars($user['email']) ?></div>
            <span class="badge bg-success bg-opacity-15 text-success mt-2"><?= htmlspecialchars($user['position']??'Teacher') ?></span>
=======
            <div style="width:80px;height:80px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:var(--secondary);margin:0 auto 1rem;box-shadow:0 4px 12px rgba(52,168,83,0.15);"><?= strtoupper(substr($dbUser['name'],0,1)) ?></div>
            <h6 class="fw-bold mb-1" id="display-name"><?= htmlspecialchars($dbUser['name']) ?></h6>
            <div style="font-size:.85rem;color:var(--gray-600);" id="display-email"><?= htmlspecialchars($dbUser['email']) ?></div>
            <span class="badge bg-success bg-opacity-15 text-success mt-3 px-3 py-2 rounded-pill">Teacher</span>
>>>>>>> 6eb1884 (Enhance student management and security question features)
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
                  <small class="text-muted fw-normal" style="font-size:0.78rem;">Choose from list</small>
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
                </div>
                <div class="form-text small" id="sec-q-help">Select a security question for password recovery.</div>
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
<<<<<<< HEAD
async function saveProfile() {
  const res = await fetch(BASE+'/api/accounts/update-profile.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({ name:document.getElementById('p-name').value, email:document.getElementById('p-email').value }) });
  const d = await res.json();
  if (d.ok) { document.getElementById('display-name').textContent=d.name; document.getElementById('display-email').textContent=d.email; showToast('Profile updated!','success'); } else showToast(d.message,'error');
=======

let currentTab = 'profile';

document.addEventListener('DOMContentLoaded', () => {
  const pendingToast = sessionStorage.getItem('pendingToast');
  if (pendingToast) {
    try {
      const data = JSON.parse(pendingToast);
      showToast(data.message, data.type || 'success');
    } catch(e){}
    sessionStorage.removeItem('pendingToast');
  }

  const urlParams = new URLSearchParams(window.location.search);
  const tabFromParam = urlParams.get('tab');
  const tabFromHash = window.location.hash.replace('#', '');
  const targetTab = tabFromParam || tabFromHash || 'profile';
  switchSubsetting(targetTab, false);
});

function switchSubsetting(subsetting, updateHistory = true) {
  const validTabs = ['profile', 'password', 'security'];
  if (!validTabs.includes(subsetting)) subsetting = 'profile';
  currentTab = subsetting;

  document.querySelectorAll('#settings-subsetting-nav .subsetting-pill').forEach(btn => {
    btn.classList.remove('active');
  });

  const activeBtn = document.getElementById('tab-' + subsetting);
  if (activeBtn) {
    activeBtn.classList.add('active');
  }

  const cards = {
    profile: document.getElementById('card-profile'),
    password: document.getElementById('card-password'),
    security: document.getElementById('card-security')
  };

  Object.keys(cards).forEach(key => {
    if (cards[key]) {
      cards[key].style.display = (key === subsetting) ? 'block' : 'none';
    }
  });

  if (updateHistory) {
    const newUrl = window.location.pathname + '?tab=' + subsetting;
    history.replaceState(null, '', newUrl);
  }
}

async function saveProfile() {
  const name = document.getElementById('p-name').value.trim();
  const email = document.getElementById('p-email').value.trim();

  if (!name || !email) {
    showToast('Please fill in both name and email fields.', 'error');
    return;
  }

  showLoading('Saving Profile...', 'Updating profile information...');
  try {
    const res = await fetch(BASE + '/api/accounts/update-profile.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email })
    });
    const d = await res.json();
    if (d.ok) {
      triggerPageRefresh('Profile Information updated successfully!', 'profile');
    } else {
      hideLoading();
      showToast(d.message || 'Failed to update profile.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('An error occurred while saving profile.', 'error');
  }
>>>>>>> 6eb1884 (Enhance student management and security question features)
}
async function changePassword() {
<<<<<<< HEAD
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
=======
  const old_password = document.getElementById('pw-old').value;
  const new_password = document.getElementById('pw-new').value;
  const confirm_password = document.getElementById('pw-confirm').value;

  if (!old_password || !new_password || !confirm_password) {
    showToast('Please fill in all password fields.', 'error');
    return;
  }

  if (new_password.length < 6) {
    showToast('New password must be at least 6 characters.', 'error');
    return;
  }

  if (new_password !== confirm_password) {
    showToast('New passwords do not match.', 'error');
    return;
  }

  showLoading('Updating Password...', 'Changing account password safely...');
  try {
    const res = await fetch(BASE + '/api/auth/change-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ old_password, new_password, confirm_password })
    });
    const d = await res.json();
    if (d.ok) {
      triggerPageRefresh('Password changed successfully!', 'password');
    } else {
      hideLoading();
      showToast(d.message || 'Failed to change password.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('An error occurred while changing password.', 'error');
  }
}
>>>>>>> 6eb1884 (Enhance student management and security question features)

async function saveSecQuestion() {
  const select = document.getElementById('sec-q');
  const question = select.value.trim();
  const answer = document.getElementById('sec-a').value.trim();

  if (!question || !answer) {
    showToast('Please select a security question and enter an answer.', 'error');
    return;
  }

  showLoading('Saving Security Question...', 'Updating your recovery question and answer...');
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
      hideLoading();
      showToast(d.message || 'Failed to save security question.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('An error occurred while saving security question.', 'error');
  }
}
</script>
</body>
</html>
<<<<<<< HEAD
=======


>>>>>>> 6eb1884 (Enhance student management and security question features)
