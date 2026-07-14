<?php
define('CURRENT_VERSION', 'v1.10');
if (!defined('BASE_URL')) require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Under Construction</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.10);
      padding: 48px 40px 40px;
      max-width: 480px;
      width: 90%;
      text-align: center;
    }
    .icon { font-size: 64px; margin-bottom: 16px; line-height: 1; }
    .version-badge {
      display: inline-block;
      background: #1a73e8;
      color: #fff;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 14px;
      border-radius: 999px;
      margin-bottom: 20px;
    }
    h1 {
      font-size: 24px;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 12px;
    }
    p {
      font-size: 15px;
      color: #5f6368;
      line-height: 1.6;
      margin-bottom: 32px;
    }
    .btn-back {
      display: inline-block;
      background: #1a73e8;
      color: #fff;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      padding: 12px 28px;
      border-radius: 8px;
      transition: background 0.2s;
      border: none;
      cursor: pointer;
    }
    .btn-back:hover { background: #1558b0; }
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.5);
      display: none; align-items: center; justify-content: center; z-index: 9999;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
      background: #fff; border-radius: 12px; padding: 1.5rem;
      max-width: 360px; width: 90%; box-shadow: 0 8px 32px rgba(0,0,0,.2); text-align: left;
    }
    .modal-box h5 { font-size: 16px; font-weight: 700; margin-bottom: .5rem; color: #1a1a2e; }
    .modal-box p { font-size: 14px; color: #5f6368; margin-bottom: 1.25rem; }
    .modal-actions { display: flex; gap: .5rem; justify-content: flex-end; }
    .modal-actions button {
      border: none; border-radius: 6px; padding: 8px 16px; font-size: 14px; font-weight: 600; cursor: pointer;
    }
    .modal-actions .btn-cancel { background: #f1f3f4; color: #3c4043; }
    .modal-actions .btn-confirm { background: #d93025; color: #fff; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">🚧</div>
    <span class="version-badge">Current Version: <?php echo CURRENT_VERSION; ?></span>
    <h1>Under Construction</h1>
    <p>This page is not yet available in the current version of the system.<br>
       It will be unlocked in a future release.</p>
    <a href="<?php echo BASE_URL; ?>/api/auth/logout.php" id="logout-link" class="btn-back"
       onclick="return showLogoutModal();">
      ⏻ Logout
    </a>
  </div>

  <div class="modal-overlay" id="logout-modal">
    <div class="modal-box">
      <h5>Logout</h5>
      <p>Are you sure you want to logout?</p>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="hideLogoutModal()">Cancel</button>
        <button type="button" class="btn-confirm" onclick="window.location.href = document.getElementById('logout-link').href;">Logout</button>
      </div>
    </div>
  </div>

  <script>
    function showLogoutModal() {
      document.getElementById('logout-modal').classList.add('show');
      return false;
    }
    function hideLogoutModal() {
      document.getElementById('logout-modal').classList.remove('show');
    }
  </script>
</body>
</html>
<?php exit; ?>
