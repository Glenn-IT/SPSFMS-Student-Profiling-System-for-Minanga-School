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
    $subjects = getSubjectsForGrade($student['grade_level'], $pdo);

    // Fetch existing grade rows
    $gStmt = $pdo->prepare('SELECT * FROM grades WHERE student_id=? AND school_year=?');
    $gStmt->execute([$studentId, $sy]);
    $rows = $gStmt->fetchAll();

    $gradeMap = [];
    foreach ($rows as $r) {
        $gradeMap[$r['subject']] = $r;
        if (!in_array($r['subject'], $subjects)) {
            $subjects[] = $r['subject'];
        }
    }

    // Build ordered response with all subjects
    $grades = [];
    foreach ($subjects as $subject) {
        $row = $gradeMap[$subject] ?? null;
        $t1 = $row ? ($row['t1'] ?? $row['q1'] ?? null) : null;
        $t2 = $row ? ($row['t2'] ?? $row['q2'] ?? null) : null;
        $t3 = $row ? ($row['t3'] ?? $row['q3'] ?? null) : null;
        $grades[$subject] = $row ? [
            'id'          => $row['id'],
            'student_id'  => $studentId,
            'school_year' => $sy,
            'grade_level' => $row['grade_level'],
            'section'     => $row['section'],
            'subject'     => $subject,
            't1'          => $t1,
            't2'          => $t2,
            't3'          => $t3,
            'q1'          => $t1,
            'q2'          => $t2,
            'q3'          => $t3,
            'q4'          => $row['q4'] ?? null,
            'final_grade' => $row['final_grade'],
            'remarks'     => $row['remarks'] ?? ''
        ] : [
            'id'          => null,
            'student_id'  => $studentId,
            'school_year' => $sy,
            'grade_level' => $student['grade_level'],
            'section'     => $student['section'],
            'subject'     => $subject,
            't1'          => null,
            't2'          => null,
            't3'          => null,
            'q1'          => null,
            'q2'          => null,
            'q3'          => null,
            'q4'          => null,
            'final_grade' => null,
            'remarks'     => ''
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

    // Support both t1/t2/t3 and q1/q2/q3
    $t1Raw = $d['t1'] ?? $d['q1'] ?? null;
    $t2Raw = $d['t2'] ?? $d['q2'] ?? null;
    $t3Raw = $d['t3'] ?? $d['q3'] ?? null;
    $q4Raw = $d['q4'] ?? null;

    $t1 = ($t1Raw !== null && $t1Raw !== '') ? (float)$t1Raw : null;
    $t2 = ($t2Raw !== null && $t2Raw !== '') ? (float)$t2Raw : null;
    $t3 = ($t3Raw !== null && $t3Raw !== '') ? (float)$t3Raw : null;
    $q4 = ($q4Raw !== null && $q4Raw !== '') ? (float)$q4Raw : null;

    if (!$studentId || !$subject) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'student_id and subject required']); exit;
    }

    // Compute final: average of 3 terms (T1 - T3)
    $filled = array_filter([$t1, $t2, $t3], fn($v) => $v !== null);
    $final  = count($filled) === 3 ? round(array_sum($filled) / 3, 2) : null;
    $remarks = $final !== null ? ($final >= 75 ? 'Passed' : 'Failed') : '';

    $pdo->prepare("INSERT INTO grades (student_id,school_year,grade_level,section,subject,t1,t2,t3,q1,q2,q3,q4,final_grade,remarks)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            t1=VALUES(t1), t2=VALUES(t2), t3=VALUES(t3),
            q1=VALUES(q1), q2=VALUES(q2), q3=VALUES(q3), q4=VALUES(q4),
            final_grade=VALUES(final_grade), remarks=VALUES(remarks)")
        ->execute([$studentId,$sy,$gradeLevel,$section,$subject,$t1,$t2,$t3,$t1,$t2,$t3,$q4,$final,$remarks]);

    echo json_encode(['ok'=>true,'final_grade'=>$final,'remarks'=>$remarks,'t1'=>$t1,'t2'=>$t2,'t3'=>$t3]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
