<?php
define('CURRENT_VERSION', 'v1.03');
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
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">🚧</div>
    <span class="version-badge">Current Version: <?php echo CURRENT_VERSION; ?></span>
    <h1>Under Construction</h1>
    <p>This page is not yet available in the current version of the system.<br>
       It will be unlocked in a future release.</p>
    <a href="javascript:history.back()" class="btn-back">← Go Back</a>
  </div>
</body>
</html>
<?php exit; ?>
