<?php
require_once 'BaseModel.php';

class StudentFee extends BaseModel {
    protected $table = 'student_fees';
    
    /**
     * Get student's fee status for current academic year
     */
    public function getStudentFeeStatus($studentId, $academicYear = null) {
        if (!$academicYear) {
            // Get student's current academic year
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT sf.*, 
                   s.first_name, 
                   s.last_name, 
                   s.grade,
                   g.name as grade_name
            FROM {$this->table} sf
            JOIN students s ON sf.student_id = s.id
            LEFT JOIN grades g ON s.grade = g.name
            WHERE sf.student_id = ? AND sf.academic_year = ?
            ORDER BY sf.term
        ");
        $stmt->execute([$studentId, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all unpaid fees for a student
     */
    public function getUnpaidFees($studentId, $academicYear = null) {
        if (!$academicYear) {
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT sf.*, g.name as grade_name
            FROM {$this->table} sf
            JOIN students s ON sf.student_id = s.id
            LEFT JOIN grades g ON s.grade = g.name
            WHERE sf.student_id = ? AND sf.academic_year = ? AND sf.paid = FALSE
            ORDER BY sf.term
        ");
        $stmt->execute([$studentId, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark fee as paid
     */
    public function markAsPaid($studentId, $term, $academicYear = null) {
        if (!$academicYear) {
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET paid = TRUE, payment_date = NOW()
            WHERE student_id = ? AND term = ? AND academic_year = ?
        ");
        return $stmt->execute([$studentId, $term, $academicYear]);
    }
    
    /**
     * Check if student has paid all fees for current grade
     */
    public function hasPaidAllFees($studentId, $academicYear = null) {
        if (!$academicYear) {
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total_fees,
                   SUM(CASE WHEN paid = TRUE THEN 1 ELSE 0 END) as paid_fees
            FROM {$this->table}
            WHERE student_id = ? AND academic_year = ?
        ");
        $stmt->execute([$studentId, $academicYear]);
        $result = $stmt->fetch();
        
        // Debug logging
        error_log("hasPaidAllFees check - Student ID: {$studentId}, Academic Year: {$academicYear}, Total: {$result['total_fees']}, Paid: {$result['paid_fees']}");
        
        return $result && $result['total_fees'] > 0 && $result['paid_fees'] == $result['total_fees'];
    }
    
    /**
     * Get students ready for grade promotion (all fees paid)
     */
    public function getStudentsReadyForPromotion() {
        $currentYear = date('Y');
        
        $stmt = $this->pdo->prepare("
            SELECT s.id, s.first_name, s.last_name, s.grade, s.academic_year,
                   COUNT(sf.id) as total_fees,
                   SUM(CASE WHEN sf.paid = TRUE THEN 1 ELSE 0 END) as paid_fees
            FROM students s
            JOIN {$this->table} sf ON s.id = sf.student_id AND s.academic_year = sf.academic_year
            WHERE s.academic_year = ?
            GROUP BY s.id, s.first_name, s.last_name, s.grade, s.academic_year
            HAVING COUNT(sf.id) > 0 AND SUM(CASE WHEN sf.paid = TRUE THEN 1 ELSE 0 END) = COUNT(sf.id)
        ");
        $stmt->execute([$currentYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create fee records for a student for all terms
     */
    public function createStudentFees($studentId, $academicYear = null, $amount = 500.00) {
        if (!$academicYear) {
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        // Check if fees already exist
        $checkStmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM {$this->table} 
            WHERE student_id = ? AND academic_year = ?
        ");
        $checkStmt->execute([$studentId, $academicYear]);
        $existingCount = $checkStmt->fetchColumn();
        
        if ($existingCount > 0) {
            return false; // Fees already exist
        }
        
        // Create fees for all 3 terms
        for ($term = 1; $term <= 3; $term++) {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (student_id, term, academic_year, amount, paid)
                VALUES (?, ?, ?, ?, FALSE)
            ");
            $stmt->execute([$studentId, $term, $academicYear, $amount]);
        }
        
        return true;
    }
    
    /**
     * Get fee summary by grade
     */
    public function getFeeSummaryByGrade($academicYear = null) {
        if (!$academicYear) {
            $academicYear = date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT g.name as grade_name,
                   COUNT(DISTINCT s.id) as total_students,
                   COUNT(sf.id) as total_fees,
                   SUM(CASE WHEN sf.paid = TRUE THEN 1 ELSE 0 END) as paid_fees,
                   SUM(CASE WHEN sf.paid = FALSE THEN 1 ELSE 0 END) as unpaid_fees,
                   SUM(CASE WHEN sf.paid = TRUE THEN sf.amount ELSE 0 END) as collected_amount,
                   SUM(CASE WHEN sf.paid = FALSE THEN sf.amount ELSE 0 END) as pending_amount
            FROM grades g
            LEFT JOIN students s ON g.name = s.grade AND s.academic_year = ?
            LEFT JOIN {$this->table} sf ON s.id = sf.student_id AND s.academic_year = sf.academic_year
            GROUP BY g.name
            ORDER BY g.name
        ");
        $stmt->execute([$academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get overdue fees (unpaid fees from previous terms)
     */
    public function getOverdueFees() {
        $currentYear = date('Y');
        
        $stmt = $this->pdo->prepare("
            SELECT s.first_name, s.last_name, s.grade,
                   sf.term, sf.amount, sf.created_at,
                   g.name as grade_name
            FROM students s
            JOIN {$this->table} sf ON s.id = sf.student_id AND s.academic_year = sf.academic_year
            LEFT JOIN grades g ON s.grade = g.name
            WHERE sf.paid = FALSE AND sf.academic_year = ?
            ORDER BY s.grade, s.last_name, s.first_name, sf.term
        ");
        $stmt->execute([$currentYear]);
        return $stmt->fetchAll();
    }
}
?>