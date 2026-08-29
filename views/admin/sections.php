<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'sections';
$gradeJson = json_encode(GRADE_LEVELS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Section Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
        <div class="page-title">Section Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Sections</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div><h3>Section Management</h3><p>Add, view, and edit class sections per grade level</p></div>
      <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus me-2"></i>Add Section
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label mb-1">Search Section</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" id="search-input" class="form-control" placeholder="Search by section name..." oninput="renderTable()">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Grade Level Filter</label>
            <select id="filter-grade" class="form-select" onchange="loadSections()">
              <option value="">All Grade Levels</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-light w-100" onclick="clearFilters()"><i class="fas fa-times me-1"></i>Clear Filters</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-th-large me-2" style="color:var(--primary);"></i>Section List (<span id="count-display">0</span> records)</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Grade Level</th>
                <th>Section Name</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="sections-tbody">
              <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add / Edit Section Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="section-modal-title"><i class="fas fa-plus me-2"></i>Add Section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="sec-id">
        <div class="mb-3">
          <label class="form-label">Grade Level <span class="text-danger">*</span></label>
          <select id="sec-grade" class="form-select" onchange="onGradeChangeInModal()">
            <option value="">— Select Grade Level —</option>
          </select>
        </div>

        <!-- Senior High School Strands Panel (Visible for Grade 11 & 12) -->
        <div id="shs-strands-panel" class="mb-3 p-3 bg-light rounded-3 border d-none">
          <label class="form-label mb-1 fw-semibold text-dark" style="font-size:.85rem;">
            <i class="fas fa-graduation-cap text-primary me-1"></i>Senior High School Strands
          </label>
          <div class="text-muted mb-2" style="font-size:.78rem;">Click a strand to set the section name:</div>
          <div class="d-flex flex-wrap gap-1 mb-2" id="shs-strand-buttons">
            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('STEM')">STEM</button>
            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('ABM')">ABM</button>
            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('HUMSS')">HUMSS</button>
            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('GAS')">GAS</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('TVL - ICT')">TVL - ICT</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('TVL - HE')">TVL - HE</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('TVL - Agri-Fishery')">TVL - Agri-Fishery</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:.8rem;" onclick="setStrandSection('TVL - Industrial Arts')">TVL - Industrial Arts</button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" id="sec-name-label">Section Name <span class="text-danger">*</span></label>
          <input type="text" id="sec-name" class="form-control" placeholder="e.g. Rizal, Mabini, STEM-A">
          <div class="form-text text-muted" id="sec-name-help" style="font-size:.76rem;">Enter the section name or strand title.</div>
        </div>
        <div id="sec-error" class="alert alert-danger d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="sec-submit-btn" onclick="submitSection()">
          <i class="fas fa-save me-1"></i> Save Section
        </button>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
const GRADE_LEVELS = <?= $gradeJson ?>;
showDesktopOnlyWarning();

let allSections = [];
let sectionModal;

// Populate grade dropdowns
['filter-grade', 'sec-grade'].forEach(id => {
  const sel = document.getElementById(id);
  GRADE_LEVELS.forEach(g => {
    const opt = document.createElement('option');
    opt.value = g;
    opt.textContent = g;
    sel.appendChild(opt);
  });
});

function onGradeChangeInModal() {
  const grade = document.getElementById('sec-grade').value;
  const panel = document.getElementById('shs-strands-panel');
  const label = document.getElementById('sec-name-label');
  const input = document.getElementById('sec-name');
  const help  = document.getElementById('sec-name-help');

  if (grade === 'Grade 11' || grade === 'Grade 12') {
    panel.classList.remove('d-none');
    label.innerHTML = `Strand / Section Name <span class="text-danger">*</span>`;
    input.placeholder = 'e.g. STEM, ABM, HUMSS, TVL - ICT';
    help.innerHTML = `<i class="fas fa-info-circle me-1"></i>Choose a Senior High School strand above or type a custom section name.`;
  } else {
    panel.classList.add('d-none');
    label.innerHTML = `Section Name <span class="text-danger">*</span>`;
    input.placeholder = 'e.g. Rizal, Mabini, Bonifacio';
    help.innerHTML = `Enter the section name for ${escHtml(grade || 'this grade level')}.`;
  }
}

function setStrandSection(strandName) {
  document.getElementById('sec-name').value = strandName;
  document.getElementById('sec-name').focus();
}

async function loadSections() {
  const grade = document.getElementById('filter-grade').value;
  const params = new URLSearchParams();
  if (grade) params.set('grade_level', grade);

  const tbody = document.getElementById('sections-tbody');
  tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

  try {
    const res = await fetch(`${BASE}/api/sections/index.php?${params}`);
    const data = await res.json();
    if (!data.ok) throw new Error(data.message);
    allSections = data.sections || [];
    renderTable();
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-3">Failed to load sections.</td></tr>`;
  }
}

function renderTable() {
  const query = document.getElementById('search-input').value.trim().toLowerCase();
  const filtered = query
    ? allSections.filter(s => s.section_name.toLowerCase().includes(query) || s.grade_level.toLowerCase().includes(query))
    : allSections;

  const tbody = document.getElementById('sections-tbody');
  document.getElementById('count-display').textContent = filtered.length;

  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><i class="fas fa-th-large"></i><p>No sections found. Click <strong>Add Section</strong> to create one.</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((s, i) => {
    const isSHS = (s.grade_level === 'Grade 11' || s.grade_level === 'Grade 12');
    const badgeType = isSHS
      ? `<span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 fw-semibold" style="font-size:.78rem; color:#6f42c1;"><i class="fas fa-graduation-cap me-1"></i>${escHtml(s.grade_level)}</span>`
      : `<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">${escHtml(s.grade_level)}</span>`;

    const sectionDisplay = isSHS
      ? `<strong>${escHtml(s.section_name)}</strong> <span class="badge bg-secondary bg-opacity-10 text-muted ms-1 fw-normal" style="font-size:.72rem;">Strand</span>`
      : `<strong>${escHtml(s.section_name)}</strong>`;

    return `<tr id="sec-row-${s.id}">
      <td>${i + 1}</td>
      <td>${badgeType}</td>
      <td>${sectionDisplay}</td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditModal(${s.id}, '${escHtml(s.grade_level)}', '${escHtml(s.section_name)}')" title="Edit">
          <i class="fas fa-edit"></i> Edit
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="deleteSection(${s.id}, '${escHtml(s.section_name)}', '${escHtml(s.grade_level)}')" title="Delete">
          <i class="fas fa-trash"></i> Delete
        </button>
      </td>
    </tr>`;
  }).join('');
}

function openAddModal() {
  if (!sectionModal) sectionModal = new bootstrap.Modal(document.getElementById('sectionModal'));
  document.getElementById('section-modal-title').innerHTML = '<i class="fas fa-plus me-2"></i>Add Section';
  document.getElementById('sec-id').value = '';
  document.getElementById('sec-grade').value = document.getElementById('filter-grade').value || '';
  document.getElementById('sec-name').value = '';
  document.getElementById('sec-error').classList.add('d-none');
  document.getElementById('sec-submit-btn').disabled = false;
  document.getElementById('sec-submit-btn').innerHTML = '<i class="fas fa-save me-1"></i> Save Section';
  onGradeChangeInModal();
  sectionModal.show();
}

function openEditModal(id, grade, name) {
  if (!sectionModal) sectionModal = new bootstrap.Modal(document.getElementById('sectionModal'));
  document.getElementById('section-modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Section';
  document.getElementById('sec-id').value = id;
  document.getElementById('sec-grade').value = grade;
  document.getElementById('sec-name').value = name;
  document.getElementById('sec-error').classList.add('d-none');
  document.getElementById('sec-submit-btn').disabled = false;
  document.getElementById('sec-submit-btn').innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
  onGradeChangeInModal();
  sectionModal.show();
}

async function submitSection() {
  document.getElementById('sec-error').classList.add('d-none');
  const id = document.getElementById('sec-id').value;
  const grade_level = document.getElementById('sec-grade').value;
  const section_name = document.getElementById('sec-name').value.trim();

  if (!grade_level || !section_name) {
    showError('Please fill in both Grade Level and Section Name.');
    return;
  }

  const btn = document.getElementById('sec-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  try {
    const res = await fetch(`${BASE}/api/sections/index.php`, {
      method: id ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(id ? { id: parseInt(id), grade_level, section_name } : { grade_level, section_name })
    });
    const data = await res.json();

    if (!data.ok) {
      showError(data.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Section';
      return;
    }

    sectionModal.hide();
    showToast(data.message, 'success');
    loadSections();
  } catch (err) {
    showError('An unexpected error occurred. Please try again.');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Section';
  }
}

async function deleteSection(id, name, grade) {
  const isSHS = (grade === 'Grade 11' || grade === 'Grade 12');
  const typeLabel = isSHS ? 'strand / section' : 'section';
  confirmModal('Delete Section', `Are you sure you want to delete ${typeLabel} <strong>${escHtml(name)}</strong> (${escHtml(grade)}${isSHS ? ' - Senior High School' : ''})?`, async () => {
    showLoading('Deleting Section...', 'Please wait...');
    try {
      const res = await fetch(`${BASE}/api/sections/index.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) {
        showToast(data.message, 'error');
        return;
      }
      showToast('Section deleted successfully.', 'success');
      loadSections();
    } catch {
      hideLoading();
      showToast('Failed to delete section.', 'error');
    }
  });
}

function showError(msg) {
  const el = document.getElementById('sec-error');
  el.textContent = msg;
  el.classList.remove('d-none');
}

function clearFilters() {
  document.getElementById('search-input').value = '';
  document.getElementById('filter-grade').value = '';
  loadSections();
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

loadSections();
</script>
</body>
</html>
