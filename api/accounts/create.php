<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

// Admin only
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$d = json_decode(file_get_contents('php://input'), true);

$role            = $d['role']            ?? '';
$username        = trim($d['username']   ?? '');
$password        = $d['password']        ?? '';
$name            = trim($d['name']       ?? '');
$email           = trim($d['email']      ?? '');
$advisoryGrade   = trim($d['advisory_grade']   ?? '');
$advisorySubject = trim($d['advisory_subject']  ?? '');
$lrn             = trim($d['lrn']        ?? '');

// ── Validate role ─────────────────────────────────────────────────────────────
if (!in_array($role, ['teacher', 'student'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Role must be teacher or student.']);
    exit;
}

// ── Required common fields ────────────────────────────────────────────────────
if (!$username || !$password || !$name || !$email) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Name, username, email, and password are required.']);
    exit;
}
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── Role-specific validation ──────────────────────────────────────────────────
$gradeLevel = null;
$section    = null;
$position   = null;

if ($role === 'teacher') {
    if (!$advisoryGrade || !$advisorySubject) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Grade level and advisory section are required for teachers.']);
        exit;
    }

    // Build readable position string
    $position = $advisoryGrade . ' - Section ' . $advisorySubject;

    // ── Duplicate advisory check ──────────────────────────────────────────────
    $dup = $pdo->prepare("SELECT id, name FROM users WHERE role='teacher' AND advisory_grade=? AND advisory_subject=? LIMIT 1");
    $dup->execute([$advisoryGrade, $advisorySubject]);
    $existingAdvisor = $dup->fetch();
    if ($existingAdvisor) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'message' => "The section '{$advisorySubject}' in {$advisoryGrade} is already assigned to advisor '" . $existingAdvisor['name'] . "'."]);
        exit;
    }

} else {
    // student — must link to an existing student record via LRN
    if (!$lrn) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'LRN is required for student accounts.']);
        exit;
    }
    $ck = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
    $ck->execute([$lrn]);
    $student = $ck->fetch();
    if (!$student) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'No student record found with that LRN.']);
        exit;
    }
    $ck2 = $pdo->prepare('SELECT id FROM users WHERE lrn = ? AND role = "student" LIMIT 1');
    $ck2->execute([$lrn]);
    if ($ck2->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'message' => 'An account already exists for this LRN.']);
        exit;
    }
    $name       = $student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name'];
    $gradeLevel = $student['grade_level'];
    $section    = $student['section'];
}

// ── Check duplicate username / email ─────────────────────────────────────────
$ck = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$ck->execute([$username, $email]);
if ($ck->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'message' => 'Username or email is already taken.']);
    exit;
}

// ── Insert ────────────────────────────────────────────────────────────────────
$stmt = $pdo->prepare('INSERT INTO users
    (role, username, password, name, email, position, advisory_grade, advisory_subject, lrn, grade_level, section, status)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,\'active\')');
$stmt->execute([
    $role,
    $username,
    password_hash($password, PASSWORD_BCRYPT),
    $name,
    $email,
    $position,
    $advisoryGrade  ?: null,
    $advisorySubject ?: null,
    $lrn            ?: null,
    $gradeLevel,
    $section,
]);

$newId   = $pdo->lastInsertId();
$newUser = $pdo->prepare('SELECT id, role, username, name, email, position, advisory_grade, advisory_subject, status, created_at FROM users WHERE id = ?');
$newUser->execute([$newId]);

echo json_encode(['ok' => true, 'message' => 'Account created successfully.', 'user' => $newUser->fetch()]);
