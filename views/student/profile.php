<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('student');
$sStmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
$sStmt->execute([$user['lrn']]);
$student = $sStmt->fetch();
$initial = $student ? strtoupper(substr($student['first_name'],0,1)) : 'S';
function fd(?string $dateStr): string {
    if (!$dateStr) return '—';
    return date('F j, Y', strtotime($dateStr));
}

// Major religion options matching system standard
$majorReligions = [
    'Roman Catholic',
    'Islam',
    'Iglesia ni Cristo',
    'Evangelical',
    'Aglipayan (Philippine Independent Church)',
    'Seventh-day Adventist',
    'Baptist',
    'United Church of Christ in the Philippines (UCCP)',
    'Jehovah\'s Witness',
    'Other'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'My Profile — Student'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student-mobile.css">
  <style>
    .profile-hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: #fff;
      border-radius: 16px;
      padding: 1.5rem 1.75rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 24px rgba(26,115,232,0.18);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }
    .profile-avatar-lg {
      width: 84px;
      height: 84px;
      border-radius: 50%;
      background: var(--primary-light);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      font-weight: 700;
      border: 4px solid #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
      margin: 0 auto 0.75rem;
    }
    .profile-modal .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 10px 40px rgba(0,0,0,.2);
    }
    .profile-modal .modal-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color: #fff;
      border-top-left-radius: 16px;
      border-top-right-radius: 16px;
      padding: 1.1rem 1.5rem;
    }
    .profile-modal .modal-header .btn-close {
      filter: invert(1);
    }
    .profile-modal .modal-title {
      font-size: 1.05rem;
      font-weight: 700;
    }
    .profile-modal .form-label {
      font-size: .8rem;
      font-weight: 600;
      color: #475569;
      margin-bottom: 4px;
    }
    .profile-modal .form-section-title {
      font-size: .76rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: var(--primary);
      margin-top: .75rem;
      margin-bottom: .5rem;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 4px;
    }
    .profile-modal .form-control, .profile-modal .form-select {
      font-size: .88rem;
      border-radius: 8px;
    }
    .profile-modal .form-control:focus, .profile-modal .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15);
    }
    .uneditable-badge {
      font-size: .72rem;
      color: #64748b;
      background: #f1f5f9;
      padding: 2px 6px;
      border-radius: 4px;
      margin-left: 4px;
    }
  </style>
</head>
<body>

<?php
$activeNav = 'profile';
$navTitle = 'My Profile';
$showBack = true;
include __DIR__ . '/../../includes/student-navbar.php';
?>

