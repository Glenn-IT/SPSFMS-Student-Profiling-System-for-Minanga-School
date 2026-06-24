<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['ok'=>false,'message'=>'Unauthorized']);
    exit;
}

// Enrollment by level
$stmt = $pdo->query("SELECT grade_level, COUNT(*) as cnt FROM students WHERE status='active' GROUP BY grade_level ORDER BY grade_level");
$byGrade = $stmt->fetchAll();

$elem = $jhs = $shs = 0;
$gradeCounts = [];
foreach ($byGrade as $row) {
    $g = (int) str_replace('Grade ', '', $row['grade_level']);
    $gradeCounts[$row['grade_level']] = (int)$row['cnt'];
    if ($g <= 6)       $elem += $row['cnt'];
    elseif ($g <= 10)  $jhs  += $row['cnt'];
    else               $shs  += $row['cnt'];
}
$total = $elem + $jhs + $shs;

// Gender
$genderStmt = $pdo->query("SELECT sex, COUNT(*) as cnt FROM students WHERE status='active' GROUP BY sex");
$genderRows = $genderStmt->fetchAll();
$male = $female = 0;
foreach ($genderRows as $r) {
    if ($r['sex'] === 'Male') $male = (int)$r['cnt'];
    else $female = (int)$r['cnt'];
}

// Gender by level
$gblStmt = $pdo->query("SELECT grade_level, sex, COUNT(*) as cnt FROM students WHERE status='active' GROUP BY grade_level, sex ORDER BY grade_level");
$genderByLevel = [];
foreach ($gblStmt->fetchAll() as $r) {
    $genderByLevel[$r['grade_level']][$r['sex']] = (int)$r['cnt'];
}

// Top sections
$secStmt = $pdo->query("SELECT section, COUNT(*) as cnt FROM students WHERE status='active' GROUP BY section ORDER BY cnt DESC LIMIT 5");
$topSections = $secStmt->fetchAll();

// Mock 3-year trend (current year from DB, prior years estimated)
$trend = [
    '2023-2024' => max(0, $total - 8),
    '2024-2025' => max(0, $total - 3),
    '2025-2026' => $total,
];

echo json_encode([
    'ok'             => true,
    'total'          => $total,
    'elem'           => $elem,
    'jhs'            => $jhs,
    'shs'            => $shs,
    'male'           => $male,
    'female'         => $female,
    'grade_counts'   => $gradeCounts,
    'gender_by_level'=> $genderByLevel,
    'top_sections'   => $topSections,
    'trend'          => $trend,
]);
