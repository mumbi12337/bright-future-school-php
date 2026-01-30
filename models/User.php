<?php
require_once 'BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create a new user
     */
    public function createUser($email, $password, $role) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role
        ];
        
        return $this->create($data);
    }
}
?>