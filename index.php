<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

if (!empty($_SESSION['user'])) {
    $map = ['admin'=>BASE_URL.'/views/admin/dashboard.php','teacher'=>BASE_URL.'/views/teacher/dashboard.php','student'=>BASE_URL.'/views/student/dashboard.php'];
    $dest = $map[$_SESSION['user']['role']] ?? BASE_URL.'/views/auth/login.php';
} else {
    $dest = BASE_URL.'/views/auth/login.php';
}
header('Location: '.$dest);
exit;
