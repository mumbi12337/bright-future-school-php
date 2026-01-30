<?php
require_once '../includes/Auth.php';
require_once '../models/Student.php';
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
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || strpos($_SERVER['REQUEST_URI'], '/api/students/list') !== false) {
    $auth->requireLogin();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        listStudents();
        break;
    case 'get':
        if ($id) {
            getStudent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Student ID is required']);
        }
        break;
    case 'create':
        createStudent();
        break;
    case 'update':
        if ($id) {
            updateStudent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Student ID is required']);
        }
        break;
    case 'delete':
        if ($id) {
            deleteStudent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Student ID is required']);
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function listStudents() {
    try {
        $studentModel = new Student();
        $students = $studentModel->getAllStudentsWithParents();
        
        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch students: ' . $e->getMessage()]);
    }
}

function getStudent($id) {
    try {
        $studentModel = new Student();
        $student = $studentModel->getStudentWithParent($id);
        
        if ($student) {
            echo json_encode([
                'success' => true,
                'data' => $student
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch student: ' . $e->getMessage()]);
    }
}

function createStudent() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['first_name']) || !isset($input['last_name']) || 
        !isset($input['date_of_birth']) || !isset($input['grade'])) {
        http_response_code(400);
        echo json_encode(['error' => 'First name, last name, date of birth, and grade are required']);
        return;
    }
    
    try {
        $studentModel = new Student();
        $parentId = $input['parent_id'] ?? null;
        
        $studentId = $studentModel->createStudent(
            $input['first_name'],
            $input['last_name'],
            $input['date_of_birth'],
            $input['grade'],
            $parentId,
            $input['photo_url'] ?? null
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Student created successfully',
            'id' => $studentId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create student: ' . $e->getMessage()]);
    }
}

function updateStudent($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $studentModel = new Student();
        $student = $studentModel->findById($id);
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
            return;
        }
        
        // Prepare update data (only include fields that are provided)
        $updateData = [];
        if (isset($input['first_name'])) $updateData['first_name'] = $input['first_name'];
        if (isset($input['last_name'])) $updateData['last_name'] = $input['last_name'];
        if (isset($input['date_of_birth'])) $updateData['date_of_birth'] = $input['date_of_birth'];
        if (isset($input['grade'])) $updateData['grade'] = $input['grade'];
        if (isset($input['parent_id'])) $updateData['parent_id'] = $input['parent_id'];
        if (isset($input['photo_url'])) $updateData['photo_url'] = $input['photo_url'];
        
        // If parent_id is being updated, also update parent-related fields
        if (isset($input['parent_id'])) {
            $parentModel = new ParentModel();
            $parent = $parentModel->findById($input['parent_id']);
            
            if ($parent) {
                $updateData['parent_name'] = $parent['first_name'] . ' ' . $parent['last_name'];
                $updateData['parent_email'] = $parent['email'];
                $updateData['parent_phone'] = $parent['phone'];
            }
        }
        
        $result = $studentModel->update($id, $updateData);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Student updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update student']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update student: ' . $e->getMessage()]);
    }
}

function deleteStudent($id) {
    try {
        $studentModel = new Student();
        $student = $studentModel->findById($id);
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
            return;
        }
        
        // Start transaction to ensure data integrity
        $pdo = $studentModel->getPdo();
        $pdo->beginTransaction();
        
        try {
            // Check for related attendance records
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ?");
            $stmt->execute([$id]);
            $attendanceCount = $stmt->fetchColumn();
            
            // Check for related marks
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM marks WHERE student_id = ?");
            $stmt->execute([$id]);
            $marksCount = $stmt->fetchColumn();
            
            // Check for related student fees
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM student_fees WHERE student_id = ?");
            $stmt->execute([$id]);
            $feesCount = $stmt->fetchColumn();
            
            // If student has related records, we need to handle them
            if ($attendanceCount > 0 || $marksCount > 0 || $feesCount > 0) {
                // Delete related marks first
                if ($marksCount > 0) {
                    $stmt = $pdo->prepare("DELETE FROM marks WHERE student_id = ?");
                    $stmt->execute([$id]);
                }
                
                // Delete related attendance records
                if ($attendanceCount > 0) {
                    $stmt = $pdo->prepare("DELETE FROM attendance WHERE student_id = ?");
                    $stmt->execute([$id]);
                }
                
                // Delete related student fees records
                if ($feesCount > 0) {
                    $stmt = $pdo->prepare("DELETE FROM student_fees WHERE student_id = ?");
                    $stmt->execute([$id]);
                }
            }
            
            // Now delete the student
            $result = $studentModel->delete($id);
            
            if ($result) {
                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Student deleted successfully'
                ]);
            } else {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete student']);
            }
        } catch (Exception $innerException) {
            $pdo->rollBack();
            throw $innerException;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete student: ' . $e->getMessage()]);
    }
}
?>