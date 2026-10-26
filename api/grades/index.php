<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sy      = $_GET['school_year']  ?? SCHOOL_YEAR;
    $grade   = $_GET['grade_level']  ?? '';
    $section = $_GET['section']      ?? '';
    $subject = $_GET['subject']      ?? '';

    // If teacher, enforce advisory class restriction when specific grade & section are requested
    if ($_SESSION['user']['role'] === 'teacher' && $grade && $section) {
        $teacherClasses = getTeacherAdvisoryClasses($pdo, $_SESSION['user']['id']);
        $hasAccess = false;
        foreach ($teacherClasses as $tc) {
            if ($tc['grade_level'] === $grade && $tc['section'] === $section) {
                $hasAccess = true;
                break;
            }
        }
        if (!$hasAccess && !empty($teacherClasses)) {
            http_response_code(403);
            echo json_encode(['ok'=>false,'message'=>'Access denied: You can only view classes assigned to your advisory.']);
            exit;
        }
    }

    // Class roster grading for a specific subject
    if ($subject && $grade && $section) {
        $sql = "SELECT s.id as student_id, s.last_name, s.first_name, s.middle_name, s.lrn, s.sex,
                       s.grade_level, s.section,
                       g.id as grade_id, g.subject, g.t1, g.t2, g.t3, g.q1, g.q2, g.q3, g.q4, g.final_grade, g.remarks
                FROM students s
                LEFT JOIN grades g ON g.student_id = s.id AND g.school_year = ? AND g.subject = ?
                WHERE s.status = 'active' AND s.grade_level = ? AND s.section = ?
                ORDER BY s.last_name ASC, s.first_name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sy, $subject, $grade, $section]);
        $rows = $stmt->fetchAll();

        // Normalize term values (support t1..t3 fallback to q1..q3)
        foreach ($rows as &$r) {
            $r['t1'] = $r['t1'] ?? $r['q1'] ?? null;
            $r['t2'] = $r['t2'] ?? $r['q2'] ?? null;
            $r['t3'] = $r['t3'] ?? $r['q3'] ?? null;
        }
        unset($r);

        $subjects = getSubjectsForGrade($grade, $pdo);
        echo json_encode([
            'ok' => true,
            'students' => $rows,
            'count' => count($rows),
            'subject' => $subject,
            'subjects' => $subjects,
            'grade_level' => $grade,
            'section' => $section,
            'school_year' => $sy
        ]);
        exit;
    }

    // Default listing query
    $where  = ['g.school_year = ?'];
    $params = [$sy];

    if ($grade)   { $where[] = 'g.grade_level = ?'; $params[] = $grade; }
    if ($section) { $where[] = 'g.section = ?';     $params[] = $section; }

    $sql = "SELECT s.id as student_id, s.last_name, s.first_name, s.middle_name, s.lrn,
                   g.grade_level, g.section, g.subject, g.t1, g.t2, g.t3, g.q1, g.q2, g.q3, g.q4, g.final_grade, g.remarks
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
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['ok'=>false, 'message'=>'Invalid JSON payload']);
        exit;
    }

    $sy         = $data['school_year']  ?? SCHOOL_YEAR;
    $gradeLevel = $data['grade_level']  ?? '';
    $section    = $data['section']      ?? '';
    $subject    = $data['subject']      ?? '';
    $grades     = $data['grades']       ?? [];

    if (!$gradeLevel || !$section || !$subject || !is_array($grades)) {
        http_response_code(400);
        echo json_encode(['ok'=>false, 'message'=>'Grade level, section, subject, and grades array are required.']);
        exit;
    }

    // Verify teacher authorization
    if ($_SESSION['user']['role'] === 'teacher') {
        $teacherClasses = getTeacherAdvisoryClasses($pdo, $_SESSION['user']['id']);
        $isMyClass = false;
        foreach ($teacherClasses as $tc) {
            if ($tc['grade_level'] === $gradeLevel && $tc['section'] === $section) {
                $isMyClass = true;
                break;
            }
        }
        if (!$isMyClass && !empty($teacherClasses)) {
            http_response_code(403);
            echo json_encode(['ok'=>false, 'message'=>'Access denied: You can only edit grades for your assigned advisory class.']);
            exit;
        }
    }

    $pdo->beginTransaction();
    try {
        $upsertStmt = $pdo->prepare("
            INSERT INTO grades (student_id, school_year, grade_level, section, subject, t1, t2, t3, q1, q2, q3, q4, final_grade, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                t1 = VALUES(t1), t2 = VALUES(t2), t3 = VALUES(t3),
                q1 = VALUES(q1), q2 = VALUES(q2), q3 = VALUES(q3), q4 = VALUES(q4),
                final_grade = VALUES(final_grade), remarks = VALUES(remarks)
        ");

        $isKinder = isKindergartenGrade($gradeLevel);
        $savedCount = 0;
        foreach ($grades as $item) {
            $studentId = (int)($item['student_id'] ?? 0);
            if (!$studentId) continue;

            $t1Raw = trim((string)($item['t1'] ?? ''));
            $t2Raw = trim((string)($item['t2'] ?? ''));
            $t3Raw = trim((string)($item['t3'] ?? ''));

            if ($isKinder) {
                $t1 = isKinderLetter($t1Raw) ? strtoupper($t1Raw) : null;
                $t2 = isKinderLetter($t2Raw) ? strtoupper($t2Raw) : null;
                $t3 = isKinderLetter($t3Raw) ? strtoupper($t3Raw) : null;

                $s1 = $t1 ? getKinderLetterScore($t1) : null;
                $s2 = $t2 ? getKinderLetterScore($t2) : null;
                $s3 = $t3 ? getKinderLetterScore($t3) : null;

                $validScores = array_filter([$s1, $s2, $s3], fn($v) => $v !== null);
                if (count($validScores) === 3) {
                    $avgScore = array_sum($validScores) / 3;
                    $final = scoreToKinderLetter($avgScore);
                    $remarks = in_array($final, ['A','B','C'], true) ? 'Passed' : 'Failed';
                } else {
                    $final = null;
                    $remarks = '';
                }
            } else {
                $t1 = ($t1Raw !== '') ? (float)$t1Raw : null;
                $t2 = ($t2Raw !== '') ? (float)$t2Raw : null;
                $t3 = ($t3Raw !== '') ? (float)$t3Raw : null;

                $filled = array_filter([$t1, $t2, $t3], fn($v) => $v !== null);
                $final = count($filled) === 3 ? round(array_sum($filled) / 3, 2) : null;
                $remarks = $final !== null ? ($final >= 75 ? 'Passed' : 'Failed') : '';
            }

            $upsertStmt->execute([
                $studentId, $sy, $gradeLevel, $section, $subject,
                $t1, $t2, $t3, $t1, $t2, $t3, null, $final, $remarks
            ]);
            $savedCount++;
        }

        $pdo->commit();

        logUserActivity($pdo, 'BATCH_SAVE_GRADES', "Batch saved grades for {$gradeLevel} - {$section} in {$subject} ({$savedCount} students, S.Y. {$sy}).");

        echo json_encode([
            'ok' => true,
            'message' => "Successfully saved grades for {$savedCount} students in {$subject}!",
            'saved_count' => $savedCount
        ]);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['ok'=>false, 'message'=>'Database error saving grades: ' . $e->getMessage()]);
        exit;
    }
}

http_response_code(405);
echo json_encode(['ok'=>false, 'message'=>'Method not allowed']);
