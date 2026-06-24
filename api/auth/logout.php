<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
session_destroy();
header('Location: ' . BASE_URL . '/views/auth/login.php');
exit;
