<?php
require_once 'BaseModel.php';

class Fee extends BaseModel {
    protected $table = 'fees';
    
    /**
     * Get fees by grade
     */
    public function getByGradeId($gradeId) {
        $stmt = $this->pdo->prepare("
            SELECT f.*, g.name as grade_name
            FROM {$this->table} f
            JOIN grades g ON f.grade_id = g.id
            WHERE f.grade_id = ?
            ORDER BY f.term
        ");
        $stmt->execute([$gradeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get fee by grade and term
     */
    public function getByGradeAndTerm($gradeId, $term) {
        $stmt = $this->pdo->prepare("
            SELECT f.*, g.name as grade_name
            FROM {$this->table} f
            JOIN grades g ON f.grade_id = g.id
            WHERE f.grade_id = ? AND f.term = ?
        ");
        $stmt->execute([$gradeId, $term]);
        return $stmt->fetch();
    }
    
    /**
     * Create or update fee
     */
    public function createOrUpdateFee($gradeId, $amount, $term) {
        // Check if fee already exists for this grade and term
        $existing = $this->getByGradeAndTerm($gradeId, $term);
        
        if ($existing) {
            // Update existing fee
            $data = [
                'amount' => $amount,
                'term' => $term
            ];
            return $this->update($existing['id'], $data);
        } else {
            // Create new fee
            return $this->create([
                'grade_id' => $gradeId,
                'amount' => $amount,
                'term' => $term
            ]);
        }
    }
    
    /**
     * Find fee by grade name and term
     */
    public function findByGradeNameAndTerm($gradeName, $term) {
        // First get the grade_id from the grades table
        $gradeStmt = $this->pdo->prepare("SELECT id FROM grades WHERE name = ?");
        $gradeStmt->execute([$gradeName]);
        $grade = $gradeStmt->fetch();
        
        if (!$grade) {
            return false;
        }
        
        // Then check if fee exists for that grade_id and term
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE grade_id = ? AND term = ?");
        $stmt->execute([$grade['id'], $term]);
        return $stmt->fetch();
    }
    
    /**
     * Create fee with grade name instead of grade_id
     */
    public function createByGradeName($gradeName, $term, $amount) {
        // Get the grade_id from the grades table
        $gradeStmt = $this->pdo->prepare("SELECT id FROM grades WHERE name = ?");
        $gradeStmt->execute([$gradeName]);
        $grade = $gradeStmt->fetch();
        
        if (!$grade) {
            throw new Exception("Grade '$gradeName' not found");
        }
        
        // Create the fee with the grade_id
        return $this->create([
            'grade_id' => $grade['id'],
            'term' => $term,
            'amount' => $amount
        ]);
    }
    
    /**
     * Get fees by student ID
     */
    public function getByStudentId($studentId) {
        // First get the student's grade
        $studentStmt = $this->pdo->prepare("SELECT grade FROM students WHERE id = ?");
        $studentStmt->execute([$studentId]);
        $student = $studentStmt->fetch();
        
        if (!$student) {
            return [];
        }
        
        // Get the grade_id
        $gradeStmt = $this->pdo->prepare("SELECT id FROM grades WHERE name = ?");
        $gradeStmt->execute([$student['grade']]);
        $grade = $gradeStmt->fetch();
        
        if (!$grade) {
            return [];
        }
        
        // Get fees for that grade
        return $this->getByGradeId($grade['id']);
    }
    
    /**
     * Get all fees with grade names
     */
    public function findAllWithGradeNames() {
        $stmt = $this->pdo->prepare("
            SELECT f.*, g.name as grade_name
            FROM {$this->table} f
            JOIN grades g ON f.grade_id = g.id
            ORDER BY g.name, f.term
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}