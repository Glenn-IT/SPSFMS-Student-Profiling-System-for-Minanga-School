<?php require_once __DIR__ . '/../../components/under-construction.php'; ?>
<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('admin');
$activePage = 'analytics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Analytics — Admin'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div id="desktop-only-overlay"><i class="fas fa-desktop"></i><h4>Desktop Required</h4><p>Please use a computer (1024px+) to access this portal.</p></div>

<?php include __DIR__ . '/../../includes/admin-sidebar.php'; ?>

<div class="app-wrapper">
  <div class="main-content page-content">
    <nav class="top-navbar">
      <div>
        <div class="page-title">Analytics</div>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item text-muted">Admin</li><li class="breadcrumb-item active">Analytics</li></ol></nav>
      </div>
      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
          <div><div class="user-name"><?= htmlspecialchars($user['name']) ?></div><div class="user-role">Administrator</div></div>
        </div>
      </div>
    </nav>

    <div class="page-header"><h3>Analytics</h3><p>Enrollment trends and distribution data for <?= SCHOOL_NAME ?></p></div>

    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header"><i class="fas fa-chart-line me-2" style="color:var(--primary);"></i>3-Year Enrollment Trend</div>
          <div class="card-body"><canvas id="trendChart" style="max-height:280px;"></canvas></div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header"><i class="fas fa-chart-pie me-2" style="color:var(--primary);"></i>Level Distribution</div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <canvas id="levelChart" style="max-height:250px;"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header"><i class="fas fa-trophy me-2" style="color:var(--primary);"></i>Top Sections by Enrollment</div>
          <div class="card-body p-0">
            <table class="table table-modern mb-0" id="top-sections-table">
              <thead><tr><th>Rank</th><th>Section</th><th>Students</th></tr></thead>
              <tbody><tr><td colspan="3" class="text-center text-muted py-3">Loading...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header"><i class="fas fa-venus-mars me-2" style="color:var(--primary);"></i>Gender by Grade Level</div>
          <div class="card-body"><canvas id="genderLevelChart" style="max-height:250px;"></canvas></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
<script>
showDesktopOnlyWarning();
const BASE = '<?= BASE_URL ?>';

fetch(BASE + '/api/analytics/index.php')
  .then(r => r.json())
  .then(d => {
    if (!d.ok) return;

    // Trend
    const trendLabels = Object.keys(d.trend);
    const trendVals   = Object.values(d.trend);
    new Chart(document.getElementById('trendChart'), {
      type:'line',
      data:{ labels:trendLabels, datasets:[{ label:'Total Students', data:trendVals, borderColor:'#1a73e8', backgroundColor:'rgba(26,115,232,.1)', tension:.4, fill:true, pointRadius:5, pointBackgroundColor:'#1a73e8' }] },
      options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:false, ticks:{ precision:0 } } } }
    });

    // Level doughnut
    new Chart(document.getElementById('levelChart'), {
      type:'doughnut',
      data:{ labels:['Elementary','Junior High','Senior High'], datasets:[{ data:[d.elem,d.jhs,d.shs], backgroundColor:['#34a853','#1a73e8','#ea4335'], borderWidth:0 }] },
      options:{ plugins:{ legend:{ position:'bottom', labels:{ font:{ size:11 } } } }, cutout:'60%' }
    });

    // Top sections
    const medals = ['🥇','🥈','🥉','4.','5.'];
    const tbody = document.querySelector('#top-sections-table tbody');
    if (d.top_sections && d.top_sections.length) {
      tbody.innerHTML = d.top_sections.map((s,i) =>
        `<tr><td>${medals[i]||''}</td><td><strong>${s.section}</strong></td><td><span class="badge bg-primary">${s.cnt}</span></td></tr>`
      ).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';
    }

    // Gender by level
    const levels = Object.keys(d.gender_by_level);
    const maleVals   = levels.map(l => d.gender_by_level[l]['Male']   || 0);
    const femaleVals = levels.map(l => d.gender_by_level[l]['Female'] || 0);
    new Chart(document.getElementById('genderLevelChart'), {
      type:'bar',
      data:{
        labels: levels.map(l => l.replace('Grade','Gr.')),
        datasets:[
          { label:'Male',   data:maleVals,   backgroundColor:'#1a73e8', borderRadius:4 },
          { label:'Female', data:femaleVals, backgroundColor:'#ea4335', borderRadius:4 }
        ]
      },
      options:{ plugins:{ legend:{ position:'top' } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, ticks:{ precision:0 } } } }
    });
  });
</script>
</body>
</html>
