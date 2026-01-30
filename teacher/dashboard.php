<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/Teacher.php';
require_once '../models/Student.php';
require_once '../models/Attendance.php';
require_once '../models/Grade.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isTeacher()) {
    header('Location: ../login.php');
    exit;
}

$teacherModel = new Teacher($pdo);
$studentModel = new Student($pdo);
$attendanceModel = new Attendance($pdo);
$gradeModel = new Grade($pdo);

// Get teacher information
$currentUser = $auth->getCurrentUser();
$teacher = $teacherModel->findByEmail($currentUser['email']);

// If no teacher record exists, create one
if (!$teacher) {
    $userModel = new User($pdo);
    $user = $userModel->findById($currentUser['id']);
    
    if ($user) {
        $teacherData = [
            'first_name' => $user['first_name'] ?? 'Teacher',
            'last_name' => $user['last_name'] ?? 'User',
            'email' => $user['email'],
            'phone' => '',
            'subjects' => 'General',
            'grade' => 'Grade 1'
        ];
        
        $teacherId = $teacherModel->create($teacherData);
        if ($teacherId) {
            $teacher = $teacherModel->findById($teacherId);
        }
    }
}

// Get students in teacher's assigned class/grade
$studentsInClass = [];
$assignedGrade = '';
if ($teacher) {
    $assignedGrade = $teacher['grade'] ?? 'N/A';
    $studentsInClass = $studentModel->getByGradeLevel($assignedGrade);
}

// Get today's attendance status
$todayAttendance = $attendanceModel->getTodayAttendanceByGrade($assignedGrade);
$attendanceStatus = 'Pending';
if ($todayAttendance && count($todayAttendance) > 0) {
    $presentCount = 0;
    foreach ($todayAttendance as $record) {
        if ($record['status'] === 'Present' || strtolower($record['status']) === 'present') {
            $presentCount++;
        }
    }
    $attendanceStatus = $presentCount . '/' . count($todayAttendance);
}

// Get current date for display
$currentDate = date('l, j F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Dashboard - Bright Future School</title>
  <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
  <?php $dashboard_type = 'teacher'; include '../includes/mobile_nav.php'; ?>

  <!-- TEACHER DASHBOARD -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container mobile-layout" style="display: flex; gap: 2rem;">
      
      <!-- SIDEBAR -->
      <div class="desktop-sidebar" style="width: 250px; background: var(--color-surface); border-radius: 12px; padding: 1.5rem; height: fit-content; position: sticky; top: 6rem;">
        <h3 style="color: white; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border);">Teacher Menu</h3>
        
        <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
          <a href="dashboard.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-primary); background: rgba(96, 165, 250, 0.1); border-radius: 8px; text-decoration: none; font-weight: 500;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
          </a>
          
          <a href="attendance.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
            </svg>
            Attendance
          </a>
          
          <a href="grades.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 13c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-1-8v3h2V8h-2z"/>
            </svg>
            Grades
          </a>
          
          <a href="marks/marks.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
            </svg>
            Marks
          </a>
          
          <a href="events.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
            </svg>
            Events
          </a>
          
          <a href="profile.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            My Profile
          </a>
          
          <a href="../logout.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #ef4444; border-radius: 8px; text-decoration: none; transition: all 0.2s; margin-top: 1rem; border-top: 1px solid var(--color-border);">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
            </svg>
            Logout
          </a>
        </nav>
      </div>
      
      <!-- MAIN CONTENT -->
      <div class="main-content-mobile" style="flex: 1;">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          Teacher
          <span class="gradient">Dashboard</span>
        </h1>
        <p class="section-description">
          Welcome back, <strong style="color: var(--color-primary); font-weight: 600;"><?php echo htmlspecialchars($teacher ? $teacher['first_name'] . ' ' . $teacher['last_name'] : 'Teacher'); ?></strong> • <?php echo date('l, F j, Y'); ?> • Assigned Class: <?php echo htmlspecialchars($assignedGrade); ?>
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid-3" style="margin-bottom: 3rem;">
        <div class="academic-card blue">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            👨‍🎓
          </div>
          <h4>My Students</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;"><?php echo count($studentsInClass); ?></div>
          <p>Students in your class</p>
        </div>

        <div class="academic-card purple">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            🏫
          </div>
          <h4>My Class</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;"><?php echo htmlspecialchars($assignedGrade); ?></div>
          <p>Your assigned grade</p>
        </div>

        <div class="academic-card green">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            ✅
          </div>
          <h4>Attendance Today</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;"><?php echo $attendanceStatus; ?></div>
          <p><?php echo count($todayAttendance) > 0 ? 'Attendance submitted' : 'Attendance not submitted'; ?></p>
        </div>
      </div>

      <div class="grid-2">
        <!-- Classroom Management -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Classroom Management</h3>
          <div style="display: grid; gap: 1rem;">
            <a href="attendance.php" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none;">
              Mark Attendance
            </a>
            <a href="grades.php" class="btn btn-secondary" style="text-align: center; display: block; text-decoration: none;">
              Enter Marks
            </a>
            <a href="events.php" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none; background: linear-gradient(to right, var(--color-warning), #f59e0b);">
              View Events
            </a>
          </div>
        </div>

        <!-- Profile Section -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Profile</h3>
          <a href="profile.php" class="btn btn-secondary" style="text-align: center; display: block; text-decoration: none;">
            View My Profile
          </a>
        </div>
      </div>
    </div>
  </section>



  <script src="../public/js/main.js"></script>
  <script>
    // Teacher dashboard specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      // Show welcome notification
      setTimeout(() => {
        showNotification('Welcome back, Teacher!', 'success');
      }, 1000);
    });
  </script>
</body>
</html>
