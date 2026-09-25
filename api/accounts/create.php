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

// ── Required initial fields ───────────────────────────────────────────────────
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Username and password are required.']);
    exit;
}
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

// ── Role-specific validation ──────────────────────────────────────────────────
$gradeLevel = null;
$section    = null;
$position   = null;
$advisoryClasses = [];

if ($role === 'teacher') {
    if (isset($d['advisory_classes']) && is_array($d['advisory_classes'])) {
        foreach ($d['advisory_classes'] as $c) {
            $g = trim($c['grade_level'] ?? '');
            $s = trim($c['section'] ?? '');
            if ($g && $s) {
                $advisoryClasses[] = ['grade_level' => $g, 'section' => $s];
            }
        }
    } else if ($advisoryGrade && $advisorySubject) {
        $advisoryClasses[] = ['grade_level' => $advisoryGrade, 'section' => $advisorySubject];
    }

    if (empty($advisoryClasses) && !empty($d['position'])) {
        $position = trim($d['position']);
    } else if (empty($advisoryClasses)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'At least one advisory class (grade level and section) is required for teachers.']);
        exit;
    } else {
        if (count($advisoryClasses) > 3) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'A teacher can handle a maximum of 3 advisory classes.']);
            exit;
        }

        // Duplicate check within submitted classes
        $seen = [];
        foreach ($advisoryClasses as $c) {
            $k = $c['grade_level'] . '|' . $c['section'];
            if (isset($seen[$k])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'message' => "Duplicate class selected: {$c['grade_level']} - {$c['section']}."]);
                exit;
            }
            $seen[$k] = true;
        }

        // Check duplicate advisory against other teachers in teacher_classes
        $dupStmt = $pdo->prepare("
            SELECT tc.teacher_id, u.name
            FROM teacher_classes tc
            JOIN users u ON u.id = tc.teacher_id
            WHERE tc.grade_level = ? AND tc.section = ?
            LIMIT 1
        ");

        foreach ($advisoryClasses as $c) {
            $dupStmt->execute([$c['grade_level'], $c['section']]);
            $existingAdvisor = $dupStmt->fetch();
            if ($existingAdvisor) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'message' => "The section '{$c['section']}' in {$c['grade_level']} is already assigned to advisor '" . $existingAdvisor['name'] . "'."]);
                exit;
            }
        }

        $posParts = [];
        foreach ($advisoryClasses as $c) {
            $posParts[] = $c['grade_level'] . ' - Section ' . $c['section'];
        }
        $position = implode(', ', $posParts);
        $advisoryGrade   = $advisoryClasses[0]['grade_level'];
        $advisorySubject = $advisoryClasses[0]['section'];
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
    $name       = trim($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']);
    $gradeLevel = $student['grade_level'];
    $section    = $student['section'];
    if (empty($email) && !empty($student['email'])) {
        $email = trim($student['email']);
    }
}

// ── Validate resolved name & email ───────────────────────────────────────────
if (!$name) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Full name is required.']);
    exit;
}
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
    exit;
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
    $advisoryGrade   ?: null,
    $advisorySubject ?: null,
    $lrn             ?: null,
    $gradeLevel,
    $section,
]);

$newId = (int)$pdo->lastInsertId();

if ($role === 'teacher' && !empty($advisoryClasses)) {
    syncTeacherAdvisoryClasses($pdo, $newId, $advisoryClasses);
}

$newUser = $pdo->prepare('SELECT id, role, username, name, email, position, advisory_grade, advisory_subject, status, created_at FROM users WHERE id = ?');
$newUser->execute([$newId]);
$userData = $newUser->fetch();
if ($role === 'teacher') {
    $userData['advisory_classes'] = getTeacherAdvisoryClasses($pdo, $newId);
}

echo json_encode(['ok' => true, 'message' => 'Account created successfully.', 'user' => $userData]);

