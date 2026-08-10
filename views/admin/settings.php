<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

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
  <style>
    .refresh-loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.7);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeInOverlay 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes fadeInOverlay {
      from { opacity: 0; transform: scale(1.02); }
      to { opacity: 1; transform: scale(1); }
    }
    .refresh-loading-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 2.5rem;
      width: 90%;
      max-width: 420px;
      text-align: center;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transform: scale(0.95);
      animation: popInCard 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes popInCard {
      to { transform: scale(1); }
    }
    .refresh-spinner-container {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }
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
      color: var(--primary);
    }
    .subsetting-pill.active {
      background: var(--primary) !important;
      color: #fff !important;
      border-color: var(--primary) !important;
      box-shadow: 0 4px 12px rgba(26, 115, 232, 0.25);
    }
    .subsetting-card {
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>

<!-- Full-screen Refresh Loading Overlay -->
<div id="refresh-loading-overlay" class="refresh-loading-overlay" style="display:none;">
  <div class="refresh-loading-card">
    <div class="refresh-spinner-container">
      <div class="spinner-border text-primary" role="status" style="width: 3.5rem; height: 3.5rem; border-width: 0.28em;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <h5 class="fw-bold mt-2 mb-1" id="refresh-loading-title" style="color:var(--dark);">Updating Settings...</h5>
    <p class="text-muted small mb-3" id="refresh-loading-sub">Auto refreshing the page to apply changes...</p>
    <div class="progress" style="height: 5px; border-radius: 4px; overflow: hidden; background: #e9ecef;">
      <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%;"></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Settings</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <li class="breadcrumb-item active">Settings</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($dbUser['name'],0,1)) ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($dbUser['name']) ?></div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
      <div>
        <h3>Account Settings</h3>
        <p>Manage your profile, login credentials, and security preferences</p>
      </div>
    </div>

    <!-- Sub-settings Navigation Bar -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body p-2 bg-white rounded-3 border">
        <div class="d-flex flex-wrap gap-2" id="settings-subsetting-nav">
          <button class="subsetting-pill active" id="tab-profile" onclick="switchSubsetting('profile')">
            <i class="fas fa-user-edit me-2"></i>Profile Info
          </button>
          <button class="subsetting-pill" id="tab-password" onclick="switchSubsetting('password')">
            <i class="fas fa-key me-2"></i>Change Password
          </button>
          <button class="subsetting-pill" id="tab-security" onclick="switchSubsetting('security')">
            <i class="fas fa-shield-alt me-2"></i>Security Question
          </button>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Profile Overview Sidebar Card -->
      <div class="col-md-4">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center py-4">
            <div style="width:80px;height:80px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:var(--primary);margin:0 auto 1rem;box-shadow:0 4px 12px rgba(26,115,232,0.15);"><?= strtoupper(substr($dbUser['name'],0,1)) ?></div>
            <h6 class="fw-bold mb-1" id="display-name"><?= htmlspecialchars($dbUser['name']) ?></h6>
            <div style="font-size:.85rem;color:var(--gray-600);" id="display-email"><?= htmlspecialchars($dbUser['email']) ?></div>
            <span class="badge bg-primary bg-opacity-15 text-primary mt-3 px-3 py-2 rounded-pill">Administrator</span>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <!-- Subsetting 1: Profile Edit -->
        <div class="card mb-3 shadow-sm border-0 subsetting-card" id="card-profile">
          <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-user me-2" style="color:var(--primary);"></i>Profile Information</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" id="p-name" class="form-control" value="<?= htmlspecialchars($dbUser['name']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" id="p-email" class="form-control" value="<?= htmlspecialchars($dbUser['email']) ?>">
              </div>
              <div class="col-12 mt-3">
                <button class="btn btn-primary btn-sm px-3" onclick="saveProfile()">
                  <i class="fas fa-save me-2"></i>Save Profile
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Subsetting 2: Change Password -->
        <div class="card mb-3 shadow-sm border-0 subsetting-card" id="card-password">
          <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-lock me-2" style="color:var(--primary);"></i>Change Password</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Current Password</label>
                <input type="password" id="pw-old" class="form-control" placeholder="••••••••">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" id="pw-new" class="form-control" placeholder="At least 6 chars">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Confirm New Password</label>
                <input type="password" id="pw-confirm" class="form-control" placeholder="Re-type new password">
              </div>
              <div class="col-12 mt-3">
                <button class="btn btn-primary btn-sm px-3" onclick="changePassword()">
                  <i class="fas fa-key me-2"></i>Change Password
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Subsetting 3: Security Question -->
        <div class="card mb-3 shadow-sm border-0 subsetting-card" id="card-security">
          <div class="card-header bg-white py-3 fw-bold"><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>Security Question</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-7">
                <label class="form-label fw-semibold">Security Question</label>
                <select id="sec-q" class="form-select">
                  <option value="">Select a security question</option>
                  <?php 
                  $currentQ = $dbUser['sec_question'] ?? '';
                  foreach ($secQuestions as $q): 
                    $selected = ($q === $currentQ) ? 'selected' : '';
                  ?>
                  <option value="<?= htmlspecialchars($q) ?>" <?= $selected ?>><?= htmlspecialchars($q) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-5">
                <label class="form-label fw-semibold">Your Answer</label>
                <input type="text" id="sec-a" class="form-control" value="<?= htmlspecialchars($dbUser['sec_answer'] ?? '') ?>" placeholder="Your answer">
              </div>
              <div class="col-12 mt-3">
                <button class="btn btn-primary btn-sm px-3" onclick="saveSecQuestion()">
                  <i class="fas fa-save me-2"></i>Save Security Question
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger shadow-sm subsetting-card" id="card-danger">
          <div class="card-header bg-danger bg-opacity-10 text-danger fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</div>
          <div class="card-body">
            <p class="text-muted small mb-3">Logging out will terminate your current administrative session.</p>
            <a href="<?= BASE_URL ?>/api/auth/logout.php" class="btn btn-danger btn-sm px-3" onclick="return confirmLogout(this)"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
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

let currentTab = 'profile';

document.addEventListener('DOMContentLoaded', () => {
  // Check if a toast notice is pending from pre-refresh action
  const pendingToast = sessionStorage.getItem('pendingToast');
  if (pendingToast) {
    try {
      const data = JSON.parse(pendingToast);
      showToast(data.message, data.type || 'success');
    } catch(e){}
    sessionStorage.removeItem('pendingToast');
  }

  // Restore active subsetting tab from URL parameter or hash
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

  // Update nav pills
  document.querySelectorAll('#settings-subsetting-nav .subsetting-pill').forEach(btn => {
    btn.classList.remove('active');
  });

  const activeBtn = document.getElementById('tab-' + subsetting);
  if (activeBtn) {
    activeBtn.classList.add('active');
  }

  // Toggle card visibility
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

function showRefreshLoading(titleMessage, subMessage) {
  const overlay = document.getElementById('refresh-loading-overlay');
  const titleEl = document.getElementById('refresh-loading-title');
  const subEl = document.getElementById('refresh-loading-sub');

  if (titleEl && titleMessage) titleEl.textContent = titleMessage;
  if (subEl && subMessage) subEl.textContent = subMessage;

  if (overlay) {
    overlay.style.display = 'flex';
  }
}

function triggerPageRefresh(successMessage, activeTab) {
  showRefreshLoading('Updating Settings...', 'Auto-refreshing whole page to apply updates...');
  sessionStorage.setItem('pendingToast', JSON.stringify({ message: successMessage, type: 'success' }));
  setTimeout(() => {
    window.location.href = window.location.pathname + '?tab=' + activeTab;
  }, 1000);
}

async function saveProfile() {
  const name = document.getElementById('p-name').value.trim();
  const email = document.getElementById('p-email').value.trim();

  if (!name || !email) {
    showToast('Please fill in both name and email fields.', 'error');
    return;
  }

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
      showToast(d.message || 'Failed to update profile.', 'error');
    }
  } catch (err) {
    showToast('An error occurred while saving profile.', 'error');
  }
}

async function changePassword() {
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
      showToast(d.message || 'Failed to change password.', 'error');
    }
  } catch (err) {
    showToast('An error occurred while changing password.', 'error');
  }
}

async function saveSecQuestion() {
  const question = document.getElementById('sec-q').value;
  const answer = document.getElementById('sec-a').value.trim();

  if (!question || !answer) {
    showToast('Please select a security question and enter an answer.', 'error');
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

