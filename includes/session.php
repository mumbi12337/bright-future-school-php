<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define project root directory
$projectRoot = dirname(__DIR__); // This gives us the main project directory

// Include database connection
require_once $projectRoot . '/includes/db.php';

// Include necessary model files
require_once $projectRoot . '/models/User.php';

// Initialize authentication system
class SimpleAuth {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user info
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Check if current user has specific role
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $_SESSION['user_role'] === $role;
    }
    
    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        return $this->hasRole('admin') || $this->hasRole('ADMIN');
    }
    
    /**
     * Check if current user is teacher
     */
    public function isTeacher() {
        return $this->hasRole('teacher') || $this->hasRole('TEACHER');
    }
    
    /**
     * Check if current user is parent
     */
    public function isParent() {
        return $this->hasRole('parent') || $this->hasRole('PARENT');
    }
}

// Create global auth instance
$auth = new SimpleAuth();
$currentUser = null;
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
}
?>