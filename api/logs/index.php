<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$user = requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $where  = [];
    $params = [];

    // If teacher, restrict to their own activity records
    if ($role === 'teacher') {
        $where[]  = 'user_id = ?';
        $params[] = (int)$user['id'];
    } else {
        // Admin filters
        if (!empty($_GET['role'])) {
            $where[]  = 'role = ?';
            $params[] = $_GET['role'];
        }
    }

    if (!empty($_GET['action'])) {
        $where[]  = 'action = ?';
        $params[] = $_GET['action'];
    }

    if (!empty($_GET['search'])) {
        $kw = '%' . trim($_GET['search']) . '%';
        $where[]  = '(username LIKE ? OR full_name LIKE ? OR details LIKE ? OR ip_address LIKE ?)';
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
    }

    if (!empty($_GET['date'])) {
        $where[]  = 'DATE(created_at) = ?';
        $params[] = $_GET['date'];
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $limit = isset($_GET['limit']) ? min(500, max(10, (int)$_GET['limit'])) : 200;

    $stmt = $pdo->prepare("
        SELECT id, user_id, username, role, full_name, action, details, ip_address, created_at
        FROM activity_logs
        $whereSql
        ORDER BY created_at DESC, id DESC
        LIMIT $limit
    ");
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    // Summary stats
    $stats = [
        'total'    => (int)$pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn(),
        'today'    => (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURRENT_DATE()")->fetchColumn(),
        'teachers' => (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE role = 'teacher'")->fetchColumn(),
        'admins'   => (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE role = 'admin'")->fetchColumn(),
    ];

    echo json_encode(['ok' => true, 'logs' => $logs, 'stats' => $stats]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to fetch logs: ' . $e->getMessage()]);
}
