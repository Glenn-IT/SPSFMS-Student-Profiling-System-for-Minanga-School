<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$lrnCheck = isset($_GET['check_lrn']) ? trim($_GET['check_lrn']) : '';

if ($lrnCheck !== '') {
    // Single LRN lookup & account existence verification
    $stmt = $pdo->prepare("SELECT id, lrn, first_name, middle_name, last_name, grade_level, section, email, school_year FROM students WHERE lrn = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$lrnCheck]);
    $student = $stmt->fetch();

    if (!$student) {
        echo json_encode([
            'ok' => true,
            'exists' => false,
            'message' => 'No active student found with this LRN.'
        ]);
        exit;
    }

    $uStmt = $pdo->prepare("SELECT id, username, name, email FROM users WHERE lrn = ? AND role = 'student' LIMIT 1");
    $uStmt->execute([$lrnCheck]);
    $userAcc = $uStmt->fetch();

    echo json_encode([
        'ok' => true,
        'exists' => true,
        'has_account' => !empty($userAcc),
        'existing_user' => $userAcc ?: null,
        'student' => $student
    ]);
    exit;
}

// Return all active students who DO NOT have an account yet
try {
    $sql = "
        SELECT s.id, s.lrn, s.first_name, s.middle_name, s.last_name, s.grade_level, s.section, s.email, s.school_year
        FROM students s
        LEFT JOIN users u ON u.lrn = s.lrn AND u.role = 'student'
        WHERE u.id IS NULL AND s.status = 'active'
        ORDER BY s.grade_level, s.last_name, s.first_name
    ";
    $students = $pdo->query($sql)->fetchAll();

    echo json_encode([
        'ok' => true,
        'students' => $students,
        'count' => count($students)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to load eligible students: ' . $e->getMessage()]);
}
