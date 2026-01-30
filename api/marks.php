<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/Mark.php';

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

$markModel = new Mark();

try {
    switch ($method) {
        case 'GET':
            $marks = $markModel->getAllWithDetails();
            echo json_encode(['success' => true, 'data' => $marks]);
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['exam_id', 'student_id', 'score'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    exit;
                }
            }
            
            $result = $markModel->recordMark($input['exam_id'], $input['student_id'], $input['score'], $input['grade'] ?? null);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to create mark']);
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
            $mark = $markModel->findById($id);
            
            if (!$mark) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Mark not found']);
                exit;
            }
            
            $data = [];
            if (isset($input['exam_id'])) $data['exam_id'] = $input['exam_id'];
            if (isset($input['student_id'])) $data['student_id'] = $input['student_id'];
            if (isset($input['score'])) $data['score'] = $input['score'];
            if (isset($input['grade'])) $data['grade'] = $input['grade'];
            
            $result = $markModel->update($id, $data);
            
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to update mark']);
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
            $mark = $markModel->findById($id);
            
            if (!$mark) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Mark not found']);
                exit;
            }
            
            $result = $markModel->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Mark deleted successfully']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Failed to delete mark']);
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