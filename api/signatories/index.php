<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Ensure table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `report_signatories` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `prepared_by_type` ENUM('teacher','custom') NOT NULL DEFAULT 'teacher',
      `prepared_by_user_id` INT UNSIGNED DEFAULT NULL,
      `prepared_by_name` VARCHAR(150) DEFAULT NULL,
      `prepared_by_title` VARCHAR(150) DEFAULT NULL,
      `noted_by_type` ENUM('teacher','custom') NOT NULL DEFAULT 'custom',
      `noted_by_user_id` INT UNSIGNED DEFAULT NULL,
      `noted_by_name` VARCHAR(150) DEFAULT NULL,
      `noted_by_title` VARCHAR(150) DEFAULT 'School Head / Principal',
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$checkRow = $pdo->query("SELECT COUNT(*) FROM report_signatories WHERE id = 1")->fetchColumn();
if ($checkRow == 0) {
    $pdo->exec("INSERT INTO `report_signatories` (`id`, `prepared_by_type`, `noted_by_type`, `noted_by_title`) VALUES (1, 'teacher', 'custom', 'School Head / Principal')");
}

// GET — Return current signatories settings and teachers list
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM report_signatories WHERE id = 1");
    $signatories = $stmt->fetch();

    $tStmt = $pdo->query("SELECT id, name, position, role, email, advisory_grade, advisory_subject FROM users WHERE role IN ('teacher', 'admin') AND status = 'active' ORDER BY name ASC");
    $teachers = $tStmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'signatories' => $signatories,
        'teachers' => $teachers
    ]);
    exit;
}

// POST or PUT — Update signatories settings (Admin only)
if ($method === 'POST' || $method === 'PUT') {
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Forbidden. Admin access required.']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $prepType   = in_array($data['prepared_by_type'] ?? '', ['teacher', 'custom']) ? $data['prepared_by_type'] : 'teacher';
    $prepUserId = !empty($data['prepared_by_user_id']) ? (int)$data['prepared_by_user_id'] : null;
    $prepName   = trim($data['prepared_by_name'] ?? '');
    $prepTitle  = trim($data['prepared_by_title'] ?? '');

    $notedType   = in_array($data['noted_by_type'] ?? '', ['teacher', 'custom']) ? $data['noted_by_type'] : 'custom';
    $notedUserId = !empty($data['noted_by_user_id']) ? (int)$data['noted_by_user_id'] : null;
    $notedName   = trim($data['noted_by_name'] ?? '');
    $notedTitle  = trim($data['noted_by_title'] ?? '');

    // If teacher selected, pull defaults if names/titles empty
    if ($prepType === 'teacher' && $prepUserId) {
        $uStmt = $pdo->prepare("SELECT name, position FROM users WHERE id = ?");
        $uStmt->execute([$prepUserId]);
        $u = $uStmt->fetch();
        if ($u) {
            if (empty($prepName))  $prepName  = $u['name'];
            if (empty($prepTitle)) $prepTitle = $u['position'] ?: 'Teacher';
        }
    }

    if ($notedType === 'teacher' && $notedUserId) {
        $uStmt = $pdo->prepare("SELECT name, position FROM users WHERE id = ?");
        $uStmt->execute([$notedUserId]);
        $u = $uStmt->fetch();
        if ($u) {
            if (empty($notedName))  $notedName  = $u['name'];
            if (empty($notedTitle)) $notedTitle = $u['position'] ?: 'School Head / Principal';
        }
    }

    $updateStmt = $pdo->prepare("
        UPDATE report_signatories SET
            prepared_by_type = ?,
            prepared_by_user_id = ?,
            prepared_by_name = ?,
            prepared_by_title = ?,
            noted_by_type = ?,
            noted_by_user_id = ?,
            noted_by_name = ?,
            noted_by_title = ?
        WHERE id = 1
    ");

    $updateStmt->execute([
        $prepType,
        $prepUserId,
        $prepName,
        $prepTitle,
        $notedType,
        $notedUserId,
        $notedName,
        $notedTitle
    ]);

    $fetchStmt = $pdo->query("SELECT * FROM report_signatories WHERE id = 1");
    $updated = $fetchStmt->fetch();

    echo json_encode([
        'ok' => true,
        'message' => 'Report signatories settings saved successfully.',
        'signatories' => $updated
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
