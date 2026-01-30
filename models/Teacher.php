<?php
require_once 'BaseModel.php';

class Teacher extends BaseModel {
    protected $table = 'teachers';
    
    /**
     * Find teacher by email
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Get teacher with their subjects
     */
    public function getTeacherWithSubjects($teacherId) {
        $teacher = $this->findById($teacherId);
        
        if (!$teacher) {
            return null;
        }
        
        // Parse subjects if stored as comma-separated values
        if (!empty($teacher['subjects'])) {
            $teacher['subject_list'] = explode(',', $teacher['subjects']);
        } else {
            $teacher['subject_list'] = [];
        }
        
        return $teacher;
    }
    
    /**
     * Create a new teacher
     */
    public function createTeacher($firstName, $lastName, $email, $phone, $subjects, $grade = null) {
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'subjects' => $subjects,
            'grade' => $grade
        ];
        
        return $this->create($data);
    }
    
    /**
     * Get all teachers with their assigned grade information
     */
    public function findAllWithGrade() {
        $stmt = $this->pdo->prepare("SELECT t.*, g.name as grade_name FROM {$this->table} t LEFT JOIN grades g ON t.grade = g.name ORDER BY t.last_name, t.first_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>