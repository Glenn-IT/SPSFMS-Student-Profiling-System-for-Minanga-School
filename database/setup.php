<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SPSMIS — Database Setup</title>
<link rel="stylesheet" href="/SPSFMS-Student-Profiling-System-for-Minanga-School/assets/lib/bootstrap.min.css">
<style>body{padding:2rem;background:#f8f9fa;} .log{font-family:monospace;font-size:.85rem;} .ok{color:#198754;} .err{color:#dc3545;} .info{color:#0d6efd;}</style>
</head>
<body>
<div class="container" style="max-width:700px;">
<h3 class="mb-1">SPSMIS Database Setup</h3>
<p class="text-muted mb-4">Creates the <code>spsmis</code> database, all tables, and seeds sample data.</p>
<div class="card"><div class="card-body log" id="log"></div></div>
</div>
<script>
function log(msg, cls='info') {
  const d = document.getElementById('log');
  d.innerHTML += `<div class="${cls}">${msg}</div>`;
}
</script>
<?php
define('BASE_URL', '/SPSFMS-Student-Profiling-System-for-Minanga-School');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

function out(string $msg, string $cls = 'info'): void {
    echo "<script>log(" . json_encode($msg) . ", '$cls');</script>\n";
    flush();
    ob_flush();
}

ob_start();

// ── Connect without selecting DB ─────────────────────────────────────────────
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    out('✔ Connected to MySQL.', 'ok');
} catch (PDOException $e) {
    out('✘ Cannot connect to MySQL: ' . $e->getMessage(), 'err');
    exit;
}

// ── Create database ──────────────────────────────────────────────────────────
$pdo->exec("CREATE DATABASE IF NOT EXISTS `spsmis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `spsmis`");
out('✔ Database <strong>spsmis</strong> ready.', 'ok');

