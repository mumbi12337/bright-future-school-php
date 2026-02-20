<?php
require_once '../../includes/Auth.php';
require_once '../../includes/db.php';
require_once '../../models/Student.php';
require_once '../../models/StudentFee.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $studentModel = new Student();
    $studentFeeModel = new StudentFee();

    // Delete ALL student fee records (paid and unpaid) to start fresh
    // This clears duplicates and wrong amounts completely
    $deleteStmt = $pdo->prepare("DELETE FROM student_fees");
    $deleteStmt->execute();
    $deletedCount = $deleteStmt->rowCount();

    // Get all students and recreate their fees from the fee structure
    $students = $studentModel->findAll();

    $createdCount = 0;
    $skippedCount = 0;
    $errors = [];

    foreach ($students as $student) {
        try {
            $created = $studentFeeModel->createStudentFees($student['id']);
            if ($created) {
                $createdCount++;
            } else {
                $skippedCount++;
            }
        } catch (Exception $e) {
            $errors[] = "Student ID {$student['id']}: " . $e->getMessage();
        }
    }

    $message = "Reset complete. Cleared {$deletedCount} old fee record(s). "
             . "Recreated fees for {$createdCount} student(s) from the current fee structure.";

    if ($skippedCount > 0) {
        $message .= " Skipped {$skippedCount} student(s) (no matching fee structure for their grade).";
    }

    if (!empty($errors)) {
        $message .= " Encountered " . count($errors) . " error(s): " . implode(', ', $errors);
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'deleted'  => $deletedCount,
            'created'  => $createdCount,
            'skipped'  => $skippedCount,
            'errors'   => $errors
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error resetting student fees: ' . $e->getMessage()
    ]);
}
?>
