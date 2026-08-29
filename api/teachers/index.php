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
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id, u.name, u.username, u.email, u.position, u.advisory_grade, u.advisory_subject, u.status, u.created_at
            FROM users u
            LEFT JOIN teacher_classes tc ON tc.teacher_id = u.id
            WHERE u.role = 'teacher'
              AND (
                u.name LIKE ?
                OR u.username LIKE ?
                OR u.position LIKE ?
                OR tc.grade_level LIKE ?
                OR tc.section LIKE ?
              )
            ORDER BY u.name
        ");
        $sTerm = "%$search%";
        $stmt->execute([$sTerm, $sTerm, $sTerm, $sTerm, $sTerm]);
    } else {
        $stmt = $pdo->query("SELECT id, name, username, email, position, advisory_grade, advisory_subject, status, created_at FROM users WHERE role='teacher' ORDER BY name");
    }
    $teachers = $stmt->fetchAll();
    foreach ($teachers as &$t) {
        $t['advisory_classes'] = getTeacherAdvisoryClasses($pdo, $t['id']);
    }
    unset($t);

    echo json_encode(['ok'=>true, 'teachers'=>$teachers]);
    exit;
}

// PUT — update teacher profile
if ($method === 'PUT') {
    $d                = json_decode(file_get_contents('php://input'), true);
    $id               = (int)($d['id'] ?? 0);
    $name             = trim($d['name'] ?? '');
    $email            = trim($d['email'] ?? '');
    $newPw            = $d['new_password'] ?? '';

    // Handle advisory classes (array of 1 to 3 classes or fallback single)
    $advisoryClasses = [];
    if (isset($d['advisory_classes']) && is_array($d['advisory_classes'])) {
        foreach ($d['advisory_classes'] as $c) {
            $g = trim($c['grade_level'] ?? '');
            $s = trim($c['section'] ?? '');
            if ($g && $s) {
                $advisoryClasses[] = ['grade_level' => $g, 'section' => $s];
            }
        }
    } else {
        $advisoryGrade   = trim($d['advisory_grade'] ?? '');
        $advisorySubject = trim($d['advisory_subject'] ?? '');
        if ($advisoryGrade && $advisorySubject) {
            $advisoryClasses[] = ['grade_level' => $advisoryGrade, 'section' => $advisorySubject];
        }
    }

    if (!$id || !$name || !$email) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id, name, and email required.']); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid email.']); exit;
    }
    if (empty($advisoryClasses)) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'At least one advisory class (grade and section) is required.']); exit;
    }
    if (count($advisoryClasses) > 3) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'A teacher can handle a maximum of 3 advisory classes.']); exit;
    }

    // Check duplicate within submitted classes
    $seen = [];
    foreach ($advisoryClasses as $c) {
        $k = $c['grade_level'] . '|' . $c['section'];
        if (isset($seen[$k])) {
            http_response_code(400); echo json_encode(['ok'=>false,'message'=>"Duplicate class selected: {$c['grade_level']} - {$c['section']}."]); exit;
        }
        $seen[$k] = true;
    }

    // Check email uniqueness (exclude self)
    $ck = $pdo->prepare('SELECT id FROM users WHERE email=? AND id!=? LIMIT 1');
    $ck->execute([$email, $id]);
    if ($ck->fetch()) {
        http_response_code(409); echo json_encode(['ok'=>false,'message'=>'Email already used by another account.']); exit;
    }

    // Duplicate advisory check against OTHER teachers
    $dupStmt = $pdo->prepare("
        SELECT tc.teacher_id, u.name
        FROM teacher_classes tc
        JOIN users u ON u.id = tc.teacher_id
        WHERE tc.grade_level = ? AND tc.section = ? AND tc.teacher_id != ?
        LIMIT 1
    ");

    foreach ($advisoryClasses as $c) {
        $dupStmt->execute([$c['grade_level'], $c['section'], $id]);
        $existingAdvisor = $dupStmt->fetch();
        if ($existingAdvisor) {
            http_response_code(409);
            echo json_encode(['ok'=>false,'message'=>"The section '{$c['section']}' in {$c['grade_level']} is already assigned to advisor '" . $existingAdvisor['name'] . "'."]);
            exit;
        }
    }

    // Format position string and update users table
    $primaryGrade   = $advisoryClasses[0]['grade_level'];
    $primarySection = $advisoryClasses[0]['section'];
    $posParts = [];
    foreach ($advisoryClasses as $c) {
        $posParts[] = $c['grade_level'] . ' - Section ' . $c['section'];
    }
    $position = implode(', ', $posParts);

    $pdo->prepare("UPDATE users SET name=?, email=?, position=?, advisory_grade=?, advisory_subject=? WHERE id=? AND role='teacher'")
        ->execute([$name, $email, $position, $primaryGrade, $primarySection, $id]);

    // Sync teacher_classes table
    syncTeacherAdvisoryClasses($pdo, $id, $advisoryClasses);

    if ($newPw !== '') {
        if (strlen($newPw) < 6) { echo json_encode(['ok'=>false,'message'=>'Password must be at least 6 characters.']); exit; }
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($newPw, PASSWORD_BCRYPT), $id]);
    }

    $updated = $pdo->prepare('SELECT id,name,username,email,position,advisory_grade,advisory_subject,status FROM users WHERE id=?');
    $updated->execute([$id]);
    $tData = $updated->fetch();
    $tData['advisory_classes'] = getTeacherAdvisoryClasses($pdo, $id);

    echo json_encode(['ok'=>true,'message'=>'Teacher updated.','teacher'=>$tData]);
    exit;
}

// DELETE — remove teacher account
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);
    if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'id required.']); exit; }
    $pdo->prepare("DELETE FROM teacher_classes WHERE teacher_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM users WHERE id=? AND role='teacher'")->execute([$id]);
    echo json_encode(['ok'=>true,'message'=>'Teacher account deleted.']);
    exit;
}

http_response_code(405); echo json_encode(['ok'=>false,'message'=>'Method not allowed']);

