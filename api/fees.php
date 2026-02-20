<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/Fee.php';
require_once '../models/StudentFee.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$feeModel = new Fee();
$studentFeeModel = new StudentFee();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['student_id'])) {
                // Get student fee status
                $studentId = $_GET['student_id'];
                $academicYear = $_GET['academic_year'] ?? null;
                $fees = $studentFeeModel->getStudentFeeStatus($studentId, $academicYear);
                echo json_encode(['success' => true, 'data' => $fees]);
            } else {
                // Get all fee structure
                $fees = $feeModel->findAllWithGradeNames();
                echo json_encode(['success' => true, 'data' => $fees]);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['action'])) {
                switch ($input['action']) {
                    case 'pay_fee':
                        // Process fee payment
                        if (!isset($input['student_id']) || !isset($input['term'])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Student ID and term are required']);
                            break;
                        }
                        
                        $result = $studentFeeModel->markAsPaid($input['student_id'], $input['term']);
                        if ($result) {
                            echo json_encode(['success' => true, 'message' => 'Fee marked as paid']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'message' => 'Failed to mark fee as paid']);
                        }
                        break;
                        
                    case 'create_student_fees':
                        // Create fees for student
                        if (!isset($input['student_id'])) {
                            http_response_code(400);
                            echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                            break;
                        }
                        
                        $amount = $input['amount'] ?? 500.00;
                        $academicYear = $input['academic_year'] ?? null;
                        
                        $created = $studentFeeModel->createStudentFees($input['student_id'], $academicYear, $amount);
                        if ($created) {
                            echo json_encode(['success' => true, 'message' => 'Student fees created successfully']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'message' => 'Failed to create student fees']);
                        }
                        break;
                        
                    default:
                        // Create/update fee structure
                        $required_fields = ['grade_id', 'term', 'amount'];
                        foreach ($required_fields as $field) {
                            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                                http_response_code(400);
                                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                                exit;
                            }
                        }
                        
                        $result = $feeModel->createOrUpdateFee(
                            $input['grade_id'],
                            floatval($input['amount']),
                            $input['term']
                        );
                        
                        if ($result) {
                            echo json_encode(['success' => true, 'message' => 'Fee saved successfully']);
                        } else {
                            http_response_code(500);
                            echo json_encode(['success' => false, 'message' => 'Failed to save fee']);
                        }
                }
            } else {
                // Create new fee
                $required_fields = ['grade_id', 'term', 'amount'];
                foreach ($required_fields as $field) {
                    if (!isset($input[$field]) || empty(trim($input[$field]))) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                        exit;
                    }
                }
                
                $result = $feeModel->create([
                    'grade_id' => $input['grade_id'],
                    'term' => $input['term'],
                    'amount' => floatval($input['amount'])
                ]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Fee created successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to create fee']);
                }
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fee ID is required']);
                break;
            }
            
            $result = $feeModel->update($id, $input);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Fee updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update fee']);
            }
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fee ID is required']);
                break;
            }
            
            $result = $feeModel->delete($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Fee deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete fee']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}