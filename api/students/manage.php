<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Student ID required']); exit; }

$method = $_SERVER['REQUEST_METHOD'];

// GET — fetch one student
if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    if (!$student) { http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Student not found']); exit; }
    echo json_encode(['ok'=>true,'student'=>$student]);
    exit;
}

// POST — update student (using POST since HTML forms don't support PUT)
if ($method === 'POST') {
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403); echo json_encode(['ok'=>false,'message'=>'Admin only']); exit;
    }
    $d = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE students SET
        lrn=?, grade_level=?, section=?, first_name=?, middle_name=?, last_name=?,
        sex=?, birthdate=?, age=?, mother_tongue=?, religion=?, address=?,
        mother_name=?, father_name=?, guardian_name=?, guardian_relation=?,
        contact=?, email=?
        WHERE id=?");
    $stmt->execute([
        $d['lrn'], $d['grade_level'], $d['section'],
        $d['first_name'], $d['middle_name'] ?? null, $d['last_name'],
        $d['sex'], $d['birthdate'], $d['age'] ?? 0,
        $d['mother_tongue'] ?? null, $d['religion'] ?? null, $d['address'] ?? null,
        $d['mother_name'] ?? null, $d['father_name'] ?? null,
        $d['guardian_name'] ?? null, $d['guardian_relation'] ?? null,
        $d['contact'] ?? null, $d['email'] ?? null, $id
    ]);
    $upd = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $upd->execute([$id]);
    echo json_encode(['ok'=>true,'student'=>$upd->fetch()]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
