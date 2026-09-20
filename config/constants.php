<?php
define('APP_NAME',    'SPSMIS');
define('SCHOOL_NAME', 'Minanga Integrated School');
define('SCHOOL_ADDRESS', 'Minanga, Piat, Cagayan');
define('BASE_URL',    '/SPSFMS-Student-Profiling-System-for-Minanga-School');

function getActiveSchoolYear(?PDO $pdo = null): string {
    $db = $pdo ?? ($GLOBALS['pdo'] ?? null);
    if ($db instanceof PDO) {
        try {
            $stmt = $db->query("SELECT year_label FROM school_years WHERE is_active = 1 LIMIT 1");
            $val = $stmt->fetchColumn();
            if (!empty($val)) return $val;
        } catch (Exception $e) {}
    }
    return '2025-2026';
}

function getSchoolYearsList(?PDO $pdo = null): array {
    $db = $pdo ?? ($GLOBALS['pdo'] ?? null);
    if ($db instanceof PDO) {
        try {
            $stmt = $db->query("SELECT year_label, is_active FROM school_years ORDER BY year_label DESC");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) return $rows;
        } catch (Exception $e) {}
    }
    return [
        ['year_label' => '2026-2027', 'is_active' => 0],
        ['year_label' => '2025-2026', 'is_active' => 1],
        ['year_label' => '2024-2025', 'is_active' => 0]
    ];
}

define('SCHOOL_YEAR', getActiveSchoolYear());


define('GRADE_LEVELS', [
    'Kindergarten',
    'Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
    'Grade 7','Grade 8','Grade 9','Grade 10',
    'Grade 11','Grade 12'
]);

function getDynamicSectionMap(): array {
    $map = [];
    foreach (GRADE_LEVELS as $g) { $map[$g] = []; }

    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        try {
            $stmt = $GLOBALS['pdo']->query("SELECT grade_level, section_name FROM sections ORDER BY id ASC");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                foreach ($rows as $r) {
                    if (isset($map[$r['grade_level']])) {
                        $map[$r['grade_level']][] = $r['section_name'];
                    }
                }
                return $map;
            }
        } catch (Exception $e) {}
    }

    return [
        'Kindergarten' => ['Sampaguita'],
        'Grade 1'  => ['Mabini'],
        'Grade 2'  => ['Mabini'],
        'Grade 3'  => ['Mabini'],
        'Grade 4'  => ['Bonifacio'],
        'Grade 5'  => ['Bonifacio'],
        'Grade 6'  => ['Bonifacio'],
        'Grade 7'  => ['Rizal'],
        'Grade 8'  => ['Luna'],
        'Grade 9'  => ['Luna'],
        'Grade 10' => ['Mabini'],
        'Grade 11' => ['STEM','ABM','HUMSS'],
        'Grade 12' => ['STEM','ABM','HUMSS'],
    ];
}

define('SECTION_MAP', getDynamicSectionMap());

define('SUBJECTS_KINDER', [
    'Literacy and Language',
    'Mathematics',
    'Socio-Emotional Development',
    'Values Education',
    'Physical Health and Motor Development',
    'Understanding the Physical and Natural Environment'
]);

define('SUBJECTS_ELEM', [
    'Filipino','English','Mathematics','Science',
    'Araling Panlipunan','Edukasyon sa Pagpapakatao','MAPEH','Mother Tongue'
]);

define('SUBJECTS_JHS', [
    'Filipino','English','Mathematics','Science',
    'Araling Panlipunan','Edukasyon sa Pagpapakatao',
    'Technology and Livelihood Education','MAPEH'
]);

define('SUBJECTS_SHS', [
    'Oral Communication','Reading and Writing','Komunikasyon at Pananaliksik',
    '21st Century Literature','Contemporary Philippine Arts',
    'Media and Information Literacy','General Mathematics',
    'Statistics and Probability','Earth and Life Science',
    'Physical Science','Introduction to Philosophy','Physical Education and Health'
]);

function getSubjectsForGrade(string $gradeLevel, ?PDO $pdo = null): array {
    if (stripos($gradeLevel, 'kinder') !== false) {
        $group = 'kindergarten';
    } else {
        $g = (int) str_replace('Grade ', '', $gradeLevel);
        $group = 'shs';
        if ($g <= 6) {
            $group = 'elementary';
        } elseif ($g <= 10) {
            $group = 'jhs';
        }
    }

    $db = $pdo ?? ($GLOBALS['pdo'] ?? null);
    if ($db instanceof PDO) {
        try {
            $stmt = $db->prepare("SELECT name FROM subjects WHERE grade_type = ? ORDER BY id ASC");
            $stmt->execute([$group]);
            $dbSubjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($dbSubjects)) {
                return $dbSubjects;
            }
        } catch (Exception $e) {}
    }

    if ($group === 'kindergarten') return SUBJECTS_KINDER;
    if ($group === 'elementary')   return SUBJECTS_ELEM;
    if ($group === 'jhs')          return SUBJECTS_JHS;
    return SUBJECTS_SHS;
}
