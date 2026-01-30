<?php
require_once 'BaseModel.php';

class Grade extends BaseModel {
    protected $table = 'grades';
    
    /**
     * Find grade by name
     */
    public function findByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    /**
     * Get grade with associated exams and fees
     */
    public function getGradeWithDetails($gradeId) {
        $grade = $this->findById($gradeId);
        
        if (!$grade) {
            return null;
        }
        
        // Get exams for this grade
        $stmt = $this->pdo->prepare("SELECT * FROM exams WHERE grade_id = ? ORDER BY date DESC");
        $stmt->execute([$gradeId]);
        $grade['exams'] = $stmt->fetchAll();
        
        // Get fees for this grade
        $stmt = $this->pdo->prepare("SELECT * FROM fees WHERE grade_id = ? ORDER BY term");
        $stmt->execute([$gradeId]);
        $grade['fees'] = $stmt->fetchAll();
        
        return $grade;
    }
    
    /**
     * Get all grades with counts
     */
    public function getAllGradesWithCounts() {
        $stmt = $this->pdo->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM students WHERE grade = g.name) as student_count,
                   (SELECT COUNT(*) FROM exams WHERE grade_id = g.id) as exam_count,
                   (SELECT COUNT(*) FROM fees WHERE grade_id = g.id) as fee_count
            FROM {$this->table} g
            ORDER BY g.name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new grade
     */
    public function createGrade($name, $notes = null) {
        $data = [
            'name' => $name,
            'notes' => $notes
        ];
        
        return $this->create($data);
    }
}
?>