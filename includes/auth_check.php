<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Prevent browser from caching authenticated pages — fixes back-button-after-logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

function requireAuth(string $role): array {
    if (empty($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/views/auth/login.php?role=' . $role);
        exit;
    }
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: ' . BASE_URL . '/views/auth/login.php?error=unauthorized&role=' . $role);
        exit;
    }
    return $_SESSION['user'];
}

function getLoggedInUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function redirectByRole(string $role): void {
    $map = [
        'admin'   => BASE_URL . '/views/admin/dashboard.php',
        'teacher' => BASE_URL . '/views/teacher/dashboard.php',
        'student' => BASE_URL . '/views/student/dashboard.php',
    ];
    header('Location: ' . ($map[$role] ?? BASE_URL . '/views/auth/login.php'));
    exit;
}
