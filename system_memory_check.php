<?php
/**
 * SPSMIS System Memory & File Synchronization Checker
 * 
 * Comprehensive integrity checker verifying that all files, database tables,
 * API endpoints, views, sidebars, includes, and assets are fully synchronized.
 * 
 * Usage:
 *   CLI:     php system_memory_check.php
 *   Browser: http://localhost/SPSFMS-Student-Profiling-System-for-Minanga-School/system_memory_check.php
 */

$isCli = (php_sapi_name() === 'cli');
$rootDir = __DIR__;

if (!$isCli) {
    header('Content-Type: text/html; charset=UTF-8');
}

function c(string $text, string $color, bool $bold = false): string {
    global $isCli;
    if (!$isCli) return $text;
    $colors = [
        'red'     => '31',
        'green'   => '32',
        'yellow'  => '33',
        'blue'    => '34',
        'magenta' => '35',
        'cyan'    => '36',
        'white'   => '37',
        'gray'    => '90',
    ];
    $code = $colors[$color] ?? '37';
    $b = $bold ? '1;' : '';
    return "\033[{$b}{$code}m{$text}\033[0m";
}

$results = [
    'syntax'     => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'includes'   => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'apis'       => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'sidebar'    => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'auth'       => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'database'   => ['pass' => 0, 'fail' => 0, 'errors' => []],
    'assets'     => ['pass' => 0, 'fail' => 0, 'errors' => []],
];

// ── 1. Gather all PHP files (skipping .git, .claude, etc.) ───────────────────
function scanPhpFiles(string $dir, string $base): array {
    $files = [];
    $ignore = ['.git', '.claude', 'node_modules', 'vendor'];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (in_array($item, $ignore)) continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scanPhpFiles($path, $base));
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $rel = str_replace('\\', '/', substr($path, strlen($base) + 1));
            $files[] = $rel;
        }
    }
    return $files;
}

$phpFiles = scanPhpFiles($rootDir, $rootDir);
sort($phpFiles);

// ── 2. Check PHP Syntax (fast in-process tokenizer parsing) ──────────────────
foreach ($phpFiles as $rel) {
    if ($rel === 'system_memory_check.php') continue;
    $fullPath = $rootDir . '/' . $rel;
    $code = file_get_contents($fullPath);
    try {
        token_get_all($code, TOKEN_PARSE);
        $results['syntax']['pass']++;
    } catch (ParseError $e) {
        $results['syntax']['fail']++;
        $results['syntax']['errors'][] = [
            'file' => $rel,
            'message' => "Line " . $e->getLine() . ": " . $e->getMessage()
        ];
    }
}

// ── 3. Check Includes / Requires ─────────────────────────────────────────────
foreach ($phpFiles as $rel) {
    $fullPath = $rootDir . '/' . $rel;
    $content = file_get_contents($fullPath);
    $dir = dirname($fullPath);

    if (preg_match_all('/(?:require|require_once|include|include_once)\s*(?:\(?\s*)([^;\)\n]+)/', $content, $m)) {
        foreach ($m[1] as $incExpr) {
            $incExpr = trim($incExpr);
            if (str_starts_with($incExpr, '$')) continue;

            $resolved = null;
            if (preg_match('/^__DIR__\s*\.\s*[\'"]([^\'"]+)[\'"]/', $incExpr, $dm)) {
                $resolved = $dir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($dm[1], '/\\'));
            } elseif (preg_match('/^[\'"]([^\'"]+)[\'"]/', $incExpr, $sm)) {
                $target = $sm[1];
                if (file_exists($dir . DIRECTORY_SEPARATOR . $target)) {
                    $resolved = $dir . DIRECTORY_SEPARATOR . $target;
                } elseif (file_exists($rootDir . DIRECTORY_SEPARATOR . $target)) {
                    $resolved = $rootDir . DIRECTORY_SEPARATOR . $target;
                }
            }

            if ($resolved) {
                $real = realpath($resolved);
                if ($real && file_exists($real)) {
                    $results['includes']['pass']++;
                } else {
                    $results['includes']['fail']++;
                    $results['includes']['errors'][] = [
                        'file' => $rel,
                        'message' => "Unresolved include target: {$incExpr}"
                    ];
                }
            }
        }
    }
}

