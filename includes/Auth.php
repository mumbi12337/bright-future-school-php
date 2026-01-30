<?php
// Use __DIR__ to get absolute path from this file's location
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/db.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Login a user
     */
    public function login($email, $password) {
        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        return true;
    }
    
    /**
     * Logout current user
     */
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }
    
    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        return $this->isLoggedIn() && (strtoupper($_SESSION['user_role'] ?? '') === 'ADMIN');
    }
    
    /**
     * Check if current user is teacher
     */
    public function isTeacher() {
        return $this->isLoggedIn() && (strtoupper($_SESSION['user_role'] ?? '') === 'TEACHER');
    }
    
    /**
     * Check if current user is parent
     */
    public function isParent() {
        return $this->isLoggedIn() && (strtoupper($_SESSION['user_role'] ?? '') === 'PARENT');
    }
    
    /**
     * Require login (redirect to login page if not logged in)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole($role) {
        $this->requireLogin();
        
        if (($_SESSION['user_role'] ?? '') !== $role) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Redirect user based on their role
     */
    public function redirectUser() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
        
        $role = $_SESSION['user_role'] ?? '';
        
        switch (strtoupper($role)) {
            case 'ADMIN':
                header('Location: admin/index.php');
                break;
            case 'TEACHER':
                header('Location: teacher/dashboard.php');
                break;
            case 'PARENT':
                header('Location: parent/parent.php');
                break;
            default:
                header('Location: index.php');
        }
        exit();
    }
    
    /**
     * Get user's full name
     */
    public function getUserName() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($userId);
        
        if ($user) {
            return ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
        }
        
        return null;
    }
}