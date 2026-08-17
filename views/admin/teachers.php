<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'teachers';
$gradeJson = json_encode(GRADE_LEVELS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Teacher Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Teacher Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Teachers</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between">
      <div><h3>Teacher Management</h3><p>View, edit, and manage teacher accounts</p></div>
      <button class="btn btn-primary" onclick="openAddTeacherModal()">
        <i class="fas fa-user-plus me-2"></i>Add Teacher
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label mb-1">Search</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" id="search-input" class="form-control" placeholder="Search by name, username, grade, or section...">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1">Status</label>
            <select id="filter-status" class="form-select">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-light w-100" onclick="clearFilters()"><i class="fas fa-times"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-chalkboard-teacher me-2" style="color:var(--primary);"></i>Teacher List (<span id="count-display">0</span> records)</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead>
              <tr>
                <th>#</th><th>Name</th><th>Username</th><th>Grade</th><th>Advisory Section</th><th>Status</th><th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="teachers-tbody">
              <tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Add Teacher Modal ── -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Teacher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" id="at-name" class="form-control" placeholder="e.g. Juan Dela Cruz">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Grade <span class="text-danger">*</span></label>
            <select id="at-grade" class="form-select" onchange="loadSectionsFor('at-section','at-grade')">
              <option value="">— Select Grade —</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Advisory Section <span class="text-danger">*</span></label>
            <select id="at-section" class="form-select" disabled>
              <option value="">— Select Grade first —</option>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Username <span class="text-danger">*</span></label>
          <input type="text" id="at-username" class="form-control" placeholder="Login username" autocomplete="off">
        </div>
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" id="at-email" class="form-control" placeholder="email@example.com">
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" id="at-password" class="form-control" placeholder="Min. 6 characters" autocomplete="new-password">
          </div>
          <div class="col-6">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" id="at-confirm" class="form-control" placeholder="Repeat password">
          </div>
        </div>
        <div id="at-error" class="alert alert-danger mt-3 d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="at-submit-btn" onclick="submitAddTeacher()">
          <i class="fas fa-user-plus me-1"></i> Add Teacher
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── Edit Teacher Modal ── -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Teacher</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="et-id">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" id="et-username" class="form-control" readonly style="background:var(--gray-100);font-family:monospace;">
        </div>
        <div class="mb-3">
          <label class="form-label">Full Name <span class="text-danger">*</span></label>
          <input type="text" id="et-name" class="form-control" placeholder="Full name">
        </div>
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" id="et-email" class="form-control" placeholder="email@example.com">
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label">Grade <span class="text-danger">*</span></label>
            <select id="et-grade" class="form-select" onchange="loadSectionsFor('et-section','et-grade')">
              <option value="">— Select Grade —</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Advisory Section <span class="text-danger">*</span></label>
            <select id="et-section" class="form-select" disabled>
              <option value="">— Select Grade first —</option>
            </select>
          </div>
        </div>
        <hr>
        <div class="mb-1">
          <label class="form-label fw-semibold" style="font-size:.82rem;">
            <i class="fas fa-key me-1 text-warning"></i>Reset Password
            <span class="text-muted fw-normal ms-1">(leave blank to keep current)</span>
          </label>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><input type="password" id="et-new-pw" class="form-control" placeholder="New password" autocomplete="new-password"></div>
          <div class="col-6"><input type="password" id="et-confirm-pw" class="form-control" placeholder="Confirm password"></div>
        </div>
        <div id="et-error" class="alert alert-danger d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-danger" onclick="deleteTeacher()">
          <i class="fas fa-trash me-1"></i> Delete Account
        </button>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="et-save-btn" onclick="saveTeacher()">
            <i class="fas fa-save me-1"></i> Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE        = '<?= BASE_URL ?>';
const GRADE_LEVELS = <?= $gradeJson ?>;
showDesktopOnlyWarning();

let allTeachers  = [];
let addTeacherModal, editModal;

/* ── Populate grade dropdowns on page load ── */
['at-grade','et-grade'].forEach(id => {
  const sel = document.getElementById(id);
  GRADE_LEVELS.forEach(g => sel.innerHTML += `<option value="${g}">${g}</option>`);
});

const DEFAULT_SECTIONS = {
  'Grade 1': ['Rizal', 'Mabini'],
  'Grade 2': ['Bonifacio', 'Mabini'],
  'Grade 3': ['Mabini', 'Rizal'],
  'Grade 4': ['Bonifacio', 'Aguinaldo'],
  'Grade 5': ['Bonifacio', 'Del Pilar'],
  'Grade 6': ['Bonifacio', 'Silang'],
  'Grade 7': ['Rizal', 'Mabini'],
  'Grade 8': ['Luna', 'Rizal'],
  'Grade 9': ['Luna', 'Bonifacio'],
  'Grade 10': ['Mabini', 'Rizal'],
  'Grade 11': ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL'],
  'Grade 12': ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL']
};

async function loadSectionsFor(sectionSelId, gradeSelId, preselect = '') {
  const gradeVal = document.getElementById(gradeSelId).value;
  const secSel   = document.getElementById(sectionSelId);
  secSel.innerHTML = '<option value="">Loading...</option>';
  secSel.disabled  = true;

  if (!gradeVal) {
    secSel.innerHTML = '<option value="">— Select Grade first —</option>';
    return;
  }

  let list = [];
  try {
    const res  = await fetch(`${BASE}/api/sections/index.php?grade_level=${encodeURIComponent(gradeVal)}`);
    const data = await res.json();
    if (data.ok && data.sections && data.sections.length > 0) {
      list = data.sections.map(s => s.section_name);
    }
  } catch (e) {
    console.error(e);
  }

  const defs = DEFAULT_SECTIONS[gradeVal] || ['Rizal', 'Mabini', 'Bonifacio', 'Luna', 'STEM', 'ABM', 'HUMSS'];
  defs.forEach(d => {
    if (!list.includes(d)) list.push(d);
  });

  if (preselect && !list.includes(preselect)) {
    list.push(preselect);
  }

  secSel.innerHTML = '<option value="">— Select Advisory Section —</option>';
  list.forEach(secName => {
    const opt = document.createElement('option');
    opt.value = secName;
    opt.textContent = secName;
    if (secName === preselect) opt.selected = true;
    secSel.appendChild(opt);
  });
  secSel.disabled = false;
}

/* ══ ADD TEACHER ══ */
function openAddTeacherModal() {
  if (!addTeacherModal) addTeacherModal = new bootstrap.Modal(document.getElementById('addTeacherModal'));
  ['at-name','at-username','at-email','at-password','at-confirm'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('at-grade').value    = '';
  document.getElementById('at-section').innerHTML = '<option value="">— Select Grade first —</option>';
  document.getElementById('at-section').disabled  = true;
  document.getElementById('at-error').classList.add('d-none');
  document.getElementById('at-submit-btn').disabled = false;
  document.getElementById('at-submit-btn').innerHTML = '<i class="fas fa-user-plus me-1"></i> Add Teacher';
  addTeacherModal.show();
}

async function submitAddTeacher() {
  document.getElementById('at-error').classList.add('d-none');
  const name            = document.getElementById('at-name').value.trim();
  const advisory_grade  = document.getElementById('at-grade').value;
  const advisory_subject= document.getElementById('at-section').value;
  const username        = document.getElementById('at-username').value.trim();
  const email           = document.getElementById('at-email').value.trim();
  const password        = document.getElementById('at-password').value;
  const confirm         = document.getElementById('at-confirm').value;

  if (!name)             return showError('at-error','Full name is required.');
  if (!advisory_grade)   return showError('at-error','Please select a grade level.');
  if (!advisory_subject) return showError('at-error','Please select an advisory section.');
  if (!username || !email || !password || !confirm) return showError('at-error','Please fill in all required fields.');
  if (password.length < 6) return showError('at-error','Password must be at least 6 characters.');
  if (password !== confirm) return showError('at-error','Passwords do not match.');

  const btn = document.getElementById('at-submit-btn');
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding...';

  try {
    const res  = await fetch(`${BASE}/api/accounts/create.php`, {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ role:'teacher', name, advisory_grade, advisory_subject, username, email, password })
    });
    const data = await res.json();
    if (!data.ok) {
      showError('at-error', data.message);
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-plus me-1"></i> Add Teacher';
      return;
    }
    addTeacherModal.hide();
    showToast(`Teacher ${data.user.name} added successfully!`, 'success');
    loadTeachers();
  } catch {
    showError('at-error','An unexpected error occurred.');
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-user-plus me-1"></i> Add Teacher';
  }
}

