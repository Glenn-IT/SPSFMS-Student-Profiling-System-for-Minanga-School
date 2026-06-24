<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit; }
$users = $pdo->query("SELECT id,role,username,name,email,position,status,created_at FROM users ORDER BY role,name")->fetchAll();
echo json_encode(['ok'=>true,'users'=>$users]);