// ── 4. Check API Endpoints & Client Fetch Synchronization ─────────────────────
$apiCalls = [];
$scannedFiles = array_merge($phpFiles, ['assets/js/components.js']);

foreach ($scannedFiles as $rel) {
    $fullPath = $rootDir . '/' . $rel;
    if (!file_exists($fullPath)) continue;
    $content = file_get_contents($fullPath);

    if (preg_match_all('/(?:fetch|url|href|action)\s*[:=\(]\s*[`\'"](?:\$\{[^}]+\}|<\?=\s*BASE_URL\s*\?>|\/SPSFMS-Student-Profiling-System-for-Minanga-School)?(\/api\/[a-zA-Z0-9_\-\/]+\.php)(\?[^`\'"]*)?[`\'"]/i', $content, $m)) {
        foreach ($m[1] as $idx => $apiPath) {
            $apiRel = ltrim($apiPath, '/');
            $apiCalls[$apiRel][] = $rel;
        }
    }
}

foreach ($apiCalls as $apiRel => $callers) {
    $fullApi = $rootDir . '/' . $apiRel;
    if (file_exists($fullApi)) {
        $results['apis']['pass']++;
    } else {
        $results['apis']['fail']++;
        $results['apis']['errors'][] = [
            'file' => implode(', ', array_unique($callers)),
            'message' => "Referenced API endpoint does not exist: {$apiRel}"
        ];
    }
}

// ── 5. Check Sidebar Navigation vs Existing Views ────────────────────────────
// Admin Sidebar
$adminSidebarFile = $rootDir . '/includes/admin-sidebar.php';
if (file_exists($adminSidebarFile)) {
    $c = file_get_contents($adminSidebarFile);
    if (preg_match_all('/\'href\'\s*=>\s*BASE_URL\s*\.\s*\'([^\']+)\'/', $c, $m)) {
        foreach ($m[1] as $path) {
            $targetRel = ltrim($path, '/');
            $targetFull = $rootDir . '/' . $targetRel;
            if (file_exists($targetFull)) {
                $results['sidebar']['pass']++;
            } else {
                $results['sidebar']['fail']++;
                $results['sidebar']['errors'][] = [
                    'file' => 'includes/admin-sidebar.php',
                    'message' => "Admin sidebar links to missing view: {$targetRel}"
                ];
            }
        }
    }

    $adminViews = array_filter($phpFiles, fn($f) => str_starts_with($f, 'views/admin/'));
    foreach ($adminViews as $av) {
        $basename = basename($av);
        if (!str_contains($c, $basename)) {
            $results['sidebar']['errors'][] = [
                'file' => $av,
                'message' => "Notice: Admin view '{$av}' is not linked in admin-sidebar.php"
            ];
        }
    }
}

// Teacher Sidebar
$teacherSidebarFile = $rootDir . '/includes/teacher-sidebar.php';
if (file_exists($teacherSidebarFile)) {
    $c = file_get_contents($teacherSidebarFile);
    if (preg_match_all('/\'href\'\s*=>\s*BASE_URL\s*\.\s*\'([^\']+)\'/', $c, $m)) {
        foreach ($m[1] as $path) {
            $targetRel = ltrim($path, '/');
            $targetFull = $rootDir . '/' . $targetRel;
            if (file_exists($targetFull)) {
                $results['sidebar']['pass']++;
            } else {
                $results['sidebar']['fail']++;
                $results['sidebar']['errors'][] = [
                    'file' => 'includes/teacher-sidebar.php',
                    'message' => "Teacher sidebar links to missing view: {$targetRel}"
                ];
            }
        }
    }
}

