<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');
$activePage = 'student-profiles';

// Fetch fresh teacher profile details to ensure up-to-date advisory assignment
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$freshUser = $uStmt->fetch() ?: $user;

// Get all advisory classes
$myClasses = getTeacherAdvisoryClasses($pdo, $freshUser['id']);
if (empty($myClasses)) {
    $myClasses = [['grade_level' => 'Grade 1', 'section' => 'Mabini']];
}

$selectedClass = $_GET['class'] ?? 'all';
$where  = ['s.status = "active"'];
$params = [];

if ($selectedClass !== 'all' && is_numeric($selectedClass) && isset($myClasses[(int)$selectedClass])) {
    $c = $myClasses[(int)$selectedClass];
    $where[] = 's.grade_level = ? AND s.section = ?';
    $params[] = $c['grade_level'];
    $params[] = $c['section'];
} else {
    // Show all students across all assigned advisory classes
    $classOr = [];
    foreach ($myClasses as $c) {
        $classOr[] = '(s.grade_level = ? AND s.section = ?)';
        $params[] = $c['grade_level'];
        $params[] = $c['section'];
    }
    if (!empty($classOr)) {
        $where[] = '(' . implode(' OR ', $classOr) . ')';
    } else {
        $where[] = '1 = 0';
    }
}

