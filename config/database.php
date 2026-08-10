<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'spsmis');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Ensure security_questions table exists and is seeded
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `security_questions` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `question` VARCHAR(255) NOT NULL UNIQUE,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $sqCount = $pdo->query("SELECT COUNT(*) FROM `security_questions`")->fetchColumn();
        if ($sqCount == 0) {
            $secQs = [
              "What is the name of your first pet?",
              "What is your mother's maiden name?",
              "What city were you born in?",
              "What is the name of your elementary school?"
            ];
            $insSq = $pdo->prepare("INSERT IGNORE INTO `security_questions` (`question`) VALUES (?)");
            foreach ($secQs as $sq) {
                $insSq->execute([$sq]);
            }
        }
    } catch (Exception $sqErr) {}
} catch (PDOException $e) {

    http_response_code(500);
    // Show friendly error rather than exposing credentials
    die(json_encode(['ok' => false, 'message' => 'Database connection failed. Is MySQL running?']));
}