// ── 6. Check Auth Guards on Views and APIs ────────────────────────────────────
foreach ($phpFiles as $rel) {
    $fullPath = $rootDir . '/' . $rel;
    $c = file_get_contents($fullPath);

    if (str_starts_with($rel, 'views/admin/')) {
        if (str_contains($c, "requireAuth('admin')")) {
            $results['auth']['pass']++;
        } else {
            $results['auth']['fail']++;
            $results['auth']['errors'][] = [
                'file' => $rel,
                'message' => "Missing requireAuth('admin') guard"
            ];
        }
    } elseif (str_starts_with($rel, 'views/teacher/')) {
        if (str_contains($c, "requireAuth('teacher')")) {
            $results['auth']['pass']++;
        } else {
            $results['auth']['fail']++;
            $results['auth']['errors'][] = [
                'file' => $rel,
                'message' => "Missing requireAuth('teacher') guard"
            ];
        }
    } elseif (str_starts_with($rel, 'views/student/')) {
        if (str_contains($c, "requireAuth('student')")) {
            $results['auth']['pass']++;
        } else {
            $results['auth']['fail']++;
            $results['auth']['errors'][] = [
                'file' => $rel,
                'message' => "Missing requireAuth('student') guard"
            ];
        }
    }
}

// ── 7. Check Database Schema Synchronization ──────────────────────────────────
$expectedTables = [
    'users',
    'teacher_classes',
    'students',
    'grades',
    'announcements',
    'security_questions',
    'report_signatories',
    'sections',
    'subjects',
    'school_years'
];

$schemaSql = file_exists($rootDir . '/database/schema.sql') ? file_get_contents($rootDir . '/database/schema.sql') : '';
$setupPhp  = file_exists($rootDir . '/database/setup.php')  ? file_get_contents($rootDir . '/database/setup.php')  : '';

foreach ($expectedTables as $tbl) {
    $inSchema = (bool)preg_match("/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:[`']?{$tbl}[`']?)/i", $schemaSql);
    $inSetup  = (bool)preg_match("/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?(?:[`']?{$tbl}[`']?)/i", $setupPhp);

    if ($inSchema && $inSetup) {
        $results['database']['pass']++;
    } else {
        $results['database']['fail']++;
        $missing = [];
        if (!$inSchema) $missing[] = 'database/schema.sql';
        if (!$inSetup)  $missing[] = 'database/setup.php';
        $results['database']['errors'][] = [
            'file' => implode(' & ', $missing),
            'message' => "Table '{$tbl}' definition missing from schema or seeder"
        ];
    }
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=spsmis;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT
    ]);
    $liveTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($expectedTables as $tbl) {
        if (in_array($tbl, $liveTables)) {
            $results['database']['pass']++;
        } else {
            $results['database']['fail']++;
            $results['database']['errors'][] = [
                'file' => 'MySQL Database (spsmis)',
                'message' => "Table '{$tbl}' does not exist in active MySQL database"
            ];
        }
    }
} catch (Exception $e) {
    $results['database']['errors'][] = [
        'file' => 'MySQL Connection',
        'message' => 'Notice: Live MySQL check skipped (' . $e->getMessage() . ')'
    ];
}

// ── 8. Check Static Asset Links ───────────────────────────────────────────────
$assetReferences = [
    'assets/css/theme.css',
    'assets/css/admin.css',
    'assets/css/student-mobile.css',
    'assets/js/components.js',
    'assets/lib/bootstrap.min.css',
    'assets/lib/bootstrap.bundle.min.js',
    'assets/lib/fa.min.css',
    'assets/lib/chart.umd.min.js',
    'img/MIS-Logo.jpg'
];

foreach ($assetReferences as $assetRel) {
    $assetFull = $rootDir . '/' . $assetRel;
    if (file_exists($assetFull)) {
        $results['assets']['pass']++;
    } else {
        $results['assets']['fail']++;
        $results['assets']['errors'][] = [
            'file' => $assetRel,
            'message' => "Asset file missing on disk"
        ];
    }
}

// ── 9. Render Report ─────────────────────────────────────────────────────────
$totalPass = 0;
$totalFail = 0;

foreach ($results as $cat => $data) {
    $totalPass += $data['pass'];
    $totalFail += $data['fail'];
}

$systemHealthy = ($totalFail === 0);

