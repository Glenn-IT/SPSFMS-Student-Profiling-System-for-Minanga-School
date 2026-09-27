<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','teacher','student'])) {
    http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Unauthorized']); exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Student ID required']); exit; }

$role = $_SESSION['user']['role'];

// If logged in as student, enforce strict self-access guard
if ($role === 'student') {
    $myLrn = $_SESSION['user']['lrn'] ?? '';
    $chkStmt = $pdo->prepare('SELECT id FROM students WHERE lrn = ? LIMIT 1');
    $chkStmt->execute([$myLrn]);
    $myStudentId = (int)$chkStmt->fetchColumn();
    if (!$myStudentId || $myStudentId !== $id) {
        http_response_code(403); echo json_encode(['ok'=>false,'message'=>'Forbidden: You can only access your own profile']); exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — fetch one student
if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    if (!$student) { http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Student not found']); exit; }
    echo json_encode(['ok'=>true,'student'=>$student]);
    exit;
}

// POST — update student (using POST since HTML forms don't support PUT)
if ($method === 'POST') {
    if ($role !== 'admin' && $role !== 'student') {
        http_response_code(403); echo json_encode(['ok'=>false,'message'=>'Admin or self student only']); exit;
    }

    // Fetch existing student record
    $currStmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $currStmt->execute([$id]);
    $currStudent = $currStmt->fetch();
    if (!$currStudent) {
        http_response_code(404); echo json_encode(['ok'=>false,'message'=>'Student not found']); exit;
    }

    $d = json_decode(file_get_contents('php://input'), true);
    if (!$d) {
        http_response_code(400); echo json_encode(['ok'=>false,'message'=>'Invalid JSON payload']); exit;
    }

    $namePattern = "/^[A-Za-zÑñ' .\\-]+$/u";
    foreach (['first_name','last_name'] as $f) {
        if (!preg_match($namePattern, $d[$f] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok'=>false,'message'=>'Names must not contain numbers or special characters.']);
            exit;
        }
    }
    foreach (['middle_name','mother_name','father_name','guardian_name'] as $f) {
        if (!empty($d[$f]) && !preg_match($namePattern, $d[$f])) {
            http_response_code(400);
            echo json_encode(['ok'=>false,'message'=>'Names must not contain numbers or special characters.']);
            exit;
        }
    }
    if (!empty($d['contact']) && !preg_match('/^09\d{9}$/', $d['contact'])) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'message'=>'Contact No. must be an 11-digit PH mobile number starting with 09.']);
        exit;
    }
    if (($d['birthdate'] ?? '') > date('Y-m-d')) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'message'=>'Birthdate cannot be a future date.']);
        exit;
    }

    // Check if new LRN is taken by another student
    if ($role === 'admin' && !empty($d['lrn']) && $d['lrn'] !== $currStudent['lrn']) {
        $checkLrn = $pdo->prepare('SELECT id FROM students WHERE lrn = ? AND id != ?');
        $checkLrn->execute([$d['lrn'], $id]);
        if ($checkLrn->fetch()) {
            http_response_code(409);
            echo json_encode(['ok'=>false,'message'=>'LRN already exists for another student.']);
            exit;
        }
    }

    // Determine values. If student is editing, protect academic classification (LRN, grade level, section, school year)
    $lrn        = ($role === 'admin') ? ($d['lrn'] ?? $currStudent['lrn']) : $currStudent['lrn'];
    $gradeLevel = ($role === 'admin') ? ($d['grade_level'] ?? $currStudent['grade_level']) : $currStudent['grade_level'];
    $section    = ($role === 'admin') ? ($d['section'] ?? $currStudent['section']) : $currStudent['section'];
    $schoolYear = ($role === 'admin' && !empty($d['school_year'])) ? trim($d['school_year']) : $currStudent['school_year'];
    $firstName  = trim($d['first_name'] ?? '');
    $middleName = trim($d['middle_name'] ?? '');
    $lastName   = trim($d['last_name'] ?? '');
    $sex        = in_array($d['sex'] ?? '', ['Male','Female']) ? $d['sex'] : $currStudent['sex'];
    $birthdate  = $d['birthdate'] ?? $currStudent['birthdate'];
    $age        = isset($d['age']) ? (int)$d['age'] : (int)$currStudent['age'];
    $tongue     = $d['mother_tongue'] ?? null;
    $religion   = $d['religion'] ?? null;
    $address    = $d['address'] ?? null;
    $mother     = $d['mother_name'] ?? null;
    $father     = $d['father_name'] ?? null;
    $guardian   = $d['guardian_name'] ?? null;
    $relation   = $d['guardian_relation'] ?? null;
    $contact    = $d['contact'] ?? null;
    $email      = $d['email'] ?? null;

    // Recalculate age accurately if birthdate provided
    if ($birthdate) {
        $bDate = new DateTime($birthdate);
        $now = new DateTime();
        $age = (int)$now->diff($bDate)->y;
    }

    $stmt = $pdo->prepare("UPDATE students SET
        lrn=?, grade_level=?, section=?, first_name=?, middle_name=?, last_name=?,
        sex=?, birthdate=?, age=?, mother_tongue=?, religion=?, address=?,
        mother_name=?, father_name=?, guardian_name=?, guardian_relation=?,
        contact=?, email=?, school_year=?
        WHERE id=?");
    $stmt->execute([
        $lrn, $gradeLevel, $section,
        $firstName, $middleName ?: null, $lastName,
        $sex, $birthdate, $age,
        $tongue ?: null, $religion ?: null, $address ?: null,
        $mother ?: null, $father ?: null,
        $guardian ?: null, $relation ?: null,
        $contact ?: null, $email ?: null,
        $schoolYear,
        $id
    ]);

    // Synchronize users table if a matching student account exists
    $fullName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
    try {
        if ($role === 'student') {
            $uSync = $pdo->prepare("UPDATE users SET name = ?, email = COALESCE(?, email) WHERE id = ? AND role = 'student'");
            $uSync->execute([$fullName, $email ?: null, $_SESSION['user']['id']]);
            $_SESSION['user']['name'] = $fullName;
            if ($email) {
                $_SESSION['user']['email'] = $email;
            }
        } elseif ($role === 'admin') {
            // Update student user account if one exists matching this student's LRN
            $targetLrn = !empty($currStudent['lrn']) ? $currStudent['lrn'] : $lrn;
            $uSync = $pdo->prepare("UPDATE users SET name = ?, email = COALESCE(?, email), lrn = ?, grade_level = ?, section = ? WHERE lrn = ? AND role = 'student'");
            $uSync->execute([$fullName, $email ?: null, $lrn, $gradeLevel, $section, $targetLrn]);
        }
    } catch (PDOException $syncErr) {
        // Safe fallback in case of user email conflict
        error_log("Student user account sync error: " . $syncErr->getMessage());
    }

    $upd = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $upd->execute([$id]);
    echo json_encode(['ok'=>true,'message'=>'Profile updated successfully!','student'=>$upd->fetch()]);
    exit;
}

http_response_code(405);
echo json_encode(['ok'=>false,'message'=>'Method not allowed']);
