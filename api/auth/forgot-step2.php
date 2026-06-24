<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']); exit; }

$data    = json_decode(file_get_contents('php://input'), true);
$userId  = (int)($data['user_id'] ?? 0);
$answer  = strtolower(trim($data['answer'] ?? ''));

if (!$userId || !$answer) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Missing required fields.']); exit; }

$stmt = $pdo->prepare('SELECT sec_answer FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user || strtolower(trim($user['sec_answer'])) !== $answer) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'message'=>'Incorrect answer. Please try again.']);
    exit;
}

// Generate temp password and update DB
$tempPassword = 'Temp@' . rand(1000, 9999);
$pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($tempPassword, PASSWORD_BCRYPT), $userId]);

echo json_encode(['ok'=>true,'temp_password'=>$tempPassword]);
