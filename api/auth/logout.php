<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
if (!empty($_SESSION['user'])) {
    logUserActivity($pdo, 'LOGOUT', "User '{$_SESSION['user']['username']}' logged out.", $_SESSION['user']);
}
session_destroy();
header('Location: ' . BASE_URL . '/views/auth/login.php');
exit;
