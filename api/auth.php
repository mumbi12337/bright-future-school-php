<?php
require_once '../includes/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        checkAuth();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleLogin() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }
    
    $auth = new Auth();
    $result = $auth->login($input['email'], $input['password']);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $result['id'],
                'email' => $result['email'],
                'role' => $result['role']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
}

function handleLogout() {
    $auth = new Auth();
    $result = $auth->logout();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error logging out']);
    }
}

function checkAuth() {
    $auth = new Auth();
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo json_encode([
            'authenticated' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}
?>