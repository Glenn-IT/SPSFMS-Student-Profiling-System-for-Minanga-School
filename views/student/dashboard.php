<?php
require_once __DIR__ . '/../../includes/auth_check.php';
$user = requireAuth('student');

// Fetch student record by LRN
$sStmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
$sStmt->execute([$user['lrn']]);
$student = $sStmt->fetch();

// Fetch grades for active school year
$grades = [];
if ($student) {
    $gStmt = $pdo->prepare("SELECT * FROM grades WHERE student_id=? AND school_year=? ORDER BY subject");
    $gStmt->execute([$student['id'], SCHOOL_YEAR]);
    $grades = $gStmt->fetchAll();
}

// Announcements for all & student audience
$annStmt = $pdo->query("SELECT * FROM announcements WHERE audience IN ('all','student') ORDER BY posted_at DESC LIMIT 5");
$announcements = $annStmt->fetchAll();

$initial = $student ? strtoupper(substr($student['first_name'], 0, 1)) : 'S';
$firstName = $student ? $student['first_name'] : ($user['name'] ?? 'Student');
$fullName = $student ? ($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'].' ' : '') . $student['last_name']) : ($user['name'] ?? 'Student');
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

// Calculate averages and counts
$finals = array_filter(array_column($grades, 'final_grade'), fn($v) => $v !== null && $v !== '');
$avg = count($finals) ? round(array_sum($finals) / count($finals), 2) : null;
$totalSubjects = count($grades);
$passedCount = 0;
foreach ($grades as $g) {
    if (($g['remarks'] ?? '') === 'Passed' || (isset($g['final_grade']) && $g['final_grade'] >= 75)) {
        $passedCount++;
    }
}
$standing = 'In Progress';
if ($avg !== null) {
    if ($avg >= 90) {
        $standing = 'With Honors';
    } elseif ($avg >= 75) {
        $standing = 'Passed';
    } else {
        $standing = 'Needs Improvement';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php $pageTitle = 'Dashboard — Student'; include __DIR__ . '/../../includes/head.php'; ?>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/student-mobile.css">
</head>
<body>

<?php
$activeNav = 'dashboard';
$navTitle = 'Dashboard';
$showBack = false;
include __DIR__ . '/../../includes/student-navbar.php';
?>

<div class="student-app">

  <!-- Responsive Hero Banner -->
  <div class="student-hero-banner">
    <div class="hero-text">
      <h4><?= $greeting ?>, <?= htmlspecialchars($firstName) ?>!</h4>
      <p>Welcome back to your SPSMIS Student Portal. Track your academic progress and school updates below.</p>
      <div class="hero-badges">
        <span class="hero-badge"><i class="fas fa-id-badge"></i> LRN: <?= htmlspecialchars($user['lrn'] ?? ($student['lrn'] ?? 'N/A')) ?></span>
        <?php if ($student): ?>
          <span class="hero-badge"><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($student['grade_level']) ?> - Section <?= htmlspecialchars($student['section']) ?></span>
        <?php endif; ?>
        <span class="hero-badge"><i class="fas fa-calendar-alt"></i> S.Y. <?= SCHOOL_YEAR ?></span>
      </div>
    </div>
    <div class="d-none d-md-flex align-items-center gap-2">
      <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="<?= SCHOOL_NAME ?>" style="width:78px;height:78px;border-radius:16px;object-fit:cover;border:3px solid rgba(255,255,255,0.4);box-shadow:0 4px 14px rgba(0,0,0,0.18);" onerror="this.style.display='none'">
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Profile Card, Quick Actions, Announcements -->
    <div class="col-12 col-lg-4">
      
      <!-- Student Identity Card -->
      <div class="desktop-card">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="profile-avatar"><?= $initial ?></div>
          <div>
            <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($fullName) ?></h6>
            <div class="text-muted small"><?= $student ? htmlspecialchars($student['grade_level'] . ' | Sec. ' . $student['section']) : 'Learner' ?></div>
            <div class="text-secondary small font-monospace">LRN: <?= htmlspecialchars($user['lrn'] ?? ($student['lrn'] ?? '—')) ?></div>
          </div>
        </div>
        <div class="border-top pt-2 mt-2">
          <div class="d-flex justify-content-between py-1 small">
            <span class="text-muted">Academic Status:</span>
            <span class="fw-bold <?= $standing === 'With Honors' ? 'text-success' : ($standing === 'Passed' ? 'text-primary' : 'text-dark') ?>"><?= $standing ?></span>
          </div>
          <div class="d-flex justify-content-between align-items-center py-1 small">
            <span class="text-muted">School:</span>
            <span class="fw-semibold text-truncate ms-2 d-flex align-items-center gap-1" style="max-width:180px;">
              <img src="<?= BASE_URL ?>/img/MIS-Logo.jpg" alt="Logo" style="width:18px;height:18px;border-radius:4px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'">
              <?= SCHOOL_NAME ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-bolt text-primary"></i> Quick Actions</h6>
        </div>
        <div class="desktop-quick-grid">
          <a href="#grades-section" class="desktop-action-btn">
            <i class="fas fa-chart-bar"></i>
            <span>View Grades</span>
          </a>
          <a href="profile.php" class="desktop-action-btn">
            <i class="fas fa-user-circle"></i>
            <span>My Profile</span>
          </a>
          <a href="settings.php" class="desktop-action-btn">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </a>
          <a href="<?= BASE_URL ?>/api/auth/logout.php" class="desktop-action-btn logout-btn" onclick="return confirmLogout(this)">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>

      <!-- Announcements Feed -->
      <div class="desktop-card">
        <div class="desktop-card-header">
          <h6 class="desktop-card-title"><i class="fas fa-bullhorn text-primary"></i> Announcements</h6>
          <span class="badge bg-light text-muted border"><?= count($announcements) ?></span>
        </div>
        <?php if (empty($announcements)): ?>
          <div class="text-center py-4 text-muted small">No announcements posted at this time.</div>
        <?php else: ?>
          <?php foreach ($announcements as $ann): ?>
            <div class="announcement-item">
              <div class="announcement-title"><?= htmlspecialchars($ann['title']) ?></div>
              <div class="announcement-body"><?= htmlspecialchars($ann['body']) ?></div>
              <div class="announcement-date">
                <i class="far fa-clock"></i> <?= date('F j, Y', strtotime($ann['posted_at'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>

    <!-- Right Column: Academic Summary & Grades -->
    <div class="col-12 col-lg-8">

      <!-- Academic KPI Stats Row -->
      <div class="student-kpi-row">
        <div class="student-kpi-card">
          <div class="student-kpi-icon blue">
            <i class="fas fa-award"></i>
          </div>
          <div>
            <div class="student-kpi-value text-primary"><?= $avg !== null ? number_format($avg, 2) : '—' ?></div>
            <div class="student-kpi-label">General Average</div>
          </div>
        </div>

        <div class="student-kpi-card">
          <div class="student-kpi-icon green">
            <i class="fas fa-book-reader"></i>
          </div>
          <div>
            <div class="student-kpi-value text-success"><?= $passedCount ?> / <?= $totalSubjects ?></div>
            <div class="student-kpi-label">Subjects Passed</div>
          </div>
        </div>

        <div class="student-kpi-card">
          <div class="student-kpi-icon yellow">
            <i class="fas fa-chart-line"></i>
          </div>
          <div>
            <div class="student-kpi-value" style="font-size:1.15rem; color:#b06a00;"><?= $standing ?></div>
            <div class="student-kpi-label">Standing</div>
          </div>
        </div>
      </div>

      <!-- Grades Section -->
      <div class="sf10-table-card" id="grades-section">
        <div class="sf10-table-header">
          <div>
            <h6 class="sf10-table-title"><i class="fas fa-file-invoice me-2 text-primary"></i>My Grades — S.Y. <?= SCHOOL_YEAR ?></h6>
            <div class="text-muted small" style="font-size:.75rem;">DepEd SF10 / Form 137 Learner Performance</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <?php if ($avg !== null): ?>
              <div class="badge bg-primary px-3 py-2" style="font-size:.85rem;">
                General Average: <?= number_format($avg, 2) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (empty($grades)): ?>
          <div class="p-4 text-center text-muted">
            <i class="fas fa-folder-open mb-2" style="font-size:2rem; opacity:.4;"></i>
            <p class="mb-0 small">No grades recorded for this school year yet. Check back once term grades are finalized by your advisers.</p>
          </div>
        <?php else: ?>

          <!-- Desktop Table (visible >= 768px) -->
          <div class="table-responsive d-none d-md-block">
            <table class="table sf10-table">
              <thead>
                <tr>
                  <th style="width:38%;">Subject</th>
                  <th class="text-center" style="width:12%;">Term 1</th>
                  <th class="text-center" style="width:12%;">Term 2</th>
                  <th class="text-center" style="width:12%;">Term 3</th>
                  <th class="text-center" style="width:13%;">Final</th>
                  <th class="text-center" style="width:13%;">Remarks</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($grades as $g): ?>
                  <?php
                    $final = $g['final_grade'];
                    $isPass = ($g['remarks'] === 'Passed' || ($final !== null && $final >= 75));
                    $color = $final >= 90 ? 'text-success' : ($final >= 75 ? 'text-primary' : 'text-danger');
                  ?>
                  <tr>
                    <td class="subject-col"><?= htmlspecialchars($g['subject']) ?></td>
                    <td class="grade-val"><?= $g['t1'] ?? $g['q1'] ?? '—' ?></td>
                    <td class="grade-val"><?= $g['t2'] ?? $g['q2'] ?? '—' ?></td>
                    <td class="grade-val"><?= $g['t3'] ?? $g['q3'] ?? '—' ?></td>
                    <td class="final-val <?= $color ?>"><?= $final ?? '—' ?></td>
                    <td class="text-center">
                      <?php if ($g['remarks']): ?>
                        <span class="remarks-badge <?= $isPass ? 'passed' : 'failed' ?>">
                          <?= htmlspecialchars($g['remarks']) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <?php if ($avg !== null): ?>
                <tfoot>
                  <tr style="background:#f8fafc; font-weight:700;">
                    <td colspan="4" class="text-end text-uppercase" style="font-size:.8rem; letter-spacing:.5px; color:#475569;">General Average</td>
                    <td class="text-center text-primary" style="font-size:1.05rem;"><?= number_format($avg, 2) ?></td>
                    <td class="text-center">
                      <span class="remarks-badge <?= $avg >= 75 ? 'passed' : 'failed' ?>">
                        <?= $avg >= 75 ? 'Passed' : 'Failed' ?>
                      </span>
                    </td>
                  </tr>
                </tfoot>
              <?php endif; ?>
            </table>
          </div>

          <!-- Mobile Cards (visible < 768px) -->
          <div class="p-3 d-block d-md-none">
            <?php foreach ($grades as $g): ?>
              <?php
                $final = $g['final_grade'];
                $isPass = ($g['remarks'] === 'Passed' || ($final !== null && $final >= 75));
                $color = $final >= 90 ? 'var(--secondary)' : ($final >= 75 ? 'var(--primary)' : 'var(--danger)');
              ?>
              <div class="mobile-card">
                <div class="card-row">
                  <span class="card-label fw-semibold text-dark"><?= htmlspecialchars($g['subject']) ?></span>
                  <span class="card-value fw-bold" style="color:<?= $color ?>;"><?= $final ?? '—' ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top" style="font-size:.75rem;">
                  <span class="text-muted">T1: <?= $g['t1']??$g['q1']??'—' ?> | T2: <?= $g['t2']??$g['q2']??'—' ?> | T3: <?= $g['t3']??$g['q3']??'—' ?></span>
                  <?php if ($g['remarks']): ?>
                    <span class="remarks-badge <?= $isPass ? 'passed' : 'failed' ?>"><?= htmlspecialchars($g['remarks']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>

            <?php if ($avg !== null): ?>
              <div class="mobile-card" style="background:var(--primary-light); border-color:rgba(26,115,232,0.3);">
                <div class="card-row">
                  <span class="card-label fw-bold text-primary">General Average</span>
                  <span class="card-value fw-bold" style="color:var(--primary);font-size:1.15rem;"><?= number_format($avg, 2) ?></span>
                </div>
              </div>
            <?php endif; ?>
          </div>

        <?php endif; ?>
      </div>

    </div>
  </div>

</div>

<script src="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/components.js"></script>
</body>
</html>
