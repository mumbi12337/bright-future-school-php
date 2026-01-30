<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/Fee.php';

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

try {
    switch ($method) {
        case 'GET':
            $fees = $feeModel->findAll();
            echo json_encode(['success' => true, 'data' => $fees]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['grade', 'term', 'amount'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit;
                }
            }
            
            $result = $feeModel->create([
                'grade' => $input['grade'],
                'term' => $input['term'],
                'amount' => floatval($input['amount'])
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to create fee']);
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                exit;
            }
            
            $id = intval($input['id']);
            $fee = $feeModel->findById($id);
            
            if (!$fee) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Fee not found']);
                exit;
            }
            
            $data = [];
            if (isset($input['grade'])) $data['grade'] = $input['grade'];
            if (isset($input['term'])) $data['term'] = $input['term'];
            if (isset($input['amount'])) $data['amount'] = floatval($input['amount']);
            
            $result = $feeModel->update($id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to update fee']);
            }
            break;
            
        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                exit;
            }
            
            $id = intval($input['id']);
            $fee = $feeModel->findById($id);
            
            if (!$fee) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Fee not found']);
                exit;
            }
            
            $result = $feeModel->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Fee deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to delete fee']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>