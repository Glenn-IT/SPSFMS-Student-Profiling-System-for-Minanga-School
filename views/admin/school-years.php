<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

$activePage = 'school_years';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Manage School Year — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    .badge-active-sy {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      font-weight: 700;
      padding: .4rem .85rem;
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      gap: .4rem;
    }
    .badge-inactive-sy {
      background: #f1f5f9;
      color: #64748b;
      border: 1px solid #e2e8f0;
      font-weight: 600;
      padding: .35rem .75rem;
      border-radius: 20px;
    }
    .pulse-dot {
      width: 8px;
      height: 8px;
      background-color: #22c55e;
      border-radius: 50%;
      display: inline-block;
      box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
      animation: pulse 1.6s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
      70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
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
        <div class="page-title">Manage School Year</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <li class="breadcrumb-item text-muted">Settings</li>
            <li class="breadcrumb-item active">Manage School Year</li>
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

    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
      <div>
        <h3 class="mb-1">School Year Management</h3>
        <p class="mb-0 text-muted">Configure academic calendar school years and toggle the active school year</p>
      </div>
      <button class="btn btn-primary px-3 py-2 fw-semibold" onclick="openAddModal()">
        <i class="fas fa-calendar-plus me-2"></i>Add School Year
      </button>
    </div>

    <!-- Overview KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid var(--success) !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">CURRENT ACTIVE S.Y.</div>
              <h4 class="fw-bold mb-0 text-success" id="kpi-active-sy">—</h4>
            </div>
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
              <i class="fas fa-calendar-check fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid var(--primary) !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">TOTAL SCHOOL YEARS</div>
              <h4 class="fw-bold mb-0 text-primary" id="kpi-total-sy">0</h4>
            </div>
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
              <i class="fas fa-layer-group fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid var(--secondary) !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">ENROLLED ACTIVE STUDENTS</div>
              <h4 class="fw-bold mb-0 text-dark" id="kpi-enrolled-sy">0</h4>
            </div>
            <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
              <i class="fas fa-user-graduate fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- School Years Table Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
        <span><i class="fas fa-calendar-alt me-2 text-primary"></i>School Years Catalogue</span>
        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-2 rounded-pill fw-semibold" id="sy-count-badge">0 Years</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern table-hover align-middle mb-0" id="school-years-table">
            <thead>
              <tr>
                <th style="width:60px;" class="text-center">#</th>
                <th>School Year</th>
                <th class="text-center">Status</th>
                <th>Schedule / Duration</th>
                <th class="text-center">Enrolled Students</th>
                <th class="text-center" style="width:240px;">Actions</th>
              </tr>
            </thead>
            <tbody id="sy-table-tbody">
              <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading school years...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="syModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="sy-modal-title"><i class="fas fa-calendar-plus me-2"></i>Add School Year</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="sy-id">
        
        <div class="mb-3">
          <label class="form-label fw-semibold">School Year Label <span class="text-danger">*</span></label>
          <input type="text" id="sy-label" class="form-control" placeholder="e.g. 2026-2027" maxlength="9">
          <div class="form-text">Must be 4-digit years separated by hyphen (e.g., 2026-2027).</div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label fw-semibold">Classes Start Date</label>
            <input type="date" id="sy-start" class="form-control">
          </div>
          <div class="col-6">
            <label class="form-label fw-semibold">Classes End Date</label>
            <input type="date" id="sy-end" class="form-control">
          </div>
        </div>

        <div class="form-check form-switch mb-2">
          <input class="form-check-input" type="checkbox" id="sy-is-active">
          <label class="form-check-label fw-semibold" for="sy-is-active">Set as Active School Year</label>
          <div class="form-text text-muted">Setting this as active will set all other school years as inactive.</div>
        </div>

        <div id="sy-error" class="alert alert-danger d-none mt-3" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4 fw-semibold" id="btn-save-sy" onclick="submitSchoolYear()">
          <i class="fas fa-save me-1"></i>Save School Year
        </button>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
showDesktopOnlyWarning();

let allSchoolYears = [];
let syModal = null;

async function loadSchoolYears() {
  const tbody = document.getElementById('sy-table-tbody');
  tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading school years...</td></tr>';
  
  try {
    const res = await fetch(`${BASE}/api/school-years/index.php`);
    const json = await res.json();

    if (!json.ok) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${json.message || 'Failed to load school years.'}</td></tr>`;
      return;
    }

    allSchoolYears = json.data || [];
    renderTable();
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error connecting to server.</td></tr>';
  }
}

function renderTable() {
  const tbody = document.getElementById('sy-table-tbody');
  document.getElementById('sy-count-badge').textContent = `${allSchoolYears.length} Years`;
  document.getElementById('kpi-total-sy').textContent = allSchoolYears.length;

  if (allSchoolYears.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No school years found. Click "Add School Year" to create one.</td></tr>';
    document.getElementById('kpi-active-sy').textContent = 'None';
    document.getElementById('kpi-enrolled-sy').textContent = '0';
    return;
  }

  let activeSy = allSchoolYears.find(s => parseInt(s.is_active) === 1);
  if (activeSy) {
    document.getElementById('kpi-active-sy').textContent = activeSy.year_label;
    document.getElementById('kpi-enrolled-sy').textContent = activeSy.student_count || 0;
  } else {
    document.getElementById('kpi-active-sy').textContent = 'None';
    document.getElementById('kpi-enrolled-sy').textContent = '0';
  }

  tbody.innerHTML = allSchoolYears.map((sy, i) => {
    const isActive = parseInt(sy.is_active) === 1;
    const statusBadge = isActive
      ? `<span class="badge-active-sy"><span class="pulse-dot"></span> Active</span>`
      : `<span class="badge-inactive-sy">Archived</span>`;

    const schedule = (sy.start_date || sy.end_date)
      ? `<small class="text-muted"><i class="far fa-clock me-1"></i>${formatDate(sy.start_date)} — ${formatDate(sy.end_date)}</small>`
      : `<span class="text-muted small">Not specified</span>`;

    const students = `<span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-2 py-1">${sy.student_count || 0} students</span>`;

    let actionBtns = ``;
    if (!isActive) {
      actionBtns += `
        <button class="btn btn-sm btn-outline-success me-1" onclick="setActiveYear(${sy.id}, '${escHtml(sy.year_label)}')" title="Set Active">
          <i class="fas fa-check-circle me-1"></i>Activate
        </button>
      `;
    }

    actionBtns += `
      <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${sy.id})" title="Edit">
        <i class="fas fa-edit"></i>
      </button>
    `;

    if (!isActive && parseInt(sy.student_count) === 0) {
      actionBtns += `
        <button class="btn btn-sm btn-outline-danger" onclick="deleteSchoolYear(${sy.id}, '${escHtml(sy.year_label)}')" title="Delete">
          <i class="fas fa-trash"></i>
        </button>
      `;
    } else if (!isActive) {
      actionBtns += `
        <button class="btn btn-sm btn-light text-muted" disabled title="Cannot delete: has enrolled students">
          <i class="fas fa-lock"></i>
        </button>
      `;
    }

    return `
      <tr class="${isActive ? 'table-success bg-opacity-10' : ''}">
        <td class="text-center fw-bold text-muted">${i + 1}</td>
        <td>
          <div class="fw-bold fs-6">${escHtml(sy.year_label)}</div>
          ${isActive ? '<small class="text-success fw-semibold"><i class="fas fa-star me-1"></i>Current School Calendar</small>' : ''}
        </td>
        <td class="text-center">${statusBadge}</td>
        <td>${schedule}</td>
        <td class="text-center">${students}</td>
        <td class="text-center">${actionBtns}</td>
      </tr>
    `;
  }).join('');
}

