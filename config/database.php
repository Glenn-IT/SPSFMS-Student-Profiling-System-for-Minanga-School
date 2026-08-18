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

    // Ensure report_signatories table exists and is seeded
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `report_signatories` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `prepared_by_type` ENUM('teacher','custom') NOT NULL DEFAULT 'teacher',
              `prepared_by_user_id` INT UNSIGNED DEFAULT NULL,
              `prepared_by_name` VARCHAR(150) DEFAULT NULL,
              `prepared_by_title` VARCHAR(150) DEFAULT NULL,
              `noted_by_type` ENUM('teacher','custom') NOT NULL DEFAULT 'custom',
              `noted_by_user_id` INT UNSIGNED DEFAULT NULL,
              `noted_by_name` VARCHAR(150) DEFAULT NULL,
              `noted_by_title` VARCHAR(150) DEFAULT 'School Head / Principal',
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $rsCount = $pdo->query("SELECT COUNT(*) FROM `report_signatories`")->fetchColumn();
        if ($rsCount == 0) {
            $pdo->exec("INSERT INTO `report_signatories` (`id`, `prepared_by_type`, `noted_by_type`, `noted_by_title`) VALUES (1, 'teacher', 'custom', 'School Head / Principal')");
        }
    } catch (Exception $rsErr) {}
} catch (PDOException $e) {

    http_response_code(500);
    // Show friendly error rather than exposing credentials
    die(json_encode(['ok' => false, 'message' => 'Database connection failed. Is MySQL running?']));
}

if (!function_exists('getSignatories')) {
    function getSignatories($pdo, $currentUser = null) {
        $sig = [
            'prepared_by_name'  => $currentUser['name'] ?? 'Administrator',
            'prepared_by_title' => $currentUser['position'] ?? 'School Administrator',
            'noted_by_name'     => '',
            'noted_by_title'    => 'School Head / Principal',
            'raw'               => null
        ];
        try {
            $stmt = $pdo->query("SELECT * FROM report_signatories WHERE id = 1");
            $row = $stmt->fetch();
            if ($row) {
                $sig['raw'] = $row;
                // Prepared by
                if ($row['prepared_by_type'] === 'teacher' && !empty($row['prepared_by_user_id'])) {
                    $uStmt = $pdo->prepare("SELECT name, position FROM users WHERE id = ?");
                    $uStmt->execute([$row['prepared_by_user_id']]);
                    $u = $uStmt->fetch();
                    if ($u) {
                        $sig['prepared_by_name']  = $u['name'];
                        $sig['prepared_by_title'] = $u['position'] ?: 'Teacher';
                    }
                } elseif (!empty($row['prepared_by_name'])) {
                    $sig['prepared_by_name']  = $row['prepared_by_name'];
                    $sig['prepared_by_title'] = $row['prepared_by_title'] ?: ($currentUser['position'] ?? 'Administrator');
                }

                // Noted by
                if ($row['noted_by_type'] === 'teacher' && !empty($row['noted_by_user_id'])) {
                    $uStmt = $pdo->prepare("SELECT name, position FROM users WHERE id = ?");
                    $uStmt->execute([$row['noted_by_user_id']]);
                    $u = $uStmt->fetch();
                    if ($u) {
                        $sig['noted_by_name']  = $u['name'];
                        $sig['noted_by_title'] = $u['position'] ?: 'School Head / Principal';
                    }
                } else {
                    $sig['noted_by_name']  = $row['noted_by_name'] ?: '';
                    $sig['noted_by_title'] = $row['noted_by_title'] ?: 'School Head / Principal';
                }
            }
        } catch (Exception $e) {}
        return $sig;
    }
}
