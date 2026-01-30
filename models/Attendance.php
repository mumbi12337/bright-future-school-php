<?php
require_once 'BaseModel.php';

class Attendance extends BaseModel {
    protected $table = 'attendance';
    
    /**
     * Get attendance for a specific student
     */
    public function getByStudentId($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, s.first_name, s.last_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
            FROM {$this->table} a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get attendance for a specific date
     */
    public function getByDate($date) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, s.first_name, s.last_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
            FROM {$this->table} a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE DATE(a.date) = ?
            ORDER BY s.last_name, s.first_name
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get attendance for a student on a specific date
     */
    public function getByStudentIdAndDate($studentId, $date) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, s.first_name, s.last_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
            FROM {$this->table} a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE a.student_id = ? AND DATE(a.date) = ?
        ");
        $stmt->execute([$studentId, $date]);
        return $stmt->fetch();
    }
    
    /**
     * Get recent attendance for a specific student
     */
    public function getRecentByStudentId($studentId, $limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, 
                   CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                   s.grade AS student_grade,
                   CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
            FROM {$this->table} a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC
            LIMIT ?"
        );
        $stmt->execute([$studentId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark attendance for a student
     */
    public function markAttendance($studentId, $status, $date = null, $teacherId = null) {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }
        
        $data = [
            'student_id' => $studentId,
            'status' => $status,
            'date' => $date,
            'teacher_id' => $teacherId
        ];
        
        // Check if attendance already exists for this student on this date
        $existing = $this->getByStudentIdAndDate($studentId, date('Y-m-d', strtotime($date)));
        
        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], $data);
        } else {
            // Create new record
            return $this->create($data);
        }
    }
    
    /**
     * Get attendance statistics for a student
     */
    public function getAttendanceStats($studentId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
            FROM {$this->table} 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetch();
    }
    
    /**
     * Get today's attendance records by grade
     */
    public function getTodayAttendanceByGrade($gradeLevel) {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, s.first_name, s.last_name, s.grade 
            FROM {$this->table} a 
            JOIN students s ON a.student_id = s.id 
            WHERE DATE(a.date) = CURRENT_DATE AND s.grade = ? 
            ORDER BY s.last_name, s.first_name"
        );
        $stmt->execute([$gradeLevel]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get attendance records by grade level
     */
    public function getByGradeLevel($gradeLevel) {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, s.first_name, s.last_name, s.grade 
            FROM {$this->table} a 
            JOIN students s ON a.student_id = s.id 
            WHERE s.grade = ? 
            ORDER BY a.date DESC"
        );
        $stmt->execute([$gradeLevel]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get overall attendance rate for current month
     */
    public function getCurrentMonthAttendanceRate() {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                ROUND((SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 1) as attendance_rate
            FROM {$this->table} 
            WHERE DATE_TRUNC('month', date) = DATE_TRUNC('month', CURRENT_DATE)"
        );
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Get recent attendance activities for admin dashboard
     */
    public function getRecentActivities($limit = 10) {
        $stmt = $this->pdo->prepare(
            "SELECT 
                a.id,
                a.status,
                a.date,
                s.first_name as student_first_name,
                s.last_name as student_last_name,
                t.first_name as teacher_first_name,
                t.last_name as teacher_last_name
            FROM {$this->table} a
            JOIN students s ON a.student_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            ORDER BY a.date DESC
            LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>