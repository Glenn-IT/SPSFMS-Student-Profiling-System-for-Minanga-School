<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']); exit; }
$d    = json_decode(file_get_contents('php://input'), true);
$name = trim($d['name'] ?? '');
$email= trim($d['email'] ?? '');
if (!$name || !$email) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Name and email required']); exit; }
$pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([$name, $email, $_SESSION['user']['id']]);
$_SESSION['user']['name']  = $name;
$_SESSION['user']['email'] = $email;
echo json_encode(['ok'=>true,'name'=>$name,'email'=>$email]);