/* ══ LOAD & RENDER TABLE ══ */
async function loadTeachers() {
  const search = document.getElementById('search-input').value.trim();
  const params = new URLSearchParams();
  if (search) params.set('search', search);
  try {
    const res  = await fetch(`${BASE}/api/teachers/index.php?${params}`);
    const data = await res.json();
    if (!data.ok) throw new Error();
    allTeachers = data.teachers;
    renderTable();
  } catch {
    document.getElementById('teachers-tbody').innerHTML =
      '<tr><td colspan="7" class="text-center text-danger py-3">Failed to load teachers.</td></tr>';
  }
}

function renderTable() {
  const statusFilter = document.getElementById('filter-status').value;
  const filtered = statusFilter ? allTeachers.filter(t => t.status === statusFilter) : allTeachers;
  const tbody = document.getElementById('teachers-tbody');
  document.getElementById('count-display').textContent = filtered.length;
  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-chalkboard-teacher"></i><p>No teachers found.</p></div></td></tr>`;
    return;
  }
  tbody.innerHTML = filtered.map((t, i) => {
    const isActive = t.status === 'active';
    return `<tr id="teacher-row-${t.id}">
      <td>${i + 1}</td>
      <td><strong>${escHtml(t.name)}</strong></td>
      <td><span style="font-family:monospace;font-size:.82rem;">${escHtml(t.username)}</span></td>
      <td><span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">${escHtml(t.advisory_grade || '—')}</span></td>
      <td style="font-size:.85rem;">${escHtml(t.advisory_subject || '—')}</td>
      <td>
        <span class="badge ${isActive?'bg-success':'bg-danger'} bg-opacity-15 text-${isActive?'success':'danger'} fw-semibold" id="t-badge-${t.id}">
          ${isActive ? 'Active' : 'Inactive'}
        </span>
      </td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditModal(${t.id})" title="Edit">
          <i class="fas fa-edit"></i> Edit
        </button>
        <button class="btn btn-sm ${isActive?'btn-outline-danger':'btn-outline-success'}" id="t-status-btn-${t.id}"
          onclick="toggleStatus(${t.id},'${isActive?'inactive':'active'}')">
          <i class="fas ${isActive?'fa-ban':'fa-check'}"></i> ${isActive?'Deactivate':'Activate'}
        </button>
      </td>
    </tr>`;
  }).join('');
}

