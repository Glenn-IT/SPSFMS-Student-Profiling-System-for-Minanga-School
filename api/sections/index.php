<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — list sections
if ($method === 'GET') {
    $grade = $_GET['grade_level'] ?? '';
    if ($grade) {
        $stmt = $pdo->prepare('SELECT * FROM sections WHERE grade_level = ? ORDER BY id ASC');
        $stmt->execute([$grade]);
    } else {
        $stmt = $pdo->query('SELECT * FROM sections ORDER BY FIELD(grade_level, "Grade 1","Grade 2","Grade 3","Grade 4","Grade 5","Grade 6","Grade 7","Grade 8","Grade 9","Grade 10","Grade 11","Grade 12"), id ASC');
    }
    echo json_encode(['ok' => true, 'sections' => $stmt->fetchAll()]);
    exit;
}

// POST — add section
if ($method === 'POST') {
    $d           = json_decode(file_get_contents('php://input'), true);
    $gradeLevel  = trim($d['grade_level'] ?? '');
    $sectionName = trim($d['section_name'] ?? '');

    if (!$gradeLevel || !$sectionName) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Grade level and section name are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO sections (grade_level, section_name) VALUES (?, ?)');
        $stmt->execute([$gradeLevel, $sectionName]);
        $id = $pdo->lastInsertId();
        $row = $pdo->prepare('SELECT * FROM sections WHERE id = ?');
        $row->execute([$id]);
        echo json_encode(['ok' => true, 'message' => 'Section added successfully.', 'section' => $row->fetch()]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'message' => "Section '{$sectionName}' already exists in {$gradeLevel}."]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Database error.']);
        }
    }
    exit;
}

// PUT — update section
if ($method === 'PUT') {
    $d           = json_decode(file_get_contents('php://input'), true);
    $id          = (int)($d['id'] ?? 0);
    $gradeLevel  = trim($d['grade_level'] ?? '');
    $sectionName = trim($d['section_name'] ?? '');

    if (!$id || !$gradeLevel || !$sectionName) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID, grade level, and section name are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE sections SET grade_level = ?, section_name = ? WHERE id = ?');
        $stmt->execute([$gradeLevel, $sectionName, $id]);
        $row = $pdo->prepare('SELECT * FROM sections WHERE id = ?');
        $row->execute([$id]);
        echo json_encode(['ok' => true, 'message' => 'Section updated successfully.', 'section' => $row->fetch()]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'message' => "Section '{$sectionName}' already exists in {$gradeLevel}."]);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Database error.']);
        }
    }
    exit;
}

// DELETE — delete section
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Section ID required.']);
        exit;
    }

    // Check if section is currently assigned to any students
    $sec = $pdo->prepare('SELECT grade_level, section_name FROM sections WHERE id = ?');
    $sec->execute([$id]);
    $sectionData = $sec->fetch();

    if ($sectionData) {
        $checkStudents = $pdo->prepare('SELECT COUNT(*) FROM students WHERE grade_level = ? AND section = ?');
        $checkStudents->execute([$sectionData['grade_level'], $sectionData['section_name']]);
        $studentCount = (int)$checkStudents->fetchColumn();

        if ($studentCount > 0) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'message' => "Cannot delete section. It is currently assigned to {$studentCount} student(s)."]);
            exit;
        }
    }

    $pdo->prepare('DELETE FROM sections WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true, 'message' => 'Section deleted successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
