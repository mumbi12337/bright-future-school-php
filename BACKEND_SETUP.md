# Backend Setup for Bright Future School Management System

## Overview
This document outlines the backend implementation for the school management system using PHP and PostgreSQL.

## Database Schema
The database schema has been created based on the Prisma schema provided, with the following tables:

- **users**: Stores user accounts with roles (ADMIN, TEACHER, PARENT)
- **parents**: Stores parent information
- **students**: Stores student information with relationships to parents
- **teachers**: Stores teacher information
- **attendance**: Tracks student attendance records
- **grades**: Defines grade levels
- **fees**: Stores fee information per grade and term
- **events**: Stores school events
- **exams**: Stores exam information
- **marks**: Stores student marks for exams
- **applications**: Stores student admission applications

## File Structure
```
includes/
├── db.php          # Database configuration and connection
├── Auth.php        # Authentication system
├── helpers.php     # Utility functions
├── template.php    # HTML templates (existing)

models/
├── BaseModel.php   # Base model with CRUD operations
├── User.php        # User model
├── Parent.php      # Parent model
├── Student.php     # Student model
├── Teacher.php     # Teacher model
├── Attendance.php  # Attendance model
├── Grade.php       # Grade model
├── Fee.php         # Fee model
├── Event.php       # Event model
├── Exam.php        # Exam model
├── Mark.php        # Mark model
└── Application.php # Application model

database/
├── schema.sql      # PostgreSQL schema definition
└── migrate.php     # Migration script

api/
├── auth.php        # Authentication API endpoints
├── students.php    # Student management API
├── attendance.php  # Attendance management API
├── test.php        # API test endpoint
└── index.php       # API documentation endpoint
```

## Setup Instructions

1. Make sure your PostgreSQL server is running
2. Update the database credentials in `includes/db.php` if needed:
   - DB_HOST: Usually 'localhost' or '127.0.0.1'
   - DB_PORT: Usually '5432'
   - DB_NAME: Your database name
   - DB_USER: Your PostgreSQL username
   - DB_PASS: Your PostgreSQL password

3. Run the migration to create tables:
   ```bash
   php database/migrate.php
   ```

4. The migration will create a default admin user:
   - Email: admin@school.edu
   - Password: admin123

## API Endpoints

### Authentication
- `POST /api/auth.php?action=login` - Login user
- `GET /api/auth.php?action=logout` - Logout user
- `GET /api/auth.php?action=check` - Check authentication status

### Students
- `GET /api/students.php?action=list` - Get all students
- `GET /api/students.php?action=get&id={id}` - Get specific student
- `POST /api/students.php?action=create` - Create new student
- `PUT /api/students.php?action=update&id={id}` - Update student
- `DELETE /api/students.php?action=delete&id={id}` - Delete student

### Attendance
- `GET /api/attendance.php?action=list` - Get all attendance records
- `GET /api/attendance.php?action=by-student&id={id}` - Get attendance for student
- `GET /api/attendance.php?action=by-date&date={date}` - Get attendance for date
- `POST /api/attendance.php?action=mark` - Mark attendance
- `GET /api/attendance.php?action=stats&id={id}` - Get attendance stats

## Security Features
- Password hashing using PHP's password_hash()
- Session-based authentication
- Input sanitization
- Prepared statements to prevent SQL injection
- Role-based access control

## Models
All models extend the BaseModel class which provides:
- CRUD operations (create, read, update, delete)
- Flexible querying methods
- Consistent interface across all entities