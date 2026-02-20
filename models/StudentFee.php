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
            SELECT sf.id,
                   sf.student_id,
                   sf.fee_id,
                   sf.term,
                   sf.academic_year,
                   sf.amount,
                   CASE WHEN sf.paid = TRUE THEN 1 ELSE 0 END as paid,
                   sf.payment_date,
                   s.first_name, 
                   s.last_name, 
                   s.grade,
                   g.name as grade_name,
                   f.amount as fee_amount,
                   f.term as fee_term
            FROM {$this->table} sf
            JOIN students s ON sf.student_id = s.id
            LEFT JOIN grades g ON s.grade = g.name
            LEFT JOIN fees f ON sf.fee_id = f.id
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
            SELECT sf.*, f.amount as fee_amount, f.term as fee_term
            FROM {$this->table} sf
            LEFT JOIN fees f ON sf.fee_id = f.id
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
     * Check if student has paid all fees for current academic year
     * Counts distinct terms that are paid — immune to duplicate rows
     */
    public function hasPaidAllFees($studentId, $academicYear = null) {
        if (!$academicYear) {
            $studentStmt = $this->pdo->prepare("SELECT academic_year FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        // Count distinct terms that have at least one paid record
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT term) as paid_terms
            FROM {$this->table}
            WHERE student_id = ? AND academic_year = ? AND paid = TRUE
        ");
        $stmt->execute([$studentId, $academicYear]);
        $result = $stmt->fetch();
        
        // All 3 terms must be paid
        return (int)$result['paid_terms'] === 3;
    }
    
    /**
     * Delete all fee records for a student in a given academic year
     * Used before promotion to ensure a clean slate
     */
    public function clearFeesForYear($studentId, $academicYear) {
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->table}
            WHERE student_id = ? AND academic_year = ?
        ");
        return $stmt->execute([$studentId, $academicYear]);
    }
    
    /**
     * Create student fees for academic year
     */
    public function createStudentFees($studentId, $academicYear = null, $amount = 0.00, $gradeName = null) {
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
            return false; // Fees already exist for this academic year
        }
        
        // Get student's grade
        if ($gradeName) {
            // Use the provided grade name (for promotion scenarios)
            $studentGrade = $gradeName;
        } else {
            // Get current grade from student record
            $studentStmt = $this->pdo->prepare("SELECT grade FROM students WHERE id = ?");
            $studentStmt->execute([$studentId]);
            $student = $studentStmt->fetch();
            
            if (!$student) {
                return false;
            }
            
            $studentGrade = $student['grade'];
        }
        
        // Get fee structure for student's grade
        $gradeStmt = $this->pdo->prepare("SELECT id FROM grades WHERE name = ?");
        $gradeStmt->execute([$studentGrade]);
        $grade = $gradeStmt->fetch();
        
        if (!$grade) {
            return false;
        }
        
        // Create fees for all 3 terms
        for ($term = 1; $term <= 3; $term++) {
            // The fees table stores term as "Term 1", "Term 2", "Term 3" (VARCHAR)
            // while student_fees.term is an INTEGER (1, 2, 3)
            $termLabel = "Term {$term}";
            
            // Try to get specific fee for this term
            $feeStmt = $this->pdo->prepare("
                SELECT id, amount FROM fees 
                WHERE grade_id = ? AND term = ?
            ");
            $feeStmt->execute([$grade['id'], $termLabel]);
            $fee = $feeStmt->fetch();
            
            $feeId = $fee ? $fee['id'] : null;
            $feeAmount = $fee ? $fee['amount'] : $amount;
            
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (student_id, fee_id, term, academic_year, amount, paid)
                VALUES (?, ?, ?, ?, ?, FALSE)
            ");
            $stmt->execute([$studentId, $feeId, $term, $academicYear, $feeAmount]);
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
                   COUNT(sf.id) as total_fee_records,
                   SUM(CASE WHEN sf.paid = TRUE THEN sf.amount ELSE 0 END) as total_paid,
                   SUM(CASE WHEN sf.paid = FALSE THEN sf.amount ELSE 0 END) as total_pending,
                   COUNT(CASE WHEN sf.paid = TRUE THEN 1 END) as paid_count,
                   COUNT(CASE WHEN sf.paid = FALSE THEN 1 END) as pending_count
            FROM grades g
            LEFT JOIN students s ON g.name = s.grade
            LEFT JOIN {$this->table} sf ON s.id = sf.student_id AND sf.academic_year = ?
            WHERE s.id IS NOT NULL
            GROUP BY g.name
            ORDER BY g.name
        ");
        $stmt->execute([$academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get students ready for promotion (all fees paid)
     */
    public function getStudentsReadyForPromotion($academicYear = null) {
        if (!$academicYear) {
            $academicYear = date('Y');
        }
        
        $stmt = $this->pdo->prepare("
            SELECT s.*, 
                   COUNT(sf.id) as total_fees,
                   COUNT(CASE WHEN sf.paid = TRUE THEN 1 END) as paid_fees
            FROM students s
            LEFT JOIN {$this->table} sf ON s.id = sf.student_id AND sf.academic_year = ?
            WHERE s.academic_year = ?
            GROUP BY s.id
            HAVING COUNT(sf.id) = COUNT(CASE WHEN sf.paid = TRUE THEN 1 END) AND COUNT(sf.id) > 0
        ");
        $stmt->execute([$academicYear, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get payment history for student
     */
    public function getPaymentHistory($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT sf.*, f.term as fee_term, f.amount as fee_amount
            FROM {$this->table} sf
            LEFT JOIN fees f ON sf.fee_id = f.id
            WHERE sf.student_id = ?
            ORDER BY sf.academic_year DESC, sf.term
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}