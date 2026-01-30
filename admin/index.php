<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Exam.php';
require_once __DIR__ . '/../models/ParentModel.php';

$auth = new Auth();

// Check if user is logged in and is an admin
if (!$auth->isLoggedIn() || $auth->getCurrentUser()['role'] !== 'ADMIN') {
    header('Location: ../login.php');
    exit;
}

$user = $auth->getCurrentUser();

$studentModel = new Student();
$teacherModel = new Teacher();
$attendanceModel = new Attendance();
$examModel = new Exam();
$parentModel = new ParentModel();

// Get real stats
$studentCount = count($studentModel->findAll());
$teacherCount = count($teacherModel->findAll());
$parentCount = count($parentModel->findAll());
$examCount = $examModel->getTotalExamCount();

// Get real attendance rate
$attendanceStats = $attendanceModel->getCurrentMonthAttendanceRate();
$attendanceRate = $attendanceStats['attendance_rate'] ?? 0;

// Get real recent activities
$recentAttendanceActivities = $attendanceModel->getRecentActivities(5);
$recentActivities = [];

foreach ($recentAttendanceActivities as $activity) {
    $recentActivities[] = [
        'id' => $activity['id'],
        'action' => 'marked ' . $activity['status'] . ' for',
        'teacher' => ($activity['teacher_first_name'] ?? 'Unknown') . ' ' . ($activity['teacher_last_name'] ?? 'Teacher'),
        'student' => $activity['student_first_name'] . ' ' . $activity['student_last_name'],
        'date' => date('d/m/Y', strtotime($activity['date']))
    ];
}

// If no attendance activities, show recent exam activities
if (empty($recentActivities)) {
    $recentExams = $examModel->getRecentExams(3);
    foreach ($recentExams as $exam) {
        $recentActivities[] = [
            'id' => $exam['id'],
            'action' => 'created exam',
            'teacher' => 'Admin',
            'student' => $exam['title'] . ' for ' . $exam['grade_name'],
            'date' => date('d/m/Y', strtotime($exam['date']))
        ];
    }
}

