<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

$activePage = 'sec_questions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Manage Security QT — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
        <div class="page-title">Manage Security QT</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <li class="breadcrumb-item text-muted">Settings</li>
            <li class="breadcrumb-item active">Manage Security QT</li>
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
        <h3>Manage Security Questions</h3>
        <p>View, edit, add, or delete security questions in the DataGridView table</p>
      </div>
    </div>

    <!-- DataGridView Main Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
        <span><i class="fas fa-shield-alt me-2" style="color:var(--primary);"></i>Security Questions Table (DataGridView)</span>
        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-2 rounded-pill fw-semibold" id="sq-count-badge">0 Questions</span>
      </div>
      <div class="card-body p-0">
        <!-- DataGridView Table -->
        <div class="table-responsive">
          <table class="table table-modern table-hover align-middle mb-0" id="sec-questions-datagrid">
            <thead>
              <tr>
                <th style="width:70px;" class="text-center">#</th>
                <th>Sec Question</th>
                <th class="text-center" style="width:200px;">Action</th>
              </tr>
            </thead>
            <tbody id="sq-datagrid-tbody">
              <tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading security questions...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add/Edit Form Card below DataGridView table -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3" id="sq-form-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Security Question</h5>
        <div class="row g-3 align-items-end">
          <div class="col-md-9">
            <label class="form-label fw-semibold">Security Question Text</label>
            <input type="text" id="sq-input-text" class="form-control" placeholder="Enter security question text here...">
          </div>
          <div class="col-md-3 d-flex gap-2">
            <button type="button" id="btn-save-sq-grid" class="btn btn-primary px-4 py-2 w-100 fw-semibold" onclick="saveSqFromGrid()">
              <i class="fas fa-save me-1"></i>Save
            </button>
            <button type="button" id="btn-cancel-sq-grid" class="btn btn-secondary px-3 py-2 d-none" onclick="cancelSqEdit()">
              <i class="fas fa-times me-1"></i>Cancel
            </button>
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

let editingSqId = null;

document.addEventListener('DOMContentLoaded', () => {
  loadSecQuestionsGrid();
});

async function loadSecQuestionsGrid() {
  const tbody = document.getElementById('sq-datagrid-tbody');
  tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading security questions...</td></tr>';
  
  try {
    const res = await fetch(BASE + '/api/security-questions/index.php');
    const d = await res.json();
    if (!d.ok || !d.data) {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-4">Failed to load security questions table.</td></tr>';
      return;
    }

    const items = d.data;
    document.getElementById('sq-count-badge').textContent = items.length + ' Question(s)';

    if (items.length === 0) {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No security questions found in database table.</td></tr>';
    } else {
      tbody.innerHTML = items.map((sq, idx) => {
        const safeQ = sq.question.replace(/'/g, "\\'").replace(/"/g, "&quot;");
        return `
          <tr>
            <td class="text-center fw-bold text-muted">${idx + 1}</td>
            <td class="fw-semibold text-dark">${sq.question}</td>
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
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger py-4">An error occurred while loading table.</td></tr>';
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
  title.innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Edit Security Question';
  btnSave.className = 'btn btn-primary px-4 py-2 w-100 fw-semibold';
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
  title.innerHTML = '<i class="fas fa-plus-circle me-2 text-primary"></i>Add Security Question';
  btnSave.className = 'btn btn-primary px-4 py-2 w-100 fw-semibold';
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
    showLoading('Deleting Security Question...', 'Removing item from database table...');
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