<div class="student-app">

  <!-- Desktop Breadcrumb & Hero -->
  <div class="profile-hero d-none d-md-flex">
    <div>
      <h4 class="fw-bold mb-1"><i class="fas fa-id-card me-2"></i>Learner Profile Record</h4>
      <p class="mb-0 opacity-75 small">DepEd Standard Student Profile & Family Background Information</p>
    </div>
    <?php if ($student): ?>
      <button class="btn btn-light fw-bold text-primary shadow-sm px-3 py-2 rounded-3" onclick="openEditProfileModal()">
        <i class="fas fa-user-edit me-1"></i> Edit Personal Information
      </button>
    <?php endif; ?>
  </div>

  <?php if ($student): ?>
  <div class="row g-4">
    
    <!-- Left Column: Identity, Actions, Contact, Academic Overview -->
    <div class="col-12 col-lg-4">
      
      <!-- Identity Card -->
      <div class="desktop-card text-center">
        <div class="profile-avatar-lg"><?= $initial ?></div>
        <h5 class="fw-bold mb-1 text-dark" id="disp-fullname"><?= htmlspecialchars($student['last_name'].', '.$student['first_name'].' '.($student['middle_name']??'')) ?></h5>
        <div class="text-muted small mb-2"><?= htmlspecialchars($student['grade_level'].' | Section '.$student['section']) ?></div>
        <div class="d-inline-block px-3 py-1 bg-light border rounded-pill font-monospace small text-secondary mb-3">
          LRN: <?= htmlspecialchars($student['lrn']) ?>
        </div>

        <!-- Action Button -->
        <button class="btn btn-primary w-100 fw-bold py-2 rounded-3 shadow-sm" onclick="openEditProfileModal()">
          <i class="fas fa-user-edit me-2"></i> Edit Personal Information
        </button>
      </div>

      <!-- Contact Information Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-address-book text-primary"></i> Contact Information</h6>
        </div>
        <div class="mb-3 pb-2 border-bottom">
          <div class="info-label text-muted small text-uppercase fw-bold" style="font-size:.72rem;">Mobile Contact No.</div>
          <div class="info-value fw-semibold text-dark mt-1" id="card-contact"><?= htmlspecialchars($student['contact']??'—') ?></div>
        </div>
        <div>
          <div class="info-label text-muted small text-uppercase fw-bold" style="font-size:.72rem;">Email Address</div>
          <div class="info-value fw-semibold text-dark mt-1 text-truncate" id="card-email"><?= htmlspecialchars($student['email']??'—') ?></div>
        </div>
      </div>

      <!-- Academic Metadata Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-school text-primary"></i> Academic Status</h6>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">School Year:</span>
          <span class="fw-bold text-dark">S.Y. <?= SCHOOL_YEAR ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">Grade Level:</span>
          <span class="fw-bold text-dark"><?= htmlspecialchars($student['grade_level']) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 border-bottom small">
          <span class="text-muted">Class Section:</span>
          <span class="fw-bold text-dark"><?= htmlspecialchars($student['section']) ?></span>
        </div>
        <div class="d-flex justify-content-between py-2 small">
          <span class="text-muted">School:</span>
          <span class="fw-semibold text-dark text-truncate ms-2"><?= SCHOOL_NAME ?></span>
        </div>
      </div>

    </div>

    <!-- Right Column: Personal Details & Family Information -->
    <div class="col-12 col-lg-8">

      <!-- Personal Information Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-user text-primary"></i> Personal Details</h6>
          <span class="badge bg-light text-muted border">DepEd SF1</span>
        </div>

        <div class="profile-info-grid">
          <div class="profile-info-item full-width">
            <div class="info-label">Full Name</div>
            <div class="info-value" id="card-fullname"><?= htmlspecialchars($student['first_name'].' '.($student['middle_name']??'').' '.$student['last_name']) ?></div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Sex</div>
            <div class="info-value" id="card-sex"><?= htmlspecialchars($student['sex']) ?></div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Birthdate</div>
            <div class="info-value" id="card-birthdate"><?= fd($student['birthdate']) ?></div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Age</div>
            <div class="info-value" id="card-age"><?= $student['age'] ?> years old</div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Mother Tongue</div>
            <div class="info-value" id="card-tongue"><?= htmlspecialchars($student['mother_tongue']??'—') ?></div>
          </div>
          <div class="profile-info-item full-width">
            <div class="info-label">Religion</div>
            <div class="info-value" id="card-religion"><?= htmlspecialchars($student['religion']??'—') ?></div>
          </div>
          <div class="profile-info-item full-width">
            <div class="info-label">Residential Address</div>
            <div class="info-value" id="card-address"><?= htmlspecialchars($student['address']??'—') ?></div>
          </div>
        </div>
      </div>

      <!-- Family Information Card -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-users text-primary"></i> Family Information</h6>
        </div>

        <div class="profile-info-grid">
          <div class="profile-info-item">
            <div class="info-label">Mother's Name</div>
            <div class="info-value" id="card-mother"><?= htmlspecialchars($student['mother_name']??'—') ?></div>
          </div>
          <div class="profile-info-item">
            <div class="info-label">Father's Name</div>
            <div class="info-value" id="card-father"><?= htmlspecialchars($student['father_name']??'—') ?></div>
          </div>
          <div class="profile-info-item full-width">
            <div class="info-label">Guardian's Name & Relationship</div>
            <div class="info-value" id="card-guardian"><?= htmlspecialchars(($student['guardian_name']??'—').($student['guardian_relation']?' ('.$student['guardian_relation'].')':'')) ?></div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php else: ?>
    <div class="desktop-card text-center py-5 text-muted">
      <i class="fas fa-user-slash mb-2" style="font-size:2.5rem; opacity:.4;"></i>
      <h6 class="fw-bold mt-2">Student Record Not Found</h6>
      <p class="small mb-0">No profile details could be located for your LRN (<?= htmlspecialchars($user['lrn'] ?? '') ?>). Please consult the school registrar or administrator.</p>
    </div>
  <?php endif; ?>

