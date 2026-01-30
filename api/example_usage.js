/*
 * Example JavaScript code for interacting with the School Management API
 * This demonstrates how to use the API endpoints from the frontend
 */

class SchoolManagementAPI {
    constructor(baseURL = '/api') {
        this.baseURL = baseURL;
    }

    // Authentication methods
    async login(email, password) {
        try {
            const response = await fetch(`${this.baseURL}/auth.php?action=login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    async logout() {
        try {
            const response = await fetch(`${this.baseURL}/auth.php?action=logout`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Logout error:', error);
            throw error;
        }
    }

    async checkAuth() {
        try {
            const response = await fetch(`${this.baseURL}/auth.php?action=check`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Auth check error:', error);
            throw error;
        }
    }

    // Student methods
    async getStudents() {
        try {
            const response = await fetch(`${this.baseURL}/students.php?action=list`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Get students error:', error);
            throw error;
        }
    }

    async getStudent(id) {
        try {
            const response = await fetch(`${this.baseURL}/students.php?action=get&id=${id}`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Get student error:', error);
            throw error;
        }
    }

    async createStudent(studentData) {
        try {
            const response = await fetch(`${this.baseURL}/students.php?action=create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(studentData)
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Create student error:', error);
            throw error;
        }
    }

    async updateStudent(id, studentData) {
        try {
            const response = await fetch(`${this.baseURL}/students.php?action=update&id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(studentData)
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Update student error:', error);
            throw error;
        }
    }

    async deleteStudent(id) {
        try {
            const response = await fetch(`${this.baseURL}/students.php?action=delete&id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Delete student error:', error);
            throw error;
        }
    }

    // Attendance methods
    async getAttendanceByStudent(studentId) {
        try {
            const response = await fetch(`${this.baseURL}/attendance.php?action=by-student&id=${studentId}`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Get attendance by student error:', error);
            throw error;
        }
    }

    async markAttendance(attendanceData) {
        try {
            const response = await fetch(`${this.baseURL}/attendance.php?action=mark`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(attendanceData)
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Mark attendance error:', error);
            throw error;
        }
    }

    async getAttendanceStats(studentId) {
        try {
            const response = await fetch(`${this.baseURL}/attendance.php?action=stats&id=${studentId}`, {
                method: 'GET'
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Get attendance stats error:', error);
            throw error;
        }
    }
}

// Example usage:
/*
const api = new SchoolManagementAPI();

// Example: Login
api.login('admin@school.edu', 'admin123')
    .then(response => {
        if (response.success) {
            console.log('Login successful', response.user);
            
            // Example: Get all students
            return api.getStudents();
        } else {
            console.log('Login failed', response.error);
        }
    })
    .then(studentsData => {
        if (studentsData.success) {
            console.log('Students:', studentsData.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
*/