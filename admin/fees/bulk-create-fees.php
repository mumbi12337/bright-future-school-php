<?php
require_once '../../includes/Auth.php';
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
    
    // Get all students
    $students = $studentModel->findAll();
    
    if (empty($students)) {
        echo json_encode([
            'success' => false,
            'message' => 'No students found in the system'
        ]);
        exit;
    }
    
    $createdCount = 0;
    $skippedCount = 0;
    $errors = [];
    
    foreach ($students as $student) {
        try {
            // Check if student already has fees for current academic year
            $existingFees = $studentFeeModel->getStudentFeeStatus($student['id']);
            
            if (empty($existingFees)) {
                // Create fees for this student
                $created = $studentFeeModel->createStudentFees($student['id'], null, 500.00);
                if ($created) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                $skippedCount++;
            }
        } catch (Exception $e) {
            $errors[] = "Student ID {$student['id']}: " . $e->getMessage();
        }
    }
    
    $message = "Successfully created fee records for {$createdCount} students.";
    if ($skippedCount > 0) {
        $message .= " Skipped {$skippedCount} students (already had fees or errors).";
    }
    
    if (!empty($errors)) {
        $message .= " Encountered " . count($errors) . " errors.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => [
            'created' => $createdCount,
            'skipped' => $skippedCount,
            'errors' => $errors
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error creating student fees: ' . $e->getMessage()
    ]);
}
?>