<?php
require_once 'BaseModel.php';
require_once 'Grade.php';
require_once 'ParentModel.php';
require_once 'StudentFee.php';

class Student extends BaseModel {
    protected $table = 'students';
    
    /**
     * Get student with their parent
     */
    public function getStudentWithParent($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, p.first_name as parent_first_name, p.last_name as parent_last_name, 
                   p.email as parent_email, p.phone as parent_phone, p.address as parent_address
            FROM {$this->table} s
            LEFT JOIN parents p ON s.parent_id = p.id
            WHERE s.id = ?
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all students with parent info
     */
    public function getAllStudentsWithParents() {
        $stmt = $this->pdo->prepare("
            SELECT s.*, p.first_name as parent_first_name, p.last_name as parent_last_name, 
                   p.email as parent_email, p.phone as parent_phone
            FROM {$this->table} s
            LEFT JOIN parents p ON s.parent_id = p.id
            ORDER BY s.last_name, s.first_name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get students by parent ID
     */
    public function getByParentId($parentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new student
     */
    public function createStudent($firstName, $lastName, $dateOfBirth, $grade, $parentId = null, $photoUrl = null) {
        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $dateOfBirth,
            'grade' => $grade,
            'parent_id' => $parentId,
            'photo_url' => $photoUrl
        ];
        
        // Set parent-related fields if parent exists
        if ($parentId) {
            $parentModel = new ParentModel();
            $parent = $parentModel->findById($parentId);
            
            if ($parent) {
                $data['parent_name'] = $parent['first_name'] . ' ' . $parent['last_name'];
                $data['parent_email'] = $parent['email'];
                $data['parent_phone'] = $parent['phone'];
            }
        }
        
        return $this->create($data);
    }
    
    /**
     * Get all students with their related grade and parent information
     */
    public function findAllWithDetails() {
        $stmt = $this->pdo->prepare("SELECT s.*, g.name as grade_name, g.id as grade_id, p.first_name as parent_first_name, p.last_name as parent_last_name, p.email as parent_email, p.phone as parent_phone FROM {$this->table} s LEFT JOIN grades g ON s.grade = g.name LEFT JOIN parents p ON s.parent_id = p.id ORDER BY s.last_name, s.first_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get students by grade level
     */
    public function getByGradeLevel($gradeLevel) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE grade = ? ORDER BY last_name, first_name");
        $stmt->execute([$gradeLevel]);
        return $stmt->fetchAll();
    }
    
    /**
     * Process fee payment and handle term/grade progression
     */
    public function processFeePayment($studentId, $term) {
        try {
            $this->pdo->beginTransaction();
            
            // Get current student info
            $student = $this->findById($studentId);
            if (!$student) {
                throw new Exception("Student not found");
            }
            
            // Debug logging
            error_log("processFeePayment - Student ID: {$studentId}, Term: {$term}, Current Grade: {$student['grade']}, Current Term: {$student['current_term']}, Academic Year: {$student['academic_year']}");
            
            $studentFeeModel = new StudentFee($this->pdo);
            
            // Mark fee as paid
            $studentFeeModel->markAsPaid($studentId, $term, $student['academic_year']);
            
            // Check if all fees are paid for current grade
            $allFeesPaid = $studentFeeModel->hasPaidAllFees($studentId, $student['academic_year']);
            
            // Debug logging
            error_log("processFeePayment - All fees paid: " . ($allFeesPaid ? 'YES' : 'NO'));
            
            $progressionResult = [
                'grade_promoted' => false,
                'term_advanced' => false,
                'new_grade' => $student['grade'],
                'new_term' => $student['current_term'],
                'message' => 'Fee payment recorded successfully'
            ];
            
            // Check if all fees are paid for current grade for promotion
            if ($allFeesPaid) {
                $nextGrade = $this->getNextGrade($student['grade']);
                
                // Debug logging
                error_log("processFeePayment - Next grade: " . ($nextGrade ? $nextGrade : 'NONE - Student graduated'));
                
                if ($nextGrade) {
                    // Promote to next grade
                    $updateStmt = $this->pdo->prepare(
                        "UPDATE students SET grade = ?, current_term = 1, academic_year = academic_year + 1 WHERE id = ?"
                    );
                    $updateStmt->execute([$nextGrade, $studentId]);
                    
                    // Create new fee records for next academic year
                    $studentFeeModel->createStudentFees($studentId, $student['academic_year'] + 1, 500.00);
                    
                    $progressionResult['grade_promoted'] = true;
                    $progressionResult['new_grade'] = $nextGrade;
                    $progressionResult['new_term'] = 1;
                    $progressionResult['message'] = "Student promoted to {$nextGrade}, Term 1!";
                } else {
                    // Student has graduated - no more grades available
                    $progressionResult['graduated'] = true;
                    $progressionResult['message'] = "Congratulations! Student has graduated from the school.";
                }
            } else {
                // Just advance term within current grade (only if current term < 3)
                if ($student['current_term'] < 3) {
                    $nextTerm = $student['current_term'] + 1;
                    $updateStmt = $this->pdo->prepare(
                        "UPDATE students SET current_term = ? WHERE id = ?"
                    );
                    $updateStmt->execute([$nextTerm, $studentId]);
                    
                    $progressionResult['term_advanced'] = true;
                    $progressionResult['new_term'] = $nextTerm;
                    $progressionResult['message'] = "Student advanced to Term {$nextTerm}";
                } else {
                    // If already at term 3 but not all fees paid, just record payment
                    $progressionResult['message'] = "Fee payment recorded successfully";
                }
            }
            
            $this->pdo->commit();
            return $progressionResult;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    /**
     * Get next grade in sequence (only if it exists in the system)
     */
    public function getNextGrade($currentGrade) {
        // Check what grades actually exist in the grades table
        $stmt = $this->pdo->prepare("SELECT name FROM grades ORDER BY name");
        $stmt->execute();
        $existingGrades = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // If no grades exist in system, use default sequence
        if (empty($existingGrades)) {
            $gradeSequence = [
                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 
                'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
                'Grade 11', 'Grade 12'
            ];
        } else {
            // Use existing grades as the sequence
            $gradeSequence = $existingGrades;
        }
        
        $currentIndex = array_search($currentGrade, $gradeSequence);
        if ($currentIndex !== false && $currentIndex < count($gradeSequence) - 1) {
            return $gradeSequence[$currentIndex + 1];
        }
        
        return null; // No next grade available (student has graduated)
    }
    
    /**
     * Check if student has graduated (no more grades to advance to)
     */
    public function hasGraduated($studentId) {
        $student = $this->findById($studentId);
        if (!$student) return false;
        
        $nextGrade = $this->getNextGrade($student['grade']);
        return $nextGrade === null;
    }
    
    /**
     * Get available grades in the system
     */
    public function getAvailableGrades() {
        $stmt = $this->pdo->prepare("SELECT DISTINCT grade FROM students WHERE grade IS NOT NULL ORDER BY grade");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get student's fee status
     */
    public function getFeeStatus($studentId, $academicYear = null) {
        if (!$academicYear) {
            $student = $this->findById($studentId);
            $academicYear = $student['academic_year'] ?? date('Y');
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT term, paid, payment_date, amount 
             FROM student_fees 
             WHERE student_id = ? AND academic_year = ? 
             ORDER BY term"
        );
        $stmt->execute([$studentId, $academicYear]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get students who need fee payment reminders
     */
    public function getStudentsNeedingPayment() {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, sf.term, sf.amount 
             FROM students s 
             JOIN student_fees sf ON s.id = sf.student_id 
             WHERE sf.paid = FALSE AND sf.academic_year = s.academic_year 
             ORDER BY s.grade, s.last_name, s.first_name"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>