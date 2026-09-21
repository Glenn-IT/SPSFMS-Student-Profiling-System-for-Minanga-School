<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'announcements';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Announcements — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
        <div class="page-title">Announcements</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Announcements</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
      <div>
        <h3>Announcement Management</h3>
        <p>Post, edit, and remove school announcements visible to students and/or teachers.</p>
      </div>
      <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus me-2"></i>New Announcement
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-5">
            <label class="form-label mb-1">Search</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" id="search-input" class="form-control" placeholder="Search by title or content..." oninput="renderTable()">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label mb-1">Audience Filter</label>
            <select id="filter-audience" class="form-select" onchange="loadAnnouncements()">
              <option value="">All Audiences</option>
              <option value="all">Everyone (All)</option>
              <option value="student">Students Only</option>
              <option value="teacher">Teachers Only</option>
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
        <span><i class="fas fa-bullhorn me-2" style="color:var(--primary);"></i>Announcements (<span id="count-display">0</span> records)</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead>
              <tr>
                <th style="width:3%">#</th>
                <th style="width:28%">Title</th>
                <th style="width:37%">Body</th>
                <th style="width:12%" class="text-center">Audience</th>
                <th style="width:12%" class="text-center">Date Posted</th>
                <th style="width:8%" class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="announcements-tbody">
              <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add / Edit Announcement Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ann-modal-title"><i class="fas fa-plus me-2"></i>New Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ann-id">

        <div class="mb-3">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" id="ann-title" class="form-control" placeholder="e.g. No Classes on October 4, 2025" maxlength="255">
        </div>

        <div class="mb-3">
          <label class="form-label">Body / Message <span class="text-danger">*</span></label>
          <textarea id="ann-body" class="form-control" rows="5" placeholder="Write the full announcement message here..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Audience <span class="text-danger">*</span></label>
          <select id="ann-audience" class="form-select">
            <option value="all">Everyone (Students &amp; Teachers)</option>
            <option value="student">Students Only</option>
            <option value="teacher">Teachers Only</option>
          </select>
          <div class="form-text text-muted" style="font-size:.76rem;">
            <i class="fas fa-info-circle me-1"></i>Choose who will see this announcement on their dashboard.
          </div>
        </div>

        <div id="ann-error" class="alert alert-danger d-none" style="font-size:.85rem;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="ann-submit-btn" onclick="submitAnnouncement()">
          <i class="fas fa-paper-plane me-1"></i> Post Announcement
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

let allAnnouncements = [];
let annModal;

// ──────────────────────────────────────────
// Load
// ──────────────────────────────────────────
async function loadAnnouncements() {
  const audience = document.getElementById('filter-audience').value;
  const params   = new URLSearchParams();
  if (audience) params.set('audience', audience);

  const tbody = document.getElementById('announcements-tbody');
  tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>';

  try {
    const res  = await fetch(`${BASE}/api/announcements/index.php?${params}`);
    const data = await res.json();
    if (!data.ok) throw new Error(data.message);
    allAnnouncements = data.announcements || [];
    renderTable();
  } catch (err) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">Failed to load announcements.</td></tr>`;
  }
}

// ──────────────────────────────────────────
// Render
// ──────────────────────────────────────────
function renderTable() {
  const query    = document.getElementById('search-input').value.trim().toLowerCase();
  const filtered = query
    ? allAnnouncements.filter(a =>
        a.title.toLowerCase().includes(query) ||
        a.body.toLowerCase().includes(query))
    : allAnnouncements;

  const tbody = document.getElementById('announcements-tbody');
  document.getElementById('count-display').textContent = filtered.length;

  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="fas fa-bullhorn"></i><p>No announcements found. Click <strong>New Announcement</strong> to post one.</p></div></td></tr>`;
    return;
  }

  tbody.innerHTML = filtered.map((a, i) => {
    const audienceBadge = audienceBadgeHtml(a.audience);
    const bodyPreview   = escHtml(a.body.length > 80 ? a.body.substring(0, 80) + '…' : a.body);
    const datePosted    = formatDate(a.posted_at);

    return `<tr id="ann-row-${a.id}">
      <td>${i + 1}</td>
      <td><strong>${escHtml(a.title)}</strong></td>
      <td class="text-muted small">${bodyPreview}</td>
      <td class="text-center">${audienceBadge}</td>
      <td class="text-center small text-muted">${datePosted}</td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-secondary me-1" onclick='openEditModal(${JSON.stringify(a)})' title="Edit">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="deleteAnnouncement(${a.id}, '${escHtml(a.title)}')" title="Delete">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>`;
  }).join('');
}

