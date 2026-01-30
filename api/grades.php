<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/Grade.php';

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

$gradeModel = new Grade();

try {
    switch ($method) {
        case 'GET':
            $grades = $gradeModel->getAllGradesWithCounts();
            echo json_encode(['success' => true, 'data' => $grades]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['name'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit;
                }
            }
            
            $result = $gradeModel->createGrade($input['name'], $input['notes'] ?? null);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to create grade']);
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
            $grade = $gradeModel->findById($id);
            
            if (!$grade) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Grade not found']);
                exit;
            }
            
            $data = [];
            if (isset($input['name'])) $data['name'] = $input['name'];
            if (isset($input['notes'])) $data['notes'] = $input['notes'];
            
            $result = $gradeModel->update($id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to update grade']);
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
            $grade = $gradeModel->findById($id);
            
            if (!$grade) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Grade not found']);
                exit;
            }
            
            $result = $gradeModel->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Grade deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to delete grade']);
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