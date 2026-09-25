<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

// Already logged in — redirect
if (!empty($_SESSION['user'])) {
    $map = ['admin'=>BASE_URL.'/views/admin/dashboard.php','teacher'=>BASE_URL.'/views/teacher/dashboard.php','student'=>BASE_URL.'/views/student/dashboard.php'];
    header('Location: '.($map[$_SESSION['user']['role']] ?? BASE_URL.'/views/auth/login.php')); exit;
}

$role = $_GET['role'] ?? 'student';
if (!in_array($role, ['teacher', 'student'])) $role = 'student';

// Pre-fetch assigned advisory classes so taken classes cannot be assigned to another teacher
$assignedClasses = [];
try {
    $assignedStmt = $pdo->query("
        SELECT tc.grade_level, tc.section, u.name AS teacher_name 
        FROM teacher_classes tc 
        JOIN users u ON u.id = tc.teacher_id
    ");
    while ($r = $assignedStmt->fetch(PDO::FETCH_ASSOC)) {
        $assignedClasses[$r['grade_level'] . '|' . $r['section']] = $r['teacher_name'];
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = ucfirst($role) . ' Registration'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <style>
    body { min-height:100vh; background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%); display:flex; align-items:center; justify-content:center; padding:1.5rem 1rem; }
    .register-card { background:#fff; border-radius:16px; box-shadow:0 8px 40px rgba(0,0,0,.25); padding:2.5rem 2rem; width:100%; max-width:440px; animation:fadeIn .35s ease; }
    .register-card.teacher-card { max-width: 500px; }
    .role-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .9rem; border-radius:20px; font-size:.78rem; font-weight:600; margin-bottom:1.25rem; }
    .role-badge.teacher { background:var(--secondary-light); color:var(--secondary); }
    .role-badge.student { background:var(--warning-light); color:#b06a00; }
    .login-header { text-align:center; margin-bottom:1.75rem; }
    .login-icon { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.6rem; margin:0 auto .75rem; }
    .login-icon.teacher { background:var(--secondary-light); color:var(--secondary); }
    .login-icon.student { background:var(--warning-light); color:#b06a00; }
    .login-header h4 { font-weight:700; color:var(--dark); font-size:1.15rem; }
    .login-header p  { font-size:.8rem; color:var(--gray-600); }
    .toggle-pw { cursor:pointer; color:var(--gray-400); font-size:.85rem; }
    .toggle-pw:hover { color:var(--gray-600); }
    input[type="password"]::-ms-reveal, input[type="password"]::-ms-clear { display:none; }
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-webkit-strong-password-auto-fill-button { display:none !important; visibility:hidden; }
    .btn-login { width:100%; padding:.65rem; font-size:.95rem; font-weight:600; border-radius:8px; }
    .btn-login.teacher-btn { background:var(--secondary); border-color:var(--secondary); }
    .btn-login.student-btn { background:#b06a00; border-color:#b06a00; }
    .back-link { display:block; text-align:center; margin-top:1rem; font-size:.82rem; color:var(--gray-600); text-decoration:none; }
    .back-link:hover { color:var(--primary); }
    .error-box { background:#fce8e6; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--danger); display:none; margin-bottom:1rem; }
    .success-box { background:#e6f4ea; border-radius:8px; padding:.6rem .9rem; font-size:.83rem; color:var(--secondary); display:none; margin-bottom:1rem; }
    .role-toggle { display:flex; gap:.5rem; margin-bottom:1.5rem; }
    .role-toggle a { flex:1; text-align:center; padding:.45rem; border-radius:8px; font-size:.82rem; font-weight:600; text-decoration:none; border:1px solid var(--gray-200); color:var(--gray-600); }
    .role-toggle a.active.teacher { background:var(--secondary-light); color:var(--secondary); border-color:var(--secondary); }
    .role-toggle a.active.student { background:var(--warning-light); color:#b06a00; border-color:#b06a00; }
  </style>
</head>
<body>
<?php
$cfg = [
  'teacher' => ['label'=>'Teacher', 'icon'=>'fa-chalkboard-teacher'],
  'student' => ['label'=>'Student', 'icon'=>'fa-user-graduate'],
][$role];
?>
<div class="register-card <?= $role === 'teacher' ? 'teacher-card' : '' ?>">
  <div class="login-header">
    <div class="login-icon <?= $role ?>"><i class="fas <?= $cfg['icon'] ?>"></i></div>
    <span class="role-badge <?= $role ?>"><i class="fas fa-circle" style="font-size:.4rem;"></i> <?= $cfg['label'] ?></span>
    <h4>Create Account</h4>
    <p><?= SCHOOL_NAME ?> — SPSMIS</p>
  </div>

  <div class="role-toggle">
    <a href="?role=teacher" class="<?= $role === 'teacher' ? 'active teacher' : '' ?>">Teacher</a>
    <a href="?role=student" class="<?= $role === 'student' ? 'active student' : '' ?>">Student</a>
  </div>

  <div class="error-box" id="error-box">
    <i class="fas fa-exclamation-circle me-1"></i> <span id="error-msg">Something went wrong.</span>
  </div>
  <div class="success-box" id="success-box">
    <i class="fas fa-check-circle me-1"></i> <span id="success-msg">Account created.</span>
  </div>

  <form id="register-form" novalidate>
    <?php if ($role === 'teacher'): ?>
      <div class="mb-3">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-user" style="color:var(--gray-400);font-size:.85rem;"></i></span>
          <input type="text" id="name" class="form-control" placeholder="Juan Dela Cruz" required>
        </div>
      </div>
      <div class="mb-3 p-3 bg-light rounded-3 border">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label class="form-label mb-0 fw-semibold text-dark">
            <i class="fas fa-chalkboard text-primary me-1"></i>Advisory Classes <span class="text-danger">*</span>
            <small class="text-muted fw-normal ms-1">(1 to 3 classes)</small>
          </label>
          <button type="button" class="btn btn-sm btn-outline-primary" id="reg-add-class-btn" onclick="addRegClassRow()">
            <i class="fas fa-plus me-1"></i>Add Class
          </button>
        </div>
        <div id="reg-classes-container" class="d-flex flex-column gap-2"></div>
      </div>
    <?php else: ?>
      <div class="mb-3">
        <label class="form-label">LRN (Learner Reference Number)</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="fas fa-id-card" style="color:var(--gray-400);font-size:.85rem;"></i></span>
          <input type="text" id="lrn" class="form-control" placeholder="12-digit LRN" required>
        </div>
        <div class="form-text" style="font-size:.75rem;">Must match a student record already on file with the Admin.</div>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-envelope" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="email" id="email" class="form-control" placeholder="you@example.com" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Username</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-user-tag" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="text" id="username" class="form-control" placeholder="Choose a username" required autocomplete="username">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="password" class="form-control" placeholder="At least 6 characters" required autocomplete="new-password">
        <span class="input-group-text bg-white toggle-pw" onclick="togglePw('password','pw-eye')"><i class="fas fa-eye" id="pw-eye"></i></span>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-lock" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="password" id="confirm_password" class="form-control" placeholder="Re-enter password" required autocomplete="new-password">
        <span class="input-group-text bg-white toggle-pw" onclick="togglePw('confirm_password','pw-eye2')"><i class="fas fa-eye" id="pw-eye2"></i></span>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Security Question <span class="text-muted" style="font-weight:400;">(for password recovery)</span></label>
      <select id="sec_question" class="form-select" required>
        <option value="">— Choose a question —</option>
        <?php
        try {
          $sqs = $pdo->query("SELECT question FROM security_questions ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
          $sqs = [
            "What is the name of your first pet?",
            "What is your mother's maiden name?",
            "What city were you born in?",
            "What is the name of your elementary school?"
          ];
        }
        foreach ($sqs as $sq):
        ?>
        <option value="<?= htmlspecialchars($sq) ?>"><?= htmlspecialchars($sq) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Answer</label>
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="fas fa-key" style="color:var(--gray-400);font-size:.85rem;"></i></span>
        <input type="text" id="sec_answer" class="form-control" placeholder="Your answer" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-login <?= $role ?>-btn">
      <span id="btn-text"><i class="fas fa-user-plus me-2"></i>Create Account</span>
      <span id="btn-spinner" style="display:none;"><i class="fas fa-spinner fa-spin me-2"></i>Creating...</span>
    </button>
  </form>

  <div style="text-align:center;margin:.75rem 0;font-size:.82rem;">
    Already have an account? <a href="login.php" style="color:var(--primary);text-decoration:none;font-weight:500;">Log in</a>
  </div>
</div>

<script>
  const BASE = '<?= BASE_URL ?>';
  const ROLE = '<?= $role ?>';
  const GRADE_LEVELS = <?= json_encode(GRADE_LEVELS) ?>;
  const SECTION_MAP  = <?= json_encode(SECTION_MAP) ?>;
  const ASSIGNED_CLASSES = <?= json_encode($assignedClasses) ?>;

  const DEFAULT_SECTIONS = {
    'Kindergarten': ['Sampaguita'],
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
    'Grade 11': ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL - ICT', 'TVL - HE'],
    'Grade 12': ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL - ICT', 'TVL - HE']
  };

  const sectionCache = {};

  async function fetchSections(gradeVal) {
    if (!gradeVal) return [];
    if (sectionCache[gradeVal]) return sectionCache[gradeVal];
    let list = [];
    try {
      const res  = await fetch(`${BASE}/api/sections/index.php?grade_level=${encodeURIComponent(gradeVal)}`);
      const data = await res.json();
      if (data.ok && data.sections && data.sections.length > 0) {
        data.sections.forEach(s => {
          list.push(s.section_name);
          if (s.assigned_id || s.adviser_name) {
            ASSIGNED_CLASSES[`${s.grade_level}|${s.section_name}`] = s.adviser_name || 'Assigned';
          }
        });
      }
    } catch (e) {
      console.error(e);
    }
    if (list.length === 0) {
      if (SECTION_MAP[gradeVal] && SECTION_MAP[gradeVal].length > 0) {
        list = [...SECTION_MAP[gradeVal]];
      } else if (DEFAULT_SECTIONS[gradeVal]) {
        list = [...DEFAULT_SECTIONS[gradeVal]];
      }
    }
    const unique = [...new Set(list)];
    sectionCache[gradeVal] = unique;
    return unique;
  }

  async function loadSectionsFor(sectionSelId, gradeSelId) {
    const gradeVal = document.getElementById(gradeSelId).value;
    const secSel   = document.getElementById(sectionSelId);
    secSel.innerHTML = '<option value="">Loading...</option>';
    secSel.disabled  = true;

    if (!gradeVal) {
      secSel.innerHTML = '<option value="">— Select Grade first —</option>';
      return;
    }

    const list = await fetchSections(gradeVal);
    secSel.innerHTML = '<option value="">— Select Section —</option>';
    list.forEach(secName => {
      const opt = document.createElement('option');
      opt.value = secName;
      opt.textContent = secName;
      secSel.appendChild(opt);
    });
    secSel.disabled = false;
  }

  /* ── Teacher Multiple Advisory Classes State ── */
  let regClassState = [{ grade: '', section: '' }];

  async function renderRegClassRows() {
    const container = document.getElementById('reg-classes-container');
    const addBtn    = document.getElementById('reg-add-class-btn');
    if (!container) return;

    if (addBtn) {
      addBtn.disabled = regClassState.length >= 3;
    }

    container.innerHTML = '';
    for (let idx = 0; idx < regClassState.length; idx++) {
      const item = regClassState[idx];
      const rowEl = document.createElement('div');
      rowEl.className = 'bg-white p-2.5 rounded-3 border shadow-sm';
      rowEl.style.padding = '0.75rem';

      let gradeOptions = `<option value="">Select Grade</option>`;
      GRADE_LEVELS.forEach(g => {
        gradeOptions += `<option value="${g}" ${item.grade === g ? 'selected' : ''}>${g}</option>`;
      });

      let secDisabled = !item.grade ? 'disabled' : '';
      let secOptions = !item.grade ? `<option value="">Select grade first</option>` : `<option value="">Loading...</option>`;

      rowEl.innerHTML = `
        <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
          <span class="badge bg-light text-primary border fw-semibold px-2 py-1" style="font-size:0.75rem;">
            <i class="fas fa-chalkboard me-1"></i>Class ${idx + 1}
          </span>
          ${regClassState.length > 1 ? `
            <button type="button" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size:0.8rem;" onclick="removeRegClassRow(${idx})" title="Remove class">
              <i class="fas fa-trash-alt me-1"></i>Remove
            </button>
          ` : ''}
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label mb-1 small text-muted">Grade Level</label>
            <select class="form-select form-select-sm reg-grade-sel" onchange="onRegGradeChange(${idx}, this.value)">
              ${gradeOptions}
            </select>
          </div>
          <div class="col-6">
            <label class="form-label mb-1 small text-muted">Section</label>
            <select class="form-select form-select-sm reg-section-sel" ${secDisabled} onchange="onRegSectionChange(${idx}, this.value)">
              ${secOptions}
            </select>
          </div>
        </div>
      `;

      container.appendChild(rowEl);

      if (item.grade) {
        populateSectionOptionsForRow(rowEl.querySelector('.reg-section-sel'), idx, item.grade, item.section);
      }
    }
  }

  async function populateSectionOptionsForRow(secSel, idx, gradeVal, currentSection) {
    if (!secSel) return;
    const rawList = await fetchSections(gradeVal);
    // Filter out already taken sections:
    // 1) Taken in database by existing teachers
    // 2) Taken in another row of this registration form
    const available = rawList.filter(s => {
      const key = `${gradeVal}|${s}`;
      if (ASSIGNED_CLASSES[key]) return false;
      const takenByOtherRow = regClassState.some((r, rIdx) => rIdx !== idx && r.grade === gradeVal && r.section === s);
      if (takenByOtherRow) return false;
      return true;
    });

    if (available.length === 0) {
      secSel.innerHTML = `<option value="" disabled selected>No sections available</option>`;
      secSel.disabled = true;
      if (regClassState[idx]) regClassState[idx].section = '';
    } else {
      let html = `<option value="">Select Section</option>`;
      available.forEach(s => {
        html += `<option value="${s}" ${currentSection === s ? 'selected' : ''}>${s}</option>`;
      });
      secSel.innerHTML = html;
      secSel.disabled = false;
      if (currentSection && !available.includes(currentSection)) {
        if (regClassState[idx]) regClassState[idx].section = '';
      }
    }
  }

  function refreshAllSectionDropdowns() {
    const container = document.getElementById('reg-classes-container');
    if (!container) return;
    const rows = container.children;
    for (let idx = 0; idx < regClassState.length; idx++) {
      const item = regClassState[idx];
      const rowEl = rows[idx];
      if (rowEl && item && item.grade) {
        const secSel = rowEl.querySelector('.reg-section-sel');
        if (secSel) {
          populateSectionOptionsForRow(secSel, idx, item.grade, item.section);
        }
      }
    }
  }

  function onRegGradeChange(idx, val) {
    if (regClassState[idx]) {
      regClassState[idx].grade = val;
      regClassState[idx].section = '';
    }
    renderRegClassRows();
  }

  function onRegSectionChange(idx, val) {
    if (regClassState[idx]) {
      regClassState[idx].section = val;
    }
    refreshAllSectionDropdowns();
  }

  function addRegClassRow() {
    if (regClassState.length >= 3) return;
    regClassState.push({ grade: '', section: '' });
    renderRegClassRows();
  }

  function removeRegClassRow(idx) {
    if (regClassState.length > 1) {
      regClassState.splice(idx, 1);
      renderRegClassRows();
    }
  }

  if (ROLE === 'teacher') {
    renderRegClassRows();
  }

  function togglePw(inputId, eyeId) {
    const pw  = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (pw.type === 'password') { pw.type = 'text'; eye.className = 'fas fa-eye-slash'; }
    else { pw.type = 'password'; eye.className = 'fas fa-eye'; }
  }

  function setLoading(loading) {
    document.getElementById('btn-text').style.display    = loading ? 'none'   : 'inline';
    document.getElementById('btn-spinner').style.display = loading ? 'inline' : 'none';
  }

  function showError(msg) {
    document.getElementById('success-box').style.display = 'none';
    const box = document.getElementById('error-box');
    document.getElementById('error-msg').textContent = msg;
    box.style.display = 'block';
  }

  function showSuccess(msg) {
    document.getElementById('error-box').style.display = 'none';
    const box = document.getElementById('success-box');
    document.getElementById('success-msg').textContent = msg;
    box.style.display = 'block';
  }

  document.getElementById('register-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const payload = {
      role: ROLE,
      email: document.getElementById('email').value.trim(),
      username: document.getElementById('username').value.trim(),
      password: document.getElementById('password').value,
      confirm_password: document.getElementById('confirm_password').value,
      sec_question: document.getElementById('sec_question').value,
      sec_answer: document.getElementById('sec_answer').value.trim(),
    };

    if (ROLE === 'teacher') {
      const name = document.getElementById('name').value.trim();
      const advisory_classes = [];
      for (const item of regClassState) {
        if (item.grade || item.section) {
          advisory_classes.push({ grade_level: (item.grade||'').trim(), section: (item.section||'').trim() });
        }
      }

      if (!name) return showError('Full name is required.');
      if (!advisory_classes.length) return showError('Please assign at least one advisory class.');
      for (let i = 0; i < advisory_classes.length; i++) {
        if (!advisory_classes[i].grade_level) return showError(`Please select grade level for Class ${i+1}.`);
        if (!advisory_classes[i].section) return showError(`Please select advisory section for Class ${i+1}.`);
      }
      const seen = {};
      for (const c of advisory_classes) {
        const k = c.grade_level + '|' + c.section;
        if (seen[k]) return showError(`Duplicate advisory class selected: ${c.grade_level} - ${c.section}.`);
        seen[k] = true;
      }

      payload.name = name;
      payload.advisory_classes = advisory_classes;
    } else {
      payload.lrn = document.getElementById('lrn').value.trim();
    }

    document.getElementById('error-box').style.display = 'none';
    document.getElementById('success-box').style.display = 'none';
    setLoading(true);

    try {
      const res  = await fetch(BASE + '/api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      setLoading(false);

      if (data.ok) {
        showSuccess(data.message + ' Redirecting to login...');
        document.getElementById('register-form').reset();
        setTimeout(() => { window.location.href = BASE + '/views/auth/login.php?role=' + ROLE; }, 1500);
      } else {
        showError(data.message);
      }
    } catch (err) {
      setLoading(false);
      showError('Network error. Please try again.');
    }
  });
</script>
</body>
</html>
