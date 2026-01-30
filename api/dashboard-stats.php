<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/ParentModel.php';

try {
    $studentModel = new Student();
    $teacherModel = new Teacher();
    $attendanceModel = new Attendance();
    $parentModel = new ParentModel();
    
    // Get current stats
    $studentCount = count($studentModel->findAll());
    $teacherCount = count($teacherModel->findAll());
    $parentCount = count($parentModel->findAll());
    
    // Get attendance rate
    $attendanceStats = $attendanceModel->getCurrentMonthAttendanceRate();
    $attendanceRate = $attendanceStats['attendance_rate'] ?? 0;
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'students' => $studentCount,
        'teachers' => $teacherCount,
        'parents' => $parentCount,
        'attendance_rate' => round($attendanceRate, 1),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch dashboard statistics: ' . $e->getMessage()
    ]);
}
?>