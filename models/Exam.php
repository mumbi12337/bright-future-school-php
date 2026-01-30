<?php
require_once 'BaseModel.php';

class Exam extends BaseModel {
    protected $table = 'exams';
    
    /**
     * Get exams by grade
     */
    public function getByGradeId($gradeId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            WHERE e.grade_id = ?
            ORDER BY e.date DESC
        ");
        $stmt->execute([$gradeId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get exams by subject
     */
    public function getBySubject($subject) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            WHERE e.subject = ?
            ORDER BY e.date DESC
        ");
        $stmt->execute([$subject]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get exams by date range
     */
    public function getByDateRange($startDate, $endDate) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            WHERE e.date BETWEEN ? AND ?
            ORDER BY e.date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get exam with grade information
     */
    public function getExamWithGrade($examId) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            WHERE e.id = ?
        ");
        $stmt->execute([$examId]);
        return $stmt->fetch();
    }
    
    /**
     * Create a new exam
     */
    public function createExam($title, $gradeId, $subject, $date) {
        $data = [
            'title' => $title,
            'grade_id' => $gradeId,
            'subject' => $subject,
            'date' => $date
        ];
        
        return $this->create($data);
    }
    
    /**
     * Get all exams with grade information
     */
    public function findAllWithGrade() {
        $stmt = $this->pdo->prepare(
            "SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            ORDER BY e.date DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get exams by grade name
     */
    public function getByGradeName($gradeName) {
        $stmt = $this->pdo->prepare(        
            "SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            WHERE g.name = ?
            ORDER BY e.date DESC"
        );
        $stmt->execute([$gradeName]);       
        return $stmt->fetchAll();
    }
        
    /**
     * Get total exam count
     */
    public function getTotalExamCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_exams FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetch()['total_exams'] ?? 0;
    }
        
    /**
     * Get recent exams
     */
    public function getRecentExams($limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT e.*, g.name as grade_name
            FROM {$this->table} e
            JOIN grades g ON e.grade_id = g.id
            ORDER BY e.date DESC
            LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>