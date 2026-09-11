<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET — List all school years
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("
            SELECT sy.*,
                   (SELECT COUNT(*) FROM students s WHERE s.school_year = sy.year_label) AS student_count,
                   (SELECT COUNT(DISTINCT g.student_id) FROM grades g WHERE g.school_year = sy.year_label) AS graded_student_count
            FROM school_years sy
            ORDER BY sy.year_label DESC
        ");
        $schoolYears = $stmt->fetchAll();
        echo json_encode(['ok' => true, 'data' => $schoolYears]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to fetch school years: ' . $e->getMessage()]);
    }
    exit;
}

// POST — Add / Edit / Set Active / Delete
if ($method === 'POST') {
    $user = requireAuth('admin');
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? 'add';

    // ADD SCHOOL YEAR
    if ($action === 'add') {
        $yearLabel = trim($input['year_label'] ?? '');
        $startDate = !empty($input['start_date']) ? $input['start_date'] : null;
        $endDate   = !empty($input['end_date']) ? $input['end_date'] : null;
        $isActive  = !empty($input['is_active']) ? 1 : 0;

        if (!$yearLabel) {
            echo json_encode(['ok' => false, 'message' => 'School year label is required (e.g. 2026-2027).']);
            exit;
        }

        // Validate format (e.g. 2025-2026)
        if (!preg_match('/^\d{4}-\d{4}$/', $yearLabel)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid format. School year must be in format YYYY-YYYY (e.g. 2026-2027).']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            if ($isActive) {
                // If setting as active, deactivate all others
                $pdo->exec("UPDATE school_years SET is_active = 0");
            }

            $stmt = $pdo->prepare("INSERT INTO school_years (year_label, is_active, start_date, end_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$yearLabel, $isActive, $startDate, $endDate]);
            $newId = $pdo->lastInsertId();
            $pdo->commit();

            echo json_encode(['ok' => true, 'message' => "School Year '{$yearLabel}' added successfully.", 'id' => $newId]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($e->getCode() == 23000) {
                echo json_encode(['ok' => false, 'message' => "School Year '{$yearLabel}' already exists."]);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
        }
        exit;
    }

    // SET ACTIVE
    if ($action === 'set_active') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'message' => 'Invalid School Year ID.']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            $pdo->exec("UPDATE school_years SET is_active = 0");
            $stmt = $pdo->prepare("UPDATE school_years SET is_active = 1 WHERE id = ?");
            $stmt->execute([$id]);

            $fetch = $pdo->prepare("SELECT year_label FROM school_years WHERE id = ?");
            $fetch->execute([$id]);
            $label = $fetch->fetchColumn();

            $pdo->commit();
            echo json_encode(['ok' => true, 'message' => "School Year '{$label}' is now set as the active school year."]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['ok' => false, 'message' => 'Failed to activate school year.']);
        }
        exit;
    }

    // EDIT SCHOOL YEAR
    if ($action === 'edit' || $action === 'update') {
        $id        = (int)($input['id'] ?? 0);
        $yearLabel = trim($input['year_label'] ?? '');
        $startDate = !empty($input['start_date']) ? $input['start_date'] : null;
        $endDate   = !empty($input['end_date']) ? $input['end_date'] : null;
        $isActive  = isset($input['is_active']) ? (int)$input['is_active'] : null;

        if (!$id || !$yearLabel) {
            echo json_encode(['ok' => false, 'message' => 'ID and School Year label are required.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{4}$/', $yearLabel)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid format. School year must be in format YYYY-YYYY (e.g. 2026-2027).']);
            exit;
        }

        try {
            $pdo->beginTransaction();
            if ($isActive === 1) {
                $pdo->exec("UPDATE school_years SET is_active = 0");
                $stmt = $pdo->prepare("UPDATE school_years SET year_label = ?, is_active = 1, start_date = ?, end_date = ? WHERE id = ?");
                $stmt->execute([$yearLabel, $startDate, $endDate, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE school_years SET year_label = ?, start_date = ?, end_date = ? WHERE id = ?");
                $stmt->execute([$yearLabel, $startDate, $endDate, $id]);
            }
            $pdo->commit();
            echo json_encode(['ok' => true, 'message' => "School Year '{$yearLabel}' updated successfully."]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($e->getCode() == 23000) {
                echo json_encode(['ok' => false, 'message' => "Another School Year already has the label '{$yearLabel}'."]);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Failed to update school year.']);
            }
        }
        exit;
    }

    // DELETE SCHOOL YEAR
    if ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'message' => 'Invalid School Year ID.']);
            exit;
        }

        try {
            $chk = $pdo->prepare("SELECT year_label, is_active FROM school_years WHERE id = ?");
            $chk->execute([$id]);
            $sy = $chk->fetch();

            if (!$sy) {
                echo json_encode(['ok' => false, 'message' => 'School year not found.']);
                exit;
            }

            if ($sy['is_active']) {
                echo json_encode(['ok' => false, 'message' => 'Cannot delete the currently active school year. Please activate another school year first.']);
                exit;
            }

            // Check if referenced by students
            $sCountStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE school_year = ?");
            $sCountStmt->execute([$sy['year_label']]);
            $sCount = $sCountStmt->fetchColumn();

            if ($sCount > 0) {
                echo json_encode(['ok' => false, 'message' => "Cannot delete '{$sy['year_label']}' because {$sCount} student record(s) are enrolled under it."]);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM school_years WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['ok' => true, 'message' => "School Year '{$sy['year_label']}' deleted successfully."]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to delete school year: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['ok' => false, 'message' => 'Invalid action.']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