if ($isCli) {
    echo "\n" . str_repeat('=', 72) . "\n";
    echo c("  SPSMIS SYSTEM MEMORY & FILE SYNCHRONIZATION CHECKER", "cyan", true) . "\n";
    echo str_repeat('=', 72) . "\n\n";

    echo "Project Directory: " . $rootDir . "\n";
    echo "PHP Files Scanned: " . count($phpFiles) . "\n\n";

    foreach ($results as $cat => $data) {
        $catTitle = strtoupper($cat);
        $statusTag = ($data['fail'] === 0) ? c("[ OK ]", "green", true) : c("[ FAIL: {$data['fail']} ]", "red", true);
        echo sprintf("%-20s %s (Passed: %d, Issues: %d)\n", $catTitle, $statusTag, $data['pass'], count($data['errors']));

        if (!empty($data['errors'])) {
            foreach ($data['errors'] as $err) {
                $isNotice = str_starts_with($err['message'], 'Notice:');
                $prefix = $isNotice ? c("  ▲ ", "yellow") : c("  ✖ ", "red");
                echo "{$prefix}" . c($err['file'], "white", true) . ": {$err['message']}\n";
            }
        }
    }

    echo "\n" . str_repeat('-', 72) . "\n";
    if ($systemHealthy) {
        echo c(" ✔ SYSTEM SYNCHRONIZATION STATUS: HEALTHY (0 Critical Failures)", "green", true) . "\n";
    } else {
        echo c(" ✘ SYSTEM SYNCHRONIZATION STATUS: DISCREPANCIES DETECTED ({$totalFail} Failures)", "red", true) . "\n";
    }
    echo str_repeat('=', 72) . "\n\n";
    exit($systemHealthy ? 0 : 1);

} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>SPSMIS — System Memory & Synchronization Report</title>
        <link rel="stylesheet" href="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.min.css">
        <link rel="stylesheet" href="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/fa.min.css">
        <style>
            body { background: #f4f6f9; font-family: 'Segoe UI', system-ui, sans-serif; padding: 2.5rem 0; }
            .container { max-width: 920px; }
            .header-card { background: #1a365d; color: white; border-radius: 12px; padding: 1.75rem; margin-bottom: 1.5rem; }
            .check-card { border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 1.2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
            .badge-pass { background: #dcfce7; color: #166534; font-weight: 600; padding: .35rem .75rem; border-radius: 20px; }
            .badge-fail { background: #fee2e2; color: #991b1b; font-weight: 600; padding: .35rem .75rem; border-radius: 20px; }
            .err-item { font-size: .88rem; padding: .6rem 0; border-bottom: 1px dashed #e2e8f0; }
            .err-item:last-child { border-bottom: none; }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="header-card d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1"><i class="fas fa-microchip me-2"></i>SPSMIS System Memory & Sync Report</h3>
                <p class="mb-0 text-white-50">Scanned <strong><?= count($phpFiles) ?></strong> PHP application files and cross-checked dependencies.</p>
            </div>
            <div>
                <?php if ($systemHealthy): ?>
                    <span class="badge bg-success fs-6 p-2"><i class="fas fa-check-circle me-1"></i> Synchronized</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6 p-2"><i class="fas fa-exclamation-triangle me-1"></i> Discrepancies</span>
                <?php endif; ?>
            </div>
        </div>

        <?php foreach ($results as $cat => $data): ?>
            <div class="card check-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <strong class="text-uppercase" style="letter-spacing: .5px;">
                        <i class="fas fa-cube text-primary me-2"></i><?= htmlspecialchars($cat) ?> Checks
                    </strong>
                    <div>
                        <span class="badge-pass me-2"><i class="fas fa-check me-1"></i><?= $data['pass'] ?> Passed</span>
                        <?php if ($data['fail'] > 0): ?>
                            <span class="badge-fail"><i class="fas fa-times me-1"></i><?= $data['fail'] ?> Failed</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($data['errors'])): ?>
                    <div class="card-body py-2">
                        <?php foreach ($data['errors'] as $err): ?>
                            <div class="err-item d-flex align-items-start">
                                <i class="fas <?= str_starts_with($err['message'], 'Notice:') ? 'fa-info-circle text-warning' : 'fa-times-circle text-danger' ?> me-2 mt-1"></i>
                                <div>
                                    <code><?= htmlspecialchars($err['file']) ?></code>
                                    <div class="text-muted"><?= htmlspecialchars($err['message']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="text-center mt-4">
            <a href="/SPSFMS-Student-Profiling-System-for-Minanga-School/" class="btn btn-primary"><i class="fas fa-arrow-left me-2"></i>Back to SPSMIS Portal</a>
        </div>
    </div>
    </body>
    </html>
    <?php
}