function formatDate(d) {
  if (!d) return 'TBA';
  const opt = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(d + 'T00:00:00').toLocaleDateString(undefined, opt);
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

function openAddModal() {
  if (!syModal) syModal = new bootstrap.Modal(document.getElementById('syModal'));
  document.getElementById('sy-modal-title').innerHTML = '<i class="fas fa-calendar-plus me-2"></i>Add School Year';
  document.getElementById('sy-id').value = '';
  
  // Suggest next school year
  let nextLabel = '';
  if (allSchoolYears.length > 0) {
    const latest = allSchoolYears[0].year_label;
    const parts = latest.split('-');
    if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
      nextLabel = `${parseInt(parts[1])}-${parseInt(parts[1]) + 1}`;
    }
  }
  document.getElementById('sy-label').value = nextLabel || '2026-2027';
  document.getElementById('sy-start').value = '';
  document.getElementById('sy-end').value = '';
  document.getElementById('sy-is-active').checked = false;
  document.getElementById('sy-error').classList.add('d-none');
  syModal.show();
}

function openEditModal(id) {
  const sy = allSchoolYears.find(s => parseInt(s.id) === parseInt(id));
  if (!sy) return;

  if (!syModal) syModal = new bootstrap.Modal(document.getElementById('syModal'));
  document.getElementById('sy-modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit School Year';
  document.getElementById('sy-id').value = sy.id;
  document.getElementById('sy-label').value = sy.year_label;
  document.getElementById('sy-start').value = sy.start_date || '';
  document.getElementById('sy-end').value = sy.end_date || '';
  document.getElementById('sy-is-active').checked = (parseInt(sy.is_active) === 1);
  document.getElementById('sy-error').classList.add('d-none');
  syModal.show();
}

async function submitSchoolYear() {
  const id       = document.getElementById('sy-id').value;
  const label    = document.getElementById('sy-label').value.trim();
  const start    = document.getElementById('sy-start').value;
  const end      = document.getElementById('sy-end').value;
  const isActive = document.getElementById('sy-is-active').checked ? 1 : 0;
  const errBox   = document.getElementById('sy-error');

  errBox.classList.add('d-none');

  if (!label) {
    errBox.textContent = 'School year label is required.';
    errBox.classList.remove('d-none');
    return;
  }

  if (!/^\d{4}-\d{4}$/.test(label)) {
    errBox.textContent = 'Invalid format. Use YYYY-YYYY (e.g., 2026-2027).';
    errBox.classList.remove('d-none');
    return;
  }

  const btn = document.getElementById('btn-save-sy');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  try {
    const res = await fetch(`${BASE}/api/school-years/index.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: id ? 'edit' : 'add',
        id: id ? parseInt(id) : undefined,
        year_label: label,
        start_date: start || null,
        end_date: end || null,
        is_active: isActive
      })
    });

    const json = await res.json();
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-1"></i>Save School Year';

    if (!json.ok) {
      errBox.textContent = json.message || 'Failed to save school year.';
      errBox.classList.remove('d-none');
      return;
    }

    syModal.hide();
    showToast(json.message, 'success');
    loadSchoolYears();
  } catch (err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-1"></i>Save School Year';
    errBox.textContent = 'Error communicating with server.';
    errBox.classList.remove('d-none');
  }
}

async function setActiveYear(id, label) {
  confirmModal('Activate School Year', `Are you sure you want to set <strong>${escHtml(label)}</strong> as the active school year across the entire system?`, async () => {
    showLoading('Activating School Year...', 'Updating system academic calendar...');
    try {
      const res = await fetch(`${BASE}/api/school-years/index.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'set_active', id: parseInt(id) })
      });
      const json = await res.json();
      hideLoading();

      if (!json.ok) {
        showToast(json.message || 'Failed to activate.', 'error');
        return;
      }

      showToast(json.message, 'success');
      loadSchoolYears();
    } catch (err) {
      hideLoading();
      showToast('Error connecting to server.', 'error');
    }
  });
}

async function deleteSchoolYear(id, label) {
  confirmModal('Delete School Year', `Are you sure you want to delete <strong>${escHtml(label)}</strong>? This action cannot be undone.`, async () => {
    showLoading('Deleting School Year...', 'Removing record...');
    try {
      const res = await fetch(`${BASE}/api/school-years/index.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: parseInt(id) })
      });
      const json = await res.json();
      hideLoading();

      if (!json.ok) {
        showToast(json.message || 'Failed to delete.', 'error');
        return;
      }

      showToast(json.message, 'success');
      loadSchoolYears();
    } catch (err) {
      hideLoading();
      showToast('Error connecting to server.', 'error');
    }
  });
}

loadSchoolYears();
</script>
</body>
</html>
