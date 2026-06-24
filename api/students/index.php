<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — list with optional filters
if ($method === 'GET') {
    $where = ['1=1'];
    $params = [];

    if (!empty($_GET['search'])) {
        $q = '%' . $_GET['search'] . '%';
        $where[] = "(first_name LIKE ? OR last_name LIKE ? OR middle_name LIKE ? OR lrn LIKE ?)";
        array_push($params, $q, $q, $q, $q);
    }
    if (!empty($_GET['grade'])) { $where[] = 'grade_level = ?'; $params[] = $_GET['grade']; }
    if (!empty($_GET['section'])) { $where[] = 'section = ?'; $params[] = $_GET['section']; }
    if (!empty($_GET['year']))  { $where[] = 'school_year = ?'; $params[] = $_GET['year']; }
    if (!empty($_GET['status'])) { $where[] = 'status = ?'; $params[] = $_GET['status']; }

    $sql = 'SELECT * FROM students WHERE ' . implode(' AND ', $where) . ' ORDER BY last_name, first_name';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();
    echo json_encode(['ok'=>true,'students'=>$students,'count'=>count($students)]);
    exit;
}

// POST — add student
if ($method === 'POST') {
    if ($_SESSION['user']['role'] !== 'admin') {
        http_response_code(403); echo json_encode(['ok'=>false,'message'=>'Admin only']); exit;
    }
    $d = json_decode(file_get_contents('php://input'), true);
    $required = ['lrn','grade_level','section','first_name','last_name','sex','birthdate'];
    foreach ($required as $f) {
        if (empty($d[$f])) {
            http_response_code(400);
            echo json_encode(['ok'=>false,'message'=>"Field '$f' is required."]);
            exit;
        }
    }
    // Check LRN unique
    $check = $pdo->prepare('SELECT id FROM students WHERE lrn = ?');
    $check->execute([$d['lrn']]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['ok'=>false,'message'=>'LRN already exists.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO students
        (lrn,grade_level,section,first_name,middle_name,last_name,sex,birthdate,age,mother_tongue,religion,address,mother_name,father_name,guardian_name,guardian_relation,contact,email,school_year,status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'2025-2026','active')");
    $stmt->execute([
        $d['lrn'], $d['grade_level'], $d['section'],
        $d['first_name'], $d['middle_name'] ?? null, $d['last_name'],
        $d['sex'], $d['birthdate'], $d['age'] ?? 0,
        $d['mother_tongue'] ?? null, $d['religion'] ?? null, $d['address'] ?? null,
        $d['mother_name'] ?? null, $d['father_name'] ?? null,
        $d['guardian_name'] ?? null, $d['guardian_relation'] ?? null,
        $d['contact'] ?? null, $d['email'] ?? null,
    ]);
    $id = $pdo->lastInsertId();
    $new = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $new->execute([$id]);
    echo json_encode(['ok'=>true,'student'=>$new->fetch()]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
