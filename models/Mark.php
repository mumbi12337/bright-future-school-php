<?php
require_once 'BaseModel.php';

class Mark extends BaseModel {
    protected $table = 'marks';
    
    /**
     * Get marks by exam
     */
    public function getByExamId($examId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, s.first_name, s.last_name, e.title as exam_title
            FROM {$this->table} m
            JOIN students s ON m.student_id = s.id
            JOIN exams e ON m.exam_id = e.id
            WHERE m.exam_id = ?
            ORDER BY s.last_name, s.first_name
        ");
        $stmt->execute([$examId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get marks by student
     */
    public function getByStudentId($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, e.title as exam_title, e.subject, e.date as exam_date, g.name as grade_name
            FROM {$this->table} m
            JOIN exams e ON m.exam_id = e.id
            JOIN grades g ON e.grade_id = g.id
            WHERE m.student_id = ?
            ORDER BY e.date DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get marks by student and exam
     */
    public function getByStudentIdAndExamId($studentId, $examId) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, s.first_name, s.last_name, e.title as exam_title, e.subject
            FROM {$this->table} m
            JOIN students s ON m.student_id = s.id
            JOIN exams e ON m.exam_id = e.id
            WHERE m.student_id = ? AND m.exam_id = ?
        ");
        $stmt->execute([$studentId, $examId]);
        return $stmt->fetch();
    }
    
    /**
     * Calculate average marks for a student
     */
    public function getAverageMarksByStudentId($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(score) as average_score, 
                   COUNT(*) as total_exams,
                   (SELECT first_name || ' ' || last_name FROM students WHERE id = ?) as student_name
            FROM {$this->table}
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId, $studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get marks by subject for a student
     */
    public function getByStudentIdAndSubject($studentId, $subject) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, e.title as exam_title, e.date as exam_date
            FROM {$this->table} m
            JOIN exams e ON m.exam_id = e.id
            WHERE m.student_id = ? AND e.subject = ?
            ORDER BY e.date DESC
        ");
        $stmt->execute([$studentId, $subject]);
        return $stmt->fetchAll();
    }
    
    /**
     * Record or update a mark
     */
    public function recordMark($examId, $studentId, $score, $grade = null) {
        // If no grade is provided, calculate it based on the score
        if (!$grade) {
            if ($score >= 95) $grade = 'A+';
            elseif ($score >= 90) $grade = 'A';
            elseif ($score >= 85) $grade = 'B+';
            elseif ($score >= 80) $grade = 'B';
            elseif ($score >= 75) $grade = 'C+';
            elseif ($score >= 70) $grade = 'C';
            elseif ($score >= 60) $grade = 'D';
            else $grade = 'F';
        }
        
        // Check if mark already exists for this exam and student
        $existing = $this->getByStudentIdAndExamId($studentId, $examId);
        
        $data = [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'score' => $score,
            'grade' => $grade
        ];
        
        if ($existing) {
            // Update existing mark
            return $this->update($existing['id'], $data);
        } else {
            // Create new mark
            return $this->create($data);
        }
    }
    
    /**
     * Get all marks with student and exam details
     */
    public function getAllWithDetails() {
        $stmt = $this->pdo->prepare(
            "SELECT m.*, 
                   CONCAT(s.first_name, ' ', s.last_name) as student_name,
                   e.title as exam_title, 
                   e.subject,
                   e.date as exam_date
            FROM {$this->table} m
            JOIN students s ON m.student_id = s.id
            JOIN exams e ON m.exam_id = e.id
            ORDER BY m.created_at DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Find mark by exam and student
     */
    public function findByExamAndStudent($examId, $studentId) {
        return $this->getByStudentIdAndExamId($studentId, $examId);
    }
    
    /**
     * Get marks for exams taught by a specific teacher
     */
    public function getByTeacher($teacherId) {
        // This would typically join with exams table where teacher_id = ?
        // For now, return all marks with exam details
        return $this->getAllWithDetails();
    }
}
?>