<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$role     = $data['role'] ?? 'any';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Username and password are required.']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE BINARY username = ? AND status = "active" LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password.']);
    exit;
}

if ($role !== 'any' && $user['role'] !== $role) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'This account is not registered as ' . ucfirst($role) . '. Please go back and select the correct role.']);
    exit;
}

$_SESSION['user'] = [
    'id'          => $user['id'],
    'role'        => $user['role'],
    'name'        => $user['name'],
    'email'       => $user['email'],
    'position'    => $user['position'],
    'lrn'         => $user['lrn'],
    'grade_level' => $user['grade_level'],
    'section'     => $user['section'],
];

echo json_encode(['ok' => true, 'role' => $user['role'], 'name' => $user['name']]);