/* ══ TOGGLE STATUS ══ */
async function toggleStatus(id, newStatus) {
  const label = newStatus === 'active' ? 'activate' : 'deactivate';
  confirmModal('Confirm', `Are you sure you want to ${label} this teacher?`, async () => {
    showLoading('Updating Status...', 'Please wait...');
    try {
      const res  = await fetch(`${BASE}/api/accounts/toggle.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id, status: newStatus })
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) { showToast(data.message,'error'); return; }
      const t = allTeachers.find(x => x.id === id);
      if (t) t.status = newStatus;
      renderTable();
      showToast(`Teacher ${label}d successfully.`,'success');
    } catch { hideLoading(); showToast('Failed to update status.','error'); }
  });
}

/* ══ EDIT TEACHER ══ */
async function openEditModal(id) {
  if (!editModal) editModal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
  const t = allTeachers.find(x => x.id === id);
  if (!t) return;

  document.getElementById('et-id').value        = t.id;
  document.getElementById('et-username').value  = t.username;
  document.getElementById('et-name').value      = t.name;
  document.getElementById('et-email').value     = t.email || '';
  document.getElementById('et-new-pw').value    = '';
  document.getElementById('et-confirm-pw').value= '';
  document.getElementById('et-error').classList.add('d-none');
  document.getElementById('et-save-btn').disabled = false;
  document.getElementById('et-save-btn').innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';

  // Set grade and load sections
  document.getElementById('et-grade').value = t.advisory_grade || '';
  if (t.advisory_grade) {
    await loadSectionsFor('et-section', 'et-grade', t.advisory_subject || '');
  } else {
    document.getElementById('et-section').innerHTML = '<option value="">— Select Grade first —</option>';
    document.getElementById('et-section').disabled = true;
  }

  editModal.show();
}

async function saveTeacher() {
  document.getElementById('et-error').classList.add('d-none');
  const id              = parseInt(document.getElementById('et-id').value);
  const name            = document.getElementById('et-name').value.trim();
  const email           = document.getElementById('et-email').value.trim();
  const advisory_grade  = document.getElementById('et-grade').value;
  const advisory_subject= document.getElementById('et-section').value;
  const newPw           = document.getElementById('et-new-pw').value;
  const confirmPw       = document.getElementById('et-confirm-pw').value;

  if (!name || !email)   return showError('et-error','Name and email are required.');
  if (!advisory_grade)   return showError('et-error','Please select a grade level.');
  if (!advisory_subject) return showError('et-error','Please select an advisory section.');
  if (newPw && newPw.length < 6) return showError('et-error','New password must be at least 6 characters.');
  if (newPw && newPw !== confirmPw) return showError('et-error','Passwords do not match.');
  if (newPw && newPw.length < 6) return showError('et-error','New password must be at least 6 characters.');
  if (newPw && newPw !== confirmPw) return showError('et-error','Passwords do not match.');

  const btn = document.getElementById('et-save-btn');
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  try {
    const res  = await fetch(`${BASE}/api/teachers/index.php`, {
      method:'PUT', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id, name, email, advisory_grade, advisory_subject, new_password: newPw })
    });
    const data = await res.json();
    if (!data.ok) {
      showError('et-error', data.message);
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
      return;
    }
    const idx = allTeachers.findIndex(x => x.id === id);
    if (idx >= 0) allTeachers[idx] = { ...allTeachers[idx], ...data.teacher };
    renderTable();
    editModal.hide();
    showToast('Teacher updated successfully!','success');
  } catch {
    showError('et-error','An unexpected error occurred.');
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
  }
}

async function deleteTeacher() {
  const id   = parseInt(document.getElementById('et-id').value);
  const name = document.getElementById('et-name').value || 'this teacher';
  confirmModal('Delete Teacher',`Permanently delete <strong>${escHtml(name)}</strong>'s account? This cannot be undone.`, async () => {
    showLoading('Deleting...','Please wait...');
    try {
      const res  = await fetch(`${BASE}/api/teachers/index.php`,{
        method:'DELETE', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id})
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) { showToast(data.message,'error'); return; }
      allTeachers = allTeachers.filter(x => x.id !== id);
      renderTable();
      editModal.hide();
      showToast('Teacher account deleted.','success');
    } catch { hideLoading(); showToast('Failed to delete teacher.','error'); }
  });
}

/* ══ HELPERS ══ */
function showError(elId, msg) {
  const el = document.getElementById(elId);
  el.textContent = msg; el.classList.remove('d-none');
}
function clearFilters() {
  document.getElementById('search-input').value  = '';
  document.getElementById('filter-status').value = '';
  loadTeachers();
}
function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str ?? ''; return d.innerHTML;
}

let searchTimer;
document.getElementById('search-input').addEventListener('input', () => {
  clearTimeout(searchTimer); searchTimer = setTimeout(loadTeachers, 400);
});
document.getElementById('filter-status').addEventListener('change', renderTable);

loadTeachers();
</script>
</body>
</html>
