<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');
if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']); exit; }
$d        = json_decode(file_get_contents('php://input'), true);
$question = trim($d['question'] ?? '');
$answer   = trim($d['answer']   ?? '');
if (!$question || !$answer) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Question and answer required']); exit; }
$pdo->prepare('UPDATE users SET sec_question=?, sec_answer=? WHERE id=?')->execute([$question, $answer, $_SESSION['user']['id']]);
echo json_encode(['ok'=>true,'message'=>'Security question saved.']);
