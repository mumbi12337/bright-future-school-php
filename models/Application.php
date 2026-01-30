<?php
require_once 'BaseModel.php';

class Application extends BaseModel {
    protected $table = 'applications';
    
    /**
     * Get applications by status
     */
    public function getByStatus($status) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE status = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all applications with counts by status
     */
    public function getApplicationsWithStatusCounts() {
        $stmt = $this->pdo->prepare("
            SELECT *,
                   (SELECT COUNT(*) FROM {$this->table} WHERE status = 'PENDING') as pending_count,
                   (SELECT COUNT(*) FROM {$this->table} WHERE status = 'APPROVED') as approved_count,
                   (SELECT COUNT(*) FROM {$this->table} WHERE status = 'REJECTED') as rejected_count
            FROM {$this->table}
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update application status
     */
    public function updateStatus($applicationId, $status) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($applicationId, $data);
    }
    
    /**
     * Create a new application
     */
    public function createApplication($studentFirstName, $studentLastName, $studentDateOfBirth, 
                                    $studentGrade, $parentFirstName, $parentLastName, $parentEmail, 
                                    $parentPhone, $parentAddress = null, $emergencyContactName = null, 
                                    $emergencyContactPhone = null, $previousSchool = null, 
                                    $medicalConditions = null, $additionalNotes = null) {
        $data = [
            'student_first_name' => $studentFirstName,
            'student_last_name' => $studentLastName,
            'student_date_of_birth' => $studentDateOfBirth,
            'student_grade' => $studentGrade,
            'parent_first_name' => $parentFirstName,
            'parent_last_name' => $parentLastName,
            'parent_email' => $parentEmail,
            'parent_phone' => $parentPhone,
            'parent_address' => $parentAddress,
            'emergency_contact_name' => $emergencyContactName,
            'emergency_contact_phone' => $emergencyContactPhone,
            'previous_school' => $previousSchool,
            'medical_conditions' => $medicalConditions,
            'additional_notes' => $additionalNotes
        ];
        
        return $this->create($data);
    }
}
?>