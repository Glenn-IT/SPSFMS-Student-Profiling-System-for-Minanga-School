<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$sy      = $_GET['school_year']  ?? '2025-2026';
$grade   = $_GET['grade_level']  ?? '';
$section = $_GET['section']      ?? '';

$where  = ['g.school_year = ?'];
$params = [$sy];

if ($grade)   { $where[] = 'g.grade_level = ?'; $params[] = $grade; }
if ($section) { $where[] = 'g.section = ?';     $params[] = $section; }

$sql = "SELECT s.id as student_id, s.last_name, s.first_name, s.middle_name, s.lrn,
               g.grade_level, g.section, g.subject, g.q1, g.q2, g.q3, g.q4, g.final_grade, g.remarks
        FROM students s
        LEFT JOIN grades g ON g.student_id = s.id AND g.school_year = ?
        WHERE s.status = 'active'" .
        ($grade   ? " AND s.grade_level = ?" : '') .
        ($section ? " AND s.section = ?"     : '') .
        " ORDER BY s.last_name, s.first_name, g.subject";

$params2 = [$sy];
if ($grade)   $params2[] = $grade;
if ($section) $params2[] = $section;

$stmt = $pdo->prepare($sql);
$stmt->execute($params2);
$rows = $stmt->fetchAll();

echo json_encode(['ok'=>true,'rows'=>$rows,'count'=>count($rows)]);
