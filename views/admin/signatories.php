<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fetch fresh user data from database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

$activePage = 'signatories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Report Signatories — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
        <div class="page-title">Report Signatories</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <li class="breadcrumb-item active">Report Signatories</li>
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
        <h3>Report Signatories</h3>
        <p>Configure default signatories (Prepared by &amp; Noted by) printed on official school reports</p>
      </div>
      <div>
        <a href="<?= BASE_URL ?>/views/admin/reports.php" class="btn btn-outline-primary rounded-pill px-3">
          <i class="fas fa-file-alt me-1"></i> View Reports
        </a>
      </div>
    </div>

    <!-- Signatories Settings Form Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
        <span><i class="fas fa-file-signature me-2" style="color:var(--primary);"></i>Signatories Configuration</span>
        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-2 rounded-pill fw-semibold">Global System Settings</span>
      </div>
      <div class="card-body p-4">
        <p class="text-muted small mb-4">
          Select existing teacher/staff accounts or specify custom names and titles for report signatories.
        </p>

        <div class="row g-4">
          <!-- Prepared By Section -->
          <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
              <h6 class="fw-bold text-primary mb-3"><i class="fas fa-pen-nib me-2"></i>Prepared By Signatory</h6>
              
              <div class="mb-3">
                <label class="form-label fw-semibold small">Signatory Source</label>
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="prep-type" id="prep-type-teacher" value="teacher" checked onchange="togglePrepType('teacher')">
                  <label class="btn btn-outline-primary btn-sm" for="prep-type-teacher"><i class="fas fa-chalkboard-teacher me-1"></i> Teacher / Staff</label>

                  <input type="radio" class="btn-check" name="prep-type" id="prep-type-custom" value="custom" onchange="togglePrepType('custom')">
                  <label class="btn btn-outline-primary btn-sm" for="prep-type-custom"><i class="fas fa-user-edit me-1"></i> Custom Name</label>
                </div>
              </div>

              <div id="prep-teacher-wrapper" class="mb-3">
                <label class="form-label fw-semibold small">Select Teacher</label>
                <select id="sig-prep-teacher" class="form-select form-select-sm" onchange="onPrepTeacherSelect()">
                  <option value="">Loading teachers...</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Full Name</label>
                <input type="text" id="sig-prep-name" class="form-control form-control-sm" placeholder="e.g. Maria L. Reyes" oninput="updateLivePreview()">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Title / Position</label>
                <input type="text" id="sig-prep-title" class="form-control form-control-sm" placeholder="e.g. School Administrator" oninput="updateLivePreview()">
              </div>
            </div>
          </div>

          <!-- Noted By Section -->
          <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
              <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-check me-2"></i>Noted By Signatory</h6>
              
              <div class="mb-3">
                <label class="form-label fw-semibold small">Signatory Source</label>
                <div class="btn-group w-100" role="group">
                  <input type="radio" class="btn-check" name="noted-type" id="noted-type-teacher" value="teacher" onchange="toggleNotedType('teacher')">
                  <label class="btn btn-outline-primary btn-sm" for="noted-type-teacher"><i class="fas fa-chalkboard-teacher me-1"></i> Teacher / Staff</label>

                  <input type="radio" class="btn-check" name="noted-type" id="noted-type-custom" value="custom" checked onchange="toggleNotedType('custom')">
                  <label class="btn btn-outline-primary btn-sm" for="noted-type-custom"><i class="fas fa-user-edit me-1"></i> Custom Name</label>
                </div>
              </div>

              <div id="noted-teacher-wrapper" class="mb-3" style="display:none;">
                <label class="form-label fw-semibold small">Select Teacher</label>
                <select id="sig-noted-teacher" class="form-select form-select-sm" onchange="onNotedTeacherSelect()">
                  <option value="">Loading teachers...</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Full Name</label>
                <input type="text" id="sig-noted-name" class="form-control form-control-sm" placeholder="e.g. Dr. Juan Dela Cruz" oninput="updateLivePreview()">
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small">Title / Position</label>
                <input type="text" id="sig-noted-title" class="form-control form-control-sm" placeholder="e.g. School Head / Principal" oninput="updateLivePreview()">
              </div>
            </div>
          </div>
        </div>

        <!-- Live Signatories Preview -->
        <div class="mt-4 p-3 border rounded bg-white shadow-sm">
          <div class="fw-bold text-secondary mb-3 small text-uppercase" style="letter-spacing:0.5px;">
            <i class="fas fa-eye me-1"></i> Live Signatories Preview (Report Footer)
          </div>
          <div class="p-3 border rounded bg-light">
            <div class="row text-center align-items-end g-3">
              <div class="col-4">
                <div style="font-size:.7rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px;">Prepared by</div>
                <div id="prev-prep-name" style="font-weight:700;font-size:.85rem;border-top:1.5px solid #222;padding-top:.35rem;margin-top:2rem;">—</div>
                <div id="prev-prep-title" style="font-size:.75rem;color:var(--gray-600);">—</div>
              </div>
              <div class="col-4">
                <div style="font-size:.7rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px;">Noted by</div>
                <div id="prev-noted-name" style="font-weight:700;font-size:.85rem;border-top:1.5px solid #222;padding-top:.35rem;margin-top:2rem;">—</div>
                <div id="prev-noted-title" style="font-size:.75rem;color:var(--gray-600);">School Head / Principal</div>
              </div>
              <div class="col-4">
                <div style="font-size:.7rem;color:var(--gray-600);text-transform:uppercase;letter-spacing:.5px;">Date Generated</div>
                <div style="font-weight:700;font-size:.85rem;border-top:1.5px solid #222;padding-top:.35rem;margin-top:2rem;"><?= date('F j, Y') ?></div>
                <div style="font-size:.75rem;color:var(--gray-600);"><?= date('g:i A') ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <button class="btn btn-primary px-4 fw-semibold" onclick="saveSignatories()">
            <i class="fas fa-save me-2"></i>Save Signatories Settings
          </button>
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

