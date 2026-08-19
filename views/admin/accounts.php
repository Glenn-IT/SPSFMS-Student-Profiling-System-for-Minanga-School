<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'accounts';
$users = $pdo->query("SELECT * FROM users ORDER BY role, name")->fetchAll();
$total = count($users);
$active = count(array_filter($users, fn($u) => $u['status'] === 'active'));
$inactive = $total - $active;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Account Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Account Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Accounts</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between">
      <div>
        <h3>Account Management</h3><p class="mb-0">Manage user accounts and access status</p>
      </div>
      <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-user-plus me-2"></i>Create Account
      </button>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4"><div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div><div><div class="stat-value" id="stat-total"><?= $total ?></div><div class="stat-label">Total Accounts</div></div></div></div>
      <div class="col-md-4"><div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div><div class="stat-value" id="stat-active"><?= $active ?></div><div class="stat-label">Active</div></div></div></div>
      <div class="col-md-4"><div class="stat-card red"><div class="stat-icon"><i class="fas fa-ban"></i></div><div><div class="stat-value" id="stat-inactive"><?= $inactive ?></div><div class="stat-label">Inactive</div></div></div></div>
    </div>

    <div class="card">
      <div class="card-header"><i class="fas fa-user-cog me-2" style="color:var(--primary);"></i>User Accounts</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Position</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody id="accounts-tbody">
              <?php foreach ($users as $i => $u): ?>
              <tr id="row-<?= $u['id'] ?>">
                <td><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($u['username']) ?></span></td>
                <td>
                  <?php $roleColor = ['admin'=>'primary','teacher'=>'success','student'=>'warning'][$u['role']] ?? 'secondary'; ?>
                  <span class="badge bg-<?= $roleColor ?> bg-opacity-15 text-<?= $roleColor ?> fw-semibold text-capitalize"><?= $u['role'] ?></span>
                </td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($u['position'] ?? '—') ?></td>
                <td>
                  <span class="badge <?= $u['status']==='active' ? 'bg-success' : 'bg-danger' ?> bg-opacity-15 text-<?= $u['status']==='active'?'success':'danger' ?> fw-semibold status-badge" id="badge-<?= $u['id'] ?>">
                    <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td class="text-center">
                  <?php if ($u['id'] != $user['id']): ?>
                  <button class="btn btn-sm <?= $u['status']==='active'?'btn-outline-danger':'btn-outline-success' ?>" id="btn-<?= $u['id'] ?>"
                    onclick="toggleStatus(<?= $u['id'] ?>, '<?= $u['status']==='active'?'inactive':'active' ?>')">
                    <i class="fas <?= $u['status']==='active'?'fa-ban':'fa-check' ?>"></i>
                    <?= $u['status']==='active'?'Deactivate':'Activate' ?>
                  </button>
                  <?php else: ?>
                  <span style="font-size:.78rem;color:var(--gray-400);">Current user</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Create Account Modal ── -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createAccountModalLabel"><i class="fas fa-user-plus me-2"></i>Create Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Role toggle -->
        <div class="mb-3">
          <label class="form-label">Account Role</label>
          <div class="d-flex gap-2">
            <button type="button" id="role-teacher-btn" class="btn btn-primary flex-fill" onclick="setRole('teacher')">
              <i class="fas fa-chalkboard-teacher me-1"></i> Teacher
            </button>
            <button type="button" id="role-student-btn" class="btn btn-light flex-fill" onclick="setRole('student')">
              <i class="fas fa-user-graduate me-1"></i> Student
            </button>
          </div>
          <input type="hidden" id="ca-role" value="teacher">
        </div>

        <hr class="my-2">

        <!-- Teacher fields -->
        <div id="teacher-fields">
          <div class="mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="ca-name" class="form-control" placeholder="e.g. Juan Dela Cruz">
          </div>
          <div class="mb-3">
            <label class="form-label">Position / Advisory <span class="text-danger">*</span></label>
            <input type="text" id="ca-position" class="form-control" placeholder="e.g. Grade 7 Adviser">
          </div>
        </div>

        <!-- Student fields -->
        <div id="student-fields" style="display:none;">
          <div class="mb-3">
            <label class="form-label">Student LRN <span class="text-danger">*</span></label>
            <input type="text" id="ca-lrn" class="form-control" placeholder="12-digit LRN">
            <div class="form-text">Student's name will be pulled automatically from their profile.</div>
          </div>
        </div>

        <!-- Common fields -->
        <div class="mb-3">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" id="ca-username" class="form-control" placeholder="Login username" autocomplete="off">
        </div>
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" id="ca-email" class="form-control" placeholder="email@example.com">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" id="ca-password" class="form-control" placeholder="Min. 6 characters" autocomplete="new-password">
          </div>
          <div class="col-6">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" id="ca-confirm" class="form-control" placeholder="Repeat password">
          </div>
        </div>

        <div id="ca-error" class="alert alert-danger mt-3 d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="ca-submit-btn" onclick="submitCreateAccount()">
          <i class="fas fa-user-plus me-1"></i> Create Account
        </button>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
const CURRENT_USER_ID = <?= (int)$user['id'] ?>;
showDesktopOnlyWarning();