// If still no activities, show system activities
if (empty($recentActivities)) {
    $recentActivities = [
        [
            'id' => 1,
            'action' => 'completed system initialization',
            'teacher' => 'System',
            'student' => 'Dashboard',
            'date' => date('d/m/Y')
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Admin Dashboard - Bright Future Primary School</title>
  <link rel="stylesheet" href="../public/css/styles.css">
  <style>
    .academic-card.orange {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      border: 1px solid rgba(245, 158, 11, 0.3);
    }
  </style>
</head>
<body>
  <?php $dashboard_type = 'admin'; include '../includes/mobile_nav.php'; ?>

  <!-- ADMIN DASHBOARD -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container mobile-layout" style="display: flex; gap: 2rem;">
      
      <!-- SIDEBAR -->
      <div class="desktop-sidebar" style="width: 250px; background: var(--color-surface); border-radius: 12px; padding: 1.5rem; height: fit-content; position: sticky; top: 6rem;">
        <h3 style="color: white; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border);">Admin Menu</h3>
        
        <nav style="display: flex; flex-direction: column; gap: 0.5rem;">
          <a href="index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-primary); background: rgba(96, 165, 250, 0.1); border-radius: 8px; text-decoration: none; font-weight: 500;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
          </a>
          
          <a href="students/student.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            Students
          </a>
          
          <a href="teachers/teacher.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>
            Teachers
          </a>
          
          <a href="parents/parent.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>
            Parents
          </a>
          
          <a href="grades/grades.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 3L2 12h3v8h14v-8h3L12 3zm0 13c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm-1-8v3h2V8h-2z"/>
            </svg>
            Grades
          </a>
          
          <a href="exams/exams.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
            </svg>
            Exams
          </a>
          
          <a href="attendance/attendance.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
            </svg>
            Attendance
          </a>

          <a href="events/events.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
            </svg>
            Events
          </a>
          
          
          <a href="applications/application.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            Applications
          </a>
          
          <a href="fees/fees.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: var(--color-text); border-radius: 8px; text-decoration: none; transition: all 0.2s;">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v8h-2v-6h-2v-2h2zm-2 10H7v-2h2v2zm8 0h-2v-2h2v2zm-2-4h-2v-2h2v2z"/>
            </svg>
            Fees
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
            <span class="badge-text">Administration</span>
          </div>
          <h1 class="section-title">
            Admin
            <span class="gradient">Dashboard</span>
          </h1>
          <p class="section-description">
            Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Administrator'); ?> <?php echo htmlspecialchars($user['last_name'] ?? ''); ?> • <?php echo date('l, F j, Y'); ?> • Comprehensive overview of school operations
          </p>
        </div>

      <!-- Stats Cards -->
      <div class="grid-4" style="margin-bottom: 2rem; gap: 1rem;">
        <div class="academic-card blue" style="padding: 1rem;">
          <div class="academic-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">
            👨‍🎓
          </div>
          <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Total Students</h4>
          <div class="stat-value" style="font-size: 2rem; margin: 0.5rem 0; color: white;"><?= $studentCount ?></div>
          <p style="font-size: 0.875rem; margin-top: 0.25rem;">Active enrollment</p>
        </div>

        <div class="academic-card purple" style="padding: 1rem;">
          <div class="academic-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">
            👩‍🏫
          </div>
          <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Total Teachers</h4>
          <div class="stat-value" style="font-size: 2rem; margin: 0.5rem 0; color: white;"><?= $teacherCount ?></div>
          <p style="font-size: 0.875rem; margin-top: 0.25rem;">Qualified staff</p>
        </div>

        <div class="academic-card green" style="padding: 1rem;">
          <div class="academic-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">
            ✅
          </div>
          <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Attendance Rate</h4>
          <div class="stat-value" style="font-size: 2rem; margin: 0.5rem 0; color: white;"><?= $attendanceRate ?>%</div>
          <p style="font-size: 0.875rem; margin-top: 0.25rem;">Current monthly average</p>
        </div>

        <div class="academic-card orange" style="padding: 1rem;">
          <div class="academic-icon" style="font-size: 2rem; margin-bottom: 0.5rem;">
            📚
          </div>
          <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">Active Parents</h4>
          <div class="stat-value" style="font-size: 2rem; margin: 0.5rem 0; color: white;"><?= $parentCount ?></div>
          <p style="font-size: 0.875rem; margin-top: 0.25rem;">Parent accounts</p>
        </div>
      </div>

      <div class="grid-2">
        <!-- Recent Activities -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Recent Classroom Activities</h3>
          <?php foreach ($recentActivities as $activity): ?>
          <div class="step-card" style="margin-bottom: 1rem;">
            <div class="step-number"><?= str_pad($activity['id'], 2, '0', STR_PAD_LEFT) ?></div>
            <div class="step-content">
              <h4><?= htmlspecialchars($activity['teacher']) ?> <?= htmlspecialchars($activity['action']) ?></h4>
              <p style="color: var(--color-muted); font-size: 0.875rem;"><?= htmlspecialchars($activity['student']) ?> - <?= htmlspecialchars($activity['date']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Administrative Functions -->
        <div>
          <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;">Quick Actions</h3>
            <div style="display: grid; gap: 1rem;">
              <a href="students/new.php" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none;">
                Add New Student
              </a>
              <a href="teachers/new.php" class="btn btn-secondary" style="text-align: center; display: block; text-decoration: none;">
                Add New Teacher
              </a>
              <a href="exams/new.php" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none; background: linear-gradient(to right, var(--color-warning), #f59e0b);">
                Create Exam
              </a>
              <a href="parents/new.php" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none;">
                Add New Parent
              </a>
            </div>
          </div>

          <div class="card">
            <h3 style="margin-bottom: 1.5rem;">System Overview</h3>
            <div class="requirement-list">
              <div class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">All systems operational</span>
              </div>
              <div class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Database backup completed</span>
              </div>
              <div class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Security protocols active</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </section>



  <script src="../public/js/main.js"></script>
  <script>
    // Admin dashboard specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      // Show welcome notification
      setTimeout(() => {
        const firstName = '<?php echo addslashes($user['first_name'] ?? 'Administrator'); ?>';
        showNotification(`Welcome back, ${firstName}! Dashboard showing real-time data.`, 'success');
      }, 1000);
      
      // Auto-refresh stats every 2 minutes
      setInterval(refreshDashboardData, 120000);
    });
    
    // Function to refresh dashboard data
    function refreshDashboardData() {
      fetch('../api/dashboard-stats.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update stats
            document.querySelectorAll('.stat-value')[0].textContent = data.students;
            document.querySelectorAll('.stat-value')[1].textContent = data.teachers;
            document.querySelectorAll('.stat-value')[2].textContent = data.attendance_rate + '%';
            document.querySelectorAll('.stat-value')[3].textContent = data.parents;
            
            // Show refresh notification
            showNotification('Dashboard data refreshed', 'info');
          }
        })
        .catch(error => {
          console.log('Auto-refresh failed:', error);
        });
    }
    
    // Show notification function
    function showNotification(message, type = 'info') {
      // Remove existing notifications
      const existing = document.getElementById('admin-notification');
      if (existing) existing.remove();
      
      // Create notification
      const notification = document.createElement('div');
      notification.id = 'admin-notification';
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        background-color: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
      `;
      notification.textContent = message;
      
      document.body.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      // Auto-remove
      setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.remove();
          }
        }, 300);
      }, 3000);
    }
  </script>
</body>
</html>


