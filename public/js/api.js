/**
 * API Client for Bright Future School Management System
 * Provides methods to interact with the backend API
 */

class SchoolAPI {
    constructor(baseURL = '/bright-future-school-php/api') {
        this.baseURL = baseURL;
    }

    /**
     * Generic method to make API requests
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP error! status: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error(`API request failed: ${url}`, error);
            throw error;
        }
    }

    // Authentication methods
    async login(email, password) {
        return this.request('/auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    }

    async logout() {
        return this.request('/auth.php?action=logout');
    }

    async checkAuth() {
        return this.request('/auth.php?action=check');
    }

    // Student methods
    async getStudents() {
        return this.request('/students.php?action=list');
    }

    async getStudent(id) {
        return this.request(`/students.php?action=get&id=${id}`);
    }

    async createStudent(studentData) {
        return this.request('/students.php?action=create', {
            method: 'POST',
            body: JSON.stringify(studentData)
        });
    }

    async updateStudent(id, studentData) {
        return this.request(`/students.php?action=update&id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(studentData)
        });
    }

    async deleteStudent(id) {
        return this.request(`/students.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
    }

    // Attendance methods
    async getAttendanceByStudent(studentId) {
        return this.request(`/attendance.php?action=by-student&id=${studentId}`);
    }

    async getAttendanceByDate(date) {
        return this.request(`/attendance.php?action=by-date&date=${date}`);
    }

    async markAttendance(attendanceData) {
        return this.request('/attendance.php?action=mark', {
            method: 'POST',
            body: JSON.stringify(attendanceData)
        });
    }

    async getAttendanceStats(studentId) {
        return this.request(`/attendance.php?action=stats&id=${studentId}`);
    }

    // Teacher methods
    async getTeachers() {
        return this.request('/teachers.php?action=list');
    }

    async getTeacher(id) {
        return this.request(`/teachers.php?action=get&id=${id}`);
    }

    async createTeacher(teacherData) {
        return this.request('/teachers.php?action=create', {
            method: 'POST',
            body: JSON.stringify(teacherData)
        });
    }

    async updateTeacher(id, teacherData) {
        return this.request(`/teachers.php?action=update&id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(teacherData)
        });
    }

    async deleteTeacher(id) {
        return this.request(`/teachers.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
    }

    // Parent methods
    async getParents() {
        return this.request('/parents.php?action=list');
    }

    async getParent(id) {
        return this.request(`/parents.php?action=get&id=${id}`);
    }

    async createParent(parentData) {
        return this.request('/parents.php?action=create', {
            method: 'POST',
            body: JSON.stringify(parentData)
        });
    }

    async updateParent(id, parentData) {
        return this.request(`/parents.php?action=update&id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(parentData)
        });
    }

    async deleteParent(id) {
        return this.request(`/parents.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
    }

    // Exam methods
    async getExams() {
        return this.request('/exams.php?action=list');
    }

    async getExam(id) {
        return this.request(`/exams.php?action=get&id=${id}`);
    }

    async createExam(examData) {
        return this.request('/exams.php?action=create', {
            method: 'POST',
            body: JSON.stringify(examData)
        });
    }

    async updateExam(id, examData) {
        return this.request(`/exams.php?action=update&id=${id}`, {
            method: 'PUT',
            body: JSON.stringify(examData)
        });
    }

    async deleteExam(id) {
        return this.request(`/exams.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
    }

    // Mark methods
    async getMarksByStudent(studentId) {
        return this.request(`/marks.php?action=by-student&id=${studentId}`);
    }

    async getMarksByExam(examId) {
        return this.request(`/marks.php?action=by-exam&id=${examId}`);
    }

    async recordMark(markData) {
        return this.request('/marks.php?action=record', {
            method: 'POST',
            body: JSON.stringify(markData)
        });
    }
}

// Create a global instance
const schoolAPI = new SchoolAPI();

// Make SchoolAPI available globally
window.SchoolAPI = SchoolAPI;
window.schoolAPI = schoolAPI;

// Export for module systems (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SchoolAPI;
}