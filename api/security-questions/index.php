<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, question, created_at FROM security_questions ORDER BY id ASC");
        $questions = $stmt->fetchAll();
        echo json_encode(['ok' => true, 'data' => $questions]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to fetch security questions.']);
    }
    exit;
}

if ($method === 'POST') {
    $user = requireAuth('admin'); // Only admins can add/edit/delete security questions table
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'add';
    
    if ($action === 'add') {
        $question = trim($input['question'] ?? '');
        if (!$question) {
            echo json_encode(['ok' => false, 'message' => 'Security question text cannot be empty.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO security_questions (question) VALUES (?)");
            $stmt->execute([$question]);
            echo json_encode(['ok' => true, 'message' => 'Security question added successfully.', 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['ok' => false, 'message' => 'This security question already exists in the table.']);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Failed to add security question.']);
            }
        }
        exit;
    }

    if ($action === 'edit' || $action === 'update') {
        $id = (int)($input['id'] ?? 0);
        $question = trim($input['question'] ?? '');
        if (!$id || !$question) {
            echo json_encode(['ok' => false, 'message' => 'Invalid ID or question text.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE security_questions SET question = ? WHERE id = ?");
            $stmt->execute([$question, $id]);
            echo json_encode(['ok' => true, 'message' => 'Security question updated successfully.']);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['ok' => false, 'message' => 'Another security question already has this exact text.']);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Failed to update security question.']);
            }
        }
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'message' => 'Invalid security question ID.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM security_questions WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['ok' => true, 'message' => 'Security question deleted successfully.']);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to delete security question.']);
        }
        exit;
    }

    echo json_encode(['ok' => false, 'message' => 'Invalid action.']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