// ── Create tables ────────────────────────────────────────────────────────────
$pdo->exec("
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role`         ENUM('admin','teacher','student') NOT NULL,
  `username`     VARCHAR(50) NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `name`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `position`     VARCHAR(150) DEFAULT NULL,
  `lrn`          VARCHAR(20)  DEFAULT NULL,
  `grade_level`  VARCHAR(20)  DEFAULT NULL,
  `section`      VARCHAR(50)  DEFAULT NULL,
  `status`       ENUM('active','inactive') DEFAULT 'active',
  `sec_question` VARCHAR(200) DEFAULT NULL,
  `sec_answer`   VARCHAR(200) DEFAULT NULL,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
out('✔ Table <strong>users</strong> ready.', 'ok');

$pdo->exec("
CREATE TABLE IF NOT EXISTS `students` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lrn`              VARCHAR(20) NOT NULL,
  `grade_level`      VARCHAR(20) NOT NULL,
  `section`          VARCHAR(50) NOT NULL,
  `first_name`       VARCHAR(80) NOT NULL,
  `middle_name`      VARCHAR(80) DEFAULT NULL,
  `last_name`        VARCHAR(80) NOT NULL,
  `sex`              ENUM('Male','Female') NOT NULL,
  `birthdate`        DATE NOT NULL,
  `age`              TINYINT UNSIGNED NOT NULL,
  `mother_tongue`    VARCHAR(80)  DEFAULT NULL,
  `religion`         VARCHAR(80)  DEFAULT NULL,
  `address`          TEXT         DEFAULT NULL,
  `mother_name`      VARCHAR(100) DEFAULT NULL,
  `father_name`      VARCHAR(100) DEFAULT NULL,
  `guardian_name`    VARCHAR(100) DEFAULT NULL,
  `guardian_relation` VARCHAR(50) DEFAULT NULL,
  `contact`          VARCHAR(20)  DEFAULT NULL,
  `email`            VARCHAR(150) DEFAULT NULL,
  `school_year`      VARCHAR(10)  DEFAULT '2025-2026',
  `status`           ENUM('active','inactive') DEFAULT 'active',
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_lrn` (`lrn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
out('✔ Table <strong>students</strong> ready.', 'ok');

$pdo->exec("
CREATE TABLE IF NOT EXISTS `grades` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id`  INT UNSIGNED NOT NULL,
  `school_year` VARCHAR(10) NOT NULL DEFAULT '2025-2026',
  `grade_level` VARCHAR(20) NOT NULL,
  `section`     VARCHAR(50) NOT NULL,
  `subject`     VARCHAR(100) NOT NULL,
  `q1`          DECIMAL(5,2) DEFAULT NULL,
  `q2`          DECIMAL(5,2) DEFAULT NULL,
  `q3`          DECIMAL(5,2) DEFAULT NULL,
  `q4`          DECIMAL(5,2) DEFAULT NULL,
  `final_grade` DECIMAL(5,2) DEFAULT NULL,
  `remarks`     ENUM('Passed','Failed','') DEFAULT '',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uq_grade` (`student_id`,`school_year`,`subject`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
out('✔ Table <strong>grades</strong> ready.', 'ok');

$pdo->exec("
CREATE TABLE IF NOT EXISTS `announcements` (
  `id`        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`     VARCHAR(200) NOT NULL,
  `body`      TEXT NOT NULL,
  `audience`  ENUM('all','student','teacher') DEFAULT 'all',
  `posted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
out('✔ Table <strong>announcements</strong> ready.', 'ok');

// ── Seed Users ───────────────────────────────────────────────────────────────
$existing = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($existing == 0) {
    $ins = $pdo->prepare("INSERT INTO users (role,username,password,name,email,position,lrn,grade_level,section,status,sec_question,sec_answer) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $users = [
        ['admin',   'admin',      password_hash('admin123',   PASSWORD_BCRYPT), 'Maria L. Reyes',           'admin@minanga.edu.ph',                    'School Administrator',            null,           null,       null,    'active', 'What is the name of your first pet?',       'Bantay'],
        ['teacher', 'teacher',    password_hash('teacher123', PASSWORD_BCRYPT), 'Ricardo G. Santos',        'rsantos@minanga.edu.ph',                  'Grade 7 Adviser / Math Teacher',  null,           null,       null,    'active', "What is your mother's maiden name?",       'Dela Cruz'],
        ['teacher', 'teacher2',   password_hash('teacher123', PASSWORD_BCRYPT), 'Josephine A. Villanueva',  'jvillanueva@minanga.edu.ph',              'Grade 1 Adviser / Filipino Teacher',null,          null,       null,    'active', 'What city were you born in?',               'Cagayan de Oro'],
        ['student', 'student2025',password_hash('student123', PASSWORD_BCRYPT), 'Juan P. Dela Cruz',        'juan.delacruz@student.minanga.edu.ph',    null,                              '123456789001', 'Grade 7',  'Rizal', 'active', 'What is the name of your elementary school?','Minanga Elementary'],
    ];
    foreach ($users as $u) $ins->execute($u);
    out('✔ Seeded <strong>' . count($users) . ' users</strong>.', 'ok');
} else {
    out("ℹ Users already exist ($existing rows) — skipping.", 'info');
}

// ── Seed Students ────────────────────────────────────────────────────────────
$existingSt = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
if ($existingSt == 0) {
    $ins = $pdo->prepare("INSERT INTO students (lrn,grade_level,section,first_name,middle_name,last_name,sex,birthdate,age,mother_tongue,religion,address,mother_name,father_name,guardian_name,guardian_relation,contact,email,school_year,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sy = '2025-2026'; $addr = 'Minanga, Cagayan de Oro';
    $students = [
      // Grade 1 — Mabini
      ['100000000001','Grade 1','Mabini','Ana','B.','Garcia','Female','2018-03-12',7,'Cebuano','Roman Catholic','Purok 1, '.$addr,'Luz B. Garcia','Roberto Garcia','Luz B. Garcia','Mother','09171234001','ana.garcia@student.minanga.edu.ph',$sy,'active'],
      ['100000000002','Grade 1','Mabini','Carlo','D.','Mendoza','Male','2018-05-20',7,'Cebuano','Roman Catholic','Purok 2, '.$addr,'Elena D. Mendoza','Jose Mendoza','Elena D. Mendoza','Mother','09171234002','carlo.mendoza@student.minanga.edu.ph',$sy,'active'],
      ['100000000003','Grade 1','Mabini','Diana','F.','Cruz','Female','2018-07-08',7,'Cebuano','Iglesia ni Cristo','Purok 3, '.$addr,'Perla F. Cruz','Armando Cruz','Perla F. Cruz','Mother','09171234003','diana.cruz@student.minanga.edu.ph',$sy,'active'],
      ['100000000004','Grade 1','Mabini','Emilio','S.','Torres','Male','2018-01-15',7,'Cebuano','Roman Catholic','Purok 4, '.$addr,'Rosa S. Torres','Manuel Torres','Rosa S. Torres','Mother','09171234004','emilio.torres@student.minanga.edu.ph',$sy,'active'],
      ['100000000005','Grade 1','Mabini','Fatima','L.','Villanueva','Female','2018-09-22',7,'Maranao','Islam','Purok 5, '.$addr,'Aisha L. Villanueva','Ahmad Villanueva','Aisha L. Villanueva','Mother','09171234005','fatima.villanueva@student.minanga.edu.ph',$sy,'active'],
      // Grade 4 — Bonifacio
      ['100000000006','Grade 4','Bonifacio','Gerald','M.','Pascual','Male','2015-04-10',10,'Cebuano','Roman Catholic','Purok 1, '.$addr,'Nora M. Pascual','Fernando Pascual','Nora M. Pascual','Mother','09171234006','gerald.pascual@student.minanga.edu.ph',$sy,'active'],
      ['100000000007','Grade 4','Bonifacio','Hannah','C.','Reyes','Female','2015-06-18',10,'Cebuano','Born Again','Purok 2, '.$addr,'Carmen C. Reyes','Eduardo Reyes','Carmen C. Reyes','Mother','09171234007','hannah.reyes@student.minanga.edu.ph',$sy,'active'],
      ['100000000008','Grade 4','Bonifacio','Ivan','R.','Lim','Male','2015-02-28',10,'Bisaya','Roman Catholic','Purok 3, '.$addr,'Teresita R. Lim','William Lim','Teresita R. Lim','Mother','09171234008','ivan.lim@student.minanga.edu.ph',$sy,'active'],
      // Grade 7 — Rizal  (id 9–13)
      ['123456789001','Grade 7','Rizal','Juan','P.','Dela Cruz','Male','2012-08-14',13,'Cebuano','Roman Catholic','Purok 2, '.$addr,'Nelia P. Dela Cruz','Roberto Dela Cruz','Nelia P. Dela Cruz','Mother','09181234001','juan.delacruz@student.minanga.edu.ph',$sy,'active'],
      ['123456789002','Grade 7','Rizal','Maria','C.','Santos','Female','2012-03-25',13,'Cebuano','Roman Catholic','Purok 4, '.$addr,'Gloria C. Santos','Eduardo Santos','Gloria C. Santos','Mother','09181234002','maria.santos@student.minanga.edu.ph',$sy,'active'],
      ['123456789003','Grade 7','Rizal','Pedro','A.','Reyes','Male','2012-11-07',13,'Cebuano','Iglesia ni Cristo','Purok 6, '.$addr,'Caridad A. Reyes','Alejandro Reyes','Caridad A. Reyes','Mother','09181234003','pedro.reyes@student.minanga.edu.ph',$sy,'active'],
      ['123456789004','Grade 7','Rizal','Lourdes','B.','Fernandez','Female','2012-05-30',13,'Cebuano','Roman Catholic','Purok 7, '.$addr,'Mercy B. Fernandez','Carlos Fernandez','Mercy B. Fernandez','Mother','09181234004','lourdes.fernandez@student.minanga.edu.ph',$sy,'active'],
      ['123456789005','Grade 7','Rizal','Ramon','E.','Bautista','Male','2012-01-19',13,'Bisaya','Roman Catholic','Purok 8, '.$addr,'Josefa E. Bautista','Ernesto Bautista','Josefa E. Bautista','Mother','09181234005','ramon.bautista@student.minanga.edu.ph',$sy,'active'],
      // Grade 8 — Luna
      ['123456789006','Grade 8','Luna','Sofia','G.','Aquino','Female','2011-04-22',14,'Cebuano','Roman Catholic','Purok 1, '.$addr,'Leticia G. Aquino','Ramon Aquino','Leticia G. Aquino','Mother','09191234001','sofia.aquino@student.minanga.edu.ph',$sy,'active'],
      ['123456789007','Grade 8','Luna','Marco','T.','Ramos','Male','2011-09-13',14,'Cebuano','Born Again','Purok 2, '.$addr,'Virginia T. Ramos','Dante Ramos','Virginia T. Ramos','Mother','09191234002','marco.ramos@student.minanga.edu.ph',$sy,'active'],
      ['123456789008','Grade 8','Luna','Cristina','V.','Navarro','Female','2011-12-05',14,'Cebuano','Roman Catholic','Purok 3, '.$addr,'Bella V. Navarro','Nestor Navarro','Bella V. Navarro','Mother','09191234003','cristina.navarro@student.minanga.edu.ph',$sy,'active'],
      // Grade 10 — Mabini
      ['123456789009','Grade 10','Mabini','Jerome','O.','Castillo','Male','2009-07-17',16,'Cebuano','Roman Catholic','Purok 5, '.$addr,'Rosario O. Castillo','Ignacio Castillo','Rosario O. Castillo','Mother','09201234001','jerome.castillo@student.minanga.edu.ph',$sy,'active'],
      ['123456789010','Grade 10','Mabini','Kathleen','D.','Soriano','Female','2009-02-14',16,'Cebuano','Roman Catholic','Purok 6, '.$addr,'Elsa D. Soriano','Andres Soriano','Elsa D. Soriano','Mother','09201234002','kathleen.soriano@student.minanga.edu.ph',$sy,'active'],
      // Grade 11 — STEM  (id 19–22)
      ['123456789011','Grade 11','STEM','Lorenzo','P.','Miranda','Male','2008-06-30',17,'Cebuano','Roman Catholic','Purok 1, '.$addr,'Patricia P. Miranda','Luis Miranda','Patricia P. Miranda','Mother','09211234001','lorenzo.miranda@student.minanga.edu.ph',$sy,'active'],
      ['123456789012','Grade 11','STEM','Michelle','R.','Santos','Female','2008-10-25',17,'Cebuano','Roman Catholic','Purok 2, '.$addr,'Aida R. Santos','Victor Santos','Aida R. Santos','Mother','09211234002','michelle.santos@student.minanga.edu.ph',$sy,'active'],
      ['123456789013','Grade 11','STEM','Noel','C.','Garcia','Male','2008-04-08',17,'Bisaya','Roman Catholic','Purok 3, '.$addr,'Celia C. Garcia','Rodrigo Garcia','Celia C. Garcia','Mother','09211234003','noel.garcia@student.minanga.edu.ph',$sy,'active'],
      ['123456789014','Grade 11','STEM','Olivia','M.','Dela Torre','Female','2008-08-16',17,'Cebuano','Born Again','Purok 4, '.$addr,'Gina M. Dela Torre','Oscar Dela Torre','Gina M. Dela Torre','Mother','09211234004','olivia.delatorre@student.minanga.edu.ph',$sy,'active'],
      // Grade 11 — ABM
      ['123456789015','Grade 11','ABM','Paolo','N.','Cruz','Male','2008-01-12',17,'Cebuano','Roman Catholic','Purok 5, '.$addr,'Norma N. Cruz','Benjamin Cruz','Norma N. Cruz','Mother','09211234005','paolo.cruz@student.minanga.edu.ph',$sy,'active'],
      ['123456789016','Grade 11','ABM','Queenie','S.','Flores','Female','2008-03-29',17,'Cebuano','Roman Catholic','Purok 6, '.$addr,'Susan S. Flores','Danilo Flores','Susan S. Flores','Mother','09211234006','queenie.flores@student.minanga.edu.ph',$sy,'active'],
      // Grade 12 — HUMSS
      ['123456789017','Grade 12','HUMSS','Rafael','J.','Morales','Male','2007-05-21',18,'Cebuano','Roman Catholic','Purok 7, '.$addr,'Imee J. Morales','Felix Morales','Imee J. Morales','Mother','09221234001','rafael.morales@student.minanga.edu.ph',$sy,'active'],
    ];
    foreach ($students as $s) $ins->execute($s);
    out('✔ Seeded <strong>' . count($students) . ' students</strong>.', 'ok');
} else {
    out("ℹ Students already exist ($existingSt rows) — skipping.", 'info');
}

// ── Seed Grades ──────────────────────────────────────────────────────────────
$existingGr = $pdo->query("SELECT COUNT(*) FROM grades")->fetchColumn();
if ($existingGr == 0) {
    $ins = $pdo->prepare("INSERT INTO grades (student_id,school_year,grade_level,section,subject,q1,q2,q3,q4,final_grade,remarks) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $sy = '2025-2026';
    $gradeData = [
      // student_id 9 = Juan Dela Cruz (Grade 7)
      [9,$sy,'Grade 7','Rizal','Filipino',88,87,89,90,89,'Passed'],
      [9,$sy,'Grade 7','Rizal','English',85,86,84,87,86,'Passed'],
      [9,$sy,'Grade 7','Rizal','Mathematics',90,92,91,93,92,'Passed'],
      [9,$sy,'Grade 7','Rizal','Science',87,88,86,89,88,'Passed'],
      [9,$sy,'Grade 7','Rizal','Araling Panlipunan',83,84,85,86,85,'Passed'],
      [9,$sy,'Grade 7','Rizal','Edukasyon sa Pagpapakatao',90,91,92,91,91,'Passed'],
      [9,$sy,'Grade 7','Rizal','Technology and Livelihood Education',88,87,89,88,88,'Passed'],
      [9,$sy,'Grade 7','Rizal','MAPEH',86,87,88,87,87,'Passed'],
      // student_id 10 = Maria Santos (Grade 7)
      [10,$sy,'Grade 7','Rizal','Filipino',92,93,91,94,93,'Passed'],
      [10,$sy,'Grade 7','Rizal','English',94,95,93,96,95,'Passed'],
      [10,$sy,'Grade 7','Rizal','Mathematics',88,89,90,91,90,'Passed'],
      [10,$sy,'Grade 7','Rizal','Science',91,92,90,93,92,'Passed'],
      [10,$sy,'Grade 7','Rizal','Araling Panlipunan',89,90,91,92,91,'Passed'],
      [10,$sy,'Grade 7','Rizal','Edukasyon sa Pagpapakatao',95,94,96,95,95,'Passed'],
      [10,$sy,'Grade 7','Rizal','Technology and Livelihood Education',90,91,92,91,91,'Passed'],
      [10,$sy,'Grade 7','Rizal','MAPEH',93,92,94,93,93,'Passed'],
      // student_id 11 = Pedro Reyes (Grade 7)
      [11,$sy,'Grade 7','Rizal','Filipino',78,79,80,81,80,'Passed'],
      [11,$sy,'Grade 7','Rizal','English',75,76,74,77,76,'Passed'],
      [11,$sy,'Grade 7','Rizal','Mathematics',82,83,81,84,83,'Passed'],
      [11,$sy,'Grade 7','Rizal','Science',79,78,80,79,79,'Passed'],
      [11,$sy,'Grade 7','Rizal','Araling Panlipunan',76,77,78,79,78,'Passed'],
      [11,$sy,'Grade 7','Rizal','Edukasyon sa Pagpapakatao',83,84,85,84,84,'Passed'],
      [11,$sy,'Grade 7','Rizal','Technology and Livelihood Education',80,81,82,81,81,'Passed'],
      [11,$sy,'Grade 7','Rizal','MAPEH',77,78,79,80,79,'Passed'],
      // student_id 12 = Lourdes Fernandez (Grade 7)
      [12,$sy,'Grade 7','Rizal','Filipino',85,86,87,88,87,'Passed'],
      [12,$sy,'Grade 7','Rizal','English',88,89,87,90,89,'Passed'],
      [12,$sy,'Grade 7','Rizal','Mathematics',72,73,71,74,73,'Passed'],
      [12,$sy,'Grade 7','Rizal','Science',83,84,82,85,84,'Passed'],
      [12,$sy,'Grade 7','Rizal','Araling Panlipunan',87,88,89,90,89,'Passed'],
      [12,$sy,'Grade 7','Rizal','Edukasyon sa Pagpapakatao',90,91,92,91,91,'Passed'],
      [12,$sy,'Grade 7','Rizal','Technology and Livelihood Education',85,86,87,86,86,'Passed'],
      [12,$sy,'Grade 7','Rizal','MAPEH',89,90,88,91,90,'Passed'],
      // student_id 13 = Ramon Bautista (Grade 7)
      [13,$sy,'Grade 7','Rizal','Filipino',70,71,69,72,71,'Passed'],
      [13,$sy,'Grade 7','Rizal','English',73,72,74,73,73,'Passed'],
      [13,$sy,'Grade 7','Rizal','Mathematics',68,69,67,70,69,'Passed'],
      [13,$sy,'Grade 7','Rizal','Science',72,71,73,72,72,'Passed'],
      [13,$sy,'Grade 7','Rizal','Araling Panlipunan',74,75,76,75,75,'Passed'],
      [13,$sy,'Grade 7','Rizal','Edukasyon sa Pagpapakatao',78,79,80,79,79,'Passed'],
      [13,$sy,'Grade 7','Rizal','Technology and Livelihood Education',76,77,78,77,77,'Passed'],
      [13,$sy,'Grade 7','Rizal','MAPEH',74,75,73,76,75,'Passed'],
      // student_id 19 = Lorenzo Miranda (Grade 11 STEM)
      [19,$sy,'Grade 11','STEM','Oral Communication',90,91,89,92,91,'Passed'],
      [19,$sy,'Grade 11','STEM','Reading and Writing',88,89,87,90,89,'Passed'],
      [19,$sy,'Grade 11','STEM','Komunikasyon at Pananaliksik',85,86,84,87,86,'Passed'],
      [19,$sy,'Grade 11','STEM','21st Century Literature',87,88,86,89,88,'Passed'],
      [19,$sy,'Grade 11','STEM','General Mathematics',92,93,94,95,94,'Passed'],
      [19,$sy,'Grade 11','STEM','Statistics and Probability',90,91,92,93,92,'Passed'],
      [19,$sy,'Grade 11','STEM','Earth and Life Science',88,89,87,90,89,'Passed'],
      [19,$sy,'Grade 11','STEM','Physical Science',91,92,90,93,92,'Passed'],
      // student_id 20 = Michelle Santos (Grade 11 STEM)
      [20,$sy,'Grade 11','STEM','Oral Communication',95,94,96,95,95,'Passed'],
      [20,$sy,'Grade 11','STEM','Reading and Writing',93,94,92,95,94,'Passed'],
      [20,$sy,'Grade 11','STEM','Komunikasyon at Pananaliksik',91,92,90,93,92,'Passed'],
      [20,$sy,'Grade 11','STEM','21st Century Literature',94,95,93,96,95,'Passed'],
      [20,$sy,'Grade 11','STEM','General Mathematics',89,90,91,92,91,'Passed'],
      [20,$sy,'Grade 11','STEM','Statistics and Probability',87,88,89,90,89,'Passed'],
      [20,$sy,'Grade 11','STEM','Earth and Life Science',92,93,91,94,93,'Passed'],
      [20,$sy,'Grade 11','STEM','Physical Science',88,89,87,90,89,'Passed'],
    ];
    foreach ($gradeData as $g) $ins->execute($g);
    out('✔ Seeded <strong>' . count($gradeData) . ' grade rows</strong>.', 'ok');
} else {
    out("ℹ Grades already exist ($existingGr rows) — skipping.", 'info');
}

// ── Seed Announcements ───────────────────────────────────────────────────────
$existingAnn = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
if ($existingAnn == 0) {
    $ins = $pdo->prepare("INSERT INTO announcements (title,body,audience,posted_at) VALUES (?,?,?,?)");
    $ins->execute(['Welcome to S.Y. 2025–2026!', 'Minanga Integrated School welcomes all students to the new school year. Classes begin on June 3, 2025.', 'all', '2025-06-01 07:00:00']);
    $ins->execute(['Card Giving — Q1', 'First quarter report cards will be distributed on October 10, 2025. Parents are required to attend.', 'student', '2025-09-25 08:00:00']);
    $ins->execute(['Teachers Meeting', 'Monthly faculty meeting scheduled on June 28, 2025 at 2:00 PM in the conference room.', 'teacher', '2025-06-20 10:00:00']);
    out('✔ Seeded 3 announcements.', 'ok');
} else {
    out("ℹ Announcements already exist — skipping.", 'info');
}

out('<hr><strong style="color:#198754;">✔ Setup complete! You can now <a href="' . BASE_URL . '/">open the system</a>.</strong>', 'ok');
?>
</body>
</html>
