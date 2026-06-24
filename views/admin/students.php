<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'students';
$gradeJson    = json_encode(GRADE_LEVELS);
$sectionJson  = json_encode(SECTION_MAP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Student Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
        <div class="page-title">Student Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Students</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex align-items-start justify-content-between">
      <div><h3>Student Management</h3><p>Add, view, and edit student records</p></div>
      <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus me-2"></i>Add Student</button>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label mb-1">Search</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" id="search-input" class="form-control" placeholder="Search by name or LRN..." oninput="renderTable()">
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label mb-1">Grade Level</label>
            <select id="filter-grade" class="form-select" onchange="updateSectionFilter();renderTable()">
              <option value="">All Grade Levels</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label mb-1">Section</label>
            <select id="filter-section" class="form-select" onchange="renderTable()">
              <option value="">All Sections</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label mb-1">School Year</label>
            <select id="filter-sy" class="form-select" onchange="renderTable()">
              <option value="">All Years</option>
              <option value="2025-2026" selected>2025–2026</option>
              <option value="2024-2025">2024–2025</option>
            </select>
          </div>
          <div class="col-md-1">
            <button class="btn btn-light w-100" onclick="clearFilters()"><i class="fas fa-times"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-list me-2" style="color:var(--primary);"></i>Student List (<span id="count-display">0</span> records)</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Grade</th><th>Section</th><th>Sex</th><th>Age</th><th>Contact</th><th class="text-center">Actions</th></tr></thead>
            <tbody id="students-tbody"><tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="modal-title">Add Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="student-form">
          <input type="hidden" id="form-student-id">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">LRN *</label><input type="text" id="f-lrn" class="form-control" maxlength="12" required></div>
            <div class="col-md-4"><label class="form-label">Grade Level *</label><select id="f-grade" class="form-select" required onchange="updateFormSection()"><option value="">Select Grade</option></select></div>
            <div class="col-md-4"><label class="form-label">Section *</label><select id="f-section" class="form-select" required><option value="">Select Section</option></select></div>
            <div class="col-md-4"><label class="form-label">First Name *</label><input type="text" id="f-first" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Middle Name</label><input type="text" id="f-middle" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Last Name *</label><input type="text" id="f-last" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Sex *</label><select id="f-sex" class="form-select" required><option value="">Select</option><option>Male</option><option>Female</option></select></div>
            <div class="col-md-3"><label class="form-label">Birthdate *</label><input type="date" id="f-birthdate" class="form-control" required onchange="updateAge()"></div>
            <div class="col-md-2"><label class="form-label">Age</label><input type="number" id="f-age" class="form-control" readonly></div>
            <div class="col-md-4"><label class="form-label">Mother Tongue</label><input type="text" id="f-tongue" class="form-control" value="Cebuano"></div>
            <div class="col-md-4"><label class="form-label">Religion</label><input type="text" id="f-religion" class="form-control" value="Roman Catholic"></div>
            <div class="col-12"><label class="form-label">Address</label><input type="text" id="f-address" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Mother's Name</label><input type="text" id="f-mother" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Father's Name</label><input type="text" id="f-father" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Guardian's Name</label><input type="text" id="f-guardian" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Relation to Guardian</label><input type="text" id="f-relation" class="form-control" value="Mother"></div>
            <div class="col-md-6"><label class="form-label">Contact Number</label><input type="text" id="f-contact" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" id="f-email" class="form-control"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="saveStudent()"><i class="fas fa-save me-2"></i>Save Student</button>
      </div>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Student Profile</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="view-modal-body"></div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
const BASE = '<?= BASE_URL ?>';
showDesktopOnlyWarning();

const GRADE_LEVELS = <?= $gradeJson ?>;
const SECTION_MAP  = <?= $sectionJson ?>;
let allStudents = [];

// Populate grade dropdowns
['filter-grade','f-grade'].forEach(id => {
  const sel = document.getElementById(id);
  const includeAll = id === 'filter-grade';
  GRADE_LEVELS.forEach(g => sel.innerHTML += `<option value="${g}">${g}</option>`);
});

function updateSectionFilter() {
  const g = document.getElementById('filter-grade').value;
  const sel = document.getElementById('filter-section');
  sel.innerHTML = '<option value="">All Sections</option>';
  (SECTION_MAP[g] || []).forEach(s => sel.innerHTML += `<option value="${s}">${s}</option>`);
}
function updateFormSection() {
  const g = document.getElementById('f-grade').value;
  const sel = document.getElementById('f-section');
  sel.innerHTML = '<option value="">Select Section</option>';
  (SECTION_MAP[g] || []).forEach(s => sel.innerHTML += `<option value="${s}">${s}</option>`);
}
function updateAge() {
  const bd = document.getElementById('f-birthdate').value;
  if (!bd) return;
  const today = new Date(), b = new Date(bd);
  let age = today.getFullYear() - b.getFullYear();
  if (today.getMonth() - b.getMonth() < 0 || (today.getMonth() === b.getMonth() && today.getDate() < b.getDate())) age--;
  document.getElementById('f-age').value = age;
}
function clearFilters() {
  ['search-input','filter-grade','filter-section','filter-sy'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('filter-section').innerHTML = '<option value="">All Sections</option>';
  loadStudents();
}

async function loadStudents() {
  const params = new URLSearchParams();
  const q = document.getElementById('search-input').value;
  const g = document.getElementById('filter-grade').value;
  const s = document.getElementById('filter-section').value;
  const sy = document.getElementById('filter-sy').value;
  if (q) params.set('search', q);
  if (g) params.set('grade', g);
  if (s) params.set('section', s);
  if (sy) params.set('year', sy);

  const res = await fetch(BASE + '/api/students/index.php?' + params);
  const data = await res.json();
  allStudents = data.students || [];
  renderTable();
}

function renderTable() {
  const tbody = document.getElementById('students-tbody');
  document.getElementById('count-display').textContent = allStudents.length;
  if (!allStudents.length) {
    tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><i class="fas fa-user-slash"></i><p>No students found.</p></div></td></tr>`;
    return;
  }
  tbody.innerHTML = allStudents.map((s,i) => `
    <tr>
      <td>${i+1}</td>
      <td><span style="font-family:monospace;font-size:.82rem;">${s.lrn}</span></td>
      <td><strong>${s.last_name}</strong>, ${s.first_name} ${s.middle_name||''}</td>
      <td><span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">${s.grade_level}</span></td>
      <td>${s.section}</td>
      <td>${s.sex}</td>
      <td>${s.age}</td>
      <td>${s.contact||'—'}</td>
      <td class="text-center">
        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewStudent(${s.id})" title="View"><i class="fas fa-eye"></i></button>
        <button class="btn btn-sm btn-outline-secondary" onclick="editStudent(${s.id})" title="Edit"><i class="fas fa-edit"></i></button>
      </td>
    </tr>`).join('');
}

const studentModal = new bootstrap.Modal(document.getElementById('studentModal'));
const viewModal    = new bootstrap.Modal(document.getElementById('viewModal'));

function openAddModal() {
  document.getElementById('modal-title').textContent = 'Add Student';
  document.getElementById('student-form').reset();
  document.getElementById('form-student-id').value = '';
  document.getElementById('f-section').innerHTML = '<option value="">Select Section</option>';
  studentModal.show();
}

function editStudent(id) {
  const s = allStudents.find(x => x.id == id);
  if (!s) return;
  document.getElementById('modal-title').textContent = 'Edit Student';
  document.getElementById('form-student-id').value = s.id;
  document.getElementById('f-lrn').value = s.lrn;
  document.getElementById('f-grade').value = s.grade_level; updateFormSection();
  setTimeout(() => document.getElementById('f-section').value = s.section, 50);
  document.getElementById('f-first').value = s.first_name;
  document.getElementById('f-middle').value = s.middle_name || '';
  document.getElementById('f-last').value = s.last_name;
  document.getElementById('f-sex').value = s.sex;
  document.getElementById('f-birthdate').value = s.birthdate;
  document.getElementById('f-age').value = s.age;
  document.getElementById('f-tongue').value = s.mother_tongue || '';
  document.getElementById('f-religion').value = s.religion || '';
  document.getElementById('f-address').value = s.address || '';
  document.getElementById('f-mother').value = s.mother_name || '';
  document.getElementById('f-father').value = s.father_name || '';
  document.getElementById('f-guardian').value = s.guardian_name || '';
  document.getElementById('f-relation').value = s.guardian_relation || '';
  document.getElementById('f-contact').value = s.contact || '';
  document.getElementById('f-email').value = s.email || '';
  studentModal.show();
}

async function saveStudent() {
  const id = document.getElementById('form-student-id').value;
  const data = {
    lrn: document.getElementById('f-lrn').value.trim(),
    grade_level: document.getElementById('f-grade').value,
    section: document.getElementById('f-section').value,
    first_name: document.getElementById('f-first').value.trim(),
    middle_name: document.getElementById('f-middle').value.trim(),
    last_name: document.getElementById('f-last').value.trim(),
    sex: document.getElementById('f-sex').value,
    birthdate: document.getElementById('f-birthdate').value,
    age: parseInt(document.getElementById('f-age').value) || 0,
    mother_tongue: document.getElementById('f-tongue').value,
    religion: document.getElementById('f-religion').value,
    address: document.getElementById('f-address').value,
    mother_name: document.getElementById('f-mother').value,
    father_name: document.getElementById('f-father').value,
    guardian_name: document.getElementById('f-guardian').value,
    guardian_relation: document.getElementById('f-relation').value,
    contact: document.getElementById('f-contact').value,
    email: document.getElementById('f-email').value,
  };
  if (!data.lrn || !data.grade_level || !data.section || !data.first_name || !data.last_name) {
    showToast('Please fill in all required fields.','error'); return;
  }
  const url = id ? BASE+'/api/students/manage.php?id='+id : BASE+'/api/students/index.php';
  const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data) });
  const result = await res.json();
  if (!result.ok) { showToast(result.message,'error'); return; }
  showToast(id ? 'Student updated!':'Student added!','success');
  studentModal.hide();
  loadStudents();
}

function viewStudent(id) {
  const s = allStudents.find(x => x.id == id);
  if (!s) return;
  const field = (label, val) => `<div class="col-md-4"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">${label}</div><div class="fw-semibold">${val||'—'}</div></div></div>`;
  document.getElementById('view-modal-body').innerHTML = `
    <div class="row g-3">
      <div class="col-12 text-center mb-2">
        <div style="width:64px;height:64px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:var(--primary);margin:0 auto .75rem;">${s.first_name.charAt(0)}</div>
        <h5 class="fw-bold mb-0">${s.last_name}, ${s.first_name} ${s.middle_name||''}</h5>
        <span class="badge-active">Active</span>
      </div>
      ${field('LRN',`<span style="font-family:monospace">${s.lrn}</span>`)}
      ${field('Grade Level',s.grade_level)}
      ${field('Section',s.section)}
      ${field('Sex',s.sex)}
      ${field('Birthdate',new Date(s.birthdate).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}))}
      ${field('Age',s.age+' years old')}
      ${field('Mother Tongue',s.mother_tongue)}
      ${field('Religion',s.religion)}
      <div class="col-12"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">Address</div><div class="fw-semibold">${s.address||'—'}</div></div></div>
      ${field('Mother',s.mother_name)}
      ${field('Father',s.father_name)}
      ${field('Guardian',s.guardian_name?(s.guardian_name+' ('+s.guardian_relation+')'):'—')}
      ${field('Contact',s.contact)}
      ${field('Email',`<span style="font-size:.82rem">${s.email||'—'}</span>`)}
    </div>`;
  viewModal.show();
}

// Debounce search
let searchTimer;
document.getElementById('search-input').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadStudents, 400);
});
document.getElementById('filter-grade').addEventListener('change', () => { updateSectionFilter(); loadStudents(); });
document.getElementById('filter-section').addEventListener('change', loadStudents);
document.getElementById('filter-sy').addEventListener('change', loadStudents);

loadStudents();
</script>
</body>
</html>
