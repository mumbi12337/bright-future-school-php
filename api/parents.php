<?php
require_once '../includes/Auth.php';
require_once '../models/ParentModel.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();

// Authentication check - all routes except GET require authentication
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $auth->requireLogin();
}

// Check if user is admin
if (!$auth->isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: Admin access required']);
    exit;
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        listParents();
        break;
    case 'get':
        if ($id) {
            getParent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parent ID is required']);
        }
        break;
    case 'create':
        createParent();
        break;
    case 'update':
        if ($id) {
            updateParent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parent ID is required']);
        }
        break;
    case 'delete':
        if ($id) {
            deleteParent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parent ID is required']);
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function listParents() {
    try {
        $parentModel = new ParentModel();
        $parents = $parentModel->findAll();
        
        echo json_encode([
            'success' => true,
            'data' => $parents
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch parents: ' . $e->getMessage()]);
    }
}

function getParent($id) {
    try {
        $parentModel = new ParentModel();
        $parent = $parentModel->findById($id);
        
        if ($parent) {
            echo json_encode([
                'success' => true,
                'data' => $parent
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Parent not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch parent: ' . $e->getMessage()]);
    }
}

function createParent() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['first_name']) || !isset($input['last_name']) || 
        !isset($input['email']) || !isset($input['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'First name, last name, email, and phone are required']);
        return;
    }
    
    try {
        $parentModel = new ParentModel();
        $parentId = $parentModel->createParent(
            $input['first_name'],
            $input['last_name'],
            $input['email'],
            $input['phone'],
            $input['address'] ?? null
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Parent created successfully',
            'id' => $parentId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create parent: ' . $e->getMessage()]);
    }
}

function updateParent($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $parentModel = new ParentModel();
        $parent = $parentModel->findById($id);
        
        if (!$parent) {
            http_response_code(404);
            echo json_encode(['error' => 'Parent not found']);
            return;
        }
        
        // Prepare update data (only include fields that are provided)
        $updateData = [];
        if (isset($input['first_name'])) $updateData['first_name'] = $input['first_name'];
        if (isset($input['last_name'])) $updateData['last_name'] = $input['last_name'];
        if (isset($input['email'])) $updateData['email'] = $input['email'];
        if (isset($input['phone'])) $updateData['phone'] = $input['phone'];
        if (isset($input['address'])) $updateData['address'] = $input['address'];
        
        $result = $parentModel->update($id, $updateData);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Parent updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update parent']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update parent: ' . $e->getMessage()]);
    }
}

function deleteParent($id) {
    try {
        $parentModel = new ParentModel();
        $parent = $parentModel->findById($id);

        if (!$parent) {
            http_response_code(404);
            echo json_encode(['error' => 'Parent not found']);
            return;
        }

        // Check if parent has linked students - we might want to prevent deletion if so
        $pdo = $parentModel->getPdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE parent_id = ?");
        $stmt->execute([$id]);
        $studentCount = $stmt->fetchColumn();
        
        if ($studentCount > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete parent with linked students. Remove student associations first.']);
            return;
        }
        
        // Start transaction to ensure data integrity
        $pdo->beginTransaction();
        
        try {
            $result = $parentModel->delete($id);
            
            if ($result) {
                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Parent deleted successfully'
                ]);
            } else {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete parent']);
            }
        } catch (Exception $innerException) {
            $pdo->rollBack();
            throw $innerException;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete parent: ' . $e->getMessage()]);
    }
}
?>