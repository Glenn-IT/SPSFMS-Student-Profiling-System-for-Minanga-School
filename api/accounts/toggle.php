<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']); exit; }
$d = json_decode(file_get_contents('php://input'), true);
$id     = (int)($d['id'] ?? 0);
$status = $d['status'] === 'active' ? 'active' : 'inactive';
if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'ID required']); exit; }
if ($id == $_SESSION['user']['id']) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Cannot change your own status']); exit; }
$pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute([$status, $id]);
echo json_encode(['ok'=>true,'status'=>$status]);