</div>

<!-- Edit Personal Information Modal -->
<?php if ($student): ?>
<div class="modal fade profile-modal" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit me-2"></i>Edit Personal Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="student-edit-form" onsubmit="saveStudentProfile(event)">
          
          <!-- Non-editable academic metadata banner -->
          <div class="p-3 mb-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small" style="font-size:.72rem;">Learner Reference Number (LRN) <span class="uneditable-badge">Locked</span></div>
              <div class="fw-bold font-monospace" style="font-size:.9rem;"><?= htmlspecialchars($student['lrn']) ?></div>
            </div>
            <div class="text-end">
              <div class="text-muted small" style="font-size:.72rem;">Grade & Section <span class="uneditable-badge">Locked</span></div>
              <div class="fw-bold" style="font-size:.9rem;"><?= htmlspecialchars($student['grade_level'].' - '.$student['section']) ?></div>
            </div>
          </div>

          <div class="form-section-title">Personal Details</div>
          <div class="row g-3 mb-2">
            <div class="col-12 col-md-4">
              <label class="form-label">First Name *</label>
              <input type="text" id="ef-first" class="form-control name-only" value="<?= htmlspecialchars($student['first_name']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Middle Name</label>
              <input type="text" id="ef-middle" class="form-control name-only" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>" placeholder="Optional">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Last Name *</label>
              <input type="text" id="ef-last" class="form-control name-only" value="<?= htmlspecialchars($student['last_name']) ?>" required>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Sex *</label>
              <select id="ef-sex" class="form-select" required>
                <option value="Male" <?= $student['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $student['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
              </select>
            </div>
            <div class="col-6 col-md-4">
              <label class="form-label">Birthdate *</label>
              <input type="date" id="ef-birthdate" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($student['birthdate']) ?>" onchange="updateCalculatedAge()" required>
            </div>
            <div class="col-4 col-md-2">
              <label class="form-label">Age</label>
              <input type="number" id="ef-age" class="form-control bg-light" value="<?= (int)$student['age'] ?>" readonly>
            </div>
            <div class="col-8 col-md-3">
              <label class="form-label">Mother Tongue</label>
              <input type="text" id="ef-tongue" class="form-control" value="<?= htmlspecialchars($student['mother_tongue'] ?? '') ?>" placeholder="e.g. Ibanag, Ilocano">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Religion</label>
              <select id="ef-religion" class="form-select">
                <?php foreach ($majorReligions as $rel): 
                  $isSel = ($student['religion'] === $rel);
                ?>
                  <option value="<?= htmlspecialchars($rel) ?>" <?= $isSel ? 'selected' : '' ?>><?= htmlspecialchars($rel) ?></option>
                <?php endforeach; ?>
                <?php if ($student['religion'] && !in_array($student['religion'], $majorReligions)): ?>
                  <option value="<?= htmlspecialchars($student['religion']) ?>" selected><?= htmlspecialchars($student['religion']) ?></option>
                <?php endif; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Residential Address</label>
              <input type="text" id="ef-address" class="form-control" value="<?= htmlspecialchars($student['address'] ?? '') ?>" placeholder="Barangay, Municipality, Province">
            </div>
          </div>

          <div class="form-section-title mt-4">Family Information</div>
          <div class="row g-3 mb-2">
            <div class="col-12 col-md-6">
              <label class="form-label">Mother's Full Name</label>
              <input type="text" id="ef-mother" class="form-control name-only" value="<?= htmlspecialchars($student['mother_name'] ?? '') ?>" placeholder="Mother's complete maiden/married name">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Father's Full Name</label>
              <input type="text" id="ef-father" class="form-control name-only" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" placeholder="Father's complete name">
            </div>
            <div class="col-12 col-md-7">
              <label class="form-label">Guardian's Name</label>
              <input type="text" id="ef-guardian" class="form-control name-only" value="<?= htmlspecialchars($student['guardian_name'] ?? '') ?>" placeholder="Guardian's name (if not living with parents)">
            </div>
            <div class="col-12 col-md-5">
              <label class="form-label">Relationship to Guardian</label>
              <input type="text" id="ef-relation" class="form-control" value="<?= htmlspecialchars($student['guardian_relation'] ?? '') ?>" placeholder="e.g. Aunt, Grandparent">
            </div>
          </div>

          <div class="form-section-title mt-4">Contact Information</div>
          <div class="row g-3 mb-1">
            <div class="col-12 col-md-6">
              <label class="form-label">Mobile Contact No.</label>
              <input type="text" id="ef-contact" class="form-control digits-only" maxlength="11" inputmode="numeric" value="<?= htmlspecialchars($student['contact'] ?? '') ?>" placeholder="09xxxxxxxxx" pattern="09[0-9]{9}" title="11-digit PH mobile number starting with 09">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Email Address</label>
              <input type="email" id="ef-email" class="form-control" value="<?= htmlspecialchars($student['email'] ?? '') ?>" placeholder="student@example.com">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer p-3 bg-light">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="save-profile-btn" class="btn btn-primary btn-sm px-4 fw-bold" onclick="submitStudentProfile()">
          <i class="fas fa-save me-1"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
const STUDENT_ID = <?= $student ? (int)$student['id'] : 0 ?>;
let profileModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('editProfileModal');
  if (modalEl) {
    profileModalInstance = new bootstrap.Modal(modalEl);
  }

  // Real-time input filters (prevent digits in name fields, prevent non-digits in contact)
  document.querySelectorAll('.name-only').forEach(el => {
    el.addEventListener('input', () => {
      const cleaned = el.value.replace(/[^A-Za-zÑñ' .\-]/g, '');
      if (cleaned !== el.value) el.value = cleaned;
    });
  });

  document.querySelectorAll('.digits-only').forEach(el => {
    el.addEventListener('input', () => {
      const cleaned = el.value.replace(/[^0-9]/g, '');
      if (cleaned !== el.value) el.value = cleaned;
    });
  });
});

function openEditProfileModal() {
  if (profileModalInstance) {
    profileModalInstance.show();
  }
}

function updateCalculatedAge() {
  const bd = document.getElementById('ef-birthdate').value;
  if (!bd) return;
  const today = new Date(), b = new Date(bd);
  let age = today.getFullYear() - b.getFullYear();
  if (today.getMonth() - b.getMonth() < 0 || (today.getMonth() === b.getMonth() && today.getDate() < b.getDate())) age--;
  document.getElementById('ef-age').value = Math.max(0, age);
}

function submitStudentProfile() {
  const form = document.getElementById('student-edit-form');
  if (form && !form.checkValidity()) {
    form.reportValidity();
    return;
  }
  saveStudentProfile();
}

async function saveStudentProfile(e) {
  if (e) e.preventDefault();
  if (!STUDENT_ID) return;

  const btn = document.getElementById('save-profile-btn');
  if (btn && btn.disabled) return;

  const payload = {
    first_name:        document.getElementById('ef-first').value.trim(),
    middle_name:       document.getElementById('ef-middle').value.trim(),
    last_name:         document.getElementById('ef-last').value.trim(),
    sex:               document.getElementById('ef-sex').value,
    birthdate:         document.getElementById('ef-birthdate').value,
    age:               parseInt(document.getElementById('ef-age').value) || 0,
    mother_tongue:     document.getElementById('ef-tongue').value.trim(),
    religion:          document.getElementById('ef-religion').value,
    address:           document.getElementById('ef-address').value.trim(),
    mother_name:       document.getElementById('ef-mother').value.trim(),
    father_name:       document.getElementById('ef-father').value.trim(),
    guardian_name:     document.getElementById('ef-guardian').value.trim(),
    guardian_relation: document.getElementById('ef-relation').value.trim(),
    contact:           document.getElementById('ef-contact').value.trim(),
    email:             document.getElementById('ef-email').value.trim(),
  };

  // Client-side validations
  if (!payload.first_name || !payload.last_name || !payload.birthdate) {
    showToast('Please fill in required fields (First Name, Last Name, Birthdate).', 'error');
    return;
  }

  const namePattern = /^[A-Za-zÑñ' .\-]+$/;
  if (!namePattern.test(payload.first_name) || !namePattern.test(payload.last_name) ||
      (payload.middle_name && !namePattern.test(payload.middle_name)) ||
      (payload.mother_name && !namePattern.test(payload.mother_name)) ||
      (payload.father_name && !namePattern.test(payload.father_name)) ||
      (payload.guardian_name && !namePattern.test(payload.guardian_name))) {
    showToast('Names must not contain numbers.', 'error');
    return;
  }

  if (payload.contact && !/^09\d{9}$/.test(payload.contact)) {
    showToast('Contact No. must be an 11-digit PH mobile number starting with 09.', 'error');
    return;
  }

  const today = new Date().toISOString().slice(0, 10);
  if (payload.birthdate > today) {
    showToast('Birthdate cannot be a future date.', 'error');
    return;
  }

  const originalHtml = btn ? btn.innerHTML : '';
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
  }
  showLoading('Saving Profile...', 'Updating your personal information...');

  try {
    const res = await fetch(BASE + '/api/students/manage.php?id=' + STUDENT_ID, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    hideLoading();

    if (result.ok && result.student) {
      const s = result.student;
      // Close modal
      if (profileModalInstance) profileModalInstance.hide();
      showToast('Personal information updated successfully!', 'success');

      // Instantly update DOM display
      const full = s.first_name + (s.middle_name ? ' ' + s.middle_name : '') + ' ' + s.last_name;
      const formattedName = s.last_name + ', ' + s.first_name + (s.middle_name ? ' ' + s.middle_name : '');
      
      if (document.getElementById('disp-fullname')) document.getElementById('disp-fullname').textContent = formattedName;
      if (document.getElementById('card-fullname')) document.getElementById('card-fullname').textContent = full;
      if (document.getElementById('card-sex')) document.getElementById('card-sex').textContent = s.sex || '—';
      if (document.getElementById('card-birthdate') && s.birthdate) {
        const dObj = new Date(s.birthdate);
        document.getElementById('card-birthdate').textContent = dObj.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });
      }
      if (document.getElementById('card-age')) document.getElementById('card-age').textContent = (s.age || 0) + ' years old';
      if (document.getElementById('card-tongue')) document.getElementById('card-tongue').textContent = s.mother_tongue || '—';
      if (document.getElementById('card-religion')) document.getElementById('card-religion').textContent = s.religion || '—';
      if (document.getElementById('card-address')) document.getElementById('card-address').textContent = s.address || '—';
      if (document.getElementById('card-mother')) document.getElementById('card-mother').textContent = s.mother_name || '—';
      if (document.getElementById('card-father')) document.getElementById('card-father').textContent = s.father_name || '—';
      if (document.getElementById('card-guardian')) {
        document.getElementById('card-guardian').textContent = (s.guardian_name || '—') + (s.guardian_relation ? ' (' + s.guardian_relation + ')' : '');
      }
      if (document.getElementById('card-contact')) document.getElementById('card-contact').textContent = s.contact || '—';
      if (document.getElementById('card-email')) document.getElementById('card-email').textContent = s.email || '—';

    } else {
      showToast(result.message || 'Failed to update personal information.', 'error');
    }
  } catch (err) {
    hideLoading();
    showToast('A network or server error occurred while updating profile.', 'error');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  }
}
</script>
</body>
</html>
