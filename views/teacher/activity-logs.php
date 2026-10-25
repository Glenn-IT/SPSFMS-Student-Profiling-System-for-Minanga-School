<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('teacher');

// Fetch fresh teacher data
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$uStmt->execute([$user['id']]);
$dbUser = $uStmt->fetch() ?: $user;

$activePage = 'activity_logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Activity Logs — Teacher'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
  <style>
    .action-badge {
      font-size: .75rem;
      font-weight: 700;
      padding: .25rem .6rem;
      border-radius: 4px;
      letter-spacing: .5px;
      font-family: monospace;
    }
    .action-login { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .action-logout { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .action-grades { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .action-student { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .action-default { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>

<?php include __DIR__ . '/../../includes/teacher-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Activity Logs</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Teacher</li>
            <li class="breadcrumb-item active">Activity Logs</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <div class="user-menu">
          <div class="user-avatar" style="background:var(--secondary);color:#fff;"><?= strtoupper(substr($dbUser['name'], 0, 1)) ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($dbUser['name']) ?></div>
            <div class="user-role">Teacher</div>
          </div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
      <div>
        <h3 class="mb-1">My Activity Logs</h3>
        <p class="mb-0 text-muted">Review your recent session logins, grade submissions, and account activity</p>
      </div>
      <button class="btn btn-outline-success px-3 py-2 fw-semibold" onclick="loadTeacherLogs()">
        <i class="fas fa-sync-alt me-2"></i>Refresh Logs
      </button>
    </div>

    <!-- Overview KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #16a34a !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">MY TOTAL ACTIONS</div>
              <h4 class="fw-bold mb-0 text-success" id="kpi-my-total">0</h4>
            </div>
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
              <i class="fas fa-history fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #0284c7 !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">GRADE ENTRIES / EDITS</div>
              <h4 class="fw-bold mb-0 text-info" id="kpi-my-grades">0</h4>
            </div>
            <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
              <i class="fas fa-clipboard-check fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #6366f1 !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">LOGINS & SESSIONS</div>
              <h4 class="fw-bold mb-0 text-primary" id="kpi-my-logins">0</h4>
            </div>
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
              <i class="fas fa-sign-in-alt fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
              <input type="text" id="log-search" class="form-control" placeholder="Search action details or IP..." oninput="debounceFilter()">
            </div>
          </div>
          <div class="col-md-3">
            <select id="log-action" class="form-select" onchange="loadTeacherLogs()">
              <option value="">All Action Types</option>
              <option value="LOGIN">Logins</option>
              <option value="LOGOUT">Logouts</option>
              <option value="SAVE_GRADES">Grade Entries / Updates</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" id="log-date" class="form-control" onchange="loadTeacherLogs()" title="Filter by date">
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" onclick="resetFilters()" title="Reset Filters">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Logs Table Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
        <span><i class="fas fa-clipboard-list me-2 text-success"></i>My Activity History</span>
        <span class="badge bg-success bg-opacity-15 text-success px-3 py-2 rounded-pill fw-semibold" id="logs-count-badge">0 Logs</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width:60px;" class="text-center">#</th>
                <th style="width:180px;">Date & Time</th>
                <th class="text-center" style="width:150px;">Action</th>
                <th>Activity Description</th>
                <th style="width:130px;">IP Address</th>
              </tr>
            </thead>
            <tbody id="logs-tbody">
              <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading your activity logs...</td></tr>
            </tbody>
          </table>
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

let debounceTimer = null;

function debounceFilter() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => { loadTeacherLogs(); }, 300);
}

function resetFilters() {
  document.getElementById('log-search').value = '';
  document.getElementById('log-action').value = '';
  document.getElementById('log-date').value = '';
  loadTeacherLogs();
}

async function loadTeacherLogs() {
  const tbody = document.getElementById('logs-tbody');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading your activity logs...</td></tr>';

  const action = document.getElementById('log-action').value;
  const search = document.getElementById('log-search').value.trim();
  const date   = document.getElementById('log-date').value;

  const params = new URLSearchParams();
  if (action) params.append('action', action);
  if (search) params.append('search', search);
  if (date) params.append('date', date);

  try {
    const res = await fetch(`${BASE}/api/logs/index.php?${params.toString()}`);
    const data = await res.json();

    if (!data.ok) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${data.message || 'Failed to load logs.'}</td></tr>`;
      return;
    }

    const logs = data.logs || [];
    document.getElementById('logs-count-badge').textContent = `${logs.length} Log${logs.length === 1 ? '' : 's'}`;
    document.getElementById('kpi-my-total').textContent = logs.length;
    document.getElementById('kpi-my-grades').textContent = logs.filter(l => l.action.includes('GRADE')).length;
    document.getElementById('kpi-my-logins').textContent = logs.filter(l => l.action.includes('LOGIN')).length;

    if (logs.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No activity records found matching your filters.</td></tr>';
      return;
    }

    tbody.innerHTML = logs.map((log, i) => {
      const actionClass = getActionBadgeClass(log.action);
      const timeStr = formatLogTime(log.created_at);

      return `
        <tr>
          <td class="text-center fw-bold text-muted">${i + 1}</td>
          <td><small class="text-dark fw-semibold"><i class="far fa-clock me-1 text-muted"></i>${timeStr}</small></td>
          <td class="text-center">
            <span class="action-badge ${actionClass}">${escHtml(log.action)}</span>
          </td>
          <td><span style="font-size:.85rem;">${escHtml(log.details)}</span></td>
          <td><small class="text-muted" style="font-family:monospace;">${escHtml(log.ip_address || '—')}</small></td>
        </tr>
      `;
    }).join('');

  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Error connecting to server.</td></tr>';
  }
}

function getActionBadgeClass(act) {
  if (!act) return 'action-default';
  if (act.includes('LOGIN')) return 'action-login';
  if (act.includes('LOGOUT')) return 'action-logout';
  if (act.includes('GRADE')) return 'action-grades';
  return 'action-default';
}

function formatLogTime(dt) {
  if (!dt) return '—';
  const d = new Date(dt.replace(' ', 'T'));
  if (isNaN(d.getTime())) return dt;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + 
         d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str ?? '';
  return d.innerHTML;
}

loadTeacherLogs();
</script>
</body>
</html>
