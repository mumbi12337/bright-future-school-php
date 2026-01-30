<?php
require_once '../includes/Auth.php';
require_once '../models/Attendance.php';
require_once '../models/Student.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = new Auth();
$auth->requireLogin();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        listAttendance();
        break;
    case 'get':
        if ($id) {
            getAttendance($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Attendance ID is required']);
        }
        break;
    case 'by-student':
        if ($id) {
            getAttendanceByStudent($id);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Student ID is required']);
        }
        break;
    case 'by-date':
        $date = $_GET['date'] ?? null;
        if ($date) {
            getAttendanceByDate($date);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Date is required']);
        }
        break;
    case 'mark':
        markAttendance();
        break;
    case 'stats':
        if ($id) {
            getAttendanceStats($id);
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

function listAttendance() {
    try {
        $attendanceModel = new Attendance();
        $attendanceRecords = $attendanceModel->findAll();
        
        echo json_encode([
            'success' => true,
            'data' => $attendanceRecords
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch attendance records: ' . $e->getMessage()]);
    }
}

function getAttendance($id) {
    try {
        $attendanceModel = new Attendance();
        $attendance = $attendanceModel->findById($id);
        
        if ($attendance) {
            echo json_encode([
                'success' => true,
                'data' => $attendance
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Attendance record not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch attendance record: ' . $e->getMessage()]);
    }
}

function getAttendanceByStudent($studentId) {
    try {
        $attendanceModel = new Attendance();
        $attendanceRecords = $attendanceModel->getByStudentId($studentId);
        
        echo json_encode([
            'success' => true,
            'data' => $attendanceRecords
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch attendance records: ' . $e->getMessage()]);
    }
}

function getAttendanceByDate($date) {
    try {
        $attendanceModel = new Attendance();
        $attendanceRecords = $attendanceModel->getByDate($date);
        
        echo json_encode([
            'success' => true,
            'data' => $attendanceRecords
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch attendance records: ' . $e->getMessage()]);
    }
}

function markAttendance() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($input['student_id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID and status are required']);
        return;
    }
    
    // Validate status
    $validStatuses = ['present', 'absent', 'late'];
    if (!in_array(strtolower($input['status']), $validStatuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status. Valid statuses are: ' . implode(', ', $validStatuses)]);
        return;
    }
    
    try {
        $attendanceModel = new Attendance();
        $date = $input['date'] ?? null;
        $teacherId = $input['teacher_id'] ?? null;
        
        $result = $attendanceModel->markAttendance(
            $input['student_id'],
            strtolower($input['status']),
            $date,
            $teacherId
        );
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Attendance marked successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to mark attendance']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark attendance: ' . $e->getMessage()]);
    }
}

function getAttendanceStats($studentId) {
    try {
        $attendanceModel = new Attendance();
        $stats = $attendanceModel->getAttendanceStats($studentId);
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch attendance stats: ' . $e->getMessage()]);
    }
}
?>