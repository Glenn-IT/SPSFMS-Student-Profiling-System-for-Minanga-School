<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'accounts';
$users = $pdo->query("SELECT * FROM users ORDER BY role, name")->fetchAll();
$total = count($users);
$active = count(array_filter($users, fn($u) => $u['status'] === 'active'));
$inactive = $total - $active;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Account Management — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+).</p></div>
<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div><div class="page-title">Account Management</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Accounts</li></ol></nav>
      </div>
      <div class="ms-auto"><div class="user-menu"><div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div><div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div></div></div>
    </nav>

    <div class="page-header"><h3>Account Management</h3><p>Manage user accounts and access status</p></div>

    <div class="row g-3 mb-4">
      <div class="col-md-4"><div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div><div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total Accounts</div></div></div></div>
      <div class="col-md-4"><div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div><div class="stat-value"><?= $active ?></div><div class="stat-label">Active</div></div></div></div>
      <div class="col-md-4"><div class="stat-card red"><div class="stat-icon"><i class="fas fa-ban"></i></div><div><div class="stat-value"><?= $inactive ?></div><div class="stat-label">Inactive</div></div></div></div>
    </div>

    <div class="card">
      <div class="card-header"><i class="fas fa-user-cog me-2" style="color:var(--primary);"></i>User Accounts</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-modern mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Username</th><th>Role</th><th>Position</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
            <tbody id="accounts-tbody">
              <?php foreach ($users as $i => $u): ?>
              <tr id="row-<?= $u['id'] ?>">
                <td><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                <td><span style="font-family:monospace;font-size:.82rem;"><?= htmlspecialchars($u['username']) ?></span></td>
                <td>
                  <?php $roleColor = ['admin'=>'primary','teacher'=>'success','student'=>'warning'][$u['role']] ?? 'secondary'; ?>
                  <span class="badge bg-<?= $roleColor ?> bg-opacity-15 text-<?= $roleColor ?> fw-semibold text-capitalize"><?= $u['role'] ?></span>
                </td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($u['position'] ?? '—') ?></td>
                <td>
                  <span class="badge <?= $u['status']==='active' ? 'bg-success' : 'bg-danger' ?> bg-opacity-15 text-<?= $u['status']==='active'?'success':'danger' ?> fw-semibold status-badge" id="badge-<?= $u['id'] ?>">
                    <?= ucfirst($u['status']) ?>
                  </span>
                </td>
                <td class="text-center">
                  <?php if ($u['id'] != $user['id']): ?>
                  <button class="btn btn-sm <?= $u['status']==='active'?'btn-outline-danger':'btn-outline-success' ?>" id="btn-<?= $u['id'] ?>"
                    onclick="toggleStatus(<?= $u['id'] ?>, '<?= $u['status']==='active'?'inactive':'active' ?>')">
                    <i class="fas <?= $u['status']==='active'?'fa-ban':'fa-check' ?>"></i>
                    <?= $u['status']==='active'?'Deactivate':'Activate' ?>
                  </button>
                  <?php else: ?>
                  <span style="font-size:.78rem;color:var(--gray-400);">Current user</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
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

async function toggleStatus(id, newStatus) {
  const label = newStatus === 'active' ? 'activate' : 'deactivate';
  confirmModal('Confirm', `Are you sure you want to ${label} this account?`, async () => {
    const res = await fetch(BASE + '/api/accounts/toggle.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ id, status: newStatus })
    });
    const data = await res.json();
    if (!data.ok) { showToast(data.message, 'error'); return; }
    const badge = document.getElementById('badge-'+id);
    const btn   = document.getElementById('btn-'+id);
    if (newStatus === 'active') {
      badge.textContent = 'Active'; badge.className = 'badge bg-success bg-opacity-15 text-success fw-semibold status-badge';
      btn.className = 'btn btn-sm btn-outline-danger'; btn.innerHTML = '<i class="fas fa-ban"></i> Deactivate';
      btn.onclick = () => toggleStatus(id, 'inactive');
    } else {
      badge.textContent = 'Inactive'; badge.className = 'badge bg-danger bg-opacity-15 text-danger fw-semibold status-badge';
      btn.className = 'btn btn-sm btn-outline-success'; btn.innerHTML = '<i class="fas fa-check"></i> Activate';
      btn.onclick = () => toggleStatus(id, 'active');
    }
    showToast(`Account ${label}d.`, 'success');
  });
}
</script>
</body>
</html>
