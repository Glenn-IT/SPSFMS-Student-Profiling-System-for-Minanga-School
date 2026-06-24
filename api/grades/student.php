<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher','student'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — fetch grades for a student
if ($method === 'GET') {
    $studentId = (int)($_GET['student_id'] ?? 0);
    $sy        = $_GET['school_year'] ?? '2025-2026';
    if (!$studentId) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'student_id required']); exit; }

    // Get student info
    $sStmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $sStmt->execute([$studentId]);
    $student = $sStmt->fetch();
    if (!$student) { http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Student not found']); exit; }

    // Get subjects for this grade
    $subjects = getSubjectsForGrade($student['grade_level']);

    // Fetch existing grade rows
    $gStmt = $pdo->prepare('SELECT * FROM grades WHERE student_id=? AND school_year=?');
    $gStmt->execute([$studentId, $sy]);
    $rows = $gStmt->fetchAll();

    $gradeMap = [];
    foreach ($rows as $r) {
        $gradeMap[$r['subject']] = $r;
    }

    // Build ordered response with all subjects
    $grades = [];
    foreach ($subjects as $subject) {
        $grades[$subject] = $gradeMap[$subject] ?? [
            'id'=>null,'student_id'=>$studentId,'school_year'=>$sy,
            'grade_level'=>$student['grade_level'],'section'=>$student['section'],
            'subject'=>$subject,'q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null,
            'final_grade'=>null,'remarks'=>''
        ];
    }

    echo json_encode(['ok'=>true,'student'=>$student,'grades'=>$grades,'subjects'=>$subjects]);
    exit;
}

// POST — save/update one grade row
if ($method === 'POST') {
    if (!in_array($_SESSION['user']['role'], ['admin','teacher'])) {
        http_response_code(403); echo json_encode(['ok'=>false,'message'=>'Teacher/Admin only']); exit;
    }
    $d = json_decode(file_get_contents('php://input'), true);

    $studentId  = (int)($d['student_id'] ?? 0);
    $sy         = $d['school_year']  ?? '2025-2026';
    $subject    = $d['subject']      ?? '';
    $gradeLevel = $d['grade_level']  ?? '';
    $section    = $d['section']      ?? '';
    $q1 = isset($d['q1']) && $d['q1'] !== '' ? (float)$d['q1'] : null;
    $q2 = isset($d['q2']) && $d['q2'] !== '' ? (float)$d['q2'] : null;
    $q3 = isset($d['q3']) && $d['q3'] !== '' ? (float)$d['q3'] : null;
    $q4 = isset($d['q4']) && $d['q4'] !== '' ? (float)$d['q4'] : null;

    if (!$studentId || !$subject) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'student_id and subject required']); exit;
    }

    // Compute final
    $filled = array_filter([$q1,$q2,$q3,$q4], fn($v) => $v !== null);
    $final  = count($filled) === 4 ? round(array_sum($filled)/4, 2) : null;
    $remarks = $final !== null ? ($final >= 75 ? 'Passed' : 'Failed') : '';

    $pdo->prepare("INSERT INTO grades (student_id,school_year,grade_level,section,subject,q1,q2,q3,q4,final_grade,remarks)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE q1=VALUES(q1),q2=VALUES(q2),q3=VALUES(q3),q4=VALUES(q4),final_grade=VALUES(final_grade),remarks=VALUES(remarks)")
        ->execute([$studentId,$sy,$gradeLevel,$section,$subject,$q1,$q2,$q3,$q4,$final,$remarks]);

    echo json_encode(['ok'=>true,'final_grade'=>$final,'remarks'=>$remarks]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
