<?php
define('APP_NAME',    'SPSMIS');
define('SCHOOL_NAME', 'Minanga Integrated School');
define('SCHOOL_YEAR', '2025-2026');
define('BASE_URL',    '/SPSFMS-Student-Profiling-System-for-Minanga-School');

define('GRADE_LEVELS', [
    'Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
    'Grade 7','Grade 8','Grade 9','Grade 10',
    'Grade 11','Grade 12'
]);

define('SECTION_MAP', [
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

function getSubjectsForGrade(string $gradeLevel): array {
    $g = (int) str_replace('Grade ', '', $gradeLevel);
    if ($g <= 6)  return SUBJECTS_ELEM;
    if ($g <= 10) return SUBJECTS_JHS;
    return SUBJECTS_SHS;
}
