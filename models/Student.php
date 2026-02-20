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
     // ... existing code ...
/**
 * Process fee payment and handle grade progression
 */
public function processFeePayment($studentId, $term) {
    try {
        // Get student details
        $student = $this->findById($studentId);
        if (!$student) {
            throw new Exception("Student not found");
        }
        
        $studentFeeModel = new StudentFee(); // No PDO arg — uses global $pdo via BaseModel
        
        $academicYear = $student['academic_year'] ?? date('Y');
        
        // Mark the specific term's fee as paid
        $studentFeeModel->markAsPaid($studentId, $term, $academicYear);
        
        // Advance the student's current term tracker
        $newTerm = min(($student['current_term'] ?? 1) + 1, 3);
        $updateTermStmt = $this->pdo->prepare("UPDATE students SET current_term = ? WHERE id = ?");
        $updateTermStmt->execute([$newTerm, $studentId]);
        
        // Check if all fees are paid for current academic year
        $allFeesPaid = $studentFeeModel->hasPaidAllFees($studentId, $academicYear);
        
        $progressionResult = [
            'grade_promoted' => false,
            'graduated'      => false,
            'term_advanced'  => true,
            'new_grade'      => $student['grade'],
            'new_term'       => $newTerm,
            'message'        => "Fee payment for Term {$term} recorded. Student is now on Term {$newTerm}."
        ];
        
        // If all fees paid, promote the student to the next grade
        if ($allFeesPaid) {
            $nextGrade = $this->getNextGrade($student['grade']);
            
            if ($nextGrade) {
                $newAcademicYear = $academicYear + 1;
                
                // Promote student first
                $updateStmt = $this->pdo->prepare(
                    "UPDATE students SET grade = ?, current_term = 1, academic_year = ? WHERE id = ?"
                );
                $updateStmt->execute([$nextGrade, $newAcademicYear, $studentId]);
                
                // Create fee records for the NEW grade in the NEW year
                $studentFeeModel->createStudentFees($studentId, $newAcademicYear, 500.00, $nextGrade);
                
                $progressionResult['grade_promoted'] = true;
                $progressionResult['new_grade']      = $nextGrade;
                $progressionResult['new_term']       = 1;
                $progressionResult['message']        = "All fees paid! Student has been promoted to {$nextGrade} (Year {$newAcademicYear})!";
            } else {
                // Student has completed the highest grade — graduated
                $progressionResult['graduated'] = true;
                $progressionResult['message']   = "Congratulations! Student has graduated from the school.";
            }
        }
        
        return $progressionResult;
        
    } catch (Exception $e) {
        throw new Exception("Failed to process fee payment: " . $e->getMessage());
    }
}

/**
 * Get student fee status
 */
public function getFeeStatus($studentId, $academicYear = null) {
    $studentFeeModel = new StudentFee(); // No-arg — uses global $pdo
    return $studentFeeModel->getStudentFeeStatus($studentId, $academicYear);
}
// ... existing code ...
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