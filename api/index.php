<?php
// API Index - List available endpoints
header('Content-Type: application/json');

$apiEndpoints = [
    'auth' => [
        'description' => 'Authentication endpoints',
        'methods' => ['POST', 'GET'],
        'actions' => [
            'login' => 'Authenticate user',
            'logout' => 'Log out user',
            'check' => 'Check authentication status'
        ],
        'url' => '/api/auth.php?action={action}'
    ],
    'students' => [
        'description' => 'Student management',
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'actions' => [
            'list' => 'Get all students',
            'get' => 'Get specific student (requires ID)',
            'create' => 'Create new student',
            'update' => 'Update student (requires ID)',
            'delete' => 'Delete student (requires ID)'
        ],
        'url' => '/api/students.php?action={action}&id={id}'
    ],
    'attendance' => [
        'description' => 'Attendance management',
        'methods' => ['GET', 'POST'],
        'actions' => [
            'list' => 'Get all attendance records',
            'by-student' => 'Get attendance for specific student (requires ID)',
            'by-date' => 'Get attendance for specific date (requires date)',
            'mark' => 'Mark attendance (POST)',
            'stats' => 'Get attendance stats for student (requires ID)'
        ],
        'url' => '/api/attendance.php?action={action}&id={id}'
    ],
    'test' => [
        'description' => 'Test API connectivity',
        'methods' => ['GET'],
        'url' => '/api/test.php'
    ]
];

echo json_encode([
    'api' => 'Bright Future School Management API',
    'version' => '1.0',
    'description' => 'Backend API for school management system',
    'endpoints' => $apiEndpoints,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>