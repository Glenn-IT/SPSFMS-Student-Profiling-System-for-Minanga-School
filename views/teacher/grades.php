<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'grades';

$studentList = $pdo->query("SELECT id,last_name,first_name,middle_name,lrn,grade_level,section FROM students WHERE status='active' ORDER BY grade_level,last_name,first_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Grade Management — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    .sf10-header { background:#f0f4ff; border:1px solid #c7d2fe; border-radius:8px; padding:1rem; margin-bottom:1rem; }
    .sf10-table th { background:#e0e7ff; font-size:.78rem; text-align:center; }
    .sf10-table td { text-align:center; font-size:.85rem; }
    .sf10-table td:first-child { text-align:left; }
    .grade-input { width:60px; text-align:center; border:1px solid #d1d5db; border-radius:4px; padding:2px 4px; font-size:.85rem; }
    .grade-input:focus { outline:none; border-color:var(--secondary); }
    .remarks-passed { color:var(--secondary); font-weight:600; }
    .remarks-failed { color:var(--danger); font-weight:600; }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Grade Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Grades</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Teacher</div></div></div></div>
    </nav>

    <div class="page-header"><h3>Grade Management (SF10)</h3><p>Select a student to view or edit quarterly grades</p></div>

    <!-- Student Selector -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-6">
            <label class="form-label">Select Student</label>
            <select id="student-select" class="form-select" onchange="loadGrades()">
              <option value="">— Choose a student —</option>
              <?php foreach ($studentList as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['last_name'].', '.$s['first_name'].' '.($s['middle_name']??'')) ?> (<?= $s['grade_level'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">School Year</label>
            <select id="sy-select" class="form-select" onchange="loadGrades()">
              <option value="2025-2026">2025–2026</option>
              <option value="2024-2025">2024–2025</option>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn w-100" style="background:var(--secondary);color:#fff;" onclick="saveAllGrades()"><i class="fas fa-save me-2"></i>Save All Grades</button>
          </div>
        </div>
      </div>
    </div>

    <!-- SF10 Card -->
    <div id="sf10-container" style="display:none;">
      <div class="card">
        <div class="card-body">
          <!-- Republic of Philippines Header -->
          <div style="text-align:center;margin-bottom:1rem;border-bottom:2px solid #1a73e8;padding-bottom:.75rem;">
            <div style="font-size:.7rem;color:var(--gray-600);">Republic of the Philippines · Department of Education</div>
            <div style="font-weight:700;font-size:1rem;color:var(--primary);">SCHOOL FORM 10 (SF10) — LEARNER's PERMANENT ACADEMIC RECORD</div>
            <div style="font-size:.8rem;color:var(--gray-600);"><?= SCHOOL_NAME ?></div>
          </div>

          <div class="sf10-header row g-2 mb-3">
            <div class="col-md-4"><span style="font-size:.75rem;color:var(--gray-600);">LEARNER'S NAME</span><div class="fw-bold" id="sf10-name">—</div></div>
            <div class="col-md-3"><span style="font-size:.75rem;color:var(--gray-600);">LRN</span><div class="fw-bold" style="font-family:monospace;" id="sf10-lrn">—</div></div>
            <div class="col-md-2"><span style="font-size:.75rem;color:var(--gray-600);">GRADE LEVEL</span><div class="fw-bold" id="sf10-grade">—</div></div>
            <div class="col-md-2"><span style="font-size:.75rem;color:var(--gray-600);">SECTION</span><div class="fw-bold" id="sf10-section">—</div></div>
            <div class="col-md-1"><span style="font-size:.75rem;color:var(--gray-600);">S.Y.</span><div class="fw-bold" id="sf10-sy">—</div></div>
          </div>

          <div class="table-responsive">
            <table class="table sf10-table table-bordered mb-0" id="grades-table">
              <thead>
                <tr>
                  <th style="text-align:left;min-width:200px;">Learning Area / Subject</th>
                  <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                  <th style="background:#d1fae5;">Final Grade</th>
                  <th style="background:#d1fae5;">Remarks</th>
                </tr>
              </thead>
              <tbody id="grades-tbody">
                <tr><td colspan="7" class="text-center text-muted py-4">Select a student above to load grades.</td></tr>
              </tbody>
              <tfoot>
                <tr style="background:#f0fdf4;">
                  <td class="fw-bold">General Average</td>
                  <td colspan="4"></td>
                  <td class="fw-bold text-center" id="general-average">—</td>
                  <td class="fw-bold text-center" id="general-remarks">—</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div id="no-student-msg" class="text-center text-muted py-5">
      <i class="fas fa-clipboard-list fa-3x mb-3" style="color:var(--gray-300);"></i>
      <p>Select a student from the dropdown above to view their SF10 grade card.</p>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
showDesktopOnlyWarning();
let currentStudent = null;
let currentGrades  = {};

async function loadGrades() {
  const studentId = document.getElementById('student-select').value;
  const sy = document.getElementById('sy-select').value;
  if (!studentId) {
    document.getElementById('sf10-container').style.display = 'none';
    document.getElementById('no-student-msg').style.display = 'block';
    return;
  }
  const res = await fetch(`${BASE}/api/grades/student.php?student_id=${studentId}&school_year=${sy}`);
  const data = await res.json();
  if (!data.ok) { showToast(data.message, 'error'); return; }

  currentStudent = data.student;
  currentGrades  = data.grades;

  document.getElementById('sf10-name').textContent = `${data.student.last_name}, ${data.student.first_name} ${data.student.middle_name||''}`;
  document.getElementById('sf10-lrn').textContent  = data.student.lrn;
  document.getElementById('sf10-grade').textContent   = data.student.grade_level;
  document.getElementById('sf10-section').textContent = data.student.section;
  document.getElementById('sf10-sy').textContent = sy;

  const tbody = document.getElementById('grades-tbody');
  tbody.innerHTML = '';
  let gradeSum = 0, gradeCount = 0;

  data.subjects.forEach(subject => {
    const g = data.grades[subject] || {};
    const q1 = g.q1 ?? '', q2 = g.q2 ?? '', q3 = g.q3 ?? '', q4 = g.q4 ?? '';
    const final = g.final_grade ?? '';
    const remarks = g.remarks || '';
    if (final !== '' && final !== null) { gradeSum += parseFloat(final); gradeCount++; }
    const rmClass = remarks === 'Passed' ? 'remarks-passed' : (remarks === 'Failed' ? 'remarks-failed' : '');
    tbody.innerHTML += `<tr data-subject="${subject}">
      <td>${subject}</td>
      <td><input type="number" class="grade-input q-input" data-q="q1" min="0" max="100" value="${q1}" onchange="recomputeRow(this)"></td>
      <td><input type="number" class="grade-input q-input" data-q="q2" min="0" max="100" value="${q2}" onchange="recomputeRow(this)"></td>
      <td><input type="number" class="grade-input q-input" data-q="q3" min="0" max="100" value="${q3}" onchange="recomputeRow(this)"></td>
      <td><input type="number" class="grade-input q-input" data-q="q4" min="0" max="100" value="${q4}" onchange="recomputeRow(this)"></td>
      <td class="fw-bold final-cell">${final !== '' ? final : '—'}</td>
      <td class="${rmClass} remarks-cell">${remarks || '—'}</td>
    </tr>`;
  });

  const avg = gradeCount > 0 ? (gradeSum / gradeCount).toFixed(2) : '—';
  document.getElementById('general-average').textContent = avg;
  document.getElementById('general-remarks').textContent = avg !== '—' ? (parseFloat(avg) >= 75 ? 'Passed' : 'Failed') : '—';
  document.getElementById('sf10-container').style.display = 'block';
  document.getElementById('no-student-msg').style.display = 'none';
}

function recomputeRow(input) {
  const row = input.closest('tr');
  const inputs = row.querySelectorAll('.q-input');
  const vals = Array.from(inputs).map(i => i.value !== '' ? parseFloat(i.value) : null);
  if (vals.every(v => v !== null)) {
    const final = Math.round(vals.reduce((a,b) => a+b, 0) / 4 * 100) / 100;
    const remarks = final >= 75 ? 'Passed' : 'Failed';
    row.querySelector('.final-cell').textContent = final;
    row.querySelector('.remarks-cell').textContent = remarks;
    row.querySelector('.remarks-cell').className = 'remarks-cell ' + (remarks === 'Passed' ? 'remarks-passed' : 'remarks-failed');
  } else {
    row.querySelector('.final-cell').textContent = '—';
    row.querySelector('.remarks-cell').textContent = '—';
    row.querySelector('.remarks-cell').className = 'remarks-cell';
  }
  updateGeneralAverage();
}

function updateGeneralAverage() {
  const finals = Array.from(document.querySelectorAll('.final-cell'))
    .map(c => parseFloat(c.textContent)).filter(v => !isNaN(v));
  if (!finals.length) { document.getElementById('general-average').textContent = '—'; document.getElementById('general-remarks').textContent = '—'; return; }
  const avg = (finals.reduce((a,b)=>a+b,0)/finals.length).toFixed(2);
  document.getElementById('general-average').textContent = avg;
  document.getElementById('general-remarks').textContent = parseFloat(avg) >= 75 ? 'Passed' : 'Failed';
}

async function saveAllGrades() {
  const studentId = document.getElementById('student-select').value;
  const sy        = document.getElementById('sy-select').value;
  if (!studentId || !currentStudent) { showToast('No student selected.', 'error'); return; }

  const rows = document.querySelectorAll('#grades-tbody tr');
  let saved = 0;
  for (const row of rows) {
    const subject = row.dataset.subject;
    const inputs  = row.querySelectorAll('.q-input');
    const q1 = inputs[0].value, q2 = inputs[1].value, q3 = inputs[2].value, q4 = inputs[3].value;
    const payload = {
      student_id: parseInt(studentId), school_year: sy,
      grade_level: currentStudent.grade_level, section: currentStudent.section,
      subject, q1, q2, q3, q4
    };
    const res = await fetch(BASE + '/api/grades/student.php', {
      method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
    });
    const r = await res.json();
    if (r.ok) saved++;
  }
  showToast(`Grades saved (${saved} subjects)!`, 'success');
}
</script>
</body>
</html>
