<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — list all subjects (optionally filtered by grade_type)
if ($method === 'GET') {
    $type = $_GET['grade_type'] ?? '';
    if ($type) {
        $stmt = $pdo->prepare('SELECT * FROM subjects WHERE grade_type = ? ORDER BY id');
        $stmt->execute([$type]);
    } else {
        $stmt = $pdo->query('SELECT * FROM subjects ORDER BY grade_type, id');
    }
    echo json_encode(['ok'=>true, 'subjects'=>$stmt->fetchAll()]);
    exit;
}

// POST — add subject
if ($method === 'POST') {
    $d    = json_decode(file_get_contents('php://input'), true);
    $name = trim($d['name'] ?? '');
    $type = $d['grade_type'] ?? '';
    if (!$name || !in_array($type, ['elementary','jhs','shs'])) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Name and valid grade_type required.']); exit;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO subjects (name, grade_type) VALUES (?,?)');
        $stmt->execute([$name, $type]);
        $id = $pdo->lastInsertId();
        $row = $pdo->prepare('SELECT * FROM subjects WHERE id=?'); $row->execute([$id]);
        echo json_encode(['ok'=>true,'message'=>'Subject added.','subject'=>$row->fetch()]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo json_encode(['ok'=>false,'message'=>'Subject already exists for this level.']);
        else { http_response_code(500); echo json_encode(['ok'=>false,'message'=>'DB error.']); }
    }
    exit;
}

// PUT — rename subject
if ($method === 'PUT') {
    $d    = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($d['id'] ?? 0);
    $name = trim($d['name'] ?? '');
    if (!$id || !$name) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id and name required.']); exit; }
    try {
        $pdo->prepare('UPDATE subjects SET name=? WHERE id=?')->execute([$name, $id]);
        $row = $pdo->prepare('SELECT * FROM subjects WHERE id=?'); $row->execute([$id]);
        echo json_encode(['ok'=>true,'message'=>'Subject updated.','subject'=>$row->fetch()]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) echo json_encode(['ok'=>false,'message'=>'Another subject already has this name.']);
        else { http_response_code(500); echo json_encode(['ok'=>false,'message'=>'DB error.']); }
    }
    exit;
}

// DELETE — remove subject
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id required.']); exit; }
    $pdo->prepare('DELETE FROM subjects WHERE id=?')->execute([$id]);
    echo json_encode(['ok'=>true,'message'=>'Subject deleted.']);
    exit;
}

http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