let teacherList = [];

document.addEventListener('DOMContentLoaded', () => {
  loadSignatoriesSettings();
});

async function loadSignatoriesSettings() {
  try {
    const res = await fetch(BASE + '/api/signatories/index.php');
    const d = await res.json();
    if (!d.ok) throw new Error(d.message);

    teacherList = d.teachers || [];
    populateTeacherDropdowns();

    const sig = d.signatories || {};
    
    // Prepared by
    const prepType = sig.prepared_by_type || 'teacher';
    const prepRadio = document.getElementById(prepType === 'custom' ? 'prep-type-custom' : 'prep-type-teacher');
    if (prepRadio) prepRadio.checked = true;
    togglePrepType(prepType);

    if (sig.prepared_by_user_id) {
      document.getElementById('sig-prep-teacher').value = sig.prepared_by_user_id;
    }
    document.getElementById('sig-prep-name').value = sig.prepared_by_name || '';
    document.getElementById('sig-prep-title').value = sig.prepared_by_title || '';

    // Noted by
    const notedType = sig.noted_by_type || 'custom';
    const notedRadio = document.getElementById(notedType === 'custom' ? 'noted-type-custom' : 'noted-type-teacher');
    if (notedRadio) notedRadio.checked = true;
    toggleNotedType(notedType);

    if (sig.noted_by_user_id) {
      document.getElementById('sig-noted-teacher').value = sig.noted_by_user_id;
    }
    document.getElementById('sig-noted-name').value = sig.noted_by_name || '';
    document.getElementById('sig-noted-title').value = sig.noted_by_title || 'School Head / Principal';

    updateLivePreview();
  } catch (err) {
    showToast('Failed to load signatories data.', 'error');
  }
}

function populateTeacherDropdowns() {
  const prepSelect = document.getElementById('sig-prep-teacher');
  const notedSelect = document.getElementById('sig-noted-teacher');

  let opts = '<option value="">-- Select Teacher / Staff --</option>';
  teacherList.forEach(t => {
    const pos = t.position ? ` (${t.position})` : '';
    opts += `<option value="${t.id}" data-name="${escAttr(t.name)}" data-position="${escAttr(t.position || '')}">${escHtml(t.name)}${escHtml(pos)}</option>`;
  });

  prepSelect.innerHTML = opts;
  notedSelect.innerHTML = opts;
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str || '';
  return d.innerHTML;
}

