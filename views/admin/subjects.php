<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
header('Location: ' . BASE_URL . '/views/admin/dashboard.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Subject Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Subject Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Subjects</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div><h3>Subject Management</h3><p>Manage subjects per grade level group (Elementary, JHS, SHS)</p></div>
      <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus me-2"></i>Add Subject
      </button>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="subject-tabs">
      <li class="nav-item"><a class="nav-link active" href="#" onclick="switchTab('elementary',this)"><i class="fas fa-school me-1"></i>Elementary (G1–G6)</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('jhs',this)"><i class="fas fa-book me-1"></i>Junior High School (G7–G10)</a></li>
      <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('shs',this)"><i class="fas fa-graduation-cap me-1"></i>Senior High School (G11–G12)</a></li>
    </ul>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2" style="color:var(--primary);"></i>Subjects — <span id="tab-label">Elementary</span> (<span id="count-display">0</span>)</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>Subject Name</th><th>Level Group</th><th class="text-center">Actions</th></tr></thead>
            <tbody id="subjects-tbody">
              <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="subject-modal-title"><i class="fas fa-plus me-2"></i>Add Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="s-id">
        <div class="mb-3">
          <label class="form-label">Grade Level Group <span class="text-danger">*</span></label>
          <select id="s-type" class="form-select">
            <option value="elementary">Elementary (Grade 1–6)</option>
            <option value="jhs">Junior High School (Grade 7–10)</option>
            <option value="shs">Senior High School (Grade 11–12)</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Subject Name <span class="text-danger">*</span></label>
          <input type="text" id="s-name" class="form-control" placeholder="e.g. Mathematics">
        </div>
        <div id="s-error" class="alert alert-danger d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="s-submit-btn" onclick="submitSubject()">
          <i class="fas fa-save me-1"></i> Save Subject
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

let currentTab = 'elementary';
let subjectModal;
const tabLabels = { elementary: 'Elementary', jhs: 'Junior High School', shs: 'Senior High School' };

function switchTab(type, el) {
  event.preventDefault();
  currentTab = type;
  document.querySelectorAll('#subject-tabs .nav-link').forEach(a => a.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('tab-label').textContent = tabLabels[type];
  loadSubjects();
}

async function loadSubjects() {
  const tbody = document.getElementById('subjects-tbody');
  tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';
  try {
    const res  = await fetch(`${BASE}/api/subjects/index.php?grade_type=${currentTab}`);
    const data = await res.json();
    if (!data.ok) throw new Error();
    renderSubjects(data.subjects);
  } catch {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Failed to load subjects.</td></tr>';
  }
}

function renderSubjects(subjects) {
  const tbody = document.getElementById('subjects-tbody');
  document.getElementById('count-display').textContent = subjects.length;
  if (!subjects.length) {
    tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><i class="fas fa-book-open"></i><p>No subjects found for this level. Click <strong>Add Subject</strong> to get started.</p></div></td></tr>`;
    return;
  }
  const typeColors = { elementary: 'success', jhs: 'primary', shs: 'warning' };
  const typeLabels = { elementary: 'Elementary', jhs: 'JHS', shs: 'SHS' };
  tbody.innerHTML = subjects.map((s, i) => `
    <tr id="subj-row-${s.id}">
      <td>${i + 1}</td>
      <td><strong>${escHtml(s.name)}</strong></td>
      <td><span class="badge bg-${typeColors[s.grade_type]} bg-opacity-15 text-${typeColors[s.grade_type]} fw-semibold">${typeLabels[s.grade_type]}</span></td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditModal(${s.id},'${escHtml(s.name)}','${s.grade_type}')" title="Edit">
          <i class="fas fa-edit"></i> Edit
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="deleteSubject(${s.id},'${escHtml(s.name)}')" title="Delete">
          <i class="fas fa-trash"></i> Delete
        </button>
      </td>
    </tr>`).join('');
}

function openAddModal() {
  if (!subjectModal) subjectModal = new bootstrap.Modal(document.getElementById('subjectModal'));
  document.getElementById('subject-modal-title').innerHTML = '<i class="fas fa-plus me-2"></i>Add Subject';
  document.getElementById('s-id').value   = '';
  document.getElementById('s-name').value = '';
  document.getElementById('s-type').value = currentTab;
  document.getElementById('s-error').classList.add('d-none');
  document.getElementById('s-submit-btn').disabled = false;
  document.getElementById('s-submit-btn').innerHTML = '<i class="fas fa-save me-1"></i> Save Subject';
  subjectModal.show();
}

function openEditModal(id, name, type) {
  if (!subjectModal) subjectModal = new bootstrap.Modal(document.getElementById('subjectModal'));
  document.getElementById('subject-modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Subject';
  document.getElementById('s-id').value   = id;
  document.getElementById('s-name').value = name;
  document.getElementById('s-type').value = type;
  document.getElementById('s-type').disabled = true; // can't change level group when editing
  document.getElementById('s-error').classList.add('d-none');
  document.getElementById('s-submit-btn').disabled = false;
  document.getElementById('s-submit-btn').innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
  subjectModal.show();
}

async function submitSubject() {
  document.getElementById('s-error').classList.add('d-none');
  const id   = document.getElementById('s-id').value;
  const name = document.getElementById('s-name').value.trim();
  const type = document.getElementById('s-type').value;

  if (!name) {
    const el = document.getElementById('s-error');
    el.textContent = 'Subject name is required.';
    el.classList.remove('d-none');
    return;
  }

  const btn = document.getElementById('s-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  try {
    const res = await fetch(`${BASE}/api/subjects/index.php`, {
      method: id ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(id ? { id: parseInt(id), name } : { name, grade_type: type })
    });
    const data = await res.json();

    if (!data.ok) {
      const el = document.getElementById('s-error');
      el.textContent = data.message;
      el.classList.remove('d-none');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Subject';
      return;
    }

    document.getElementById('s-type').disabled = false;
    subjectModal.hide();
    showToast(data.message, 'success');
    loadSubjects();
  } catch {
    const el = document.getElementById('s-error');
    el.textContent = 'An unexpected error occurred.';
    el.classList.remove('d-none');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Subject';
  }
}

async function deleteSubject(id, name) {
  confirmModal('Delete Subject', `Are you sure you want to delete <strong>${escHtml(name)}</strong>? Existing grade records that used this subject will not be removed.`, async () => {
    showLoading('Deleting Subject...', 'Please wait...');
    try {
      const res  = await fetch(`${BASE}/api/subjects/index.php`, {
        method: 'DELETE', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) { showToast(data.message, 'error'); return; }
      document.getElementById('subj-row-' + id)?.remove();
      const cnt = document.getElementById('count-display');
      cnt.textContent = Math.max(0, parseInt(cnt.textContent) - 1);
      showToast('Subject deleted successfully.', 'success');
    } catch {
      hideLoading();
      showToast('Failed to delete subject.', 'error');
    }
  });
}

function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str ?? ''; return d.innerHTML;
}

// Close edit modal re-enable type select
document.getElementById('subjectModal').addEventListener('hidden.bs.modal', () => {
  document.getElementById('s-type').disabled = false;
});

loadSubjects();
</script>
</body>
</html>
