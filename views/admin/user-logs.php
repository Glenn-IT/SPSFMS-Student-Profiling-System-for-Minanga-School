<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');

// Fresh user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch() ?: $user;

$activePage = 'user_logs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'User Activity Logs — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
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
    .action-account { background: #fae8ff; color: #86198f; border: 1px solid #f5d0fe; }
    .action-status { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .action-default { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }
  </style>
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>

<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">User Activity Logs</div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item text-muted">Admin</li>
            <li class="breadcrumb-item active">User Logs</li>
          </ol>
        </nav>
      </div>
      <div class="ms-auto">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($dbUser['name'], 0, 1)) ?></div>
          <div>
            <div class="user-name"><?= htmlspecialchars($dbUser['name']) ?></div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
      </div>
    </nav>

    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-4">
      <div>
        <h3 class="mb-1">User Audit & Activity Logs</h3>
        <p class="mb-0 text-muted">Monitor and track teacher, administrator, and student actions across the system</p>
      </div>
      <button class="btn btn-primary px-3 py-2 fw-semibold" onclick="loadLogs()">
        <i class="fas fa-sync-alt me-2"></i>Refresh Logs
      </button>
    </div>

    <!-- Overview KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid var(--primary) !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">TOTAL ACTIVITIES</div>
              <h4 class="fw-bold mb-0 text-primary" id="kpi-total-logs">0</h4>
            </div>
            <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle">
              <i class="fas fa-list-alt fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #10b981 !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">TODAY'S LOGS</div>
              <h4 class="fw-bold mb-0 text-success" id="kpi-today-logs">0</h4>
            </div>
            <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle">
              <i class="fas fa-calendar-day fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #0284c7 !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">TEACHER ACTIVITIES</div>
              <h4 class="fw-bold mb-0 text-info" id="kpi-teacher-logs">0</h4>
            </div>
            <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle">
              <i class="fas fa-chalkboard-teacher fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3" style="border-left: 4px solid #8b5cf6 !important;">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="text-muted small fw-semibold">ADMIN ACTIVITIES</div>
              <h4 class="fw-bold mb-0 text-purple" style="color:#8b5cf6;" id="kpi-admin-logs">0</h4>
            </div>
            <div class="p-3 bg-purple bg-opacity-10 rounded-circle" style="background:#f3e8ff;color:#8b5cf6;">
              <i class="fas fa-user-shield fa-lg"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-body py-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
              <input type="text" id="log-search" class="form-control" placeholder="Search user, details, or IP..." oninput="debounceFilter()">
            </div>
          </div>
          <div class="col-md-2">
            <select id="log-role" class="form-select" onchange="loadLogs()">
              <option value="">All User Roles</option>
              <option value="teacher">Teachers</option>
              <option value="admin">Administrators</option>
              <option value="student">Students</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="log-action" class="form-select" onchange="loadLogs()">
              <option value="">All Action Types</option>
              <option value="LOGIN">User Login</option>
              <option value="LOGOUT">User Logout</option>
              <option value="SAVE_GRADES">Grade Entries / Updates</option>
              <option value="CREATE_STUDENT">Student Profiling</option>
              <option value="CREATE_ACCOUNT">Account Creation</option>
              <option value="TOGGLE_STATUS">Account Status Toggle</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" id="log-date" class="form-control" onchange="loadLogs()" title="Filter by date">
          </div>
          <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" onclick="resetLogFilters()" title="Reset Filters">
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Logs Table Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 fw-bold d-flex align-items-center justify-content-between">
        <span><i class="fas fa-history me-2 text-primary"></i>System Audit Trail</span>
        <span class="badge bg-primary bg-opacity-15 text-primary px-3 py-2 rounded-pill fw-semibold" id="logs-count-badge">0 Logs</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width:60px;" class="text-center">#</th>
                <th style="width:160px;">Date & Time</th>
                <th>User / Actor</th>
                <th class="text-center" style="width:110px;">Role</th>
                <th class="text-center" style="width:140px;">Action</th>
                <th>Activity Description</th>
                <th style="width:120px;">IP Address</th>
              </tr>
            </thead>
            <tbody id="logs-tbody">
              <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading user logs...</td></tr>
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
  debounceTimer = setTimeout(() => { loadLogs(); }, 300);
}

function resetLogFilters() {
  document.getElementById('log-search').value = '';
  document.getElementById('log-role').value = '';
  document.getElementById('log-action').value = '';
  document.getElementById('log-date').value = '';
  loadLogs();
}

async function loadLogs() {
  const tbody = document.getElementById('logs-tbody');
  tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading user logs...</td></tr>';

  const role   = document.getElementById('log-role').value;
  const action = document.getElementById('log-action').value;
  const search = document.getElementById('log-search').value.trim();
  const date   = document.getElementById('log-date').value;

  const params = new URLSearchParams();
  if (role) params.append('role', role);
  if (action) params.append('action', action);
  if (search) params.append('search', search);
  if (date) params.append('date', date);

  try {
    const res = await fetch(`${BASE}/api/logs/index.php?${params.toString()}`);
    const data = await res.json();

    if (!data.ok) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">${data.message || 'Failed to load logs.'}</td></tr>`;
      return;
    }

    // Update KPIs
    if (data.stats) {
      document.getElementById('kpi-total-logs').textContent = data.stats.total || 0;
      document.getElementById('kpi-today-logs').textContent = data.stats.today || 0;
      document.getElementById('kpi-teacher-logs').textContent = data.stats.teachers || 0;
      document.getElementById('kpi-admin-logs').textContent = data.stats.admins || 0;
    }

    const logs = data.logs || [];
    document.getElementById('logs-count-badge').textContent = `${logs.length} Log${logs.length === 1 ? '' : 's'}`;

    if (logs.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No activity logs recorded matching the criteria.</td></tr>';
      return;
    }

    const roleColors = { admin: 'primary', teacher: 'success', student: 'warning', system: 'secondary' };

    tbody.innerHTML = logs.map((log, i) => {
      const color = roleColors[log.role] || 'secondary';
      const actionClass = getActionBadgeClass(log.action);
      const timeStr = formatLogTime(log.created_at);

      return `
        <tr>
          <td class="text-center fw-bold text-muted">${i + 1}</td>
          <td><small class="text-dark fw-semibold"><i class="far fa-clock me-1 text-muted"></i>${timeStr}</small></td>
          <td>
            <div class="fw-bold">${escHtml(log.full_name || log.username || 'System')}</div>
            <small class="text-muted" style="font-family:monospace;">@${escHtml(log.username || 'system')}</small>
          </td>
          <td class="text-center">
            <span class="badge bg-${color} bg-opacity-15 text-${color} text-capitalize fw-semibold px-2 py-1">${escHtml(log.role)}</span>
          </td>
          <td class="text-center">
            <span class="action-badge ${actionClass}">${escHtml(log.action)}</span>
          </td>
          <td><span style="font-size:.85rem;">${escHtml(log.details)}</span></td>
          <td><small class="text-muted" style="font-family:monospace;">${escHtml(log.ip_address || '—')}</small></td>
        </tr>
      `;
    }).join('');

  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error connecting to server.</td></tr>';
  }
}

function getActionBadgeClass(act) {
  if (!act) return 'action-default';
  if (act.includes('LOGIN')) return 'action-login';
  if (act.includes('LOGOUT')) return 'action-logout';
  if (act.includes('GRADE')) return 'action-grades';
  if (act.includes('STUDENT')) return 'action-student';
  if (act.includes('ACCOUNT')) return 'action-account';
  if (act.includes('STATUS')) return 'action-status';
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

loadLogs();
</script>
</body>
</html>
