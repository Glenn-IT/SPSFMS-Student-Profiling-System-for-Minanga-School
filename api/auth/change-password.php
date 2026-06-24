<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$oldPw  = $data['old_password']     ?? '';
$newPw  = $data['new_password']     ?? '';
$confPw = $data['confirm_password'] ?? '';

if (!$oldPw || !$newPw || !$confPw) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'All password fields are required.']);
    exit;
}

if (strlen($newPw) < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'New password must be at least 6 characters.']);
    exit;
}

if ($newPw !== $confPw) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'New passwords do not match.']);
    exit;
}

$userId = $_SESSION['user']['id'];
$stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || !password_verify($oldPw, $user['password'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

$hash = password_hash($newPw, PASSWORD_BCRYPT);
$pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $userId]);

echo json_encode(['ok' => true, 'message' => 'Password changed successfully.']);