$stmt = $pdo->prepare('SELECT * FROM students s WHERE '.implode(' AND ',$where).' ORDER BY s.grade_level, s.section, s.last_name, s.first_name');
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Student Profiles — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Student Profiles</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Teacher</li><li class="breadcrumb-item active">Student Profiles</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Teacher</div></div></div></div>
    </nav>

    <div class="page-header d-flex flex-column gap-2">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h3 class="mb-1">Advisory Student Profiles</h3>
          <p class="mb-0 text-muted">Read-only student records for your assigned advisory classes</p>
        </div>
        <div>
          <span class="badge bg-secondary bg-opacity-10 text-dark border px-3 py-2 fw-semibold" style="font-size:.82rem;">
            <i class="fas fa-chalkboard-teacher me-1" style="color:var(--secondary);"></i>
            <?= count($myClasses) ?> Advisory <?= count($myClasses) === 1 ? 'Class' : 'Classes' ?>
          </span>
        </div>
      </div>

      <!-- Multiple Advisory Classes Switcher Tabs -->
      <?php if (count($myClasses) > 1): ?>
        <div class="p-2 bg-white rounded-3 border shadow-sm mt-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted fw-bold small me-1 ps-1"><i class="fas fa-filter me-1 text-success"></i>Filter by Class:</span>
            <a href="?class=all" class="btn btn-sm <?= $selectedClass === 'all' ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light border text-dark' ?>" style="border-radius: 20px; font-size: .84rem; padding: 4px 12px;">
              <i class="fas fa-users me-1"></i>All My Classes
            </a>
            <?php foreach ($myClasses as $idx => $cls):
              $isAct = ($selectedClass === (string)$idx);
            ?>
              <a href="?class=<?= $idx ?>" class="btn btn-sm <?= $isAct ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light border text-dark' ?>" style="border-radius: 20px; font-size: .84rem; padding: 4px 12px;">
                <i class="fas fa-chalkboard me-1 <?= $isAct ? 'text-white' : 'text-success' ?>"></i>
                <?= htmlspecialchars($cls['grade_level']) ?> — <?= htmlspecialchars($cls['section']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2 align-items-end">
          <div class="col-md-11">
            <label class="form-label mb-1">Search Advisory Students</label>
            <div class="search-bar"><i class="fas fa-search"></i>
              <input type="text" id="search-input" class="form-control" placeholder="Search name, LRN, contact..." oninput="filterStudents()">
            </div>
          </div>
          <div class="col-md-1">
            <button type="button" class="btn btn-light w-100" onclick="clearSearch()" title="Clear Search"><i class="fas fa-times"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-users me-2" style="color:var(--secondary);"></i>Students (<span id="record-count"><?= count($students) ?></span> records)</span>
        <?php if ($selectedClass !== 'all' && is_numeric($selectedClass) && isset($myClasses[(int)$selectedClass])): ?>
          <span class="badge bg-success bg-opacity-15 text-success fw-semibold">
            <?= htmlspecialchars($myClasses[(int)$selectedClass]['grade_level']) ?> - <?= htmlspecialchars($myClasses[(int)$selectedClass]['section']) ?>
          </span>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>LRN</th><th>Full Name</th><th>Grade</th><th>Section</th><th>Sex</th><th>Contact No.</th><th class="text-center">View</th></tr></thead>
            <tbody id="students-tbody">
              <?php if (empty($students)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No students found.</td></tr>
              <?php else: foreach ($students as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($s['lrn']) ?></span></td>
                <td><strong><?= htmlspecialchars($s['last_name']) ?></strong>, <?= htmlspecialchars($s['first_name'].' '.($s['middle_name']??'')) ?></td>
                <td><?= htmlspecialchars($s['grade_level']) ?></td>
                <td><?= htmlspecialchars($s['section']) ?></td>
                <td><?= htmlspecialchars($s['sex']) ?></td>
                <td><?= htmlspecialchars($s['contact']??'—') ?></td>
                <td class="text-center"><button class="btn btn-sm btn-outline-secondary" onclick="viewStudentById(<?= $s['id'] ?>)"><i class="fas fa-eye"></i></button></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--secondary);color:#fff;">
        <h5 class="modal-title">Student Profile</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="view-modal-body"></div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
showDesktopOnlyWarning();
const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
const allStudents = <?= json_encode($students) ?>;

function escHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function renderStudents(list) {
  const tbody = document.getElementById('students-tbody');
  const countSpan = document.getElementById('record-count');
  if (countSpan) countSpan.textContent = list.length;

  if (!list || list.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No students found.</td></tr>';
    return;
  }

  tbody.innerHTML = list.map((s, i) => {
    const fullName = `<strong>${escHtml(s.last_name)}</strong>, ${escHtml(s.first_name + (s.middle_name ? ' ' + s.middle_name : ''))}`;
    const contact = s.contact ? escHtml(s.contact) : '—';
    return `
      <tr>
        <td>${i + 1}</td>
        <td><span style="font-family:monospace;font-size:.82rem;">${escHtml(s.lrn)}</span></td>
        <td>${fullName}</td>
        <td>${escHtml(s.grade_level)}</td>
        <td>${escHtml(s.section)}</td>
        <td>${escHtml(s.sex)}</td>
        <td>${contact}</td>
        <td class="text-center">
          <button class="btn btn-sm btn-outline-secondary" onclick="viewStudentById(${s.id})" title="View Profile">
            <i class="fas fa-eye"></i>
          </button>
        </td>
      </tr>
    `;
  }).join('');
}

function filterStudents() {
  const query = (document.getElementById('search-input').value || '').trim().toLowerCase();
  if (!query) {
    renderStudents(allStudents);
    return;
  }

  const filtered = allStudents.filter(s => {
    const lrn = (s.lrn || '').toLowerCase();
    const firstName = (s.first_name || '').toLowerCase();
    const lastName = (s.last_name || '').toLowerCase();
    const middleName = (s.middle_name || '').toLowerCase();
    const combinedName = `${lastName}, ${firstName} ${middleName}`.toLowerCase();
    const combinedNameRev = `${firstName} ${lastName}`.toLowerCase();
    const contact = (s.contact || '').toLowerCase();
    const grade = (s.grade_level || '').toLowerCase();
    const section = (s.section || '').toLowerCase();

    return lrn.includes(query) ||
           firstName.includes(query) ||
           lastName.includes(query) ||
           middleName.includes(query) ||
           combinedName.includes(query) ||
           combinedNameRev.includes(query) ||
           contact.includes(query) ||
           grade.includes(query) ||
           section.includes(query);
  });

  renderStudents(filtered);
}

function clearSearch() {
  const input = document.getElementById('search-input');
  if (input) {
    input.value = '';
    filterStudents();
    input.focus();
  }
}

function viewStudentById(id) {
  const s = allStudents.find(x => x.id == id);
  if (s) {
    viewStudent(s);
  }
}

function viewStudent(s) {
  const field = (label, val) => `<div class="col-md-4"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">${label}</div><div class="fw-semibold">${val||'—'}</div></div></div>`;
  document.getElementById('view-modal-body').innerHTML = `
    <div class="row g-3">
      <div class="col-12 text-center mb-2">
        <div style="width:56px;height:56px;background:var(--secondary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;color:var(--secondary);margin:0 auto .5rem;">${escHtml((s.first_name || '').charAt(0))}</div>
        <h5 class="fw-bold mb-0">${escHtml(s.last_name)}, ${escHtml(s.first_name)} ${escHtml(s.middle_name||'')}</h5>
      </div>
      ${field('LRN','<span style="font-family:monospace">'+escHtml(s.lrn)+'</span>')}
      ${field('Grade Level',escHtml(s.grade_level))}
      ${field('Section',escHtml(s.section))}
      ${field('Sex',escHtml(s.sex))} ${field('Age',(s.age ? escHtml(s.age)+' years old' : '—'))}
      ${field('Religion',escHtml(s.religion))}
      <div class="col-12"><div style="background:var(--gray-100);border-radius:8px;padding:.6rem .8rem;"><div style="font-size:.72rem;color:var(--gray-600);">Address</div><div class="fw-semibold">${escHtml(s.address)||'—'}</div></div></div>
      ${field('Mother',escHtml(s.mother_name))} ${field('Father',escHtml(s.father_name))}
      ${field('Guardian',s.guardian_name?(escHtml(s.guardian_name)+' ('+escHtml(s.guardian_relation||'')+')'):'—')}
      ${field('Contact No.',escHtml(s.contact))} ${field('Email','<span style="font-size:.82rem">'+escHtml(s.email)+'</span>')}
    </div>`;
  viewModal.show();
}
</script>
</body>
</html>
