<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../models/Application.php';
require_once '../models/Grade.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Initialize application model
$appModel = new Application($pdo);
$gradeModel = new Grade($pdo);

// Route based on method and path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$resource = isset($segments[count($segments) - 1]) ? $segments[count($segments) - 1] : '';

try {
    switch ($method) {
        case 'GET':
            if ($resource === 'applications') {
                // Get all applications (admin only)
                if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
                
                $applications = $appModel->findAll();
                echo json_encode(['success' => true, 'data' => $applications]);
            } else {
                // Get specific application
                $id = $resource;
                if (!is_numeric($id)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid application ID']);
                    exit;
                }
                
                $application = $appModel->findById($id);
                if ($application) {
                    echo json_encode(['success' => true, 'data' => $application]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Application not found']);
                }
            }
            break;
            
        case 'POST':
            // Create new application
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }
            
            // Validate required fields
            $requiredFields = ['firstName', 'lastName', 'dateOfBirth', 'gradeId', 'parentFirstName', 'parentLastName', 'parentEmail', 'parentPhone'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Missing required field: $field"]);
                    exit;
                }
            }
            
            // Get grade name from grade ID
            $grade = $gradeModel->findById($input['gradeId']);
            $gradeName = $grade ? $grade['name'] : $input['gradeId'];
            
            // Create application
            $result = $appModel->create([
                'student_first_name' => $input['firstName'],
                'student_last_name' => $input['lastName'],
                'student_date_of_birth' => $input['dateOfBirth'],
                'student_grade' => $gradeName, // Store the grade name, not ID
                'parent_first_name' => $input['parentFirstName'],
                'parent_last_name' => $input['parentLastName'],
                'parent_email' => $input['parentEmail'],
                'parent_phone' => $input['parentPhone'],
                'parent_address' => $input['parentAddress'] ?? null,
                'emergency_contact_name' => $input['emergencyContactName'] ?? null,
                'emergency_contact_phone' => $input['emergencyContactPhone'] ?? null,
                'previous_school' => $input['previousSchool'] ?? null,
                'medical_conditions' => $input['medicalConditions'] ?? null,
                'additional_notes' => $input['additionalNotes'] ?? null,
                'status' => 'PENDING' // Default status
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Application submitted successfully', 'id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create application']);
            }
            break;
            
        case 'PUT':
            // Update application (admin only)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }
            
            $id = $resource;
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid application ID']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }
            
            $result = $appModel->update($id, $input);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Application updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update application']);
            }
            break;
            
        case 'DELETE':
            // Delete application (admin only)
            if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }
            
            $id = $resource;
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid application ID']);
                exit;
            }
            
            $result = $appModel->delete($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete application']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>