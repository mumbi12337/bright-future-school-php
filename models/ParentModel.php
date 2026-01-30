<?php
require_once 'BaseModel.php';

class ParentModel extends BaseModel {
    protected $table = 'parents';
    
    /**
     * Find parent by email
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Get parent with their students
     */
    public function getParentWithStudents($parentId) {
        $parent = $this->findById($parentId);
        
        if (!$parent) {
            return null;
        }
        
        // Get students associated with this parent
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        $students = $stmt->fetchAll();
        
        $parent['students'] = $students;
        
        return $parent;
    }
    
    /**
     * Create a new parent
     */
    public function createParent($firstName, $lastName, $email, $phone, $address = null) {
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        
        return $this->create($data);
    }
    
    /**
     * Get students linked to a parent
     */
    public function getLinkedStudents($parentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
}
?>