/* ── Live Account & Stat Loader ── */
async function loadAccounts() {
  try {
    const res = await fetch(BASE + '/api/accounts/index.php');
    const data = await res.json();
    if (!data.ok) return;

    let total = data.users.length;
    let active = 0;
    let inactive = 0;

    const roleColors = { admin: 'primary', teacher: 'success', student: 'warning' };
    const tbody = document.getElementById('accounts-tbody');

    tbody.innerHTML = data.users.map((u, i) => {
      const color = roleColors[u.role] ?? 'secondary';
      const isActive = u.status === 'active';
      if (isActive) active++; else inactive++;

      const actionBtn = (u.id == CURRENT_USER_ID)
        ? '<span style="font-size:.78rem;color:var(--gray-400);">Current user</span>'
        : `<button class="btn btn-sm ${isActive ? 'btn-outline-danger' : 'btn-outline-success'}" id="btn-${u.id}"
             onclick="toggleStatus(${u.id}, '${isActive ? 'inactive' : 'active'}')">
             <i class="fas ${isActive ? 'fa-ban' : 'fa-check'}"></i> ${isActive ? 'Deactivate' : 'Activate'}
           </button>`;

      return `
        <tr id="row-${u.id}">
          <td>${i + 1}</td>
          <td><strong>${escHtml(u.name)}</strong></td>
          <td><span style="font-family:monospace;font-size:.82rem;">${escHtml(u.username)}</span></td>
          <td><span class="badge bg-${color} bg-opacity-15 text-${color} fw-semibold text-capitalize">${escHtml(u.role)}</span></td>
          <td style="font-size:.82rem;">${escHtml(u.position || '—')}</td>
          <td>
            <span class="badge ${isActive ? 'bg-success' : 'bg-danger'} bg-opacity-15 text-${isActive ? 'success' : 'danger'} fw-semibold status-badge" id="badge-${u.id}">
              ${isActive ? 'Active' : 'Inactive'}
            </span>
          </td>
          <td class="text-center">${actionBtn}</td>
        </tr>
      `;
    }).join('');

    updateStatCards(total, active, inactive);
  } catch (err) {
    console.error('Failed to load live account data:', err);
  }
}

function updateStatCards(total, active, inactive) {
  const totalEl = document.getElementById('stat-total');
  const activeEl = document.getElementById('stat-active');
  const inactiveEl = document.getElementById('stat-inactive');

  if (totalEl) totalEl.textContent = total;
  if (activeEl) activeEl.textContent = active;
  if (inactiveEl) inactiveEl.textContent = inactive;
}

/* ── Toggle activate/deactivate ── */
async function toggleStatus(id, newStatus) {
  const label = newStatus === 'active' ? 'activate' : 'deactivate';
  confirmModal('Confirm', `Are you sure you want to ${label} this account?`, async () => {
    showLoading('Updating Account Status...', `Please wait while we ${label} account #${id}...`);
    try {
      const res = await fetch(BASE + '/api/accounts/toggle.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id, status: newStatus })
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) { showToast(data.message, 'error'); return; }
      showToast(`Account ${label}d successfully.`, 'success');
      // Auto update live data and stat cards after loading ok
      await loadAccounts();
    } catch (err) {
      hideLoading();
      showToast('Failed to update account status.', 'error');
    }
  });
}

/* ── Create Account modal ── */
let caModal;

function openCreateModal() {
  if (!caModal) caModal = new bootstrap.Modal(document.getElementById('createAccountModal'));
  ['ca-name','ca-position','ca-lrn','ca-username','ca-email','ca-password','ca-confirm']
    .forEach(id => document.getElementById(id).value = '');
  document.getElementById('ca-error').classList.add('d-none');
  document.getElementById('ca-submit-btn').disabled = false;
  document.getElementById('ca-submit-btn').innerHTML = '<i class="fas fa-user-plus me-1"></i> Create Account';
  setRole('teacher');
  caModal.show();
}

function setRole(role) {
  document.getElementById('ca-role').value = role;
  const isTeacher = role === 'teacher';
  document.getElementById('teacher-fields').style.display = isTeacher ? '' : 'none';
  document.getElementById('student-fields').style.display = isTeacher ? 'none' : '';
  document.getElementById('role-teacher-btn').className = 'btn flex-fill ' + (isTeacher ? 'btn-primary' : 'btn-light');
  document.getElementById('role-student-btn').className = 'btn flex-fill ' + (!isTeacher ? 'btn-primary' : 'btn-light');
}

function showCaError(msg) {
  const el = document.getElementById('ca-error');
  el.textContent = msg;
  el.classList.remove('d-none');
}

async function submitCreateAccount() {
  document.getElementById('ca-error').classList.add('d-none');

  const role     = document.getElementById('ca-role').value;
  const username = document.getElementById('ca-username').value.trim();
  const email    = document.getElementById('ca-email').value.trim();
  const password = document.getElementById('ca-password').value;
  const confirm  = document.getElementById('ca-confirm').value;
  const name     = document.getElementById('ca-name').value.trim();
  const position = document.getElementById('ca-position').value.trim();
  const lrn      = document.getElementById('ca-lrn').value.trim();

  if (!username || !email || !password || !confirm) return showCaError('Please fill in all required fields.');
  if (password.length < 6) return showCaError('Password must be at least 6 characters.');
  if (password !== confirm) return showCaError('Passwords do not match.');
  if (role === 'teacher' && (!name || !position)) return showCaError('Full name and position are required for teachers.');
  if (role === 'student' && !lrn) return showCaError('LRN is required for student accounts.');

  const btn = document.getElementById('ca-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

  try {
    const res = await fetch(BASE + '/api/accounts/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ role, username, email, password, name, position, lrn })
    });
    const data = await res.json();

    if (!data.ok) {
      showCaError(data.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-user-plus me-1"></i> Create Account';
      return;
    }

    caModal.hide();
    showToast(`Account for ${data.user.name} created successfully!`, 'success');
    // Auto update live data and stat cards
    await loadAccounts();

  } catch (err) {
    showCaError('An unexpected error occurred. Please try again.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-user-plus me-1"></i> Create Account';
  }
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

// Initial load
loadAccounts();
</script>
</body>
</html>
