<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

$activePage = 'settings';

// Fetch security questions list dynamically from database
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
  <?php $pageTitle = 'Settings — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
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
          <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>Security Question</span>
            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="openSecQuestionsModal()">
              <i class="fas fa-table me-1"></i>Manage Security Questions Table
            </button>
          </div>
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
                <div class="form-text small" id="sec-q-help">Select a question or click <strong>Manage Security Questions Table</strong> to edit or add questions.</div>
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

<!-- Security Questions DataGridView Modal -->
<div class="modal fade" id="secQuestionsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold"><i class="fas fa-table me-2"></i>Security Questions Table (DataGridView)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="text-muted small mb-3">Manage all available security questions in the system database table.</p>
        
        <!-- DataGridView Table -->
        <div class="table-responsive mb-4" style="max-height: 320px; overflow-y: auto;">
          <table class="table table-bordered table-hover align-middle mb-0" id="sec-questions-datagrid">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width:50px;" class="text-center">#</th>
                <th>Sec Question</th>
                <th class="text-center" style="width:180px;">Action</th>
              </tr>
            </thead>
            <tbody id="sq-datagrid-tbody">
              <tr><td colspan="3" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading security questions...</td></tr>
            </tbody>
          </table>
        </div>

        <!-- Add/Edit Form below table -->
        <div class="card border-0 bg-light p-3 rounded-3">
          <label class="form-label fw-bold mb-2" id="sq-form-title"><i class="fas fa-plus-circle me-1 text-primary"></i>Add Security Question</label>
          <div class="input-group">
            <input type="text" id="sq-input-text" class="form-control" placeholder="Type a security question here...">
            <button type="button" id="btn-save-sq-grid" class="btn btn-success px-4" onclick="saveSqFromGrid()">
              <i class="fas fa-save me-1"></i>Save
            </button>
            <button type="button" id="btn-cancel-sq-grid" class="btn btn-secondary px-3 d-none ms-2" onclick="cancelSqEdit()">
              <i class="fas fa-times me-1"></i>Cancel
            </button>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
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
let editingSqId = null;

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

async function saveProfile() {
  const name = document.getElementById('p-name').value.trim();
  const email = document.getElementById('p-email').value.trim();

  if (!name || !email) {
    showToast('Please fill in both name and email fields.', 'error');
    return;
  }

  showLoading('Saving Profile...', 'Updating your profile information...');
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

/* ─────────────────────────────────────────────────────────────
   SECURITY QUESTIONS DATAGRIDVIEW MANAGEMENT FUNCTIONS
   ───────────────────────────────────────────────────────────── */
function openSecQuestionsModal() {
  cancelSqEdit();
  loadSecQuestionsGrid();
  const modalEl = document.getElementById('secQuestionsModal');
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

async function loadSecQuestionsGrid() {
  const tbody = document.getElementById('sq-datagrid-tbody');
  tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-2"></i>Loading security questions...</td></tr>';
  
  try {
    const res = await fetch(BASE + '/api/security-questions/index.php');
    const d = await res.json();
    if (!d.ok || !d.data) {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-3">Failed to load questions table.</td></tr>';
      return;
    }

    const items = d.data;
    if (items.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">No security questions found in table.</td></tr>';
    } else {
      tbody.innerHTML = items.map((sq, idx) => {
        const safeQ = sq.question.replace(/'/g, "\\'").replace(/"/g, "&quot;");
        return `
          <tr>
            <td class="text-center fw-bold">${idx + 1}</td>
            <td>${sq.question}</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="startEditSq(${sq.id}, '${safeQ}')">
                <i class="fas fa-edit me-1"></i>Edit
              </button>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSq(${sq.id})">
                <i class="fas fa-trash-alt me-1"></i>Delete
              </button>
            </td>
          </tr>
        `;
      }).join('');
    }

    // Also update the select dropdown in settings page
    const select = document.getElementById('sec-q');
    const currentVal = select.value;
    select.innerHTML = '<option value="">Select a security question</option>';
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.question;
      opt.textContent = item.question;
      if (item.question === currentVal) opt.selected = true;
      select.appendChild(opt);
    });
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-3">An error occurred while loading table.</td></tr>';
  }
}

function startEditSq(id, questionText) {
  editingSqId = id;
  const input = document.getElementById('sq-input-text');
  const title = document.getElementById('sq-form-title');
  const btnSave = document.getElementById('btn-save-sq-grid');
  const btnCancel = document.getElementById('btn-cancel-sq-grid');

  input.value = questionText;
  input.focus();
  title.innerHTML = '<i class="fas fa-edit me-1 text-primary"></i>Edit Security Question';
  btnSave.className = 'btn btn-primary px-4';
  btnSave.innerHTML = '<i class="fas fa-save me-1"></i>Save Changes';
  btnCancel.classList.remove('d-none');
}

function cancelSqEdit() {
  editingSqId = null;
  const input = document.getElementById('sq-input-text');
  const title = document.getElementById('sq-form-title');
  const btnSave = document.getElementById('btn-save-sq-grid');
  const btnCancel = document.getElementById('btn-cancel-sq-grid');

  input.value = '';
  title.innerHTML = '<i class="fas fa-plus-circle me-1 text-primary"></i>Add Security Question';
  btnSave.className = 'btn btn-success px-4';
  btnSave.innerHTML = '<i class="fas fa-save me-1"></i>Save';
  btnCancel.classList.add('d-none');
}

async function saveSqFromGrid() {
  const input = document.getElementById('sq-input-text');
  const question = input.value.trim();

  if (!question) {
    showToast('Please type a security question text.', 'error');
    return;
  }

  const isEdit = editingSqId !== null;
  showLoading(isEdit ? 'Updating Security Question...' : 'Saving Security Question...', 'Updating security questions table...');

  try {
    const payload = isEdit 
      ? { action: 'edit', id: editingSqId, question }
      : { action: 'add', question };

    const res = await fetch(BASE + '/api/security-questions/index.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const d = await res.json();

    hideLoading();
    if (d.ok) {
      showToast(d.message, 'success');
      cancelSqEdit();
      loadSecQuestionsGrid();
    } else {
      showToast(d.message || 'Failed to save security question.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('An error occurred while saving security question.', 'error');
  }
}

function deleteSq(id) {
  confirmModal('Confirm Delete', 'Are you sure you want to delete this security question from the table?', async () => {
    showLoading('Deleting Security Question...', 'Removing item from table...');
    try {
      const res = await fetch(BASE + '/api/security-questions/index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id })
      });
      const d = await res.json();
      hideLoading();
      if (d.ok) {
        showToast(d.message, 'success');
        if (editingSqId === id) cancelSqEdit();
        loadSecQuestionsGrid();
      } else {
        showToast(d.message || 'Failed to delete security question.', 'error');
      }
    } catch (err) {
      hideLoading();
      showToast('An error occurred while deleting security question.', 'error');
    }
  });
}
</script>
</body>
</html>


