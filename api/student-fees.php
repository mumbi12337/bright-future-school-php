<?php
require_once '../includes/Auth.php';
require_once '../models/Student.php';
require_once '../models/StudentFee.php';
require_once '../models/Grade.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

$studentModel = new Student();
$studentFeeModel = new StudentFee();
$gradeModel = new Grade();

try {
    switch ($action) {
        case 'pay':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['student_id']) || !isset($input['term'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID and term are required']);
                break;
            }
            
            $result = $studentModel->processFeePayment($input['student_id'], $input['term']);
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'status':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $studentId = $_GET['student_id'] ?? null;
            if (!$studentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                break;
            }
            
            $status = $studentFeeModel->getStudentFeeStatus($studentId);
            echo json_encode(['success' => true, 'data' => $status]);
            break;
            
        case 'unpaid':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $studentId = $_GET['student_id'] ?? null;
            $unpaid = $studentFeeModel->getUnpaidFees($studentId);
            echo json_encode(['success' => true, 'data' => $unpaid]);
            break;
            
        case 'summary':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $summary = $studentFeeModel->getFeeSummaryByGrade();
            echo json_encode(['success' => true, 'data' => $summary]);
            break;
            
        case 'overdue':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $overdue = $studentFeeModel->getOverdueFees();
            echo json_encode(['success' => true, 'data' => $overdue]);
            break;
            
        case 'ready_for_promotion':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $readyStudents = $studentFeeModel->getStudentsReadyForPromotion();
            echo json_encode(['success' => true, 'data' => $readyStudents]);
            break;
            
        case 'create_fees':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $studentId = $input['student_id'] ?? null;
            $academicYear = $input['academic_year'] ?? null;
            $amount = $input['amount'] ?? 500.00;
            
            if (!$studentId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                break;
            }
            
            $created = $studentFeeModel->createStudentFees($studentId, $academicYear, $amount);
            if ($created) {
                echo json_encode(['success' => true, 'message' => 'Fee records created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Fee records already exist for this student']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>