function escAttr(str) {
  return (str || '').replace(/"/g, '&quot;');
}

function togglePrepType(type) {
  const wrap = document.getElementById('prep-teacher-wrapper');
  if (wrap) wrap.style.display = (type === 'teacher') ? 'block' : 'none';
  updateLivePreview();
}

function toggleNotedType(type) {
  const wrap = document.getElementById('noted-teacher-wrapper');
  if (wrap) wrap.style.display = (type === 'teacher') ? 'block' : 'none';
  updateLivePreview();
}

function onPrepTeacherSelect() {
  const select = document.getElementById('sig-prep-teacher');
  const selectedOpt = select.options[select.selectedIndex];
  if (selectedOpt && selectedOpt.value) {
    document.getElementById('sig-prep-name').value = selectedOpt.getAttribute('data-name') || '';
    document.getElementById('sig-prep-title').value = selectedOpt.getAttribute('data-position') || 'Teacher';
  }
  updateLivePreview();
}

function onNotedTeacherSelect() {
  const select = document.getElementById('sig-noted-teacher');
  const selectedOpt = select.options[select.selectedIndex];
  if (selectedOpt && selectedOpt.value) {
    document.getElementById('sig-noted-name').value = selectedOpt.getAttribute('data-name') || '';
    document.getElementById('sig-noted-title').value = selectedOpt.getAttribute('data-position') || 'School Head / Principal';
  }
  updateLivePreview();
}

function updateLivePreview() {
  const prepName = document.getElementById('sig-prep-name').value.trim() || '—';
  const prepTitle = document.getElementById('sig-prep-title').value.trim() || '—';

  const notedName = document.getElementById('sig-noted-name').value.trim() || '—';
  const notedTitle = document.getElementById('sig-noted-title').value.trim() || '—';

  const pName = document.getElementById('prev-prep-name');
  const pTitle = document.getElementById('prev-prep-title');
  const nName = document.getElementById('prev-noted-name');
  const nTitle = document.getElementById('prev-noted-title');

  if (pName) pName.textContent = prepName;
  if (pTitle) pTitle.textContent = prepTitle;
  if (nName) nName.textContent = notedName;
  if (nTitle) nTitle.textContent = notedTitle;
}

async function saveSignatories() {
  const prepTypeRadio = document.querySelector('input[name="prep-type"]:checked');
  const prepType = prepTypeRadio ? prepTypeRadio.value : 'teacher';
  const prepUserId = document.getElementById('sig-prep-teacher').value;
  const prepName = document.getElementById('sig-prep-name').value.trim();
  const prepTitle = document.getElementById('sig-prep-title').value.trim();

  const notedTypeRadio = document.querySelector('input[name="noted-type"]:checked');
  const notedType = notedTypeRadio ? notedTypeRadio.value : 'custom';
  const notedUserId = document.getElementById('sig-noted-teacher').value;
  const notedName = document.getElementById('sig-noted-name').value.trim();
  const notedTitle = document.getElementById('sig-noted-title').value.trim();

  showLoading('Saving Signatories...', 'Updating report signatories preferences...');
  try {
    const res = await fetch(BASE + '/api/signatories/index.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        prepared_by_type: prepType,
        prepared_by_user_id: prepUserId,
        prepared_by_name: prepName,
        prepared_by_title: prepTitle,
        noted_by_type: notedType,
        noted_by_user_id: notedUserId,
        noted_by_name: notedName,
        noted_by_title: notedTitle
      })
    });
    const d = await res.json();
    hideLoading();
    if (d.ok) {
      showToast(d.message || 'Report signatories saved successfully!', 'success');
      loadSignatoriesSettings();
    } else {
      showToast(d.message || 'Failed to save signatories.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('An error occurred while saving signatories.', 'error');
  }
}
</script>
</body>
</html>
