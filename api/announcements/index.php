<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user']['role'] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];

// GET — list announcements
if ($method === 'GET') {
    if ($userRole === 'admin') {
        $audience = $_GET['audience'] ?? '';
        if ($audience && in_array($audience, ['all', 'student', 'teacher'])) {
            $stmt = $pdo->prepare('SELECT * FROM announcements WHERE audience = ? ORDER BY posted_at DESC');
            $stmt->execute([$audience]);
        } else {
            $stmt = $pdo->query('SELECT * FROM announcements ORDER BY posted_at DESC');
        }
    } elseif ($userRole === 'teacher') {
        $stmt = $pdo->query("SELECT * FROM announcements WHERE audience IN ('all', 'teacher') ORDER BY posted_at DESC");
    } elseif ($userRole === 'student') {
        $stmt = $pdo->query("SELECT * FROM announcements WHERE audience IN ('all', 'student') ORDER BY posted_at DESC");
    } else {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Forbidden']);
        exit;
    }
    echo json_encode(['ok' => true, 'announcements' => $stmt->fetchAll()]);
    exit;
}

// All write operations (POST, PUT, DELETE) require admin role
if ($userRole !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Forbidden. Admin access required.']);
    exit;
}

// POST — create announcement
if ($method === 'POST') {
    $d        = json_decode(file_get_contents('php://input'), true);
    $title    = trim($d['title'] ?? '');
    $body     = trim($d['body'] ?? '');
    $audience = trim($d['audience'] ?? 'all');

    if (!$title || !$body) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Title and body are required.']);
        exit;
    }

    if (!in_array($audience, ['all', 'student', 'teacher'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Invalid audience value.']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO announcements (title, body, audience, posted_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$title, $body, $audience]);
    $id = $pdo->lastInsertId();

    $row = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $row->execute([$id]);
    echo json_encode(['ok' => true, 'message' => 'Announcement posted successfully.', 'announcement' => $row->fetch()]);
    exit;
}

// PUT — update announcement
if ($method === 'PUT') {
    $d        = json_decode(file_get_contents('php://input'), true);
    $id       = (int)($d['id'] ?? 0);
    $title    = trim($d['title'] ?? '');
    $body     = trim($d['body'] ?? '');
    $audience = trim($d['audience'] ?? 'all');

    if (!$id || !$title || !$body) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID, title, and body are required.']);
        exit;
    }

    if (!in_array($audience, ['all', 'student', 'teacher'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Invalid audience value.']);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE announcements SET title = ?, body = ?, audience = ? WHERE id = ?');
    $stmt->execute([$title, $body, $audience, $id]);

    $row = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $row->execute([$id]);
    echo json_encode(['ok' => true, 'message' => 'Announcement updated successfully.', 'announcement' => $row->fetch()]);
    exit;
}

// DELETE — delete announcement
if ($method === 'DELETE') {
    $d  = json_decode(file_get_contents('php://input'), true);
    $id = (int)($d['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Announcement ID required.']);
        exit;
    }

    $pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
    echo json_encode(['ok' => true, 'message' => 'Announcement deleted successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
