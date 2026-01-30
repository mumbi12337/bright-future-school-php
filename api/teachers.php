<?php
require_once '../includes/Auth.php';
require_once '../models/Teacher.php';
require_once '../models/User.php';
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

// Authentication check - all routes except GET require authentication
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $auth->requireLogin();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        listTeachers();
        break;
    case 'get':
        if ($id) {
            getTeacher($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Teacher ID is required']);
        }
        break;
    case 'create':
        createTeacher();
        break;
    case 'update':
        if ($id) {
            updateTeacher($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Teacher ID is required']);
        }
        break;
    case 'delete':
        if ($id) {
            deleteTeacher($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Teacher ID is required']);
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function listTeachers() {
    try {
        $teacherModel = new Teacher();
        $teachers = $teacherModel->findAllWithGrade();
        
        echo json_encode([
            'success' => true,
            'data' => $teachers
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch teachers: ' . $e->getMessage()]);
    }
}

function getTeacher($id) {
    try {
        $teacherModel = new Teacher();
        $teacher = $teacherModel->getTeacherWithSubjects($id);
        
        if ($teacher) {
            echo json_encode([
                'success' => true,
                'data' => $teacher
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Teacher not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch teacher: ' . $e->getMessage()]);
    }
}

function createTeacher() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['first_name']) || !isset($input['last_name']) || 
        !isset($input['email']) || !isset($input['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'First name, last name, email, and phone are required']);
        return;
    }
    
    try {
        $teacherModel = new Teacher();
        $userModel = new User();
        
        // Check if teacher with this email already exists
        $existingTeacher = $teacherModel->findBy(['email' => $input['email']]);
        if (!empty($existingTeacher)) {
            http_response_code(400);
            echo json_encode(['error' => 'A teacher with this email already exists']);
            return;
        }
        
        // Check if user with this email already exists
        $existingUser = $userModel->findByEmail($input['email']);
        if ($existingUser) {
            http_response_code(400);
            echo json_encode(['error' => 'A user account with this email already exists']);
            return;
        }
        
        // Start transaction to ensure data consistency
        $pdo = $teacherModel->getPdo();
        $pdo->beginTransaction();
        
        try {
            // Create teacher record
            $teacherId = $teacherModel->createTeacher(
                $input['first_name'],
                $input['last_name'],
                $input['email'],
                $input['phone'],
                $input['subjects'] ?? '',
                $input['grade'] ?? null
            );
            
            // Create corresponding user account with default password
            $defaultPassword = 'teacher123'; // Default password for new teachers
            $userId = $userModel->createUser($input['email'], $defaultPassword, 'TEACHER');
            
            // Commit transaction
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Teacher created successfully with user account',
                'teacher_id' => $teacherId,
                'user_id' => $userId,
                'default_password' => $defaultPassword
            ]);
        } catch (Exception $innerException) {
            $pdo->rollBack();
            throw $innerException;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create teacher: ' . $e->getMessage()]);
    }
}

function updateTeacher($id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $teacherModel = new Teacher();
        $teacher = $teacherModel->findById($id);
        
        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['error' => 'Teacher not found']);
            return;
        }
        
        // Prepare update data (only include fields that are provided)
        $updateData = [];
        if (isset($input['first_name'])) $updateData['first_name'] = $input['first_name'];
        if (isset($input['last_name'])) $updateData['last_name'] = $input['last_name'];
        if (isset($input['email'])) $updateData['email'] = $input['email'];
        if (isset($input['phone'])) $updateData['phone'] = $input['phone'];
        if (isset($input['subjects'])) $updateData['subjects'] = $input['subjects'];
        if (isset($input['grade'])) $updateData['grade'] = $input['grade'];
        
        $result = $teacherModel->update($id, $updateData);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Teacher updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update teacher']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update teacher: ' . $e->getMessage()]);
    }
}

function deleteTeacher($id) {
    try {
        $teacherModel = new Teacher();
        $userModel = new User();
        $teacher = $teacherModel->findById($id);

        if (!$teacher) {
            http_response_code(404);
            echo json_encode(['error' => 'Teacher not found']);
            return;
        }

        // Start transaction to ensure data integrity
        $pdo = $teacherModel->getPdo();
        $pdo->beginTransaction();
        
        try {
            // Delete teacher record
            $teacherResult = $teacherModel->delete($id);
            
            if (!$teacherResult) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete teacher record']);
                return;
            }
            
            // Delete corresponding user account
            $user = $userModel->findByEmail($teacher['email']);
            if ($user) {
                $userResult = $userModel->delete($user['id']);
                if (!$userResult) {
                    $pdo->rollBack();
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to delete user account']);
                    return;
                }
            }

            // Commit transaction
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Teacher and associated user account deleted successfully'
            ]);
        } catch (Exception $innerException) {
            $pdo->rollBack();
            throw $innerException;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete teacher: ' . $e->getMessage()]);
    }
}