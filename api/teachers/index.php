<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — list teachers
if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    if ($search) {
        $stmt = $pdo->prepare("SELECT id,name,username,email,position,advisory_grade,advisory_subject,status,created_at FROM users WHERE role='teacher' AND (name LIKE ? OR username LIKE ? OR position LIKE ?) ORDER BY name");
        $stmt->execute(["%$search%","%$search%","%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT id,name,username,email,position,advisory_grade,advisory_subject,status,created_at FROM users WHERE role='teacher' ORDER BY name");
    }
    echo json_encode(['ok'=>true,'teachers'=>$stmt->fetchAll()]);
    exit;
}

// PUT — update teacher profile
if ($method === 'PUT') {
    $d               = json_decode(file_get_contents('php://input'), true);
    $id              = (int)($d['id'] ?? 0);
    $name            = trim($d['name'] ?? '');
    $email           = trim($d['email'] ?? '');
    $advisoryGrade   = trim($d['advisory_grade'] ?? '');
    $advisorySubject = trim($d['advisory_subject'] ?? '');
    $newPw           = $d['new_password'] ?? '';

    if (!$id || !$name || !$email) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id, name, and email required.']); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid email.']); exit;
    }
    if (!$advisoryGrade || !$advisorySubject) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Grade level and advisory section are required.']); exit;
    }

    // Check email uniqueness (exclude self)
    $ck = $pdo->prepare('SELECT id FROM users WHERE email=? AND id!=? LIMIT 1');
    $ck->execute([$email, $id]);
    if ($ck->fetch()) {
        http_response_code(409); echo json_encode(['ok'=>false,'message'=>'Email already used by another account.']); exit;
    }

    // Duplicate advisory check (exclude self)
    $dup = $pdo->prepare("SELECT id, name FROM users WHERE role='teacher' AND advisory_grade=? AND advisory_subject=? AND id!=? LIMIT 1");
    $dup->execute([$advisoryGrade, $advisorySubject, $id]);
    $existingAdvisor = $dup->fetch();
    if ($existingAdvisor) {
        http_response_code(409);
        echo json_encode(['ok'=>false,'message'=>"The section '{$advisorySubject}' in {$advisoryGrade} is already assigned to advisor '" . $existingAdvisor['name'] . "'."]);
        exit;
    }

    $position = $advisoryGrade . ' - Section ' . $advisorySubject;
    $pdo->prepare("UPDATE users SET name=?, email=?, position=?, advisory_grade=?, advisory_subject=? WHERE id=? AND role='teacher'")
        ->execute([$name, $email, $position, $advisoryGrade, $advisorySubject, $id]);

    if ($newPw !== '') {
        if (strlen($newPw) < 6) { echo json_encode(['ok'=>false,'message'=>'Password must be at least 6 characters.']); exit; }
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($newPw, PASSWORD_BCRYPT), $id]);
    }

    $updated = $pdo->prepare('SELECT id,name,username,email,position,advisory_grade,advisory_subject,status FROM users WHERE id=?');
    $updated->execute([$id]);
    echo json_encode(['ok'=>true,'message'=>'Teacher updated.','teacher'=>$updated->fetch()]);
    exit;
}

// DELETE — remove teacher account
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id required.']); exit; }
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='teacher'")->execute([$id]);
    echo json_encode(['ok'=>true,'message'=>'Teacher account deleted.']);
    exit;
}

http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
