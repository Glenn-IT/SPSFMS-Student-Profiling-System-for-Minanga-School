<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$role = $data['role'] ?? '';
if (!in_array($role, ['teacher', 'student'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid role.']);
    exit;
}

$username    = trim($data['username'] ?? '');
$password    = $data['password'] ?? '';
$confirm     = $data['confirm_password'] ?? '';
$email       = trim($data['email'] ?? '');
$secQuestion = trim($data['sec_question'] ?? '');
$secAnswer   = trim($data['sec_answer'] ?? '');

if (!$username || !$password || !$email || !$secQuestion || !$secAnswer) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}
if ($password !== $confirm) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Passwords do not match.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['ok' => false, 'message' => 'Username or email is already registered.']);
    exit;
}

$name = null;
$position = null;
$lrn = null;
$gradeLevel = null;
$section = null;

if ($role === 'teacher') {
    $name     = trim($data['name'] ?? '');
    $position = trim($data['position'] ?? '');
    if (!$name || !$position) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Please enter your full name and position.']);
        exit;
    }
} else {
    $lrn = trim($data['lrn'] ?? '');
    if (!$lrn) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Please enter your LRN.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM students WHERE lrn = ? LIMIT 1');
    $stmt->execute([$lrn]);
    $student = $stmt->fetch();
    if (!$student) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'No student record found with that LRN. Please contact the Admin.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE lrn = ? AND role = "student" LIMIT 1');
    $stmt->execute([$lrn]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'message' => 'An account already exists for this LRN.']);
        exit;
    }

    $name       = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
    $gradeLevel = $student['grade_level'];
    $section    = $student['section'];
}

$stmt = $pdo->prepare('INSERT INTO users (role, username, password, name, email, position, lrn, grade_level, section, status, sec_question, sec_answer) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
$stmt->execute([
    $role,
    $username,
    password_hash($password, PASSWORD_BCRYPT),
    $name,
    $email,
    $position,
    $lrn,
    $gradeLevel,
    $section,
    'active',
    $secQuestion,
    $secAnswer,
]);

echo json_encode(['ok' => true, 'message' => 'Account created successfully. You can now log in.']);
