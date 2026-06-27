<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');

if (!$username) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Username is required.']); exit; }

$stmt = $pdo->prepare('SELECT id, sec_question FROM users WHERE username = ? AND status = "active" LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !$user['sec_question']) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'message'=>'Username not found or no security question set.']);
    exit;
}

// Do NOT reveal which question the user set — let them pick it in the UI
echo json_encode(['ok'=>true,'user_id'=>$user['id']]);