function audienceBadgeHtml(audience) {
  if (audience === 'student') return `<span class="badge bg-success bg-opacity-10 text-success fw-semibold border border-success border-opacity-25" style="font-size:.78rem;"><i class="fas fa-user-graduate me-1"></i>Students</span>`;
  if (audience === 'teacher') return `<span class="badge bg-warning bg-opacity-10 text-warning fw-semibold border border-warning border-opacity-25" style="font-size:.78rem; color:#b06a00!important;"><i class="fas fa-chalkboard-teacher me-1"></i>Teachers</span>`;
  return `<span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;"><i class="fas fa-users me-1"></i>Everyone</span>`;
}

function formatDate(dateStr) {
  if (!dateStr) return '—';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ──────────────────────────────────────────
// Modals
// ──────────────────────────────────────────
function openAddModal() {
  if (!annModal) annModal = new bootstrap.Modal(document.getElementById('announcementModal'));
  document.getElementById('ann-modal-title').innerHTML = '<i class="fas fa-plus me-2"></i>New Announcement';
  document.getElementById('ann-id').value       = '';
  document.getElementById('ann-title').value    = '';
  document.getElementById('ann-body').value     = '';
  document.getElementById('ann-audience').value = 'all';
  document.getElementById('ann-error').classList.add('d-none');
  const btn = document.getElementById('ann-submit-btn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Post Announcement';
  annModal.show();
}

function openEditModal(ann) {
  if (!annModal) annModal = new bootstrap.Modal(document.getElementById('announcementModal'));
  document.getElementById('ann-modal-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Announcement';
  document.getElementById('ann-id').value       = ann.id;
  document.getElementById('ann-title').value    = ann.title;
  document.getElementById('ann-body').value     = ann.body;
  document.getElementById('ann-audience').value = ann.audience;
  document.getElementById('ann-error').classList.add('d-none');
  const btn = document.getElementById('ann-submit-btn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
  annModal.show();
}

// ──────────────────────────────────────────
// Submit
// ──────────────────────────────────────────
async function submitAnnouncement() {
  document.getElementById('ann-error').classList.add('d-none');

  const id       = document.getElementById('ann-id').value;
  const title    = document.getElementById('ann-title').value.trim();
  const body     = document.getElementById('ann-body').value.trim();
  const audience = document.getElementById('ann-audience').value;

  if (!title || !body) {
    showAnnError('Title and body are required.');
    return;
  }

  const btn = document.getElementById('ann-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

  try {
    const res = await fetch(`${BASE}/api/announcements/index.php`, {
      method: id ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(id ? { id: parseInt(id), title, body, audience } : { title, body, audience })
    });
    const data = await res.json();

    if (!data.ok) {
      showAnnError(data.message);
      btn.disabled = false;
      btn.innerHTML = id ? '<i class="fas fa-save me-1"></i> Save Changes' : '<i class="fas fa-paper-plane me-1"></i> Post Announcement';
      return;
    }

    annModal.hide();
    showToast(data.message, 'success');
    loadAnnouncements();
  } catch (err) {
    showAnnError('An unexpected error occurred. Please try again.');
    btn.disabled = false;
    btn.innerHTML = id ? '<i class="fas fa-save me-1"></i> Save Changes' : '<i class="fas fa-paper-plane me-1"></i> Post Announcement';
  }
}

// ──────────────────────────────────────────
// Delete
// ──────────────────────────────────────────
async function deleteAnnouncement(id, title) {
  confirmModal('Delete Announcement', `Are you sure you want to delete the announcement <strong>${escHtml(title)}</strong>? This action cannot be undone.`, async () => {
    showLoading('Deleting Announcement...', 'Please wait...');
    try {
      const res  = await fetch(`${BASE}/api/announcements/index.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      });
      const data = await res.json();
      hideLoading();
      if (!data.ok) { showToast(data.message, 'error'); return; }
      showToast('Announcement deleted successfully.', 'success');
      loadAnnouncements();
    } catch {
      hideLoading();
      showToast('Failed to delete announcement.', 'error');
    }
  });
}

// ──────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────
function showAnnError(msg) {
  const el = document.getElementById('ann-error');
  el.textContent = msg;
  el.classList.remove('d-none');
}

function clearFilters() {
  document.getElementById('search-input').value    = '';
  document.getElementById('filter-audience').value = '';
  loadAnnouncements();
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

loadAnnouncements();
</script>
</body>
</html>
